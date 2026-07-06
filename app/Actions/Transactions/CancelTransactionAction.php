<?php

namespace App\Actions\Transactions;

use App\Http\Requests\Transactions\CancelTransactionRequest;
use App\Models\Office;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class CancelTransactionAction
{
    public function __invoke(int $id, CancelTransactionRequest $request): RedirectResponse
    {
        $officeId = session('office_id') ?: $request->user()?->office_id;

        abort_unless($officeId, 404, 'No hay una sucursal activa.');

        DB::transaction(function () use ($id, $request, $officeId) {
            /** @var Transaction $transaction */
            $transaction = Transaction::query()
                ->where('office_id', $officeId)
                ->lockForUpdate()
                ->findOrFail($id);

            if ($transaction->canceled_at) {
                return;
            }

            /** @var Office $office */
            $office = Office::query()
                ->lockForUpdate()
                ->findOrFail($officeId);

            if ($transaction->payment_type === 'cash') {
                $office->forceFill([
                    'cash' => round((float) $office->cash - (float) $transaction->amount, 2),
                ])->save();
            }

            $data = is_array($transaction->data) ? $transaction->data : [];

            $data['cancelled'] = [
                'by_user_id' => $request->user()?->id,
                'at' => now()->toDateTimeString(),
                'cash_before' => (float) $office->getOriginal('cash'),
                'cash_after' => (float) $office->cash,
            ];

            $transaction->forceFill([
                'canceled_at' => now(),
                'comments_cancel' => $request->validated('comments_cancel'),
                'data' => $data,
            ])->save();
        });

        return redirect()
            ->route('transactions.show', $id)
            ->with('success', 'Transacción cancelada correctamente.');
    }
}