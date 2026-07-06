<?php

namespace App\Http\Controllers;

use App\Actions\Transactions\CancelTransactionAction;
use App\Actions\Transactions\IndexTransactionAction;
use App\Actions\Transactions\ShowTransactionAction;
use App\Http\Requests\Transactions\CancelTransactionRequest;
use Illuminate\Http\Request;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class TransactionsController extends Controller
{
    public function index(Request $request, IndexTransactionAction $action): Response
    {
        return $action($request);
    }

    public function show(int $transaction, ShowTransactionAction $action): Response
    {
        return $action($transaction);
    }

    public function cancel(
        int $transaction,
        CancelTransactionRequest $request,
        CancelTransactionAction $action
    ): RedirectResponse {
        return $action($transaction, $request);
    }
}