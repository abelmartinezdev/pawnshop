<?php

namespace App\Actions\Customers;

use App\Models\Customer;
use Inertia\Inertia;
use Inertia\Response;

class EditCustomerAction
{
    public function __invoke(int $id): Response
    {
        $customer = Customer::query()->findOrFail($id);

        return Inertia::render('Customers/Edit', [
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'state' => $customer->state,
                'city' => $customer->city,
                'address' => $customer->address,
                'phone' => $customer->phone,
                'mobile' => $customer->mobile,
                'email' => $customer->email,
                'rfc' => $customer->rfc,
                'code_id' => $customer->code_id,
                'type_id' => $customer->type_id,
                'type_label' => $customer->identification_type_label,
                'inapam_code' => $customer->inapam_code,
            ],
            'identificationTypes' => Customer::identificationTypes(),
        ]);
    }
}