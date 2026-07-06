<?php

namespace App\Http\Controllers;

use App\Actions\Expenses\CancelExpenseAction;
use App\Actions\Expenses\CreateExpenseAction;
use App\Actions\Expenses\IndexExpenseAction;
use App\Actions\Expenses\ShowExpenseAction;
use App\Actions\Expenses\StoreExpenseAction;
use App\Http\Requests\Expenses\CancelExpenseRequest;
use App\Http\Requests\Expenses\StoreExpenseRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class ExpensesController extends Controller
{
    public function index(Request $request, IndexExpenseAction $action): Response
    {
        return $action($request);
    }

    public function create(CreateExpenseAction $action): Response
    {
        return $action();
    }

    public function store(StoreExpenseRequest $request, StoreExpenseAction $action): RedirectResponse
    {
        return $action($request);
    }

    public function show(int $expense, ShowExpenseAction $action): Response
    {
        return $action($expense);
    }

    public function cancel(
        int $expense,
        CancelExpenseRequest $request,
        CancelExpenseAction $action
    ): RedirectResponse {
        return $action($expense, $request);
    }
}