<?php

namespace App\Actions\Incomes;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IndexIncomeAction
{
    public function __invoke(Request $request): Response
    {
        $officeId = $this->currentOfficeId($request);

        abort_unless($officeId, 404, 'No hay una sucursal activa.');

        $search = trim((string) $request->input('search', ''));
        $paymentType = $request->input('payment_type');
        $status = $request->input('status', 'active');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $perPage = (int) $request->input('per_page', 15);

        $baseQuery = Transaction::query()
            ->where('office_id', $officeId)
            ->where('type', 'manual_income');

        $summaryQuery = clone $baseQuery;

        $summary = [
            'total_active' => (float) (clone $summaryQuery)
                ->whereNull('canceled_at')
                ->sum('amount'),

            'count_active' => (int) (clone $summaryQuery)
                ->whereNull('canceled_at')
                ->count(),

            'total_cancelled' => (float) (clone $summaryQuery)
                ->whereNotNull('canceled_at')
                ->sum('amount'),

            'count_cancelled' => (int) (clone $summaryQuery)
                ->whereNotNull('canceled_at')
                ->count(),
        ];

        $incomes = $baseQuery
            ->with(['user:id,name,email'])
            ->when($status === 'active', fn (Builder $query) => $query->whereNull('canceled_at'))
            ->when($status === 'cancelled', fn (Builder $query) => $query->whereNotNull('canceled_at'))
            ->when($paymentType, fn (Builder $query) => $query->where('payment_type', $paymentType))
            ->when($dateFrom, fn (Builder $query) => $query->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo, fn (Builder $query) => $query->whereDate('created_at', '<=', $dateTo))
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $builder) use ($search) {
                    $builder
                        ->where('id', $search)
                        ->orWhere('comments', 'like', "%{$search}%")
                        ->orWhere('comments_cancel', 'like', "%{$search}%")
                        ->orWhere('amount', 'like', "%{$search}%")
                        ->orWhere('balance', 'like', "%{$search}%")
                        ->orWhereHas('user', fn (Builder $userQuery) => $userQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (Transaction $income) => [
                'id' => $income->id,
                'amount' => (float) $income->amount,
                'balance' => (float) $income->balance,
                'payment_type' => $income->payment_type,
                'payment_type_label' => $this->labelPaymentType($income->payment_type),
                'comments' => $income->comments,
                'comments_cancel' => $income->comments_cancel,
                'canceled_at' => $income->canceled_at?->format('d/m/Y H:i'),
                'created_at' => $income->created_at?->format('d/m/Y H:i'),
                'is_cancelled' => $income->canceled_at !== null,
                'user' => $income->user ? [
                    'id' => $income->user->id,
                    'name' => $income->user->name,
                ] : null,
            ]);

        return Inertia::render('Incomes/Index', [
            'incomes' => $incomes,
            'summary' => $summary,
            'filters' => [
                'search' => $search,
                'payment_type' => $paymentType,
                'status' => $status,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'per_page' => $perPage,
            ],
            'options' => [
                'paymentTypes' => [
                    ['value' => 'cash', 'label' => 'Efectivo'],
                    ['value' => 'card', 'label' => 'Tarjeta'],
                ],
                'statuses' => [
                    ['value' => 'active', 'label' => 'Activos'],
                    ['value' => 'cancelled', 'label' => 'Cancelados'],
                    ['value' => 'all', 'label' => 'Todos'],
                ],
            ],
        ]);
    }

    private function currentOfficeId(Request $request): ?int
    {
        return session('office_id') ?: $request->user()?->office_id;
    }

    private function labelPaymentType(?string $paymentType): string
    {
        return match ($paymentType) {
            'cash' => 'Efectivo',
            'card' => 'Tarjeta',
            default => 'No especificado',
        };
    }
}