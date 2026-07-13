<?php

namespace App\Actions\Pawns;

use App\Actions\Pawns\Concerns\CalculatesPawnAuction;
use App\Models\Pawn;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class ShowPawnAuctionAction
{
    use CalculatesPawnAuction;

    public function __invoke(Pawn $pawn): Response
    {
        $this->assertPawnBelongsToCurrentContext($pawn);

        $pawn->load([
            'customer:id,name',
            'office:id,name,serie',
            'items.product',
        ]);

        abort_unless(
            $pawn->canBeAuctioned()
                && $pawn->items->isNotEmpty(),
            403,
            'Este empeño todavía no puede pasar a remate.'
        );

        $calculation = $this->calculateAuction($pawn);

        return Inertia::render(
            'Pawns/SendToAuction',
            [
                'pawn' => [
                    'id' => $pawn->id,
                    'folio' => $pawn->formatted_folio,

                    'customer' => $pawn->customer?->name
                        ?: 'Sin cliente',

                    'office' => $pawn->office?->name
                        ?: 'Sin sucursal',

                    'created_at' => $this->formatDate(
                        $pawn->created_at
                    ),

                    'date_expiration' => $this->formatDate(
                        $pawn->date_expiration
                    ),

                    'date_auction' => $this->formatDate(
                        $pawn->date_auction
                    ),
                ],

                'summary' => collect($calculation)
                    ->except('items')
                    ->all(),

                'items' => $calculation['items'],

                'urls' => [
                    'show' => Route::has('pawns.show')
                        ? route(
                            'pawns.show',
                            $pawn->id
                        )
                        : null,

                    'store' => Route::has(
                        'pawns.send-to-auction.store'
                    )
                        ? route(
                            'pawns.send-to-auction.store',
                            $pawn->id
                        )
                        : null,
                ],
            ]
        );
    }

    private function assertPawnBelongsToCurrentContext(
        Pawn $pawn
    ): void {
        $companyId = (int) (
            session('company_id')
            ?: auth()->user()?->company_id
        );

        $officeId = (int) (
            session('office_id')
            ?: auth()->user()?->office_id
        );

        abort_unless(
            $companyId > 0
                && (int) $pawn->company_id === $companyId,
            404
        );

        abort_unless(
            $officeId > 0
                && (int) $pawn->office_id === $officeId,
            404
        );
    }

    private function formatDate(
        ?CarbonInterface $date
    ): ?string {
        return $date?->format('d/m/Y');
    }
}