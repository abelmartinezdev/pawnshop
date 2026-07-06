<?php

namespace App\Actions\Expenses;

use App\Actions\Transactions\StoreCashTransactionAction;
use App\Http\Requests\Expenses\StoreExpenseRequest;
use Illuminate\Http\RedirectResponse;

class StoreExpenseAction
{
    public function __construct(
        private readonly StoreCashTransactionAction $storeCashTransaction
    ) {
    }

    public function __invoke(StoreExpenseRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $transaction = ($this->storeCashTransaction)([
            'type' => 'manual_expense',
            'amount' => -abs((float) $validated['amount']),
            'payment_type' => $validated['payment_type'],
            'comments' => $validated['comments'],
            'data' => [
                'source' => 'manual_expense',
                'created_from' => 'expenses.create',
            ],
        ]);

        return redirect()
            ->route('expenses.show', $transaction->id)
            ->with('success', 'Gasto registrado correctamente.');
    }
}