<?php

namespace App\Actions\Offices;

use App\Models\Office;
use App\Models\Pawn;
use App\Models\Transaction;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Inertia\Inertia;
use Inertia\Response;

class ShowOfficeAction
{
    public function __invoke(Office $office): Response
    {
        $office->load([
            'company:id,name,code,rfc,phone,email',
        ]);

        $today = Carbon::today();

        $transactionsToday = Transaction::query()
            ->where('office_id', $office->id)
            ->whereDate('created_at', $today)
            ->whereNull('canceled_at');

        $recentTransactions = Transaction::query()
            ->with([
                'user:id,name,email',
                'pawn:id,folio,office_id,customer_id,total',
                'pawn.customer:id,name',
            ])
            ->where('office_id', $office->id)
            ->latest('id')
            ->limit(15)
            ->get()
            ->map(fn (Transaction $transaction) => [
                'id' => $transaction->id,
                'type' => $transaction->type,
                'type_label' => $this->transactionTypeLabel($transaction->type),
                'amount' => (float) $transaction->amount,
                'balance' => (float) $transaction->balance,
                'payment_type' => $transaction->payment_type,
                'payment_type_label' => $this->paymentTypeLabel($transaction->payment_type),
                'comments' => $transaction->comments,
                'is_cancelled' => $transaction->canceled_at !== null,
                'created_at' => $this->formatDate($transaction->created_at),
                'user' => $transaction->user ? [
                    'id' => $transaction->user->id,
                    'name' => $transaction->user->name,
                    'email' => $transaction->user->email,
                ] : null,
                'pawn' => $transaction->pawn ? [
                    'id' => $transaction->pawn->id,
                    'folio' => $transaction->pawn->formatted_folio ?? str_pad((string) $transaction->pawn->folio, 6, '0', STR_PAD_LEFT),
                    'customer_name' => $transaction->pawn->customer?->name,
                    'total' => (float) $transaction->pawn->total,
                ] : null,
            ]);

        return Inertia::render('Offices/Show', [
            'office' => [
                'id' => $office->id,
                'company_id' => $office->company_id,
                'name' => $office->name,
                'display_name' => $office->display_name,
                'code' => $office->code,
                'serie' => $office->serie,
                'phone' => $office->phone,
                'address' => $office->address,
                'schedule' => $office->schedule,
                'bank_account' => $office->bank_account,
                'daily_interest_rate' => (float) $office->daily_interest_rate,
                'monthly_interest_rate' => (float) $office->monthly_interest_rate,
                'cash' => (float) $office->cash,
                'is_deleted' => $office->trashed(),
                'status_label' => $office->status_label,
                'created_at' => $this->formatDate($office->created_at),
                'updated_at' => $this->formatDate($office->updated_at),
                'deleted_at' => $this->formatDate($office->deleted_at),
                'company' => $office->company ? [
                    'id' => $office->company->id,
                    'name' => $office->company->name,
                    'code' => $office->company->code,
                    'rfc' => $office->company->rfc,
                    'phone' => $office->company->phone,
                    'email' => $office->company->email,
                ] : null,
                'transactions' => $recentTransactions,
            ],

            'summary' => [
                'transactions_today' => (clone $transactionsToday)->count(),
                'income_today' => (float) (clone $transactionsToday)->where('amount', '>', 0)->sum('amount'),
                'expenses_today' => abs((float) (clone $transactionsToday)->where('amount', '<', 0)->sum('amount')),
                'pawns_active' => Pawn::query()
                    ->where('office_id', $office->id)
                    ->whereNull('paid_at')
                    ->whereNull('canceled_at')
                    ->whereNull('auction_at')
                    ->count(),
                'pawns_paid' => Pawn::query()
                    ->where('office_id', $office->id)
                    ->whereNotNull('paid_at')
                    ->count(),
                'pawns_expired' => Pawn::query()
                    ->where('office_id', $office->id)
                    ->whereNull('paid_at')
                    ->whereNull('canceled_at')
                    ->whereNull('auction_at')
                    ->whereDate('date_expiration', '<', $today)
                    ->count(),
            ],

            'urls' => [
                'index' => route('offices.index'),
                'edit' => route('offices.edit', $office->id),
                'destroy' => route('offices.destroy', $office->id),
                'restore' => route('offices.restore', $office->id),
                'company' => $office->company ? route('companies.show', $office->company->id) : null,
                'pawns' => route('pawns.index', ['office_id' => $office->id]),
                'income' => route('incomes.create', ['office_id' => $office->id]),
                'expense' => route('expenses.create', ['office_id' => $office->id]),
            ],
        ]);
    }

    private function formatDate(mixed $value): ?string
    {
        if (! $value) {
            return null;
        }

        if ($value instanceof CarbonInterface) {
            return $value->format('d/m/Y H:i');
        }

        return (string) $value;
    }

    private function transactionTypeLabel(?string $type): string
    {
        return [
            'pawn' => 'Empeño',
            'countersign' => 'Refrendo',
            'liquidation' => 'Liquidación',
            'payment' => 'Pago',
            'manual_income' => 'Ingreso manual',
            'manual_expense' => 'Gasto manual',
            'sale' => 'Venta',
            'aside' => 'Apartado',
            'aside_payment' => 'Abono apartado',
            'adjustment' => 'Ajuste',
            'expiration_date_change' => 'Cambio de fecha',
            'interest_payment' => 'Abono a interés',
        ][$type] ?? ($type ? str_replace('_', ' ', $type) : 'Movimiento');
    }

    private function paymentTypeLabel(?string $type): string
    {
        return [
            'cash' => 'Efectivo',
            'card' => 'Tarjeta',
            'transfer' => 'Transferencia',
        ][$type] ?? 'N/A';
    }
}