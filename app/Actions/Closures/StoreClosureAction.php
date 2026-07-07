<?php

namespace App\Actions\Closures;

use App\Http\Requests\Closures\StoreClosureRequest;
use App\Models\Closure as CashClosure;
use App\Models\Office;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StoreClosureAction
{
    public function __construct(
        private readonly CalculateClosurePreviewAction $calculateClosurePreview
    ) {
    }

    public function __invoke(StoreClosureRequest $request): RedirectResponse
    {
        $officeId = session('office_id') ?: $request->user()?->office_id;

        abort_unless($officeId, 404, 'No hay una sucursal activa.');

        $closure = DB::transaction(function () use ($request, $officeId) {
            $periodEnd = now();

            /** @var Office $office */
            $office = Office::query()
                ->with('company:id,name')
                ->lockForUpdate()
                ->findOrFail($officeId);

            $preview = ($this->calculateClosurePreview)((int) $office->id, $periodEnd, $office);

            if (! $preview['can_close']) {
                throw ValidationException::withMessages([
                    'counted_cash' => $preview['close_message']
                        ?: 'No hay movimientos pendientes por cerrar.',
                ]);
            }

            $countedCash = round((float) $request->validated('counted_cash'), 2);
            $expectedCash = round((float) $preview['summary']['expected_cash'], 2);
            $difference = round($countedCash - $expectedCash, 2);

            return CashClosure::query()->create([
                'company_id' => $office->company_id,
                'office_id' => $office->id,
                'user_id' => $request->user()?->id,

                'period_start_at' => $preview['period']['start_raw'],
                'period_end_at' => $preview['period']['end_raw'],
                'closed_at' => $periodEnd,

                'opening_cash' => $preview['summary']['opening_cash'],
                'cash_in' => $preview['summary']['cash_in'],
                'cash_out' => $preview['summary']['cash_out'],
                'expected_cash' => $expectedCash,
                'counted_cash' => $countedCash,
                'difference' => $difference,
                'total_transactions' => $preview['summary']['total_transactions'],

                'comments' => $request->validated('comments'),

                'data' => [
                    'summary' => $preview['summary'],
                    'period' => $preview['period'],
                    'transactions' => collect($preview['transactions'])->pluck('id')->values()->all(),
                    'breakdown' => $preview['summary']['breakdown'],
                ],
            ]);
        });

        return redirect()
            ->route('closures.show', $closure->id)
            ->with('success', 'Caja cerrada correctamente.');
    }
}