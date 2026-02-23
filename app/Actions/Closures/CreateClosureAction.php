<?php

namespace App\Actions\Closures;

use App\Modules\Closures\Closure;
use App\Modules\Transactions\TransactionsRepo;
use Carbon\Carbon;
use Inertia\Inertia;

class CreateClosureAction
{
    public function __construct(private TransactionsRepo $transactions) {}

    public function __invoke()
    {
        $office = \Pawnshop::office();
        $company = $office->company;

        $last_closure = Closure::where('office_id', $office->id)->latest('created_at')->first();

        $start = null;
        if ($last_closure) {
            $start = Carbon::parse($last_closure->created_at->format('Y-m-d'))->addDay();
        }

        $closing_pending = false;
        if ($last_closure && !$last_closure->created_at->isYesterday() && !$last_closure->created_at->isToday()) {
            $closing_pending = true;
        }

        // Ojo: si $start es null, tu legacy truena con $start->copy()
        $total_interest = 0;
        if ($start) {
            $total_interest = $this->transactions->interestByDates($start, $company, $office, $start->copy()->endOfDay());
        }

        $closures = $office->closures()->latest('created_at')->paginate(10);

        return Inertia::render('Closures/Create', compact(
            'office',
            'company',
            'last_closure',
            'total_interest',
            'closures',
            'closing_pending'
        ));
    }
}