<?php

namespace App\Actions\Products;

use App\Models\Department;
use App\Models\Product;
use Inertia\Inertia;
use Inertia\Response;

class EditProductAction
{
    public function __invoke(int $id): Response
    {
        $product = Product::query()->findOrFail($id);

        return Inertia::render('Products/Edit', [
            'product' => [
                'id' => $product->id,
                'department_id' => $product->department_id,
                'code' => $product->code,
                'description' => $product->description,
                'unit' => $product->unit,
                'min_price' => (float) $product->min_price,
                'max_price' => (float) $product->max_price,
                'is_active' => (bool) $product->is_active,
            ],
            'departments' => $this->departments(),
            'units' => $this->units(),
        ]);
    }

    private function departments(): array
    {
        return Department::query()
            ->where('is_active', true)
            ->orderBy('code')
            ->get()
            ->map(fn (Department $department) => [
                'id' => $department->id,
                'code' => $department->code,
                'description' => $department->description,
                'display_name' => $department->display_name,
                'color' => $department->color,
            ])
            ->values()
            ->all();
    }

    private function units(): array
    {
        return [
            'PIEZA',
            'GRAMOS',
            'KILOS',
            'LOTE',
            'PAR',
            'JUEGO',
            'UNIDAD',
        ];
    }
}