<?php

namespace App\Actions\Transactions;

use App\Models\Transaction;
use Inertia\Inertia;
use Inertia\Response;

class ShowTransactionAction
{
    public function __invoke(int $id): Response
    {
        $officeId = session('office_id') ?: auth()->user()?->office_id;

        abort_unless($officeId, 404, 'No hay una sucursal activa.');

        $transaction = Transaction::query()
            ->with([
                'office:id,name,company_id,cash',
                'office.company:id,name',
                'user:id,name,email',
                'pawn:id,folio,customer_id,total,date_expiration,paid_at,canceled_at',
                'pawn.customer:id,name,phone,mobile,email',
            ])
            ->where('office_id', $officeId)
            ->findOrFail($id);

        return Inertia::render('Transactions/Show', [
            'transaction' => [
                'id' => $transaction->id,
                'type' => $transaction->type,
                'type_label' => $this->labelType($transaction->type),
                'amount' => (float) $transaction->amount,
                'balance' => (float) $transaction->balance,
                'discount_amount' => $transaction->discount_amount !== null ? (float) $transaction->discount_amount : null,
                'discount_rate' => $transaction->discount_rate !== null ? (float) $transaction->discount_rate : null,
                'payment_type' => $transaction->payment_type,
                'payment_type_label' => $this->labelPaymentType($transaction->payment_type),
                'comments' => $transaction->comments,
                'data' => $transaction->data,
                'canceled_at' => $transaction->canceled_at?->format('d/m/Y H:i'),
                'comments_cancel' => $transaction->comments_cancel,
                'created_at' => $transaction->created_at?->format('d/m/Y H:i'),
                'updated_at' => $transaction->updated_at?->format('d/m/Y H:i'),
                'is_cancelled' => $transaction->canceled_at !== null,
                'office' => $transaction->office ? [
                    'id' => $transaction->office->id,
                    'name' => $transaction->office->name,
                    'cash' => (float) $transaction->office->cash,
                    'company' => $transaction->office->company?->name,
                ] : null,
                'user' => $transaction->user ? [
                    'id' => $transaction->user->id,
                    'name' => $transaction->user->name,
                    'email' => $transaction->user->email,
                ] : null,
                'pawn' => $transaction->pawn ? [
                    'id' => $transaction->pawn->id,
                    'folio' => $transaction->pawn->folio,
                    'total' => (float) $transaction->pawn->total,
                    'date_expiration' => $transaction->pawn->date_expiration,
                    'paid_at' => $transaction->pawn->paid_at?->format('d/m/Y H:i'),
                    'canceled_at' => $transaction->pawn->canceled_at?->format('d/m/Y H:i'),
                    'customer' => $transaction->pawn->customer ? [
                        'id' => $transaction->pawn->customer->id,
                        'name' => $transaction->pawn->customer->name,
                        'phone' => $transaction->pawn->customer->phone,
                        'mobile' => $transaction->pawn->customer->mobile,
                        'email' => $transaction->pawn->customer->email,
                    ] : null,
                ] : null,
            ],
        ]);
    }

    private function labelType(?string $type): string
    {
        return match ($type) {
            'pawn' => 'Empeño',
            'countersign' => 'Refrendo',
            'liquidation' => 'Liquidación',
            'payment' => 'Pago',
            'payment_to_interest' => 'Abono a interés',
            'manual_income' => 'Ingreso manual',
            'manual_expense' => 'Gasto manual',
            'sale' => 'Venta',
            'aside' => 'Apartado',
            'aside_payment' => 'Abono a apartado',
            'adjustment' => 'Ajuste',
            default => $type ? ucfirst(str_replace('_', ' ', $type)) : 'Movimiento',
        };
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