<?php

namespace App\Http\Controllers;

use App\Actions\Customers\CreateCustomerAction;
use App\Actions\Customers\DestroyCustomerAction;
use App\Actions\Customers\EditCustomerAction;
use App\Actions\Customers\IndexCustomerAction;
use App\Actions\Customers\RestoreCustomerAction;
use App\Actions\Customers\ShowCustomerAction;
use App\Actions\Customers\StoreCustomerAction;
use App\Actions\Customers\UpdateCustomerAction;
use App\Http\Requests\Customers\StoreCustomerRequest;
use App\Http\Requests\Customers\UpdateCustomerRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class CustomersController extends Controller
{
    public function index(Request $request, IndexCustomerAction $action): Response
    {
        return $action($request);
    }

    public function create(CreateCustomerAction $action): Response
    {
        return $action();
    }

    public function store(StoreCustomerRequest $request, StoreCustomerAction $action): RedirectResponse
    {
        return $action($request);
    }

    public function show(int $customer, ShowCustomerAction $action): Response
    {
        return $action($customer);
    }

    public function edit(int $customer, EditCustomerAction $action): Response
    {
        return $action($customer);
    }

    public function update(
        int $customer,
        UpdateCustomerRequest $request,
        UpdateCustomerAction $action
    ): RedirectResponse {
        return $action($customer, $request);
    }

    public function destroy(int $customer, DestroyCustomerAction $action): RedirectResponse
    {
        return $action($customer);
    }

    public function restore(int $id, RestoreCustomerAction $action): RedirectResponse
    {
        return $action($id);
    }
}