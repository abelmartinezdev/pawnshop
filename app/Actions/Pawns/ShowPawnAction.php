<?php

namespace App\Actions\Pawns;

use App\Models\Pawn;
use Inertia\Inertia;

class ShowPawnAction
{
    public function __invoke(Pawn $pawn)
    {
        $pawn->load([
            'office:id,serie,name',
            'customer',
            'items.product',
            'transactions.user',
        ]);

        // (Opcional) para asegurar que Inertia tenga estos accesores si no aparecen:
        // $pawn->append(['interest2payminus1day', 'amount2liquidateminus1day']);

        return Inertia::render('Pawns/Show', [
            'pawn' => $pawn,
            'can' => [
                'mayBeCanceled' => $pawn->mayBeCanceled(),
                'canBeAuctioned' => $pawn->canBeAuctioned(),
            ],
        ]);
    }
}