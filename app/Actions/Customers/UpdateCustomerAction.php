<?php

namespace App\Actions\Customers;

use App\Models\Customer;

class UpdateCustomerAction
{
    public function execute(Customer $customer, array $data): Customer
    {
        $data['name'] = trim($data['name']);

        if (isset($data['rfc']) && $data['rfc'] !== null) {
            $data['rfc'] = strtoupper(trim($data['rfc']));
        }

        $customer->update($data);

        return $customer;
    }
}