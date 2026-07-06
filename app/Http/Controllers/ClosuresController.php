<?php

namespace App\Http\Controllers;

use App\Actions\Closures\CreateClosureAction;
use App\Actions\Closures\ShowClosureAction;
use App\Actions\Closures\StoreClosureAction;
use App\Actions\Closures\TicketClosureAction;
use Illuminate\Http\Request;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class ClosuresController extends Controller
{
    public function create(CreateClosureAction $action): Response
    {
        return $action();
    }

    public function store(Request $request, StoreClosureAction $action): RedirectResponse
    {
        return $action($request);
    }

    public function show(int $closure, ShowClosureAction $action): Response
    {
        return $action($closure);
    }

    public function ticket(int $closure, TicketClosureAction $action): Response
    {
        return $action($closure);
    }
}