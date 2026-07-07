<?php

namespace App\Actions\Closures;

use App\Models\Closure as CashClosure;
use Inertia\Inertia;
use Inertia\Response;

class ShowClosureAction
{
    public function __invoke(int $id): Response
    {
        $officeId = session('office_id') ?: auth()->user()?->office_id;

        abort_unless($officeId, 404, 'No hay una sucursal activa.');

        $closure = CashClosure::query()
            ->with([
                'office:id,name,company_id,cash',
                'office.company:id,name',
                'user:id,name,email',
            ])
            ->where('office_id', $officeId)
            ->findOrFail($id);

        return Inertia::render('Closures/Show', [
            'closure' => [
                'id' => $closure->id,
                'period_start_at' => $closure->period_start_at?->format('d/m/Y H:i'),
                'period_end_at' => $closure->period_end_at?->format('d/m/Y H:i'),
                'closed_at' => $closure->closed_at?->format('d/m/Y H:i'),

                'opening_cash' => (float) $closure->opening_cash,
                'cash_in' => (float) $closure->cash_in,
                'cash_out' => (float) $closure->cash_out,
                'expected_cash' => (float) $closure->expected_cash,
                'counted_cash' => (float) $closure->counted_cash,
                'difference' => (float) $closure->difference,
                'total_transactions' => (int) $closure->total_transactions,

                'comments' => $closure->comments,
                'data' => $closure->data,

                'office' => $closure->office ? [
                    'id' => $closure->office->id,
                    'name' => $closure->office->name,
                    'cash' => (float) $closure->office->cash,
                    'company' => $closure->office->company?->name,
                ] : null,

                'user' => $closure->user ? [
                    'id' => $closure->user->id,
                    'name' => $closure->user->name,
                    'email' => $closure->user->email,
                ] : null,
            ],
        ]);
    }
}