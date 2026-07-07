<?php

namespace App\Http\Controllers;

use App\Actions\Closures\CreateClosureAction;
use App\Actions\Closures\IndexClosureAction;
use App\Actions\Closures\ShowClosureAction;
use App\Actions\Closures\StoreClosureAction;
use App\Http\Requests\Closures\StoreClosureRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class ClosuresController extends Controller
{
    public function index(Request $request, IndexClosureAction $action): Response
    {
        return $action($request);
    }

    public function create(CreateClosureAction $action): Response
    {
        return $action();
    }

    public function store(StoreClosureRequest $request, StoreClosureAction $action): RedirectResponse
    {
        return $action($request);
    }

    public function show(int $closure, ShowClosureAction $action): Response
    {
        return $action($closure);
    }
}