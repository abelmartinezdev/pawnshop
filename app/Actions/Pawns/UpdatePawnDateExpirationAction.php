<?php

namespace App\Actions\Pawns;

use App\Http\Requests\Pawns\UpdatePawnDateExpirationRequest;
use App\Models\Office;
use App\Models\Pawn;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class UpdatePawnDateExpirationAction
{
    public function __invoke(UpdatePawnDateExpirationRequest $request, Pawn $pawn): RedirectResponse
    {
        $validated = $request->validated();

        $officeId = session('office_id') ?: auth()->user()?->office_id;
        $companyId = session('company_id') ?: auth()->user()?->company_id;

        abort_unless($officeId, 404, 'No hay una sucursal activa.');
        abort_unless($companyId, 404, 'No hay una empresa activa.');

        DB::transaction(function () use ($validated, $pawn, $officeId, $companyId) {
            $pawn = Pawn::query()
                ->whereKey($pawn->id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_if((int) $pawn->office_id !== (int) $officeId, 403, 'El empeño no pertenece a la sucursal activa.');
            abort_if((int) $pawn->company_id !== (int) $companyId, 403, 'El empeño no pertenece a la empresa activa.');
            abort_if($pawn->isCanceled(), 422, 'Este empeño está cancelado.');
            abort_if($pawn->isPaid(), 422, 'Este empeño ya fue liquidado.');

            $office = Office::query()
                ->whereKey($officeId)
                ->lockForUpdate()
                ->firstOrFail();

            $oldExpiration = $pawn->date_expiration?->format('Y-m-d');
            $oldAuction = $pawn->date_auction?->format('Y-m-d');

            $newExpiration = Carbon::parse($validated['date_expiration'])->toDateString();
            $newAuction = Carbon::parse($validated['date_auction'])->toDateString();

            abort_if(
                Carbon::parse($newAuction)->lt(Carbon::parse($newExpiration)),
                422,
                'La fecha de remate no puede ser menor que la fecha de expiración.'
            );

            $pawn->forceFill([
                'date_expiration' => $newExpiration,
                'date_auction' => $newAuction,
            ])->save();

            Transaction::query()->create([
                'office_id' => $office->id,
                'user_id' => auth()->id(),
                'pawn_id' => $pawn->id,
                'reference_id' => null,
                'type' => 'expiration_date_change',
                'comments' => $validated['comments']
                    ?: 'Cambio de fecha de espera del empeño ' . $pawn->formatted_folio,
                'data' => [
                    'source' => 'pawns.date-expiration',
                    'old' => [
                        'date_expiration' => $oldExpiration,
                        'date_auction' => $oldAuction,
                    ],
                    'new' => [
                        'date_expiration' => $newExpiration,
                        'date_auction' => $newAuction,
                    ],
                ],
                'amount' => 0,
                'balance' => (float) $office->cash,
                'discount_amount' => 0,
                'discount_rate' => 0,
                'payment_type' => Transaction::PAYMENT_CASH,
            ]);
        });

        return redirect()
            ->route('pawns.show', $pawn->id)
            ->with('success', 'Fecha de espera actualizada correctamente.');
    }
}