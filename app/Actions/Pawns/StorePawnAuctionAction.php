<?php

namespace App\Actions\Pawns;

use App\Actions\Pawns\Concerns\CalculatesPawnAuction;
use App\Http\Requests\Pawns\StorePawnAuctionRequest;
use App\Models\Auction;
use App\Models\Office;
use App\Models\Pawn;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Throwable;

class StorePawnAuctionAction
{
    use CalculatesPawnAuction;

    public function __invoke(
        StorePawnAuctionRequest $request,
        Pawn $pawn
    ): RedirectResponse {
        try {
            DB::transaction(
                function () use ($pawn) {
                    /*
                     * Bloqueamos el empeño para evitar que dos usuarios
                     * intenten mandarlo a remate al mismo tiempo.
                     */
                    $pawn = Pawn::query()
                        ->with([
                            'items.product',
                        ])
                        ->lockForUpdate()
                        ->findOrFail($pawn->id);

                    $this->assertPawnBelongsToCurrentContext(
                        $pawn
                    );

                    if (! $pawn->canBeAuctioned()) {
                        throw ValidationException::withMessages([
                            'confirmation' => 'Este empeño ya no puede pasar a remate.',
                        ]);
                    }

                    if ($pawn->items->isEmpty()) {
                        throw ValidationException::withMessages([
                            'confirmation' => 'El empeño no tiene prendas registradas.',
                        ]);
                    }

                    /*
                     * Verificación adicional para evitar duplicados.
                     */
                    if (
                        Auction::query()
                            ->where('pawn_id', $pawn->id)
                            ->exists()
                    ) {
                        throw ValidationException::withMessages([
                            'confirmation' => 'Este empeño ya tiene artículos registrados en remate.',
                        ]);
                    }

                    /*
                     * Bloqueamos la oficina solamente para obtener
                     * un saldo de caja consistente para la transacción.
                     *
                     * No modificamos offices.cash.
                     */
                    $office = Office::query()
                        ->lockForUpdate()
                        ->findOrFail($pawn->office_id);

                    $calculation = $this->calculateAuction(
                        $pawn
                    );

                    $auctionIds = [];

                    foreach (
                        $calculation['items']
                        as $item
                    ) {
                        $auction = Auction::query()->create([
                            'company_id' => $pawn->company_id,
                            'office_id' => $pawn->office_id,
                            'pawn_id' => $pawn->id,

                            'pawn_item_id' => $item['pawn_item_id'],
                            'product_id' => $item['product_id'],

                            'created_by' => auth()->id(),

                            'unit_number' => $item['unit_number'],
                            'quantity' => $item['quantity'],
                            'auction_mode' => $item['auction_mode'],

                            'description' => $item['description'],

                            'source_value' => $item['source_value'],
                            'value' => $item['value'],

                            'interest_amount' => $item['interest_amount'],
                            'total' => $item['total'],

                            'commission' => 0,

                            'active' => $item['active'],
                            'not_sell' => $item['not_sell'],
                        ]);

                        $auctionIds[] = $auction->id;
                    }

                    /*
                     * forceFill permite guardar auction_by aunque todavía
                     * no se encuentre en $fillable del modelo Pawn.
                     */
                    $pawn->forceFill([
                        'auction_at' => now(),
                        'auction_by' => auth()->id(),
                    ])->save();

                    $this->createAuditTransaction(
                        pawn: $pawn,
                        office: $office,
                        calculation: $calculation,
                        auctionIds: $auctionIds
                    );
                },
                3
            );

            return redirect()
                ->route(
                    'pawns.show',
                    $pawn->id
                )
                ->with(
                    'success',
                    "¡El empeño {$pawn->folio} fue enviado a remate correctamente!"
                );
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route(
                    'pawns.show',
                    $pawn->id
                )
                ->with(
                    'error',
                    'No se pudo enviar el empeño a remate. Inténtalo nuevamente.'
                );
        }
    }

    private function createAuditTransaction(
        Pawn $pawn,
        Office $office,
        array $calculation,
        array $auctionIds
    ): void {
        $transaction = new Transaction();

        /*
         * safeForceFill verifica qué columnas existen realmente
         * en transactions antes de llenar el modelo.
         */
        $this->safeForceFill(
            $transaction,
            [
                'company_id' => $pawn->company_id,
                'office_id' => $office->id,
                'pawn_id' => $pawn->id,

                'user_id' => auth()->id(),
                'created_by' => auth()->id(),

                'type' => 'pawn_to_auction',

                /*
                 * Pasar a remate no representa una entrada
                 * ni una salida de efectivo.
                 */
                'amount' => 0,
                'balance' => (float) $office->cash,
                'payment_type' => 'cash',

                'comments' => 'Pase a remate del folio '
                    . $pawn->folio
                    . '.',

                'data' => [
                    'auction_ids' => $auctionIds,

                    'auction_items_count' => count(
                        $auctionIds
                    ),

                    'days_elapsed' => $calculation[
                        'days_elapsed'
                    ],

                    'days_charged' => $calculation[
                        'days_charged'
                    ],

                    'interest_is_capped' => $calculation[
                        'interest_is_capped'
                    ],

                    'daily_interest' => $calculation[
                        'daily_interest'
                    ],

                    'gross_interest' => $calculation[
                        'gross_interest'
                    ],

                    'discount_amount' => $calculation[
                        'discount_amount'
                    ],

                    'paid_amount' => $calculation[
                        'paid_amount'
                    ],

                    'interest' => $calculation[
                        'interest'
                    ],

                    'principal' => $calculation[
                        'principal'
                    ],

                    'total' => $calculation[
                        'total'
                    ],
                ],
            ]
        );

        $transaction->save();
    }

    private function assertPawnBelongsToCurrentContext(
        Pawn $pawn
    ): void {
        $companyId = (int) (
            session('company_id')
            ?: auth()->user()?->company_id
        );

        $officeId = (int) (
            session('office_id')
            ?: auth()->user()?->office_id
        );

        abort_unless(
            $companyId > 0
                && (int) $pawn->company_id === $companyId,
            404
        );

        abort_unless(
            $officeId > 0
                && (int) $pawn->office_id === $officeId,
            404
        );
    }

    private function safeForceFill(
        Model $model,
        array $values
    ): void {
        $table = $model->getTable();

        $filtered = collect($values)
            ->filter(
                fn (
                    mixed $value,
                    string $column
                ) => Schema::hasColumn(
                    $table,
                    $column
                )
            )
            ->all();

        $model->forceFill($filtered);
    }
}