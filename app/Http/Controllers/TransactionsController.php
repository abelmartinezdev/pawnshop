<?php

namespace App\Http\Controllers;

use App\Actions\Transactions\CancelTransactionAction;
use App\Actions\Transactions\ShowTransactionAction;
use Illuminate\Http\Request;

class TransactionsController extends Controller
{
    public function __construct(){ $this->middleware('selectoffice'); }

    public function show(int $id, ShowTransactionAction $a) { return $a($id); }
    public function cancel(Request $r, int $id, CancelTransactionAction $a) { return $a($r,$id); }
}