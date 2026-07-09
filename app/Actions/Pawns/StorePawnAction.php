<?php

namespace App\Actions\Pawns;

use App\Http\Requests\Pawns\StorePawnRequest;
use App\Models\Customer;
use App\Models\Department;
use App\Models\Office;
use App\Models\Pawn;
use App\Models\Product;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
class StorePawnAction
{
    public function __invoke(StorePawnRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $officeId = session('office_id') ?: auth()->user()?->office_id;
        $companyId = session('company_id') ?: auth()->user()?->company_id;

        abort_unless($officeId, 404, 'No hay una sucursal activa.');
        abort_unless($companyId, 404, 'No hay una empresa activa.');

        $pawn = DB::transaction(function () use ($validated, $officeId, $companyId) {
            $office = Office::query()
                ->whereKey($officeId)
                ->lockForUpdate()
                ->firstOrFail();

            $customer = Customer::query()->findOrFail($validated['customer_id']);
            $department = Department::query()->findOrFail($validated['department_id']);

            $items = collect($validated['items']);

            $total = round((float) $items->sum('value'), 2);

            abort_if($total <= 0, 422, 'El total del empeño debe ser mayor a cero.');

            $nextFolio = ((int) Pawn::query()
                ->where('office_id', $office->id)
                ->max('folio')) + 1;

            $term = max((int) $department->term, 1);
            $auctionDays = max((int) $department->auction, 1);

            $today = now();
            $expirationDate = Carbon::parse($today->toDateString())->addDays($term);
            $auctionDate = $expirationDate->copy()->addDays($auctionDays);

            $pawn = Pawn::query()->create([
                'folio' => $nextFolio,
                'customer_id' => $customer->id,
                'company_id' => $companyId,
                'office_id' => $office->id,
                'created_by' => auth()->id(),

                'beneficiary' => $validated['beneficiary'] ?: $customer->name,
                'bag' => $validated['bag'] ?? null,
                'comments' => $validated['comments'] ?? null,
                'photos' => null,

                'total' => $total,
                'estimated_value' => $total,
                'loan_value' => $total,

                'loan_rate' => $department->loan_rate,
                'monthly_interest_rate' => $department->monthly_interest_rate,
                'daily_interest_rate' => $department->daily_interest_rate,
                'iva_rate' => $department->iva_rate,

                'term' => $term,
                'auction' => $auctionDays,

                'date_expiration' => $expirationDate->toDateString(),
                'date_auction' => $auctionDate->toDateString(),
                'date_settlement' => null,

                'inapam_rate' => $customer->inapam_code
                    ? (float) config('core.inapam_rate', 0.10)
                    : null,
            ]);

            $storedPhotos = $this->storePhotos($pawn, $validated['photos'] ?? []);

            if (count($storedPhotos) > 0) {
                $pawn->forceFill([
                    'photos' => json_encode($storedPhotos),
                ])->save();
            }

            foreach ($items as $item) {
                $product = Product::query()->findOrFail((int) $item['product_id']);

                $pawn->items()->create([
                    'product_id' => $product->id,
                    'quantity' => (float) $item['quantity'],
                    'description' => trim((string) $item['description']),
                    'value' => round((float) $item['value'], 2),
                ]);
            }

            $newCash = round((float) $office->cash - $total, 2);

            Transaction::query()->create([
                'office_id' => $office->id,
                'user_id' => auth()->id(),
                'pawn_id' => $pawn->id,
                'reference_id' => null,
                'type' => Transaction::TYPE_PAWN,
                'comments' => 'Empeño registrado. Folio ' . $pawn->formatted_folio,
                'data' => [
                    'source' => 'pawns.create',
                    'customer_id' => $customer->id,
                    'department_id' => $department->id,
                    'folio' => $pawn->formatted_folio,
                ],
                'amount' => -abs($total),
                'balance' => $newCash,
                'discount_amount' => 0,
                'discount_rate' => 0,
                'payment_type' => Transaction::PAYMENT_CASH,
            ]);

            $office->forceFill([
                'cash' => $newCash,
            ])->save();

            return $pawn;
        });

        return redirect()
            ->route('pawns.show', $pawn->id)
            ->with('success', 'Empeño registrado correctamente.');
    }

    private function storePhotos(Pawn $pawn, array $photos): array
    {
        $stored = [];

        foreach ($photos as $index => $photo) {
            $dataUrl = (string) ($photo['data_url'] ?? '');

            if (! preg_match('/^data:image\/(jpeg|jpg|png|webp);base64,/', $dataUrl, $matches)) {
                continue;
            }

            $extension = strtolower($matches[1]) === 'jpeg' ? 'jpg' : strtolower($matches[1]);
            $base64 = preg_replace('/^data:image\/(jpeg|jpg|png|webp);base64,/', '', $dataUrl);
            $binary = base64_decode($base64, true);

            if ($binary === false) {
                continue;
            }

            $filename = 'pawn-' . $pawn->id . '-' . ($index + 1) . '-' . Str::random(10) . '.' . $extension;
            $path = 'pawns/' . $pawn->id . '/photos/' . $filename;

            Storage::disk('public')->put($path, $binary);

            $stored[] = '/storage/' . $path;
        }

        return $stored;
    }
}