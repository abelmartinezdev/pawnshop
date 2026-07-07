<?php

namespace App\Actions\Customers;

use App\Models\Customer;
use Illuminate\Http\RedirectResponse;

class DestroyCustomerAction
{
    public function __invoke(int $id): RedirectResponse
    {
        $customer = Customer::query()->findOrFail($id);

        $customer->delete();

        return redirect()
            ->route('customers.index')
            ->with('success', 'Cliente eliminado correctamente.');
    }
}