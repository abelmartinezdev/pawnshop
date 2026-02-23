<?php

namespace App\Actions\Pawns;

use App\Models\Pawn;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ShowPawnPayFormAction
{
    public function __invoke(Request $request, Pawn $pawn)
    {
        \Pawnshop::verifyLastClosing();

        $pawn->load(['office:id,serie,name', 'customer', 'items.product']);

        return Inertia::render('Pawns/PayForm', [
            'pawn' => $pawn,
            'amounts' => [
                'interest_total' => (int) round($pawn->interest2payminus1day),
                'liquidate_total' => (int) round($pawn->amount2liquidateminus1day),
                'countersign_interest' => (int) round($pawn->interest2payminus1day),
            ],
            'defaults' => [
                'payment_type' => 'cash',
                'transaction' => 'liquidate', // liquidate | countersign | interest_payment
            ],
        ]);
    }
}