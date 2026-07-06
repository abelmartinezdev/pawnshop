<?php

namespace App\Actions\Transactions;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IndexTransactionAction
{
    public function __invoke(Request $request): Response
    {
        $officeId = $this->currentOfficeId($request);

        abort_unless($officeId, 404, 'No hay una sucursal activa.');

        $search = trim((string) $request->input('search', ''));
        $type = $request->input('type');
        $paymentType = $request->input('payment_type');
        $status = $request->input('status', 'active');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $perPage = (int) $request->input('per_page', 15);

        $transactions = Transaction::query()
            ->with([
                'user:id,name,email',
                'pawn:id,folio,customer_id',
                'pawn.customer:id,name',
            ])
            ->where('office_id', $officeId)
            ->when($status === 'active', fn (Builder $query) => $query->whereNull('canceled_at'))
            ->when($status === 'cancelled', fn (Builder $query) => $query->whereNotNull('canceled_at'))
            ->when($type, fn (Builder $query) => $query->where('type', $type))
            ->when($paymentType, fn (Builder $query) => $query->where('payment_type', $paymentType))
            ->when($dateFrom, fn (Builder $query) => $query->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo, fn (Builder $query) => $query->whereDate('created_at', '<=', $dateTo))
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $builder) use ($search) {
                    $builder
                        ->where('id', $search)
                        ->orWhere('type', 'like', "%{$search}%")
                        ->orWhere('comments', 'like', "%{$search}%")
                        ->orWhere('comments_cancel', 'like', "%{$search}%")
                        ->orWhere('amount', 'like', "%{$search}%")
                        ->orWhere('balance', 'like', "%{$search}%")
                        ->orWhereHas('user', fn (Builder $userQuery) => $userQuery->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('pawn.customer', fn (Builder $customerQuery) => $customerQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (Transaction $transaction) => [
                'id' => $transaction->id,
                'type' => $transaction->type,
                'type_label' => $this->labelType($transaction->type),
                'amount' => (float) $transaction->amount,
                'balance' => (float) $transaction->balance,
                'payment_type' => $transaction->payment_type,
                'payment_type_label' => $this->labelPaymentType($transaction->payment_type),
                'comments' => $transaction->comments,
                'comments_cancel' => $transaction->comments_cancel,
                'canceled_at' => $transaction->canceled_at?->format('d/m/Y H:i'),
                'created_at' => $transaction->created_at?->format('d/m/Y H:i'),
                'is_cancelled' => $transaction->canceled_at !== null,
                'user' => $transaction->user ? [
                    'id' => $transaction->user->id,
                    'name' => $transaction->user->name,
                ] : null,
                'pawn' => $transaction->pawn ? [
                    'id' => $transaction->pawn->id,
                    'folio' => $transaction->pawn->folio,
                    'customer' => $transaction->pawn->customer?->name,
                ] : null,
            ]);

        return Inertia::render('Transactions/Index', [
            'transactions' => $transactions,
            'filters' => [
                'search' => $search,
                'type' => $type,
                'payment_type' => $paymentType,
                'status' => $status,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'per_page' => $perPage,
            ],
            'options' => [
                'types' => $this->typeOptions(),
                'paymentTypes' => [
                    ['value' => 'cash', 'label' => 'Efectivo'],
                    ['value' => 'card', 'label' => 'Tarjeta'],
                ],
                'statuses' => [
                    ['value' => 'active', 'label' => 'Activas'],
                    ['value' => 'cancelled', 'label' => 'Canceladas'],
                    ['value' => 'all', 'label' => 'Todas'],
                ],
            ],
        ]);
    }

    private function currentOfficeId(Request $request): ?int
    {
        return session('office_id') ?: $request->user()?->office_id;
    }

    private function typeOptions(): array
    {
        return [
            ['value' => 'pawn', 'label' => 'Empeño'],
            ['value' => 'countersign', 'label' => 'Refrendo'],
            ['value' => 'liquidation', 'label' => 'Liquidación'],
            ['value' => 'payment', 'label' => 'Pago'],
            ['value' => 'payment_to_interest', 'label' => 'Abono a interés'],
            ['value' => 'manual_income', 'label' => 'Ingreso manual'],
            ['value' => 'manual_expense', 'label' => 'Gasto manual'],
            ['value' => 'sale', 'label' => 'Venta'],
            ['value' => 'aside', 'label' => 'Apartado'],
            ['value' => 'aside_payment', 'label' => 'Abono a apartado'],
            ['value' => 'adjustment', 'label' => 'Ajuste'],
        ];
    }

    private function labelType(?string $type): string
    {
        return collect($this->typeOptions())
            ->firstWhere('value', $type)['label']
            ?? ($type ? ucfirst(str_replace('_', ' ', $type)) : 'Movimiento');
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