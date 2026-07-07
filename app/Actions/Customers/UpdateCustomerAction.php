<?php

namespace App\Actions\Customers;

use App\Http\Requests\Customers\UpdateCustomerRequest;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;

class UpdateCustomerAction
{
    public function __invoke(int $id, UpdateCustomerRequest $request): RedirectResponse
    {
        $customer = Customer::query()->findOrFail($id);

        $customer->update($request->validated());

        return redirect()
            ->route('customers.show', $customer->id)
            ->with('success', 'Cliente actualizado correctamente.');
    }
}