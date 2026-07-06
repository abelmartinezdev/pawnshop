<?php

namespace App\Actions\Incomes;

use App\Models\Transaction;
use Inertia\Inertia;
use Inertia\Response;

class ShowIncomeAction
{
    public function __invoke(int $id): Response
    {
        $officeId = session('office_id') ?: auth()->user()?->office_id;

        abort_unless($officeId, 404, 'No hay una sucursal activa.');

        $income = Transaction::query()
            ->with([
                'office:id,name,company_id,cash',
                'office.company:id,name',
                'user:id,name,email',
            ])
            ->where('office_id', $officeId)
            ->where('type', 'manual_income')
            ->findOrFail($id);

        return Inertia::render('Incomes/Show', [
            'income' => [
                'id' => $income->id,
                'amount' => (float) $income->amount,
                'balance' => (float) $income->balance,
                'payment_type' => $income->payment_type,
                'payment_type_label' => $this->labelPaymentType($income->payment_type),
                'comments' => $income->comments,
                'data' => $income->data,
                'canceled_at' => $income->canceled_at?->format('d/m/Y H:i'),
                'comments_cancel' => $income->comments_cancel,
                'created_at' => $income->created_at?->format('d/m/Y H:i'),
                'updated_at' => $income->updated_at?->format('d/m/Y H:i'),
                'is_cancelled' => $income->canceled_at !== null,
                'office' => $income->office ? [
                    'id' => $income->office->id,
                    'name' => $income->office->name,
                    'cash' => (float) $income->office->cash,
                    'company' => $income->office->company?->name,
                ] : null,
                'user' => $income->user ? [
                    'id' => $income->user->id,
                    'name' => $income->user->name,
                    'email' => $income->user->email,
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