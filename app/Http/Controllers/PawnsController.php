<?php

namespace App\Http\Controllers;

use App\Actions\Pawns\CreatePawnAction;
use App\Actions\Pawns\EditPawnDateExpirationAction;
use App\Actions\Pawns\IndexPawnsAction;
use App\Actions\Pawns\PayPawnAction;
use App\Actions\Pawns\PrintCountersignTicketAction;
use App\Actions\Pawns\ShowPawnAction;
use App\Actions\Pawns\ShowPawnPayFormAction;
use App\Actions\Pawns\StorePawnAction;
use App\Actions\Pawns\UpdatePawnDateExpirationAction;
use App\Http\Requests\Pawns\PayPawnRequest;
use App\Http\Requests\Pawns\StorePawnRequest;
use App\Http\Requests\Pawns\UpdatePawnDateExpirationRequest;
use App\Models\Pawn;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use App\Actions\Pawns\PrintBigPawnTicketAction;

class PawnsController extends Controller
{
    public function index(Request $request, IndexPawnsAction $action): Response
    {
        return $action($request);
    }

    public function create(Request $request, CreatePawnAction $action): Response
    {
        return $action($request);
    }

    public function store(StorePawnRequest $request, StorePawnAction $action): RedirectResponse
    {
        return $action($request);
    }

    public function show(Pawn $pawn, ShowPawnAction $action): Response
    {
        return $action($pawn);
    }

    public function payForm(Request $request, Pawn $pawn, ShowPawnPayFormAction $action): Response
    {
        return $action($request, $pawn);
    }

    public function pay(PayPawnRequest $request, Pawn $pawn, PayPawnAction $action): RedirectResponse
    {
        return $action($request, $pawn);
    }

    public function editDateExpiration(Pawn $pawn, EditPawnDateExpirationAction $action): Response
    {
        return $action($pawn);
    }

    public function updateDateExpiration(
        UpdatePawnDateExpirationRequest $request,
        Pawn $pawn,
        UpdatePawnDateExpirationAction $action
    ): RedirectResponse {
        return $action($request, $pawn);
    }

    public function printCountersign(Pawn $pawn, PrintCountersignTicketAction $action)
    {
        return $action($pawn);
    }

    public function printBigTicket(Pawn $pawn, PrintBigPawnTicketAction $action)
    {
        return $action($pawn);
    }
}