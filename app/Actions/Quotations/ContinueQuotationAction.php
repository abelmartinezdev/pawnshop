<?php

namespace App\Actions\Quotations;

use App\Http\Requests\Quotations\ContinueQuotationRequest;
use App\Models\Department;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ContinueQuotationAction
{
    public function __invoke(
        ContinueQuotationRequest $request
    ): RedirectResponse {
        $department = Department::query()
            ->whereKey(
                $request->integer('department_id')
            )
            ->where('is_active', true)
            ->firstOrFail();

        $requestedItems = collect(
            $request->validated('items')
        );

        $productIds = $requestedItems
            ->pluck('product_id')
            ->map(
                fn ($id) => (int) $id
            )
            ->values();

        /*
         * Volvemos a consultar los productos desde la base
         * de datos para no confiar en precios enviados desde Vue.
         */
        $products = Product::query()
            ->whereIn('id', $productIds)
            ->where(
                'department_id',
                $department->id
            )
            ->where('is_active', true)
            ->get()
            ->keyBy('id');

        if (
            $products->count()
            !== $productIds->unique()->count()
        ) {
            throw ValidationException::withMessages([
                'items' =>
                    'Uno de los productos no pertenece al departamento de oro o ya no está disponible.',
            ]);
        }

        $lines = $requestedItems
            ->map(
                function (
                    array $item
                ) use (
                    $products
                ) {
                    /** @var Product $product */
                    $product = $products->get(
                        (int) $item['product_id']
                    );

                    $quantity = round(
                        (float) $item['quantity'],
                        3
                    );

                    $minimum = round(
                        (float) $product->min_price
                            * $quantity,
                        2
                    );

                    $maximum = round(
                        (float) $product->max_price
                            * $quantity,
                        2
                    );

                    return [
                        'product' => $product,

                        'quantity' => $quantity,

                        'minimum' => $minimum,

                        'maximum' => $maximum,

                        /*
                         * Utilizamos el punto medio del rango para
                         * distribuir proporcionalmente el préstamo.
                         */
                        'distribution_weight' => max(
                            (
                                $minimum
                                + $maximum
                            ) / 2,
                            0
                        ),
                    ];
                }
            )
            ->values();

        $suggestedMinimum = round(
            (float) $lines->sum('minimum'),
            2
        );

        $suggestedMaximum = round(
            (float) $lines->sum('maximum'),
            2
        );

        /*
         * El préstamo puede estar fuera del rango.
         */
        $loanAmount = round(
            (float) $request->validated(
                'total_import'
            ),
            2
        );

        $items = $this->distributeLoan(
            $lines,
            $loanAmount
        );

        $rangeStatus = $this->rangeStatus(
            $loanAmount,
            $suggestedMinimum,
            $suggestedMaximum
        );

        /*
         * Guardamos temporalmente la cotización para que
         * CreatePawnAction pueda precargarla.
         */
        session()->put(
            'quotation_draft',
            [
                'source' => 'gold_quotation',

                'department_id' => $department->id,

                'total_import' => $loanAmount,

                'suggested_minimum' =>
                    $suggestedMinimum,

                'suggested_maximum' =>
                    $suggestedMaximum,

                'range_status' => $rangeStatus,

                'warning' => $this->warningMessage(
                    status: $rangeStatus,
                    loan: $loanAmount,
                    minimum: $suggestedMinimum,
                    maximum: $suggestedMaximum
                ),

                'items' => $items,

                'created_at' => now()
                    ->toIso8601String(),
            ]
        );

        return redirect()->route(
            'pawns.create',
            [
                'department_id' => $department->id,

                'from_quotation' => 1,
            ]
        );
    }

    private function distributeLoan(
        Collection $lines,
        float $loanAmount
    ): array {
        $weightTotal = (float) $lines
            ->sum('distribution_weight');

        $count = $lines->count();

        $remaining = $loanAmount;

        return $lines
            ->values()
            ->map(
                function (
                    array $line,
                    int $index
                ) use (
                    $weightTotal,
                    $count,
                    $loanAmount,
                    &$remaining
                ) {
                    $isLast = $index === $count - 1;

                    /*
                     * El último artículo recibe el remanente
                     * para evitar diferencias por redondeo.
                     */
                    if ($isLast) {
                        $allocatedValue = round(
                            $remaining,
                            2
                        );
                    } else {
                        $ratio = $weightTotal > 0
                            ? (
                                (float) $line[
                                    'distribution_weight'
                                ]
                                / $weightTotal
                            )
                            : (
                                1
                                / max($count, 1)
                            );

                        $allocatedValue = round(
                            $loanAmount * $ratio,
                            2
                        );

                        $remaining = round(
                            $remaining
                                - $allocatedValue,
                            2
                        );
                    }

                    /** @var Product $product */
                    $product = $line['product'];

                    return [
                        'uid' => (string) Str::uuid(),

                        'product_id' => $product->id,

                        'product_code' => $product->code,

                        'product_name' => $product
                            ->description,

                        'unit' => $product->unit,

                        'quantity' => $line['quantity'],

                        'description' => $product
                            ->description,

                        'value' => $allocatedValue,

                        'min_price' => $line[
                            'minimum'
                        ],

                        'max_price' => $line[
                            'maximum'
                        ],

                        'unit_min_price' => (float) $product
                            ->min_price,

                        'unit_max_price' => (float) $product
                            ->max_price,
                    ];
                }
            )
            ->all();
    }

    private function rangeStatus(
        float $loan,
        float $minimum,
        float $maximum
    ): string {
        if (
            $minimum > 0
            && $loan < $minimum
        ) {
            return 'below';
        }

        if (
            $maximum > 0
            && $loan > $maximum
        ) {
            return 'above';
        }

        return 'within';
    }

    private function warningMessage(
        string $status,
        float $loan,
        float $minimum,
        float $maximum
    ): ?string {
        return match ($status) {
            'below' =>
                'El préstamo está $'
                . number_format(
                    $minimum - $loan,
                    2
                )
                . ' por debajo del mínimo sugerido.',

            'above' =>
                'El préstamo está $'
                . number_format(
                    $loan - $maximum,
                    2
                )
                . ' por encima del máximo sugerido.',

            default => null,
        };
    }
}