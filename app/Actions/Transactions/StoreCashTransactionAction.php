<?php

namespace App\Actions\Transactions;

use App\Models\Office;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StoreCashTransactionAction
{
    public function __invoke(array $payload): Transaction
    {
        return DB::transaction(function () use ($payload) {
            $officeId = $this->resolveOfficeId($payload);

            /** @var Office $office */
            $office = Office::query()
                ->lockForUpdate()
                ->findOrFail($officeId);

            $amount = round((float) ($payload['amount'] ?? 0), 2);

            if ($amount == 0.0) {
                throw new InvalidArgumentException('El monto de la transacción no puede ser cero.');
            }

            $paymentType = $payload['payment_type'] ?? Transaction::PAYMENT_CASH;

            if (! in_array($paymentType, [Transaction::PAYMENT_CASH, Transaction::PAYMENT_CARD], true)) {
                throw new InvalidArgumentException('El tipo de pago no es válido.');
            }

            /*
             * Por defecto:
             * - efectivo sí afecta la caja física
             * - tarjeta queda en transacciones, pero no altera offices.cash
             */
            $affectsCash = (bool) ($payload['affects_cash'] ?? $paymentType === Transaction::PAYMENT_CASH);

            $currentBalance = round((float) $office->cash, 2);
            $newBalance = $affectsCash
                ? round($currentBalance + $amount, 2)
                : $currentBalance;

            if ($affectsCash) {
                $office->forceFill([
                    'cash' => $newBalance,
                ])->save();
            }

            return Transaction::query()->create([
                'office_id' => $office->id,
                'user_id' => $payload['user_id'] ?? Auth::id(),
                'pawn_id' => $payload['pawn_id'] ?? null,
                'reference_id' => $payload['reference_id'] ?? null,
                'type' => $payload['type'],
                'comments' => $payload['comments'] ?? null,
                'data' => $payload['data'] ?? null,
                'amount' => $amount,
                'balance' => $newBalance,
                'discount_amount' => $payload['discount_amount'] ?? null,
                'discount_rate' => $payload['discount_rate'] ?? null,
                'payment_type' => $paymentType,
            ]);
        });
    }

    private function resolveOfficeId(array $payload): int
    {
        $officeId = $payload['office_id']
            ?? session('office_id')
            ?? Auth::user()?->office_id;

        if (! $officeId) {
            throw new InvalidArgumentException('No hay una sucursal activa para registrar la transacción.');
        }

        return (int) $officeId;
    }
}