<?php

namespace App\Http\Controllers;

use App\Actions\Incomes\CancelIncomeAction;
use App\Actions\Incomes\CreateIncomeAction;
use App\Actions\Incomes\IndexIncomeAction;
use App\Actions\Incomes\ShowIncomeAction;
use App\Actions\Incomes\StoreIncomeAction;
use App\Http\Requests\Incomes\CancelIncomeRequest;
use App\Http\Requests\Incomes\StoreIncomeRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class IncomesController extends Controller
{
    public function index(Request $request, IndexIncomeAction $action): Response
    {
        return $action($request);
    }

    public function create(CreateIncomeAction $action): Response
    {
        return $action();
    }

    public function store(StoreIncomeRequest $request, StoreIncomeAction $action): RedirectResponse
    {
        return $action($request);
    }

    public function show(int $income, ShowIncomeAction $action): Response
    {
        return $action($income);
    }

    public function cancel(
        int $income,
        CancelIncomeRequest $request,
        CancelIncomeAction $action
    ): RedirectResponse {
        return $action($income, $request);
    }
}