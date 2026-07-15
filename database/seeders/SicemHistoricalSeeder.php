<?php

namespace Database\Seeders;

use App\Models\Auction;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Department;
use App\Models\Office;
use App\Models\Pawn;
use App\Models\PawnItem;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Database\Factories\AuctionFactory;
use Database\Factories\PawnFactory;
use Database\Factories\PawnItemFactory;
use Database\Factories\TransactionFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class SicemHistoricalSeeder extends Seeder
{
    private const DEMO_OFFICE_CODE = 'HIST';
    private const DEMO_MARKER = '[DEMO-HISTORICO]';
    private const OPENING_CASH = 250000.00;

    private Company $company;
    private Office $office;
    private User $user;
    private Collection $customers;
    private Collection $products;
    private int $nextFolio = 900001;
    private array $events = [];

    public function run(): void
    {
        $this->guardEnvironment();
        $this->assertRequiredSchema();

        $this->company = $this->resolveCompany();
        $this->user = $this->resolveUser($this->company);
        $this->customers = $this->resolveCustomers($this->company);
        $this->products = $this->resolveProducts();

        Model::withoutEvents(function () {
            DB::transaction(function () {
                $this->office = $this->resolveDemoOffice();
                $this->cleanPreviousDemoData();
                $this->resetDemoOffice();
                $this->createHistoricalScenarios();
                $this->processCashEvents();
            }, 3);
        });

        $this->command?->newLine();
        $this->command?->info('Escenario histórico de SICEM creado correctamente.');
        $this->command?->line("Empresa: {$this->company->id} · {$this->company->name}");
        $this->command?->line("Sucursal: {$this->office->id} · {$this->office->name} ({$this->office->code})");
        $this->command?->line('Folios: H900001 en adelante');
        $this->command?->line('Caja final: $'.number_format((float) $this->office->fresh()->cash, 2));
        $this->command?->warn('Selecciona la sucursal HIST dentro de SICEM para probar los datos.');
    }

    private function createHistoricalScenarios(): void
    {
        $active = $this->createPawn(
            label: 'Vigente reciente',
            customerIndex: 0,
            createdAt: now()->subDays(5)->setTime(10, 15),
            expirationOffset: 25,
            auctionOffset: 55,
            items: [
                $this->item(0, 1, 2600, 'Cadena en buen estado con broche funcional.'),
                $this->item(1, 1, 1400, 'Dije revisado, sin daños visibles.'),
            ]
        );

        $nearExpiration = $this->createPawn(
            label: 'Próximo a vencer',
            customerIndex: 1,
            createdAt: now()->subDays(27)->setTime(11, 20),
            expirationOffset: 3,
            auctionOffset: 33,
            items: [
                $this->item(2, 1, 3200, 'Artículo completo, funcionamiento verificado.'),
            ]
        );

        $expired = $this->createPawn(
            label: 'Vencido todavía vigente para remate',
            customerIndex: 2,
            createdAt: now()->subDays(45)->setTime(9, 40),
            expirationOffset: -15,
            auctionOffset: 15,
            items: [
                $this->item(3, 1, 4800, 'Artículo usado con accesorios y cargador.'),
            ]
        );

        $readyForAuction = $this->createPawn(
            label: 'Listo para pasar a remate',
            customerIndex: 3,
            createdAt: now()->subDays(80)->setTime(12, 5),
            expirationOffset: -50,
            auctionOffset: -20,
            items: [
                $this->item(4, 2, 3600, 'Dos piezas iguales en buenas condiciones.'),
                $this->item(5, 1, 1900, 'Artículo con señales normales de uso.'),
            ]
        );

        $discountedReady = $this->createPawn(
            label: 'Listo para remate con descuento de interés',
            customerIndex: 4,
            createdAt: now()->subDays(95)->setTime(10, 30),
            expirationOffset: -65,
            auctionOffset: -35,
            items: [
                $this->item(6, 8.750, 7200, 'Prenda pesada y registrada para probar descuento por días.'),
            ]
        );

        $partialPayment = $this->createPawn(
            label: 'Con abono parcial a interés',
            customerIndex: 5,
            createdAt: now()->subDays(55)->setTime(13, 10),
            expirationOffset: -25,
            auctionOffset: 5,
            items: [
                $this->item(7, 1, 5300, 'Artículo conservado, incluye accesorios.'),
            ]
        );

        $canceledAt = now()->subDays(19)->setTime(14, 0);
        $canceled = $this->createPawn(
            label: 'Cancelado con reversa de caja',
            customerIndex: 6,
            createdAt: now()->subDays(20)->setTime(10, 0),
            expirationOffset: 10,
            auctionOffset: 40,
            items: [
                $this->item(8, 1, 2800, 'Registro cancelado por error de captura.'),
            ],
            status: [
                'canceled_at' => $canceledAt,
                'canceled_by' => $this->user->id,
                'cancellation_type' => 'capture_error',
            ]
        );

        $paidAt = now()->subDays(8)->setTime(12, 45);
        $paid = $this->createPawn(
            label: 'Liquidado históricamente',
            customerIndex: 7,
            createdAt: now()->subDays(60)->setTime(9, 15),
            expirationOffset: -30,
            auctionOffset: 0,
            items: [
                $this->item(9, 1, 6100, 'Artículo liquidado y entregado al cliente.'),
            ],
            status: [
                'paid_at' => $paidAt,
                'paid_by' => $this->user->id,
            ]
        );

        $originalCountersign = $this->createPawn(
            label: 'Empeño original refrendado',
            customerIndex: 8,
            createdAt: now()->subDays(90)->setTime(11, 0),
            expirationOffset: -60,
            auctionOffset: -30,
            items: [
                $this->item(10, 1, 4400, 'Prenda del contrato original posteriormente refrendado.'),
            ]
        );

        $countersign = $this->createPawn(
            label: 'Refrendo activo',
            customerIndex: 8,
            createdAt: now()->subDays(45)->setTime(11, 30),
            expirationOffset: -15,
            auctionOffset: 15,
            items: [
                $this->item(10, 1, 4400, 'Prenda trasladada desde el folio anterior por refrendo.'),
            ],
            status: [
                'previous_pawn' => $originalCountersign->id,
            ]
        );

        $auctionedInventory = $this->createPawn(
            label: 'En remate disponible',
            customerIndex: 9,
            createdAt: now()->subDays(120)->setTime(9, 0),
            expirationOffset: -90,
            auctionOffset: -60,
            items: [
                $this->item(11, 1, 6800, 'Artículo en inventario de remates y disponible para venta.'),
            ],
            status: [
                'auction_at' => now()->subDays(59)->setTime(10, 0),
                'auction_by' => $this->user->id,
            ]
        );

        $auctionedSold = $this->createPawn(
            label: 'Remate vendido',
            customerIndex: 10,
            createdAt: now()->subDays(150)->setTime(10, 0),
            expirationOffset: -120,
            auctionOffset: -90,
            items: [
                $this->item(12, 1, 7500, 'Artículo de remate vendido de contado.'),
            ],
            status: [
                'auction_at' => now()->subDays(89)->setTime(10, 30),
                'auction_by' => $this->user->id,
            ]
        );

        $auctionedMixed = $this->createPawn(
            label: 'Remate con modos mixtos',
            customerIndex: 11,
            createdAt: now()->subDays(140)->setTime(12, 0),
            expirationOffset: -110,
            auctionOffset: -80,
            items: [
                $this->item(13, 3, 3000, 'Lote agrupado para venta conjunta.'),
                $this->item(14, 12.500, 5200, 'Metal marcado como no vendible.'),
                $this->item(15, 1, 4600, 'Artículo movido al módulo de ventas.'),
            ],
            status: [
                'auction_at' => now()->subDays(79)->setTime(13, 0),
                'auction_by' => $this->user->id,
            ]
        );

        foreach (collect([
            $active,
            $nearExpiration,
            $expired,
            $readyForAuction,
            $discountedReady,
            $partialPayment,
            $canceled,
            $paid,
            $originalCountersign,
            $auctionedInventory,
            $auctionedSold,
            $auctionedMixed,
        ])->sortBy('created_at') as $pawn) {
            $this->addEvent(
                date: Carbon::parse($pawn->created_at),
                pawn: $pawn,
                type: 'pawn',
                amount: (float) $pawn->total,
                cashDelta: -(float) $pawn->total,
                comments: self::DEMO_MARKER." Entrega del préstamo {$pawn->folio}."
            );
        }

        $this->addEvent(
            date: Carbon::parse($countersign->created_at),
            pawn: $countersign,
            type: 'countersign',
            amount: 485,
            cashDelta: 485,
            comments: self::DEMO_MARKER." Refrendo del folio {$originalCountersign->folio}.",
            data: ['previous_pawn_id' => $originalCountersign->id]
        );

        $this->addEvent(
            date: now()->subDays(40)->setTime(12, 0),
            pawn: $discountedReady,
            type: 'interest_days_discount',
            amount: 0,
            cashDelta: 0,
            comments: self::DEMO_MARKER.' Descuento histórico de 5 días de interés.',
            data: $this->discountData($discountedReady, 5)
        );

        $this->addEvent(
            date: now()->subDays(10)->setTime(13, 0),
            pawn: $partialPayment,
            type: 'payment_to_interest',
            amount: 300,
            cashDelta: 300,
            comments: self::DEMO_MARKER.' Abono parcial a intereses.'
        );

        $this->addEvent(
            date: $canceledAt,
            pawn: $canceled,
            type: 'pawn_cancellation_reversal',
            amount: (float) $canceled->total,
            cashDelta: (float) $canceled->total,
            comments: self::DEMO_MARKER.' Reversa por cancelación del empeño.',
            data: ['cancel_original_transaction' => true]
        );

        $liquidationInterest = 925.50;
        $this->addEvent(
            date: $paidAt,
            pawn: $paid,
            type: 'liquidation',
            amount: round((float) $paid->total + $liquidationInterest, 2),
            cashDelta: round((float) $paid->total + $liquidationInterest, 2),
            comments: self::DEMO_MARKER.' Liquidación total del empeño.',
            data: [
                'principal' => (float) $paid->total,
                'interest' => $liquidationInterest,
            ]
        );

        $inventoryAuctions = $this->createAuctionRows($auctionedInventory, [
            ['mode' => Auction::MODE_SELLABLE],
        ]);

        $soldAt = now()->subDays(15)->setTime(16, 0);
        $soldAuctions = $this->createAuctionRows($auctionedSold, [
            ['mode' => Auction::MODE_SELLABLE, 'sold_at' => $soldAt],
        ]);

        $mixedAuctions = $this->createAuctionRows($auctionedMixed, [
            ['mode' => Auction::MODE_GROUPED],
            ['mode' => Auction::MODE_NOT_SELL],
            ['mode' => Auction::MODE_SELLABLE, 'move_at' => now()->subDays(20)->setTime(15, 0)],
        ]);

        foreach ([
            [$auctionedInventory, $inventoryAuctions],
            [$auctionedSold, $soldAuctions],
            [$auctionedMixed, $mixedAuctions],
        ] as [$pawn, $auctions]) {
            $this->addEvent(
                date: Carbon::parse($pawn->auction_at),
                pawn: $pawn,
                type: 'pawn_to_auction',
                amount: 0,
                cashDelta: 0,
                comments: self::DEMO_MARKER.' Pase histórico a remate.',
                data: ['auction_ids' => $auctions->pluck('id')->all()]
            );
        }

        $soldAuction = $soldAuctions->first();

        $this->addEvent(
            date: $soldAt,
            pawn: $auctionedSold,
            type: 'sale',
            amount: (float) $soldAuction->total,
            cashDelta: (float) $soldAuction->total,
            comments: self::DEMO_MARKER.' Venta histórica de artículo rematado.',
            data: ['auction_id' => $soldAuction->id],
            referenceId: $soldAuction->id
        );
    }

    private function createPawn(
        string $label,
        int $customerIndex,
        \Carbon\CarbonInterface $createdAt,
        int $expirationOffset,
        int $auctionOffset,
        array $items,
        array $status = []
    ): Pawn {
        $customer = $this->customers->get($customerIndex % $this->customers->count());
        $department = $items[0]['product']->department;
        $total = round((float) collect($items)->sum('value'), 2);
        $term = max((int) ($department?->term ?: 30), 1);
        $auctionDays = max((int) ($department?->auction ?: 30), 1);

        $attributes = array_merge([
            'folio' => $this->nextFolio++,
            'customer_id' => $customer->id,
            'company_id' => $this->company->id,
            'office_id' => $this->office->id,
            'created_by' => $this->user->id,
            'canceled_by' => null,
            'auction_by' => null,
            'previous_pawn' => null,
            'canceled_at' => null,
            'paid_at' => null,
            'auction_at' => null,
            'date_expiration' => today()->addDays($expirationOffset)->toDateString(),
            'date_auction' => today()->addDays($auctionOffset)->toDateString(),
            'date_settlement' => today()->addDays($expirationOffset)->toDateString(),
            'total' => $total,
            'estimated_value' => round($total * 1.75, 2),
            'loan_value' => $total,
            'loan_rate' => (float) ($department?->loan_rate ?: 0),
            'iva_rate' => (float) ($department?->iva_rate ?: 0.16),
            'monthly_interest_rate' => (float) ($department?->monthly_interest_rate ?: 12),
            'daily_interest_rate' => (float) ($department?->daily_interest_rate ?: 0.4),
            'term' => $term,
            'auction' => $auctionDays,
            'pay_extra' => 0,
            'comments' => self::DEMO_MARKER.' '.$label,
            'photos' => null,
            'beneficiary' => $customer->name,
            'bag' => 'HIST-'.str_pad((string) ($this->nextFolio - 1), 6, '0', STR_PAD_LEFT),
            'inapam_rate' => 0,
            'cancellation_type' => null,
            'number_investigation' => null,
            'paid_by' => null,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ], $status);

        $pawn = PawnFactory::new()->create($attributes);
        $this->forceHistoricalValues($pawn, $attributes);

        foreach ($items as $itemData) {
            $itemAttributes = [
                'pawn_id' => $pawn->id,
                'product_id' => $itemData['product']->id,
                'quantity' => $itemData['quantity'],
                'description' => $itemData['description'],
                'value' => $itemData['value'],
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ];

            $item = PawnItemFactory::new()->create($itemAttributes);
            $this->forceHistoricalValues($item, $itemAttributes);
        }

        return $pawn->fresh(['items.product']);
    }

    private function createAuctionRows(Pawn $pawn, array $definitions): Collection
    {
        $pawn->loadMissing('items');

        return $pawn->items
            ->values()
            ->map(function (PawnItem $item, int $index) use ($pawn, $definitions) {
                $definition = $definitions[$index] ?? $definitions[array_key_last($definitions)];
                $mode = $definition['mode'] ?? Auction::MODE_SELLABLE;
                $soldAt = $definition['sold_at'] ?? null;
                $moveAt = $definition['move_at'] ?? null;
                $interest = round((float) $item->value * 0.28, 2);
                $isNotSell = $mode === Auction::MODE_NOT_SELL;

                $attributes = [
                    'company_id' => $pawn->company_id,
                    'office_id' => $pawn->office_id,
                    'pawn_id' => $pawn->id,
                    'pawn_item_id' => $item->id,
                    'product_id' => $item->product_id,
                    'user_id' => $soldAt ? $this->user->id : null,
                    'created_by' => $this->user->id,
                    'unit_number' => 1,
                    'quantity' => (float) $item->quantity,
                    'auction_mode' => $mode,
                    'description' => $item->description,
                    'source_value' => (float) $item->value,
                    'value' => (float) $item->value,
                    'interest_amount' => $interest,
                    'total' => round((float) $item->value + $interest, 2),
                    'commission' => 0,
                    'active' => ! $isNotSell && ! $soldAt && ! $moveAt,
                    'not_sell' => $isNotSell,
                    'sold_at' => $soldAt,
                    'move_at' => $moveAt,
                    'created_at' => $pawn->auction_at,
                    'updated_at' => $soldAt ?: ($moveAt ?: $pawn->auction_at),
                ];

                $auction = AuctionFactory::new()->create($attributes);
                $this->forceHistoricalValues($auction, $attributes);

                return $auction;
            });
    }

    private function item(
        int $productIndex,
        float $quantity,
        float $value,
        string $description
    ): array {
        return [
            'product' => $this->products->get($productIndex % $this->products->count()),
            'quantity' => $quantity,
            'value' => $value,
            'description' => $description,
        ];
    }

    private function addEvent(
        \Carbon\CarbonInterface $date,
        Pawn $pawn,
        string $type,
        float $amount,
        float $cashDelta,
        string $comments,
        array $data = [],
        ?int $referenceId = null
    ): void {
        $this->events[] = compact(
            'date',
            'pawn',
            'type',
            'amount',
            'cashDelta',
            'comments',
            'data',
            'referenceId'
        );
    }

    private function processCashEvents(): void
    {
        usort($this->events, fn (array $a, array $b) => $a['date']->getTimestamp() <=> $b['date']->getTimestamp());

        $balance = self::OPENING_CASH;

        foreach ($this->events as $event) {
            $balance = round($balance + $event['cashDelta'], 2);

            $attributes = [
                'office_id' => $this->office->id,
                'user_id' => $this->user->id,
                'pawn_id' => $event['pawn']->id,
                'reference_id' => $event['referenceId'],
                'type' => $event['type'],
                'comments' => $event['comments'],
                'data' => array_merge([
                    'source' => 'sicem_historical_seeder',
                    'cash_delta' => $event['cashDelta'],
                ], $event['data']),
                'amount' => $event['amount'],
                'balance' => $balance,
                'discount_amount' => 0,
                'discount_rate' => 0,
                'payment_type' => 'cash',
                'canceled_at' => null,
                'comments_cancel' => null,
                'created_at' => $event['date'],
                'updated_at' => $event['date'],
            ];

            $transaction = TransactionFactory::new()->create($attributes);
            $this->forceHistoricalValues($transaction, $attributes);

            if (($event['data']['cancel_original_transaction'] ?? false) === true) {
                $original = Transaction::query()
                    ->where('office_id', $this->office->id)
                    ->where('pawn_id', $event['pawn']->id)
                    ->where('type', 'pawn')
                    ->orderBy('id')
                    ->first();

                if ($original) {
                    $this->forceHistoricalValues($original, [
                        'canceled_at' => $event['date'],
                        'comments_cancel' => 'Cancelada por el escenario histórico de prueba.',
                    ]);
                }
            }
        }

        $this->forceHistoricalValues($this->office, ['cash' => $balance]);
    }

    private function discountData(Pawn $pawn, int $days): array
    {
        $dailyInterest = round((float) $pawn->getDailyInterest(true), 2);
        $discountAmount = round($dailyInterest * $days, 2);
        $daysBefore = max((int) $pawn->days2payminus1, $days);
        $interestBefore = round($dailyInterest * $daysBefore, 2);
        $interestAfter = round(max($interestBefore - $discountAmount, 0), 2);

        return [
            'discount_days' => $days,
            'days_before' => $daysBefore,
            'days_after' => max($daysBefore - $days, 0),
            'daily_interest' => $dailyInterest,
            'discount_amount' => $discountAmount,
            'interest_after_discount' => $interestAfter,
            'amount_after_discount' => round((float) $pawn->total + $interestAfter, 2),
        ];
    }

    private function resolveDemoOffice(): Office
    {
        $office = Office::withTrashed()
            ->where('company_id', $this->company->id)
            ->where('code', self::DEMO_OFFICE_CODE)
            ->first();

        if (! $office) {
            $office = new Office();
        } elseif ($office->trashed()) {
            $office->restore();
        }

        $this->forceHistoricalValues($office, [
            'company_id' => $this->company->id,
            'name' => 'Sucursal Histórica DEMO',
            'code' => self::DEMO_OFFICE_CODE,
            'serie' => 'H',
            'phone' => '5550000000',
            'address' => 'Sucursal exclusiva para pruebas históricas',
            'schedule' => 'Lunes a sábado 09:00-18:00',
            'bank_account' => 'DEMO',
            'daily_interest_rate' => 0.4,
            'monthly_interest_rate' => 12,
            'cash' => self::OPENING_CASH,
            'created_at' => $office->created_at ?: now(),
            'updated_at' => now(),
        ]);

        return $office;
    }

    private function cleanPreviousDemoData(): void
    {
        $pawnIds = Pawn::query()
            ->where('office_id', $this->office->id)
            ->pluck('id');

        Auction::withTrashed()
            ->where('office_id', $this->office->id)
            ->forceDelete();

        Transaction::query()
            ->where('office_id', $this->office->id)
            ->delete();

        if ($pawnIds->isNotEmpty()) {
            PawnItem::query()->whereIn('pawn_id', $pawnIds)->delete();

            Pawn::query()
                ->where('office_id', $this->office->id)
                ->whereNotNull('previous_pawn')
                ->delete();

            Pawn::query()
                ->where('office_id', $this->office->id)
                ->delete();
        }

        $this->events = [];
        $this->nextFolio = 900001;
    }

    private function resetDemoOffice(): void
    {
        $this->forceHistoricalValues($this->office, [
            'cash' => self::OPENING_CASH,
        ]);
    }

    private function resolveCompany(): Company
    {
        $configuredId = (int) env('SICEM_DEMO_COMPANY_ID', 0);
        $company = $configuredId > 0
            ? Company::query()->find($configuredId)
            : Company::query()->orderBy('id')->first();

        if (! $company) {
            throw new RuntimeException('No existe una empresa. Ejecuta primero los seeders base de SICEM.');
        }

        return $company;
    }

    private function resolveUser(Company $company): User
    {
        $configuredId = (int) env('SICEM_DEMO_USER_ID', 0);
        $query = User::query();

        if ($configuredId > 0) {
            $query->whereKey($configuredId);
        } elseif (Schema::hasColumn('users', 'company_id')) {
            $query->where('company_id', $company->id);
        }

        $user = $query->orderBy('id')->first();

        if (! $user) {
            throw new RuntimeException('No existe un usuario para la empresa seleccionada.');
        }

        return $user;
    }

    private function resolveCustomers(Company $company): Collection
    {
        $query = Customer::query();

        if (Schema::hasColumn('customers', 'company_id')) {
            $query->where('company_id', $company->id);
        }

        $customers = $query->orderBy('id')->limit(20)->get();

        if ($customers->isEmpty()) {
            throw new RuntimeException('Se necesita al menos un cliente para generar datos históricos.');
        }

        return $customers;
    }

    private function resolveProducts(): Collection
    {
        $products = Product::query()
            ->with('department')
            ->where('is_active', true)
            ->whereHas('department', fn ($query) => $query->where('is_active', true))
            ->orderBy('id')
            ->limit(30)
            ->get();

        if ($products->isEmpty()) {
            throw new RuntimeException('Se necesita al menos un producto de un departamento activo.');
        }

        return $products;
    }

    private function guardEnvironment(): void
    {
        if (app()->environment('production') && ! env('SICEM_ALLOW_DEMO_SEED', false)) {
            throw new RuntimeException(
                'Este seeder está bloqueado en producción. Usa SICEM_ALLOW_DEMO_SEED=true únicamente si comprendes el riesgo.'
            );
        }
    }

    private function assertRequiredSchema(): void
    {
        foreach ([
            'companies',
            'offices',
            'users',
            'customers',
            'departments',
            'products',
            'pawns',
            'pawn_items',
            'transactions',
            'auctions',
        ] as $table) {
            if (! Schema::hasTable($table)) {
                throw new RuntimeException("Falta la tabla {$table}. Ejecuta las migraciones antes del seeder histórico.");
            }
        }

        foreach ([
            'company_id',
            'pawn_item_id',
            'auction_mode',
            'source_value',
            'interest_amount',
            'active',
            'not_sell',
        ] as $column) {
            if (! Schema::hasColumn('auctions', $column)) {
                throw new RuntimeException(
                    "Falta auctions.{$column}. Ejecuta primero las migraciones nuevas del módulo de remates."
                );
            }
        }
    }

    private function forceHistoricalValues(Model $model, array $attributes): void
    {
        $columns = Schema::getColumnListing($model->getTable());

        $model->timestamps = false;
        $model->forceFill(
            collect($attributes)
                ->only($columns)
                ->all()
        );
        $model->saveQuietly();
        $model->timestamps = true;
    }
}

