<?php

namespace App\Actions\Quotations;

use App\Models\Department;
use App\Models\Office;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class ShowQuotationAction
{
    public function __invoke(): Response
    {
        $office = $this->currentOffice();
        $department = $this->goldDepartment();

        $products = Product::query()
            ->where(
                'department_id',
                $department->id
            )
            ->where('is_active', true)
            ->orderBy('code')
            ->get()
            ->map(
                fn (Product $product) => [
                    'id' => $product->id,

                    'code' => $product->code,

                    'description' => $product->description,

                    'display_name' => $product->display_name,

                    'unit' => $product->unit,

                    'min_price' => (float) $product
                        ->min_price,

                    'max_price' => (float) $product
                        ->max_price,
                ]
            )
            ->values();

        abort_if(
            $products->isEmpty(),
            422,
            'El departamento de oro no tiene productos activos para cotizar.'
        );

        return Inertia::render(
            'Quotations/Index',
            [
                'department' => [
                    'id' => $department->id,

                    'code' => $department->code,

                    'description' => $department
                        ->description,

                    'term' => (int) $department->term,

                    'auction' => (int) $department
                        ->auction,

                    'loan_rate' => (float) $department
                        ->loan_rate,

                    'daily_interest_rate' => (float) $department
                        ->daily_interest_rate,

                    'monthly_interest_rate' => (float) $department
                        ->monthly_interest_rate,

                    'iva_rate' => (float) $department
                        ->iva_rate,

                    'color' => $department->color,
                ],

                'office' => [
                    'id' => $office->id,

                    'name' => $office->name,

                    'serie' => $office->serie,
                ],

                'products' => $products,

                'urls' => [
                    'continue' => Route::has(
                        'quotations.continue'
                    )
                        ? route('quotations.continue')
                        : null,

                    'exit' => Route::has('dashboard')
                        ? route('dashboard')
                        : null,
                ],
            ]
        );
    }

    private function currentOffice(): Office
    {
        $user = auth()->user();

        $companyId = (int) (
            session('company_id')
            ?: $user?->company_id
        );

        $officeId = (int) (
            session('office_id')
            ?: $user?->office_id
        );

        abort_unless(
            $companyId > 0 && $officeId > 0,
            404,
            'No hay una sucursal activa.'
        );

        return Office::query()
            ->whereKey($officeId)
            ->where('company_id', $companyId)
            ->firstOrFail();
    }

    private function goldDepartment(): Department
    {
        /*
         * Si agregas quotation_gold_department_id en config/core.php,
         * se utilizará ese departamento.
         *
         * Si no existe esa configuración, se buscará un departamento
         * con código ORO o cuya descripción contenga la palabra oro.
         */
        $configuredId = (int) config(
            'core.quotation_gold_department_id',
            0
        );

        return Department::query()
            ->where('is_active', true)
            ->when(
                $configuredId > 0,

                fn (Builder $query) => $query
                    ->whereKey($configuredId),

                fn (Builder $query) => $query
                    ->where(
                        function (
                            Builder $builder
                        ) {
                            $builder
                                ->whereRaw(
                                    'UPPER(code) = ?',
                                    ['ORO']
                                )
                                ->orWhere(
                                    'description',
                                    'like',
                                    '%oro%'
                                );
                        }
                    )
            )
            ->orderBy('id')
            ->firstOrFail();
    }
}