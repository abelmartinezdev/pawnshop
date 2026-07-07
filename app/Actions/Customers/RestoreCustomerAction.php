<?php

namespace App\Actions\Customers;

use App\Models\Customer;
use Illuminate\Http\RedirectResponse;

class RestoreCustomerAction
{
    public function __invoke(int $id): RedirectResponse
    {
        $customer = Customer::query()
            ->onlyTrashed()
            ->findOrFail($id);

        $customer->restore();

        return redirect()
            ->route('customers.show', $customer->id)
            ->with('success', 'Cliente restaurado correctamente.');
    }
}