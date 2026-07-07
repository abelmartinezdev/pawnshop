<?php

namespace App\Actions\Customers;

use App\Models\Customer;
use Inertia\Inertia;
use Inertia\Response;

class CreateCustomerAction
{
    public function __invoke(): Response
    {
        return Inertia::render('Customers/Create', [
            'identificationTypes' => Customer::identificationTypes(),
        ]);
    }
}