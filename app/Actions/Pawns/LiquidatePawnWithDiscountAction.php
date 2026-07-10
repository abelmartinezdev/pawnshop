<?php

namespace App\Actions\Pawns;

use App\Http\Requests\Pawns\LiquidatePawnWithDiscountRequest;
use App\Models\Office;
use App\Models\Pawn;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Throwable;

class LiquidatePawnWithDiscountAction
{
    public function __invoke(LiquidatePawnWithDiscountRequest $request, Pawn $pawn): RedirectResponse
    {
        if (! auth()->user()?->can('apply-discount')) {
            abort(403, 'No tienes permiso para aplicar descuentos.');
        }

        try {
            $transaction = DB::transaction(function () use ($request, $pawn) {
                $pawn = Pawn::query()
                    ->with(['office', 'items'])
                    ->lockForUpdate()
                    ->findOrFail($pawn->id);

                if (! $this->canLiquidateWithDiscount($pawn)) {
                    throw ValidationException::withMessages([
                        'pawn' => 'No se puede aplicar descuento a este empeño.',
                    ]);
                }

                $office = Office::query()
                    ->lockForUpdate()
                    ->findOrFail($pawn->office_id);

                $discountPercent = (float) $request->validated('discount');
                $discountRate = $discountPercent / 100;
                $paymentType = (string) $request->validated('payment_type');

                $preview = $this->preview($pawn, $discountRate);

                $amountPaid = round((float) $request->validated('amount_paid'), 2);

                if ($amountPaid < $preview['amount_to_liquidate']) {
                    throw ValidationException::withMessages([
                        'amount_paid' => 'El monto recibido no puede ser menor al total a pagar.',
                    ]);
                }

                if ($paymentType === 'cash') {
                    $office->forceFill([
                        'cash' => round((float) $office->cash + $preview['amount_to_liquidate'], 2),
                    ])->save();
                }

                $this->markPawnAsPaid($pawn);

                return $this->createLiquidationTransaction(
                    pawn: $pawn,
                    office: $office,
                    paymentType: $paymentType,
                    amountPaid: $amountPaid,
                    change: round($amountPaid - $preview['amount_to_liquidate'], 2),
                    discountRate: $discountRate,
                    discountPercent: $discountPercent,
                    preview: $preview,
                );
            });

            return redirect()
                ->route('pawns.show', $pawn->id)
                ->with('success', 'Descuento aplicado y empeño liquidado correctamente.');
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('pawns.show', $pawn->id)
                ->with('error', 'No se pudo aplicar el descuento. Revisa la información e inténtalo nuevamente.');
        }
    }

    private function canLiquidateWithDiscount(Pawn $pawn): bool
    {
        return $pawn->auction_at === null
            && $pawn->paid_at === null
            && $pawn->canceled_at === null
            && ! $pawn->hasCountersign();
    }

    private function preview(Pawn $pawn, float $discountRate): array
    {
        $principal = round((float) $pawn->total, 2);

        $interestOriginal = round((float) $pawn->interest2payminus1day, 2);
        $discountAmount = round($interestOriginal * $discountRate, 2);
        $interestWithDiscount = round(max($interestOriginal - $discountAmount, 0), 2);

        return [
            'principal' => $principal,
            'days_to_pay' => (int) $pawn->days2payminus1,
            'daily_interest' => round((float) $pawn->getDailyInterest(false), 2),

            'interest_original' => $interestOriginal,
            'interest_without_iva_original' => round((float) $pawn->getInterest2payLess1day(false), 2),
            'interest_iva_original' => round((float) $pawn->getInterestIVALess1day(), 2),

            'discount_amount' => $discountAmount,
            'interest_to_pay' => $interestWithDiscount,
            'interest_without_iva_discounted' => round((float) $pawn->getInterest2payLess1day(false, $discountRate), 2),
            'interest_iva_discounted' => round((float) $pawn->getInterestIVALess1day($discountRate), 2),

            'paid_amount' => round((float) $pawn->paidAmount(), 2),
            'amount_to_liquidate' => round($principal + $interestWithDiscount, 2),
        ];
    }

    private function markPawnAsPaid(Pawn $pawn): void
    {
        $this->safeForceFill($pawn, [
            'paid_at' => now(),
            'paid_by' => auth()->id(),
            'date_settlement' => now()->toDateString(),
        ]);

        $pawn->save();
    }

    private function createLiquidationTransaction(
        Pawn $pawn,
        Office $office,
        string $paymentType,
        float $amountPaid,
        float $change,
        float $discountRate,
        float $discountPercent,
        array $preview,
    ): Transaction {
        $transaction = new Transaction();

        $data = [
            'amount_paid' => $amountPaid,
            'change' => $change,
            'days2pay' => $preview['days_to_pay'],
            'dailyInterest' => $preview['daily_interest'],

            'interest2pay' => $preview['interest_without_iva_discounted'],
            'interestIVA' => $preview['interest_iva_discounted'],

            'interest2pay_original' => $preview['interest_without_iva_original'],
            'interestIVA_original' => $preview['interest_iva_original'],

            'paid_amount' => $preview['paid_amount'],
            'liquidate' => $preview['amount_to_liquidate'],

            'discount' => $discountRate,
            'discount_percent' => $discountPercent,
            'discount_amount' => $preview['discount_amount'],
        ];

        $this->safeForceFill($transaction, [
            'company_id' => $pawn->company_id,
            'office_id' => $office->id,
            'pawn_id' => $pawn->id,
            'user_id' => auth()->id(),
            'created_by' => auth()->id(),

            'type' => 'liquidation',
            'comments' => 'Liquidación con descuento. Folio '.$pawn->folio,
            'data' => json_encode($data),

            'amount' => $preview['amount_to_liquidate'],
            'balance' => (float) $office->cash,
            'payment_type' => $paymentType,

            'discount_rate' => $discountRate,
            'discount_amount' => $preview['discount_amount'],
        ]);

        $transaction->save();

        return $transaction;
    }

    private function safeForceFill(Model $model, array $values): void
    {
        $table = $model->getTable();

        $filtered = collect($values)
            ->filter(fn ($value, string $column) => Schema::hasColumn($table, $column))
            ->all();

        $model->forceFill($filtered);
    }
}