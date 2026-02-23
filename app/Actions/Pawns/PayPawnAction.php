<?php

namespace App\Actions\Pawns;

use App\Models\Pawn;
use App\Modules\Transactions\TransactionsRepo;
use Illuminate\Http\Request;

class PayPawnAction
{
    public function __construct(private TransactionsRepo $transactions) {}

    public function __invoke(Request $request, Pawn $pawn)
    {
        \Pawnshop::verifyLastClosing();

        $request->validate([
            'transaction'   => 'required|in:liquidate,countersign,interest_payment',
            'amount_paid'   => 'required|numeric|min:0.01',
            'payment'       => 'nullable|numeric|min:0.01',
            'pay_extra'     => 'nullable|numeric|min:0',
            'discount'      => 'nullable|numeric|min:0|max:100',
            'change'        => 'nullable',
            'payment_type'  => 'required|in:cash,card,transfer',
        ]);

        $txId = null;

        if ($request->transaction === 'liquidate') {
            $tx = $this->transactions->liquidate($pawn, $request->all(), $request->payment_type);

            // Si en tu sistema "liquidación" marca pawn como pagado:
            $pawn->paid_at = now();
            $pawn->paid_by = auth()->id();
            $pawn->save();

            $txId = $tx->id;
        }

        if ($request->transaction === 'countersign') {
            // Nota: tu legacy probablemente además crea el NUEVO pawn (refrendo) en PawnsRepo.
            // Aquí solo registramos la transacción como tu TransactionsRepo lo hace.
            $tx = $this->transactions->countersign($pawn, $request->all(), true, $request->payment_type);

            $txId = $tx->id;
        }

        if ($request->transaction === 'interest_payment') {
            $amount = (float) $request->payment;
            $tx = $this->transactions->payment2Interes($pawn, $amount, $request->all(), true, $request->payment_type);
            $txId = $tx->id;
        }

        return redirect()->route('transactions.show', $txId);
    }
}