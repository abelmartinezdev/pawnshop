<?php

namespace App\Actions\Transactions;

use App\Modules\Pawns\PawnsRepo;
use App\Modules\Transactions\Transaction;
use App\Modules\Transactions\TransactionsRepo;
use Illuminate\Http\Request;

class CancelTransactionAction
{
    public function __construct(
        private TransactionsRepo $transactions,
        private PawnsRepo $pawnsRepo
    ) {}

    public function __invoke(Request $request, int $id)
    {
        \Pawnshop::verifyLastClosing();

        $request->validate([
            'comments_cancel' => 'required|string|min:3',
        ]);

        $transaction = Transaction::findOrFail($id);

        if (!$transaction->mayBeCanceled()) {
            return back()->withErrors(['comments_cancel' => 'No puedes cancelar esta transacción.']);
        }

        $comments = 'Cancelación de ' . trans('core.transaction_types.' . $transaction->type)
            . ' - ' . $request->get('comments_cancel');

        $this->transactions->cancel($transaction, $comments);

        if ($transaction->type === 'expiration_date_change') {
            $data = json_decode($transaction->data);
            $new  = \Date::parse($data->old_date_expiration);
            $this->pawnsRepo->updateDateExpiration($transaction->pawn, $new);
        }

        return redirect()->route('pawns.show', $transaction->pawn->id);
    }
}