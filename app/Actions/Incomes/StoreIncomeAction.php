<?php

namespace App\Actions\Incomes;

use App\Actions\Transactions\StoreCashTransactionAction;
use App\Http\Requests\Incomes\StoreIncomeRequest;
use Illuminate\Http\RedirectResponse;

class StoreIncomeAction
{
    public function __construct(
        private readonly StoreCashTransactionAction $storeCashTransaction
    ) {
    }

    public function __invoke(StoreIncomeRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $transaction = ($this->storeCashTransaction)([
            'type' => 'manual_income',
            'amount' => abs((float) $validated['amount']),
            'payment_type' => $validated['payment_type'],
            'comments' => $validated['comments'],
            'data' => [
                'source' => 'manual_income',
                'created_from' => 'incomes.create',
            ],
        ]);

        return redirect()
            ->route('incomes.show', $transaction->id)
            ->with('success', 'Ingreso registrado correctamente.');
    }
}