<?php

namespace App\Actions\Pawns\Concerns;

use App\Models\Auction;
use App\Models\Pawn;
use App\Models\PawnItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

trait CalculatesPawnAuction
{
    protected function calculateAuction(Pawn $pawn): array
    {
        $pawn->loadMissing('items.product');

        /*
         * La versión anterior utilizaba days2pay y limitaba
         * el interés a un máximo de 72 días.
         */
        $daysElapsed = max((int) $pawn->days2pay, 0);
        $daysCharged = min($daysElapsed, 1);

        $dailyInterest = round(
            (float) $pawn->getDailyInterest(true),
            2
        );

        $grossInterest = round(
            $dailyInterest * $daysCharged,
            2
        );

        /*
         * Aplicamos los descuentos de días que ya existan.
         */
        $discountRequested = round(
            (float) $pawn->interestDiscountAmount(true),
            2
        );

        $discountApplied = min(
            $discountRequested,
            $grossInterest
        );

        $interestAfterDiscount = max(
            $grossInterest - $discountApplied,
            0
        );

        /*
         * También descontamos los abonos que ya se hayan
         * realizado al interés.
         */
        $paidRequested = round(
            (float) $pawn->paidAmount(),
            2
        );

        $paidApplied = min(
            $paidRequested,
            $interestAfterDiscount
        );

        $interest = round(
            max($interestAfterDiscount - $paidApplied, 0),
            2
        );

        $principal = round(
            (float) $pawn->total,
            2
        );

        /*
         * Convertimos las prendas a los registros individuales
         * o agrupados que deberán guardarse en auctions.
         */
        $expandedItems = $this->expandAuctionItems(
            $pawn->items
        );

        $weightTotal = round(
            (float) $expandedItems->sum('source_value'),
            6
        );

        $itemCount = $expandedItems->count();

        $principalRemaining = $principal;
        $interestRemaining = $interest;

        /*
         * Distribuimos proporcionalmente el préstamo y el interés.
         *
         * Esto corrige el problema de la versión vieja, que agregaba
         * el interés completo a cada prenda y duplicaba el total.
         */
        $items = $expandedItems
            ->values()
            ->map(function (
                array $item,
                int $index
            ) use (
                $itemCount,
                $weightTotal,
                $principal,
                $interest,
                &$principalRemaining,
                &$interestRemaining
            ) {
                $isLast = $index === $itemCount - 1;

                if ($isLast) {
                    /*
                     * Al último registro le asignamos los remanentes
                     * para evitar diferencias por redondeo.
                     */
                    $principalAmount = round(
                        $principalRemaining,
                        2
                    );

                    $interestAmount = round(
                        $interestRemaining,
                        2
                    );
                } else {
                    $ratio = $weightTotal > 0
                        ? (
                            (float) $item['source_value']
                            / $weightTotal
                        )
                        : (
                            1 / max($itemCount, 1)
                        );

                    $principalAmount = round(
                        $principal * $ratio,
                        2
                    );

                    $interestAmount = round(
                        $interest * $ratio,
                        2
                    );

                    $principalRemaining = round(
                        $principalRemaining - $principalAmount,
                        2
                    );

                    $interestRemaining = round(
                        $interestRemaining - $interestAmount,
                        2
                    );
                }

                return array_merge($item, [
                    'value' => $principalAmount,
                    'interest_amount' => $interestAmount,
                    'total' => round(
                        $principalAmount + $interestAmount,
                        2
                    ),
                ]);
            })
            ->all();

        return [
            'days_elapsed' => $daysElapsed,
            'days_charged' => $daysCharged,
            'interest_is_capped' => $daysElapsed > 72,

            'daily_interest' => $dailyInterest,
            'gross_interest' => $grossInterest,

            'discount_amount' => round(
                $discountApplied,
                2
            ),

            'paid_amount' => round(
                $paidApplied,
                2
            ),

            'interest' => $interest,
            'principal' => $principal,

            'total' => round(
                $principal + $interest,
                2
            ),

            'items' => $items,
        ];
    }

    private function expandAuctionItems(
        Collection $pawnItems
    ): Collection {
        return $pawnItems->flatMap(
            function (PawnItem $item) {
                $quantity = max(
                    (float) $item->quantity,
                    0.001
                );

                $mode = $this->auctionMode($item);

                $units = $this->shouldSplitIntoUnits(
                    $item,
                    $mode
                )
                    ? max((int) round($quantity), 1)
                    : 1;

                $sourceValuePerUnit = round(
                    (float) $item->value / $units,
                    3
                );

                return collect(range(1, $units))
                    ->map(
                        fn (int $unitNumber) => [
                            'pawn_item_id' => $item->id,
                            'product_id' => $item->product_id,

                            'product_code' => $item->product?->code,

                            'product_name' => $item->product?->description
                                ?: 'Producto sin catálogo',

                            'unit' => $item->product?->unit,

                            'unit_number' => $unitNumber,

                            'quantity' => $units > 1
                                ? 1
                                : $quantity,

                            'auction_mode' => $mode,

                            'description' => (string) $item->description,

                            'source_value' => $sourceValuePerUnit,

                            'active' => $mode
                                !== Auction::MODE_NOT_SELL,

                            'not_sell' => $mode
                                === Auction::MODE_NOT_SELL,
                        ]
                    );
            }
        );
    }

    private function auctionMode(
        PawnItem $item
    ): string {
        $mode = (string) (
            $item->product?->auction_mode
            ?: Auction::MODE_SELLABLE
        );

        $validModes = [
            Auction::MODE_SELLABLE,
            Auction::MODE_GROUPED,
            Auction::MODE_NOT_SELL,
        ];

        return in_array($mode, $validModes, true)
            ? $mode
            : Auction::MODE_SELLABLE;
    }

    private function shouldSplitIntoUnits(
        PawnItem $item,
        string $mode
    ): bool {
        if ($mode !== Auction::MODE_SELLABLE) {
            return false;
        }

        $quantity = (float) $item->quantity;

        /*
         * Las cantidades fraccionarias se mantienen agrupadas.
         */
        if (
            $quantity < 1
            || floor($quantity) !== $quantity
            || $quantity > 500
        ) {
            return false;
        }

        /*
         * Las unidades continuas como gramos, kilogramos o litros
         * tampoco se dividen en registros individuales.
         */
        $unit = Str::lower(
            trim((string) $item->product?->unit)
        );

        $continuousUnits = [
            'g',
            'gr',
            'grs',
            'gramo',
            'gramos',

            'kg',
            'kilogramo',
            'kilogramos',

            'ml',
            'mililitro',
            'mililitros',

            'l',
            'lt',
            'litro',
            'litros',

            'cm',
            'm',
            'metro',
            'metros',
        ];

        return ! in_array(
            $unit,
            $continuousUnits,
            true
        );
    }
}