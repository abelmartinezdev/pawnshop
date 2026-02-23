<?php

namespace App\Http\Controllers;

use App\Actions\Customers\IndexCustomersAction;
use App\Actions\Customers\CreateCustomerAction;
use App\Actions\Customers\UpdateCustomerAction;
use App\Actions\Customers\DeleteCustomerAction;
use App\Actions\Customers\RestoreCustomerAction;
use App\Http\Requests\Customers\StoreCustomerRequest;
use App\Http\Requests\Customers\UpdateCustomerRequest;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomersController extends Controller
{
    public function index(Request $request, IndexCustomersAction $action)
    {
        return $action($request);
    }

    public function create()
    {
        return inertia('Customers/Create');
    }

    public function store(StoreCustomerRequest $request, CreateCustomerAction $action)
    {
        $customer = $action->execute($request->validated());

        return redirect()->route('customers.edit', $customer->id)
            ->with('flash', ['type' => 'success', 'message' => 'Cliente creado.']);
    }

    public function edit(Customer $customer)
    {
        return inertia('Customers/Edit', [
            'customer' => $customer,
        ]);
    }

    public function update(Customer $customer, UpdateCustomerRequest $request, UpdateCustomerAction $action)
    {
        $action->execute($customer, $request->validated());

        return redirect()->route('customers.edit', $customer->id)
            ->with('flash', ['type' => 'success', 'message' => 'Cliente actualizado.']);
    }

    public function destroy(Customer $customer, DeleteCustomerAction $action)
    {
        $action->execute($customer);

        return redirect()->route('customers.index')
            ->with('flash', ['type' => 'success', 'message' => 'Cliente archivado.']);
    }

    public function restore(int $id, RestoreCustomerAction $action)
    {
        $action->execute($id);

        return redirect()->route('customers.index')
            ->with('flash', ['type' => 'success', 'message' => 'Cliente restaurado.']);
    }
}