<?php

namespace App\Actions\Auctions;

use App\Models\Auction;
use App\Models\Pawn;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class IndexAuctionsAction
{
    public function __invoke(Request $request): Response
    {
        [$companyId, $officeId] = $this->currentContext();

        $search = mb_substr(
            trim((string) $request->query('search', '')),
            0,
            100
        );

        $perPage = min(max($request->integer('per_page', 10), 10), 50);

        $eligibleQuery = $this->eligiblePawnsQuery(
            companyId: $companyId,
            officeId: $officeId,
            search: $search
        );

        $pendingCount = (clone $eligibleQuery)->count();
        $pendingPrincipal = (float) (clone $eligibleQuery)->sum('total');
        $oldestAuctionDate = (clone $eligibleQuery)->min('date_auction');

        $pawns = $eligibleQuery
            ->with([
                'office:id,serie',
                'customer:id,name',
                'items.product:id,code,description,unit',
            ])
            ->orderBy('date_auction')
            ->orderBy('created_at')
            ->paginate($perPage)
            ->withQueryString();

        $pawns->getCollection()->transform(function (Pawn $pawn) {
            $canSendToAuction = $pawn->items->isNotEmpty();

            return [
                'id' => $pawn->id,
                'folio' => $pawn->formatted_folio,
                'raw_folio' => (int) $pawn->folio,
                'customer' => [
                    'id' => $pawn->customer?->id,
                    'name' => $pawn->customer?->name ?: 'Cliente no disponible',
                ],
                'items' => $pawn->items
                    ->map(fn ($item) => [
                        'id' => $item->id,
                        'quantity' => (float) $item->quantity,
                        'unit' => $item->product?->unit,
                        'product_code' => $item->product?->code,
                        'product_name' => $item->product?->description ?: 'Producto sin catálogo',
                        'description' => $item->description,
                    ])
                    ->values(),
                'items_count' => $pawn->items->count(),
                'created_at' => $this->formatDate($pawn->created_at),
                'date_expiration' => $this->formatDate($pawn->date_expiration),
                'date_auction' => $this->formatDate($pawn->date_auction),
                'overdue_days' => $this->overdueDays($pawn),
                'total' => (float) $pawn->total,
                'daily_interest_rate' => (float) $pawn->daily_interest_rate,
                'bag' => $pawn->bag,
                'can_send_to_auction' => $canSendToAuction,
                'disabled_reason' => $canSendToAuction
                    ? null
                    : 'El empeño no tiene artículos registrados.',
                'urls' => [
                    'show' => Route::has('pawns.show')
                        ? route('pawns.show', $pawn->id)
                        : null,
                    'send_to_auction' => $canSendToAuction && Route::has('pawns.send-to-auction')
                        ? route('pawns.send-to-auction', $pawn->id)
                        : null,
                ],
            ];
        });

        return Inertia::render('Auctions/Index', [
            'pawns' => $pawns,
            'filters' => [
                'search' => $search,
                'per_page' => $perPage,
            ],
            'stats' => [
                'pending_count' => $pendingCount,
                'pending_principal' => $pendingPrincipal,
                'oldest_overdue_days' => $oldestAuctionDate
                    ? max((int) Carbon::parse($oldestAuctionDate)->startOfDay()->diffInDays(today(), false), 0)
                    : 0,
                'auctioned_today' => Pawn::query()
                    ->where('company_id', $companyId)
                    ->where('office_id', $officeId)
                    ->whereDate('auction_at', today())
                    ->count(),
                'inventory_count' => Auction::query()
                    ->where('company_id', $companyId)
                    ->where('office_id', $officeId)
                    ->whereNull('sold_at')
                    ->whereNull('move_at')
                    ->count(),
            ],
            'urls' => [
                'index' => Route::has('auctions.index')
                    ? route('auctions.index')
                    : null,
            ],
        ]);
    }

    private function eligiblePawnsQuery(
        int $companyId,
        int $officeId,
        string $search
    ): Builder {
        return Pawn::query()
            ->where('company_id', $companyId)
            ->where('office_id', $officeId)
            ->whereNull('paid_at')
            ->whereNull('canceled_at')
            ->whereNull('auction_at')
            ->whereNotNull('date_auction')
            ->whereDate('date_auction', '<=', today())
            ->whereDoesntHave('countersigns', function (Builder $query) {
                $query->whereNull('canceled_at');
            })
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $filter) use ($search) {
                    $filter
                        ->where('folio', 'like', "%{$search}%")
                        ->orWhere('bag', 'like', "%{$search}%")
                        ->orWhereHas('customer', function (Builder $customer) use ($search) {
                            $customer->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('items', function (Builder $item) use ($search) {
                            $item
                                ->where('description', 'like', "%{$search}%")
                                ->orWhereHas('product', function (Builder $product) use ($search) {
                                    $product
                                        ->where('code', 'like', "%{$search}%")
                                        ->orWhere('description', 'like', "%{$search}%");
                                });
                        });
                });
            });
    }

    private function currentContext(): array
    {
        $user = auth()->user();
        $companyId = (int) (session('company_id') ?: $user?->company_id);
        $officeId = (int) (session('office_id') ?: $user?->office_id);

        abort_unless($companyId > 0 && $officeId > 0, 404, 'No hay una sucursal activa.');

        return [$companyId, $officeId];
    }

    private function formatDate(mixed $date): ?string
    {
        return $date ? Carbon::parse($date)->format('d/m/Y') : null;
    }

    private function overdueDays(Pawn $pawn): int
    {
        if (! $pawn->date_auction) {
            return 0;
        }

        return max(
            (int) Carbon::parse($pawn->date_auction)->startOfDay()->diffInDays(today(), false),
            0
        );
    }
}