<?php

namespace App\Http\Controllers;

use App\Actions\Pawns\IndexPawnsAction;
use App\Actions\Pawns\ShowPawnAction;
use App\Actions\Pawns\ShowPawnPayFormAction;
use App\Actions\Pawns\PayPawnAction;
use App\Models\Pawn;
use Illuminate\Http\Request;

class PawnsController extends Controller
{
    public function index(Request $request, IndexPawnsAction $action) { return $action($request); }
    public function show(Pawn $pawn, ShowPawnAction $action) { return $action($pawn); }
    public function payForm(Request $request, Pawn $pawn, ShowPawnPayFormAction $action) { return $action($request, $pawn); }
    public function pay(Request $request, Pawn $pawn, PayPawnAction $action) { return $action($request, $pawn); }
}