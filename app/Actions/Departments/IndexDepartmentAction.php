<?php

namespace App\Actions\Departments;

use App\Models\Department;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IndexDepartmentAction
{
    public function __invoke(Request $request): Response
    {
        $search = trim((string) $request->input('search', ''));
        $status = $request->input('status', 'active');
        $perPage = (int) $request->input('per_page', 15);

        $query = Department::query()
            ->withCount('products')
            ->when($status === 'deleted', fn (Builder $query) => $query->onlyTrashed())
            ->when($status === 'all', fn (Builder $query) => $query->withTrashed())
            ->when($status === 'inactive', fn (Builder $query) => $query->where('is_active', false))
            ->when($status === 'active', fn (Builder $query) => $query->where('is_active', true))
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $builder) use ($search) {
                    $builder
                        ->where('code', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('loan_rate', 'like', "%{$search}%")
                        ->orWhere('daily_interest_rate', 'like', "%{$search}%")
                        ->orWhere('monthly_interest_rate', 'like', "%{$search}%");
                });
            });

        $departments = $query
            ->orderBy('code')
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (Department $department) => [
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
            ]);

        return Inertia::render('Departments/Index', [
            'departments' => $departments,
            'summary' => [
                'total' => Department::query()->withTrashed()->count(),
                'active' => Department::query()->where('is_active', true)->count(),
                'inactive' => Department::query()->where('is_active', false)->count(),
                'deleted' => Department::onlyTrashed()->count(),
            ],
            'filters' => [
                'search' => $search,
                'status' => $status,
                'per_page' => $perPage,
            ],
            'options' => [
                'statuses' => [
                    ['value' => 'active', 'label' => 'Activos'],
                    ['value' => 'inactive', 'label' => 'Inactivos'],
                    ['value' => 'deleted', 'label' => 'Eliminados'],
                    ['value' => 'all', 'label' => 'Todos'],
                ],
            ],
        ]);
    }
}