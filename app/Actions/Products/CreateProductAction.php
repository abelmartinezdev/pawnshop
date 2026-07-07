<?php

namespace App\Actions\Products;

use App\Models\Department;
use Inertia\Inertia;
use Inertia\Response;

class CreateProductAction
{
    public function __invoke(): Response
    {
        return Inertia::render('Products/Create', [
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
                'loan_rate' => (float) $department->loan_rate,
                'daily_interest_rate' => (float) $department->daily_interest_rate,
                'monthly_interest_rate' => (float) $department->monthly_interest_rate,
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