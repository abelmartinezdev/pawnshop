<?php

namespace App\Actions\Pawns;

use App\Models\Customer;
use App\Models\Department;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CreatePawnAction
{
    public function __invoke(Request $request): Response
    {
        $customerId = $request->integer('customer_id') ?: null;
        $departmentId = $request->integer('department_id') ?: null;

        $customers = Customer::query()
            ->withCount([
                'pawns as active_pawns_count' => fn (Builder $query) => $query
                    ->whereNull('paid_at')
                    ->whereNull('canceled_at'),
            ])
            ->orderBy('name')
            ->limit(150)
            ->get()
            ->map(fn (Customer $customer) => [
                'id' => $customer->id,
                'name' => $customer->name,
                'city' => $customer->city,
                'state' => $customer->state,
                'phone' => $customer->display_phone,
                'email' => $customer->email,
                'rfc' => $customer->rfc,
                'type_label' => $customer->identification_type_label,
                'code_id' => $customer->code_id,
                'inapam_code' => $customer->inapam_code,
                'active_pawns_count' => (int) $customer->active_pawns_count,
                'full_address' => $customer->full_address,
            ])
            ->values();

        $departments = Department::query()
            ->where('is_active', true)
            ->withCount([
                'products as active_products_count' => fn (Builder $query) => $query->where('is_active', true),
            ])
            ->orderBy('code')
            ->get()
            ->map(fn (Department $department) => [
                'id' => $department->id,
                'code' => $department->code,
                'description' => $department->description,
                'display_name' => $department->display_name,
                'auction' => (int) $department->auction,
                'term' => (int) $department->term,
                'loan_rate' => (float) $department->loan_rate,
                'daily_interest_rate' => (float) $department->daily_interest_rate,
                'monthly_interest_rate' => (float) $department->monthly_interest_rate,
                'iva_rate' => (float) $department->iva_rate,
                'cat_annual' => (float) $department->cat_annual,
                'cat_annual_noiva' => (float) $department->cat_annual_noiva,
                'color' => $department->color,
                'icon' => $department->icon,
                'active_products_count' => (int) $department->active_products_count,
            ])
            ->values();

        $products = Product::query()
            ->where('is_active', true)
            ->with(['department:id,code,description,color'])
            ->orderBy('code')
            ->get()
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'department_id' => $product->department_id,
                'code' => $product->code,
                'description' => $product->description,
                'display_name' => $product->display_name,
                'unit' => $product->unit,
                'min_price' => (float) $product->min_price,
                'max_price' => (float) $product->max_price,
                'department' => $product->department ? [
                    'id' => $product->department->id,
                    'code' => $product->department->code,
                    'description' => $product->department->description,
                    'color' => $product->department->color,
                ] : null,
            ])
            ->values();

        $selectedCustomer = $customerId
            ? $customers->firstWhere('id', $customerId)
            : null;

        $selectedDepartment = $departmentId
            ? $departments->firstWhere('id', $departmentId)
            : null;

        return Inertia::render('Pawns/Create', [
            'customers' => $customers,
            'departments' => $departments,
            'products' => $products,
            'selectedCustomer' => $selectedCustomer,
            'selectedDepartment' => $selectedDepartment,
            'filters' => [
                'customer_id' => $customerId,
                'department_id' => $departmentId,
            ],
            'cash' => [
                'office_cash' => (float) (auth()->user()?->office?->cash ?? 0),
            ],
        ]);
    }
}