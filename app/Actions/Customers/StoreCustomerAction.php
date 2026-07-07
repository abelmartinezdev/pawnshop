<?php

namespace App\Actions\Customers;

use App\Http\Requests\Customers\StoreCustomerRequest;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;

class StoreCustomerAction
{
    public function __invoke(StoreCustomerRequest $request): RedirectResponse
    {
        $customer = Customer::query()->create($request->validated());

        return redirect()
            ->route('customers.show', $customer->id)
            ->with('success', 'Cliente registrado correctamente.');
    }
}