<?php

namespace App\Actions\Customers;

use App\Models\Customer;

class CreateCustomerAction
{
    public function execute(array $data): Customer
    {
        $data['name'] = trim($data['name']);

        // Normaliza RFC opcional
        if (isset($data['rfc']) && $data['rfc'] !== null) {
            $data['rfc'] = strtoupper(trim($data['rfc']));
        }

        return Customer::create($data);
    }
}