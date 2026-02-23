<?php

namespace App\Http\Controllers;

use App\Actions\Closures\CreateClosureAction;
use App\Actions\Closures\ShowClosureAction;
use App\Actions\Closures\StoreClosureAction;
use App\Actions\Closures\TicketClosureAction;
use Illuminate\Http\Request;

class ClosuresController extends Controller
{
    public function __construct(){ $this->middleware('selectoffice'); }

    public function create(CreateClosureAction $a){ return $a(); }
    public function store(Request $r, StoreClosureAction $a){ return $a($r); }
    public function show(int $id, ShowClosureAction $a){ return $a($id); }
    public function ticket(int $id, TicketClosureAction $a){ return $a($id); }
}