<?php

namespace App\Actions\Expenses;

use App\Http\Requests\Expenses\CancelExpenseRequest;
use App\Models\Office;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class CancelExpenseAction
{
    public function __invoke(int $id, CancelExpenseRequest $request): RedirectResponse
    {
        $officeId = session('office_id') ?: $request->user()?->office_id;

        abort_unless($officeId, 404, 'No hay una sucursal activa.');

        DB::transaction(function () use ($id, $request, $officeId) {
            /** @var Transaction $expense */
            $expense = Transaction::query()
                ->where('office_id', $officeId)
                ->where('type', 'manual_expense')
                ->lockForUpdate()
                ->findOrFail($id);

            if ($expense->canceled_at) {
                return;
            }

            /** @var Office $office */
            $office = Office::query()
                ->lockForUpdate()
                ->findOrFail($officeId);

            $cashBefore = (float) $office->cash;

            if ($expense->payment_type === 'cash') {
                $office->forceFill([
                    'cash' => round($cashBefore - (float) $expense->amount, 2),
                ])->save();
            }

            $data = is_array($expense->data) ? $expense->data : [];

            $data['cancelled'] = [
                'by_user_id' => $request->user()?->id,
                'at' => now()->toDateTimeString(),
                'cash_before' => $cashBefore,
                'cash_after' => (float) $office->cash,
            ];

            $expense->forceFill([
                'canceled_at' => now(),
                'comments_cancel' => $request->validated('comments_cancel'),
                'data' => $data,
            ])->save();
        });

        return redirect()
            ->route('expenses.show', $id)
            ->with('success', 'Gasto cancelado correctamente.');
    }
}