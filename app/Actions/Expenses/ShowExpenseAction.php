<?php

namespace App\Actions\Expenses;

use App\Models\Transaction;
use Inertia\Inertia;
use Inertia\Response;

class ShowExpenseAction
{
    public function __invoke(int $id): Response
    {
        $officeId = session('office_id') ?: auth()->user()?->office_id;

        abort_unless($officeId, 404, 'No hay una sucursal activa.');

        $expense = Transaction::query()
            ->with([
                'office:id,name,company_id,cash',
                'office.company:id,name',
                'user:id,name,email',
            ])
            ->where('office_id', $officeId)
            ->where('type', 'manual_expense')
            ->findOrFail($id);

        return Inertia::render('Expenses/Show', [
            'expense' => [
                'id' => $expense->id,
                'amount' => (float) $expense->amount,
                'absolute_amount' => abs((float) $expense->amount),
                'balance' => (float) $expense->balance,
                'payment_type' => $expense->payment_type,
                'payment_type_label' => $this->labelPaymentType($expense->payment_type),
                'comments' => $expense->comments,
                'data' => $expense->data,
                'canceled_at' => $expense->canceled_at?->format('d/m/Y H:i'),
                'comments_cancel' => $expense->comments_cancel,
                'created_at' => $expense->created_at?->format('d/m/Y H:i'),
                'updated_at' => $expense->updated_at?->format('d/m/Y H:i'),
                'is_cancelled' => $expense->canceled_at !== null,

                'office' => $expense->office ? [
                    'id' => $expense->office->id,
                    'name' => $expense->office->name,
                    'cash' => (float) $expense->office->cash,
                    'company' => $expense->office->company?->name,
                ] : null,

                'user' => $expense->user ? [
                    'id' => $expense->user->id,
                    'name' => $expense->user->name,
                    'email' => $expense->user->email,
                ] : null,
            ],
        ]);
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