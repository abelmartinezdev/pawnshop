<?php

namespace App\Actions\Expenses;

use Inertia\Inertia;
use Inertia\Response;

class ShowExpenseAction
{
    public function __invoke(int $id): Response
    {
        return Inertia::render('Expenses/Show', [
            'expenseId' => $id,
        ]);
    }
}