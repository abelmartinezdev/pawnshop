<?php

namespace App\Actions\Customers;

use App\Models\Customer;

class RestoreCustomerAction
{
    public function execute(int $id): void
    {
        $customer = Customer::withTrashed()->findOrFail($id);

        if ($customer->trashed()) {
            $customer->restore();
        }
    }
}