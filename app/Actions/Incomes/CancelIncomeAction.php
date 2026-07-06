<?php

namespace App\Actions\Incomes;

use App\Http\Requests\Incomes\CancelIncomeRequest;
use App\Models\Office;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class CancelIncomeAction
{
    public function __invoke(int $id, CancelIncomeRequest $request): RedirectResponse
    {
        $officeId = session('office_id') ?: $request->user()?->office_id;

        abort_unless($officeId, 404, 'No hay una sucursal activa.');

        DB::transaction(function () use ($id, $request, $officeId) {
            /** @var Transaction $income */
            $income = Transaction::query()
                ->where('office_id', $officeId)
                ->where('type', 'manual_income')
                ->lockForUpdate()
                ->findOrFail($id);

            if ($income->canceled_at) {
                return;
            }

            /** @var Office $office */
            $office = Office::query()
                ->lockForUpdate()
                ->findOrFail($officeId);

            $cashBefore = (float) $office->cash;

            if ($income->payment_type === 'cash') {
                $office->forceFill([
                    'cash' => round($cashBefore - (float) $income->amount, 2),
                ])->save();
            }

            $data = is_array($income->data) ? $income->data : [];

            $data['cancelled'] = [
                'by_user_id' => $request->user()?->id,
                'at' => now()->toDateTimeString(),
                'cash_before' => $cashBefore,
                'cash_after' => (float) $office->cash,
            ];

            $income->forceFill([
                'canceled_at' => now(),
                'comments_cancel' => $request->validated('comments_cancel'),
                'data' => $data,
            ])->save();
        });

        return redirect()
            ->route('incomes.show', $id)
            ->with('success', 'Ingreso cancelado correctamente.');
    }
}