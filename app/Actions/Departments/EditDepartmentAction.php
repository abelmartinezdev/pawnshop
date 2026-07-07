<?php

namespace App\Actions\Departments;

use App\Models\Department;
use Inertia\Inertia;
use Inertia\Response;

class EditDepartmentAction
{
    public function __invoke(int $id): Response
    {
        $department = Department::query()->findOrFail($id);

        return Inertia::render('Departments/Edit', [
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
            ],
            'dayOptions' => range(1, 360),
        ]);
    }
}