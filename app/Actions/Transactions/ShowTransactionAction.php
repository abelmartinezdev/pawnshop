<?php

namespace App\Actions\Transactions;

use App\Modules\Transactions\Transaction;
use Inertia\Inertia;

class ShowTransactionAction
{
    public function __invoke(int $id)
    {
        \Pawnshop::verifyLastClosing();

        $transaction = Transaction::query()
            ->with(['pawn.office:id,serie,name', 'pawn.customer', 'user', 'office'])
            ->findOrFail($id);

        return Inertia::render('Transactions/Show', [
            'transaction' => $transaction,
            'pawn' => $transaction->pawn,
            'data' => $transaction->data ? json_decode($transaction->data, true) : null,
            'can' => [
                'mayBeCanceled' => $transaction->mayBeCanceled(),
            ],
        ]);
    }
}