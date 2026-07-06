<?php

namespace App\Actions\Dashboard;

use App\Models\Office;
use App\Models\Pawn;
use App\Models\Transaction;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShowDashboardAction
{
    public function __invoke(Request $request): Response
    {
        $office = $this->currentOffice($request);

        abort_unless($office, 404, 'No hay una sucursal activa.');

        $from = now()->subDays(30)->startOfDay();
        $to = now()->endOfDay();

        $transactions = Transaction::query()
            ->where('office_id', $office->id)
            ->whereNull('canceled_at')
            ->whereBetween('created_at', [$from, $to])
            ->latest()
            ->get();

        $summary = [
            'cash' => (float) $office->cash,

            'pawns' => abs((float) $transactions
                ->where('type', 'pawn')
                ->sum('amount')),

            'countersigns' => (float) $transactions
                ->whereIn('type', [
                    'countersign',
                    'refrendo',
                    'refinance',
                ])
                ->where('amount', '>', 0)
                ->sum('amount'),

            'interestPayments' => (float) $transactions
                ->whereIn('type', [
                    'payment',
                    'payment_to_interest',
                    'interest_payment',
                    'abono_interes',
                ])
                ->where('amount', '>', 0)
                ->sum('amount'),

            'manualIncomes' => (float) $transactions
                ->where('type', 'manual_income')
                ->where('amount', '>', 0)
                ->sum('amount'),

            'manualExpenses' => abs((float) $transactions
                ->where('type', 'manual_expense')
                ->where('amount', '<', 0)
                ->sum('amount')),

            'activePawns' => Pawn::query()
                ->where('office_id', $office->id)
                ->whereNull('canceled_at')
                ->whereNull('paid_at')
                ->count(),

            'paidPawns' => Pawn::query()
                ->where('office_id', $office->id)
                ->whereNotNull('paid_at')
                ->whereBetween('paid_at', [$from, $to])
                ->count(),
        ];

        return Inertia::render('Dashboard', [
            'summary' => $summary,
            'chart' => $this->buildChart($transactions),
            'recentTransactions' => $transactions
                ->take(8)
                ->map(fn (Transaction $transaction) => [
                    'id' => $transaction->id,
                    'type' => $this->labelType($transaction->type),
                    'raw_type' => $transaction->type,
                    'amount' => (float) $transaction->amount,
                    'balance' => (float) $transaction->balance,
                    'payment_type' => $transaction->payment_type,
                    'comments' => $transaction->comments,
                    'created_at' => $transaction->created_at?->format('d/m/Y H:i'),
                ])
                ->values(),
        ]);
    }

    private function currentOffice(Request $request): ?Office
    {
        $officeId = session('office_id') ?: $request->user()?->office_id;

        if (! $officeId) {
            return null;
        }

        return Office::query()
            ->with('company:id,name')
            ->find($officeId);
    }

    private function buildChart($transactions): array
    {
        $start = CarbonImmutable::now()->subDays(30)->startOfDay();

        return collect(range(0, 5))
            ->map(function (int $index) use ($start, $transactions) {
                $periodStart = $start->addDays($index * 5);
                $periodEnd = $periodStart->addDays(4)->endOfDay();

                $periodTransactions = $transactions->filter(function (Transaction $transaction) use ($periodStart, $periodEnd) {
                    return $transaction->created_at
                        && $transaction->created_at->between($periodStart, $periodEnd);
                });

                return [
                    'label' => $periodStart->format('d M') . ' - ' . $periodEnd->format('d M'),
                    'income' => (float) $periodTransactions
                        ->where('amount', '>', 0)
                        ->sum('amount'),
                    'expense' => abs((float) $periodTransactions
                        ->where('amount', '<', 0)
                        ->sum('amount')),
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
            'aside', 'aside_payment' => 'Apartado',
            'adjustment' => 'Ajuste',
            default => $type ? ucfirst(str_replace('_', ' ', $type)) : 'Movimiento',
        };
    }
}