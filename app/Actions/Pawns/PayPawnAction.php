<?php

namespace App\Actions\Pawns;

use App\Http\Requests\Pawns\PayPawnRequest;
use App\Models\Office;
use App\Models\Pawn;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class PayPawnAction
{
    public function __invoke(PayPawnRequest $request, Pawn $pawn): RedirectResponse
    {
        $validated = $request->validated();

        $officeId = session('office_id') ?: auth()->user()?->office_id;
        $companyId = session('company_id') ?: auth()->user()?->company_id;

        abort_unless($officeId, 404, 'No hay una sucursal activa.');
        abort_unless($companyId, 404, 'No hay una empresa activa.');

        $result = DB::transaction(function () use ($validated, $pawn, $officeId, $companyId) {
            $pawn = Pawn::query()
                ->with(['items.product', 'office', 'customer'])
                ->whereKey($pawn->id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_if((int) $pawn->office_id !== (int) $officeId, 403, 'El empeño no pertenece a la sucursal activa.');
            abort_if((int) $pawn->company_id !== (int) $companyId, 403, 'El empeño no pertenece a la empresa activa.');
            abort_if($pawn->isCanceled(), 422, 'Este empeño está cancelado.');
            abort_if($pawn->isPaid(), 422, 'Este empeño ya fue liquidado.');
            abort_if($pawn->hasCountersign(), 422, 'Este empeño ya fue refrendado.');

            $office = Office::query()
                ->whereKey($officeId)
                ->lockForUpdate()
                ->firstOrFail();

            $calculation = $this->calculateAmountDue($pawn, $validated);

            $amountDue = $calculation['amount_due'];
            $amountPaid = round((float) $validated['amount_paid'], 2);
            $change = round(max($amountPaid - $amountDue, 0), 2);

            abort_if($amountPaid < $amountDue, 422, 'El pago recibido no cubre el total de la operación.');

            $balance = (float) $office->cash;

            if ($validated['payment_type'] === Transaction::PAYMENT_CASH) {
                $balance = round($balance + $amountDue, 2);

                $office->forceFill([
                    'cash' => $balance,
                ])->save();
            }

            $newPawn = null;

            if ($validated['transaction'] === 'liquidation') {
                $this->markPawnAsPaid($pawn);
            }

            if ($validated['transaction'] === 'countersign') {
                $newPawn = $this->createCountersignPawn($pawn, (float) $calculation['pay_extra']);
            }

            $transaction = Transaction::query()->create([
                'office_id' => $office->id,
                'user_id' => auth()->id(),
                'pawn_id' => $pawn->id,
                'reference_id' => $newPawn?->id,
                'type' => $this->transactionType($validated['transaction']),
                'comments' => $this->transactionComments($validated['transaction'], $pawn, $newPawn),
                'data' => [
                    'source' => 'pawns.pay',
                    'operation' => $validated['transaction'],
                    'amount_due' => $amountDue,
                    'amount_paid' => $amountPaid,
                    'change' => $change,
                    'interest' => $calculation['interest'],
                    'discount_rate' => $calculation['discount_rate'],
                    'discount_amount' => $calculation['discount_amount'],
                    'pay_extra' => $calculation['pay_extra'],
                    'payment_to_interest' => $calculation['payment_to_interest'],
                    'new_pawn_id' => $newPawn?->id,
                    'new_pawn_folio' => $newPawn?->formatted_folio,
                ],
                'amount' => $amountDue,
                'balance' => $balance,
                'discount_amount' => $calculation['discount_amount'],
                'discount_rate' => $calculation['discount_rate'],
                'payment_type' => $validated['payment_type'],
            ]);

            return [
                'pawn_id' => $newPawn?->id ?: $pawn->id,
                'transaction_id' => $transaction->id,
                'operation' => $validated['transaction'],
            ];
        });

        $message = match ($result['operation']) {
            'liquidation' => 'Empeño liquidado correctamente.',
            'countersign' => 'Refrendo registrado correctamente.',
            'interest_payment' => 'Abono a interés registrado correctamente.',
            default => 'Pago registrado correctamente.',
        };

        return redirect()
            ->route('pawns.show', $result['pawn_id'])
            ->with('success', $message);
    }

    private function calculateAmountDue(Pawn $pawn, array $validated): array
    {
        $transaction = $validated['transaction'];
        $interest = $this->interestToPay($pawn);

        $discountRate = 0.0;
        $discountAmount = 0.0;
        $payExtra = 0.0;
        $paymentToInterest = 0.0;

        if ($transaction === 'liquidation') {
            $discountRate = round((float) ($validated['discount'] ?? 0), 2);

            if ($discountRate < 0 || $discountRate > 100) {
                $discountRate = 0;
            }

            $discountAmount = round($interest * ($discountRate / 100), 2);
            $interestAfterDiscount = round(max($interest - $discountAmount, 0), 2);

            return [
                'amount_due' => round((float) $pawn->total + $interestAfterDiscount, 2),
                'interest' => $interest,
                'discount_rate' => $discountRate,
                'discount_amount' => $discountAmount,
                'pay_extra' => 0.0,
                'payment_to_interest' => 0.0,
            ];
        }

        if ($transaction === 'countersign') {
            $payExtra = round((float) ($validated['pay_extra'] ?? 0), 2);

            abort_if($payExtra < 0, 422, 'El abono a capital no puede ser negativo.');
            abort_if($payExtra >= (float) $pawn->total, 422, 'Si el abono cubre todo el capital, usa la opción Desempeñar.');

            return [
                'amount_due' => round($interest + $payExtra, 2),
                'interest' => $interest,
                'discount_rate' => 0.0,
                'discount_amount' => 0.0,
                'pay_extra' => $payExtra,
                'payment_to_interest' => 0.0,
            ];
        }

        if ($transaction === 'interest_payment') {
            $paymentToInterest = round((float) ($validated['payment'] ?? 0), 2);

            abort_if($paymentToInterest <= 0, 422, 'La cantidad de abono es incorrecta.');
            abort_if($paymentToInterest >= $interest, 422, 'El abono a interés debe ser menor al interés total. Para pagar todo, usa Desempeñar o Refrendo.');

            return [
                'amount_due' => $paymentToInterest,
                'interest' => $interest,
                'discount_rate' => 0.0,
                'discount_amount' => 0.0,
                'pay_extra' => 0.0,
                'payment_to_interest' => $paymentToInterest,
            ];
        }

        abort(422, 'La transacción seleccionada no es válida.');
    }

    private function createCountersignPawn(Pawn $pawn, float $payExtra): Pawn
    {
        $newTotal = round((float) $pawn->total - $payExtra, 2);

        abort_if($newTotal <= 0, 422, 'El nuevo capital del refrendo debe ser mayor a cero.');

        $nextFolio = ((int) Pawn::query()
            ->where('office_id', $pawn->office_id)
            ->max('folio')) + 1;

        $term = max((int) $pawn->term, 1);
        $auctionDays = max((int) $pawn->auction, 1);

        $expirationDate = Carbon::parse(now()->toDateString())->addDays($term);
        $auctionDate = $expirationDate->copy()->addDays($auctionDays);

        $newPawn = Pawn::query()->create([
            'folio' => $nextFolio,
            'customer_id' => $pawn->customer_id,
            'company_id' => $pawn->company_id,
            'office_id' => $pawn->office_id,
            'created_by' => auth()->id(),
            'previous_pawn' => $pawn->id,

            'beneficiary' => $pawn->beneficiary,
            'bag' => $pawn->bag,
            'comments' => $pawn->comments,
            'photos' => $pawn->photos,

            'total' => $newTotal,
            'estimated_value' => $pawn->estimated_value,
            'loan_value' => $newTotal,

            'loan_rate' => $pawn->loan_rate,
            'monthly_interest_rate' => $pawn->monthly_interest_rate,
            'daily_interest_rate' => $pawn->daily_interest_rate,
            'iva_rate' => $pawn->iva_rate,
            'inapam_rate' => $pawn->inapam_rate,

            'term' => $term,
            'auction' => $auctionDays,
            'pay_extra' => $payExtra,

            'date_expiration' => $expirationDate->toDateString(),
            'date_auction' => $auctionDate->toDateString(),
            'date_settlement' => null,
        ]);

        $this->copyPawnItems($pawn, $newPawn, $newTotal);

        return $newPawn;
    }

    private function copyPawnItems(Pawn $oldPawn, Pawn $newPawn, float $newTotal): void
    {
        $items = $oldPawn->items;
        $itemsTotal = round((float) $items->sum('value'), 2);

        if ($items->isEmpty()) {
            return;
        }

        if ($itemsTotal <= 0) {
            $itemsTotal = (float) $oldPawn->total;
        }

        $remaining = $newTotal;
        $lastIndex = $items->count() - 1;

        foreach ($items as $index => $item) {
            if ($index === $lastIndex) {
                $value = round(max($remaining, 0), 2);
            } else {
                $ratio = $itemsTotal > 0 ? ((float) $item->value / $itemsTotal) : 0;
                $value = round($newTotal * $ratio, 2);
                $remaining = round($remaining - $value, 2);
            }

            $newPawn->items()->create([
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'description' => $item->description,
                'value' => $value,
            ]);
        }
    }

    private function markPawnAsPaid(Pawn $pawn): void
    {
        $data = [
            'paid_at' => now(),
            'date_settlement' => now()->toDateString(),
        ];

        if (Schema::hasColumn('pawns', 'paid_by')) {
            $data['paid_by'] = auth()->id();
        }

        $pawn->forceFill($data)->save();
    }

    private function transactionType(string $transaction): string
    {
        return match ($transaction) {
            'liquidation' => Transaction::TYPE_LIQUIDATION,
            'countersign' => Transaction::TYPE_COUNTERSIGN,
            'interest_payment' => 'interest_payment',
            default => Transaction::TYPE_PAYMENT,
        };
    }

    private function transactionComments(string $transaction, Pawn $pawn, ?Pawn $newPawn): string
    {
        return match ($transaction) {
            'liquidation' => 'Liquidación del empeño ' . $pawn->formatted_folio,
            'countersign' => 'Refrendo del empeño ' . $pawn->formatted_folio . ($newPawn ? ' al folio ' . $newPawn->formatted_folio : ''),
            'interest_payment' => 'Abono a interés del empeño ' . $pawn->formatted_folio,
            default => 'Pago del empeño ' . $pawn->formatted_folio,
        };
    }

    private function interestToPay(Pawn $pawn): float
    {
        try {
            return round((float) $pawn->getInterest2payLess1day(), 2);
        } catch (Throwable) {
            $days = max(Carbon::parse($pawn->created_at)->diffInDays(now()), 1);
            $dailyRate = (float) $pawn->daily_interest_rate / 100;
            $interest = (float) $pawn->total * $dailyRate * $days;

            $iva = (float) $pawn->iva_rate;
            $ivaFactor = $iva > 1 ? $iva / 100 : $iva;

            $interest += $interest * $ivaFactor;

            if ($pawn->inapam_rate) {
                $interest -= $interest * (float) $pawn->inapam_rate;
            }

            return round(max($interest, 0), 2);
        }
    }
}