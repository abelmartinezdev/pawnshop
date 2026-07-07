<?php

namespace App\Actions\Closures;

use App\Models\Closure as CashClosure;
use App\Models\Office;
use App\Models\Transaction;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class CalculateClosurePreviewAction
{
    public function __invoke(
        int $officeId,
        ?CarbonInterface $periodEnd = null,
        ?Office $lockedOffice = null
    ): array {
        $periodEnd = $periodEnd ? $periodEnd->copy() : now();

        $office = $lockedOffice ?: Office::query()
            ->with('company:id,name')
            ->findOrFail($officeId);

        $lastClosure = CashClosure::query()
            ->where('office_id', $office->id)
            ->latest('period_end_at')
            ->latest('closed_at')
            ->latest('id')
            ->first();

        $periodStart = $lastClosure?->period_end_at
            ?: $lastClosure?->closed_at
            ?: $lastClosure?->created_at
            ?: $periodEnd->copy()->startOfDay();

        $operator = $lastClosure ? '>' : '>=';

        $transactions = Transaction::query()
            ->with(['user:id,name,email'])
            ->where('office_id', $office->id)
            ->whereNull('canceled_at')
            ->where('payment_type', 'cash')
            ->where('created_at', $operator, $periodStart)
            ->where('created_at', '<=', $periodEnd)
            ->orderBy('created_at')
            ->get();

        $cashIn = round((float) $transactions
            ->where('amount', '>', 0)
            ->sum('amount'), 2);

        $cashOut = round(abs((float) $transactions
            ->where('amount', '<', 0)
            ->sum('amount')), 2);

        $netCash = round($cashIn - $cashOut, 2);

        /**
         * offices.cash representa la caja actual abierta.
         * El corte compara contra esa caja esperada.
         */
        $expectedCash = round((float) $office->cash, 2);
        $openingCash = round($expectedCash - $netCash, 2);

        $lastClosureDate = $lastClosure?->period_end_at
            ?: $lastClosure?->closed_at
            ?: $lastClosure?->created_at;

        $hasMovementsToClose = $transactions->count() > 0;

        $alreadyClosedTodayWithoutMovements = $lastClosure
            && $lastClosureDate
            && $lastClosureDate->isSameDay($periodEnd)
            && ! $hasMovementsToClose;

        $canClose = ! $alreadyClosedTodayWithoutMovements;

        $recentClosures = CashClosure::query()
            ->with(['user:id,name'])
            ->where('office_id', $office->id)
            ->latest('period_end_at')
            ->latest('closed_at')
            ->latest('id')
            ->take(12)
            ->get()
            ->map(fn (CashClosure $closure) => [
                'id' => $closure->id,
                'closed_at' => $closure->closed_at?->format('d/m/Y H:i'),
                'period_end_at' => $closure->period_end_at?->format('d/m/Y H:i'),
                'expected_cash' => (float) $closure->expected_cash,
                'counted_cash' => (float) $closure->counted_cash,
                'difference' => (float) $closure->difference,
                'total_transactions' => (int) $closure->total_transactions,
                'user' => $closure->user ? [
                    'id' => $closure->user->id,
                    'name' => $closure->user->name,
                ] : null,
            ])
            ->values();

        return [
            'can_close' => $canClose,
            'has_movements_to_close' => $hasMovementsToClose,
            'close_message' => $canClose
                ? null
                : 'La caja ya fue cerrada hoy y no hay movimientos nuevos pendientes por cerrar.',

            'office' => [
                'id' => $office->id,
                'name' => $office->name,
                'cash' => $expectedCash,
                'company' => $office->company ? [
                    'id' => $office->company->id,
                    'name' => $office->company->name,
                ] : null,
            ],

            'lastClosure' => $lastClosure ? [
                'id' => $lastClosure->id,
                'closed_at' => $lastClosure->closed_at?->format('d/m/Y H:i'),
                'period_end_at' => $lastClosure->period_end_at?->format('d/m/Y H:i'),
                'counted_cash' => (float) $lastClosure->counted_cash,
                'difference' => (float) $lastClosure->difference,
            ] : null,

            'recentClosures' => $recentClosures,

            'period' => [
                'start' => $periodStart->format('d/m/Y H:i'),
                'end' => $periodEnd->format('d/m/Y H:i'),
                'start_raw' => $periodStart->toDateTimeString(),
                'end_raw' => $periodEnd->toDateTimeString(),
            ],

            'summary' => [
                'opening_cash' => $openingCash,
                'cash_in' => $cashIn,
                'cash_out' => $cashOut,
                'net_cash' => $netCash,
                'expected_cash' => $expectedCash,
                'total_transactions' => $transactions->count(),
                'breakdown' => $this->buildBreakdown($transactions),
            ],

            'transactions' => $transactions
                ->map(fn (Transaction $transaction) => [
                    'id' => $transaction->id,
                    'type' => $transaction->type,
                    'type_label' => $this->labelType($transaction->type),
                    'amount' => (float) $transaction->amount,
                    'balance' => (float) $transaction->balance,
                    'payment_type' => $transaction->payment_type,
                    'comments' => $transaction->comments,
                    'created_at' => $transaction->created_at?->format('d/m/Y H:i'),
                    'user' => $transaction->user ? [
                        'id' => $transaction->user->id,
                        'name' => $transaction->user->name,
                    ] : null,
                ])
                ->values(),
        ];
    }

    private function buildBreakdown(Collection $transactions): array
    {
        return $transactions
            ->groupBy('type')
            ->map(function (Collection $items, string $type) {
                return [
                    'type' => $type,
                    'label' => $this->labelType($type),
                    'count' => $items->count(),
                    'cash_in' => round((float) $items->where('amount', '>', 0)->sum('amount'), 2),
                    'cash_out' => round(abs((float) $items->where('amount', '<', 0)->sum('amount')), 2),
                    'net' => round((float) $items->sum('amount'), 2),
                ];
            })
            ->values()
            ->all();
    }

    private function labelType(?string $type): string
    {
        return match ($type) {
            'pawn' => 'Empeño',
            'countersign', 'refrendo', 'refinance' => 'Refrendo',
            'liquidation' => 'Liquidación',
            'payment', 'payment_to_interest', 'interest_payment', 'abono_interes' => 'Abono a interés',
            'manual_income' => 'Ingreso manual',
            'manual_expense' => 'Gasto manual',
            'sale' => 'Venta',
            'aside' => 'Apartado',
            'aside_payment' => 'Abono a apartado',
            'adjustment' => 'Ajuste',
            default => $type ? ucfirst(str_replace('_', ' ', $type)) : 'Movimiento',
        };
    }
}