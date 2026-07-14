<?php

namespace App\Http\Controllers;

use App\Actions\Auctions\IndexAuctionsAction;
use App\Actions\Pawns\ShowPawnAuctionAction;
use App\Actions\Pawns\StorePawnAuctionAction;
use App\Http\Controllers\Concerns\RendersComingSoon;
use App\Http\Requests\Pawns\StorePawnAuctionRequest;
use App\Models\Auction;
use App\Models\Pawn;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class AuctionsController extends Controller
{
    use RendersComingSoon;

    public function index(
        Request $request,
        IndexAuctionsAction $action
    ): Response {
        return $action($request);
    }

    public function create(
        Pawn $pawn,
        ShowPawnAuctionAction $action
    ): Response {
        return $action($pawn);
    }

    public function store(
        StorePawnAuctionRequest $request,
        Pawn $pawn,
        StorePawnAuctionAction $action
    ): RedirectResponse {
        return $action($request, $pawn);
    }

    public function show(Auction $auction): Response
    {
        return $this->comingSoon('Detalle de remate');
    }

    public function cancel(Auction $auction): RedirectResponse
    {
        return $this->pendingAction('La cancelación del remate está pendiente de implementar.');
    }

    public function moveToSale(Auction $auction): RedirectResponse
    {
        return $this->pendingAction('El movimiento del remate a venta está pendiente de implementar.');
    }
}