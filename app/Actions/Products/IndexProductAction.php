<?php

namespace App\Actions\Products;

use App\Models\Department;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IndexProductAction
{
    public function __invoke(Request $request): Response
    {
        $search = trim((string) $request->input('search', ''));
        $status = $request->input('status', 'active');
        $departmentId = $request->input('department_id');
        $perPage = (int) $request->input('per_page', 15);

        $query = Product::query()
            ->with(['department:id,code,description,color'])
            ->withCount('pawnItems')
            ->when($status === 'deleted', fn (Builder $query) => $query->onlyTrashed())
            ->when($status === 'all', fn (Builder $query) => $query->withTrashed())
            ->when($status === 'inactive', fn (Builder $query) => $query->where('is_active', false))
            ->when($status === 'active', fn (Builder $query) => $query->where('is_active', true))
            ->when($departmentId, fn (Builder $query) => $query->where('department_id', $departmentId))
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $builder) use ($search) {
                    $builder
                        ->where('code', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('unit', 'like', "%{$search}%")
                        ->orWhereHas('department', function (Builder $departmentQuery) use ($search) {
                            $departmentQuery
                                ->where('code', 'like', "%{$search}%")
                                ->orWhere('description', 'like', "%{$search}%");
                        });
                });
            });

        $products = $query
            ->orderBy('code')
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (Product $product) => [
                'id' => $product->id,
                'department_id' => $product->department_id,
                'code' => $product->code,
                'description' => $product->description,
                'unit' => $product->unit,
                'min_price' => (float) $product->min_price,
                'max_price' => (float) $product->max_price,
                'price_range' => $product->price_range,
                'is_active' => (bool) $product->is_active,
                'pawn_items_count' => (int) $product->pawn_items_count,
                'created_at' => $product->created_at?->format('d/m/Y H:i'),
                'updated_at' => $product->updated_at?->format('d/m/Y H:i'),
                'deleted_at' => $product->deleted_at?->format('d/m/Y H:i'),
                'is_deleted' => $product->trashed(),
                'department' => $product->department ? [
                    'id' => $product->department->id,
                    'code' => $product->department->code,
                    'description' => $product->department->description,
                    'color' => $product->department->color,
                ] : null,
            ]);

        return Inertia::render('Products/Index', [
            'products' => $products,
            'summary' => [
                'total' => Product::query()->withTrashed()->count(),
                'active' => Product::query()->where('is_active', true)->count(),
                'inactive' => Product::query()->where('is_active', false)->count(),
                'deleted' => Product::onlyTrashed()->count(),
            ],
            'filters' => [
                'search' => $search,
                'status' => $status,
                'department_id' => $departmentId,
                'per_page' => $perPage,
            ],
            'options' => [
                'statuses' => [
                    ['value' => 'active', 'label' => 'Activos'],
                    ['value' => 'inactive', 'label' => 'Inactivos'],
                    ['value' => 'deleted', 'label' => 'Eliminados'],
                    ['value' => 'all', 'label' => 'Todos'],
                ],
                'departments' => $this->departments(),
            ],
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
}