<?php

namespace App\Actions\Departments;

use App\Models\Department;
use App\Models\Product;
use Inertia\Inertia;
use Inertia\Response;

class ShowDepartmentAction
{
    public function __invoke(int $id): Response
    {
        $department = Department::query()
            ->withTrashed()
            ->withCount('products')
            ->findOrFail($id);

        $products = Product::query()
            ->where('department_id', $department->id)
            ->latest('id')
            ->take(12)
            ->get()
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'code' => $product->code,
                'description' => $product->description,
                'unit' => $product->unit,
                'min_price' => (float) $product->min_price,
                'max_price' => (float) $product->max_price,
                'is_active' => (bool) $product->is_active,
            ])
            ->values();

        return Inertia::render('Departments/Show', [
            'department' => [
                'id' => $department->id,
                'code' => $department->code,
                'description' => $department->description,
                'auction' => $department->auction,
                'term' => $department->term,
                'loan_rate' => (float) $department->loan_rate,
                'daily_interest_rate' => (float) $department->daily_interest_rate,
                'monthly_interest_rate' => (float) $department->monthly_interest_rate,
                'iva_rate' => (float) $department->iva_rate,
                'cat_annual' => (float) $department->cat_annual,
                'cat_annual_noiva' => (float) $department->cat_annual_noiva,
                'color' => $department->color,
                'icon' => $department->icon,
                'is_active' => (bool) $department->is_active,
                'products_count' => (int) $department->products_count,
                'created_at' => $department->created_at?->format('d/m/Y H:i'),
                'updated_at' => $department->updated_at?->format('d/m/Y H:i'),
                'deleted_at' => $department->deleted_at?->format('d/m/Y H:i'),
                'is_deleted' => $department->trashed(),
                'products' => $products,
            ],
        ]);
    }
}