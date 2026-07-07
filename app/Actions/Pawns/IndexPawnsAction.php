<?php

namespace App\Actions\Pawns;

use App\Models\Pawn;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class IndexPawnsAction
{
    public function __invoke(Request $request): Response
    {
        $officeId = session('office_id') ?: auth()->user()?->office_id;
        $companyId = session('company_id') ?: auth()->user()?->company_id;

        abort_unless($officeId, 404, 'No hay una sucursal activa.');
        abort_unless($companyId, 404, 'No hay una empresa activa.');

        $search = trim((string) $request->input('search', ''));
        $status = (string) $request->input('status', 'active');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $perPage = (int) $request->input('per_page', 15);

        $query = Pawn::query()
            ->with([
                'customer:id,name,phone,mobile,email,rfc,code_id,type_id,city,state',
                'office:id,name,serie',
                'creator:id,name,email',
                'items:id,pawn_id,product_id,quantity,description,value',
                'items.product:id,code,description,unit',
            ])
            ->withCount([
                'items',
                'transactions',
                'countersigns as active_countersigns_count' => fn (Builder $query) => $query->whereNull('canceled_at'),
            ])
            ->where('company_id', $companyId)
            ->where('office_id', $officeId)
            ->search($search)
            ->when($dateFrom, fn (Builder $query) => $query->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo, fn (Builder $query) => $query->whereDate('created_at', '<=', $dateTo));

        $this->applyStatusFilter($query, $status);

        $pawns = $query
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (Pawn $pawn) => $this->mapPawn($pawn));

        return Inertia::render('Pawns/Index', [
            'pawns' => $pawns,
            'summary' => $this->summary($companyId, $officeId),
            'filters' => [
                'search' => $search,
                'status' => $status,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'per_page' => $perPage,
            ],
            'options' => [
                'statuses' => [
                    ['value' => 'active', 'label' => 'Pendientes'],
                    ['value' => 'expired', 'label' => 'Vencidos'],
                    ['value' => 'paid', 'label' => 'Liquidados'],
                    ['value' => 'cancelled', 'label' => 'Cancelados'],
                    ['value' => 'countersigned', 'label' => 'Refrendados'],
                    ['value' => 'auctioned', 'label' => 'En remate'],
                    ['value' => 'all', 'label' => 'Todos'],
                ],
            ],
        ]);
    }

    private function applyStatusFilter(Builder $query, string $status): void
    {
        match ($status) {
            'active' => $query
                ->whereNull('paid_at')
                ->whereNull('canceled_at')
                ->whereNull('auction_at')
                ->where(fn (Builder $builder) => $builder
                    ->whereNull('date_expiration')
                    ->orWhereDate('date_expiration', '>=', now()->toDateString()))
                ->whereDoesntHave('countersigns', fn (Builder $builder) => $builder->whereNull('canceled_at')),

            'expired' => $query
                ->whereNull('paid_at')
                ->whereNull('canceled_at')
                ->whereNull('auction_at')
                ->whereDate('date_expiration', '<', now()->toDateString())
                ->whereDoesntHave('countersigns', fn (Builder $builder) => $builder->whereNull('canceled_at')),

            'paid' => $query->whereNotNull('paid_at'),

            'cancelled' => $query->whereNotNull('canceled_at'),

            'countersigned' => $query->whereHas(
                'countersigns',
                fn (Builder $builder) => $builder->whereNull('canceled_at')
            ),

            'auctioned' => $query->whereNotNull('auction_at'),

            default => null,
        };
    }

    private function summary(int $companyId, int $officeId): array
    {
        $base = Pawn::query()
            ->where('company_id', $companyId)
            ->where('office_id', $officeId);

        $activeQuery = (clone $base)
            ->whereNull('paid_at')
            ->whereNull('canceled_at')
            ->whereNull('auction_at')
            ->where(fn (Builder $builder) => $builder
                ->whereNull('date_expiration')
                ->orWhereDate('date_expiration', '>=', now()->toDateString()))
            ->whereDoesntHave('countersigns', fn (Builder $builder) => $builder->whereNull('canceled_at'));

        return [
            'total' => (clone $base)->count(),
            'active' => (clone $activeQuery)->count(),
            'expired' => (clone $base)
                ->whereNull('paid_at')
                ->whereNull('canceled_at')
                ->whereNull('auction_at')
                ->whereDate('date_expiration', '<', now()->toDateString())
                ->whereDoesntHave('countersigns', fn (Builder $builder) => $builder->whereNull('canceled_at'))
                ->count(),
            'paid' => (clone $base)->whereNotNull('paid_at')->count(),
            'cancelled' => (clone $base)->whereNotNull('canceled_at')->count(),
            'countersigned' => (clone $base)
                ->whereHas('countersigns', fn (Builder $builder) => $builder->whereNull('canceled_at'))
                ->count(),
            'auctioned' => (clone $base)->whereNotNull('auction_at')->count(),
            'active_amount' => (float) (clone $activeQuery)->sum('total'),
        ];
    }

    private function mapPawn(Pawn $pawn): array
    {
        $status = $this->status($pawn);

        return [
            'id' => $pawn->id,
            'folio' => $this->formattedFolio($pawn),
            'raw_folio' => $pawn->folio,

            'status' => $status,
            'status_label' => $this->statusLabel($status),

            'total' => (float) $pawn->total,
            'amount_to_liquidate' => $this->safeFloat(fn () => $pawn->amount2liquidate),
            'interest_to_pay' => $this->safeFloat(fn () => $pawn->interest2pay),
            'daily_interest' => $this->safeFloat(fn () => $pawn->daily_interest),
            'days_to_pay' => $this->safeInt(fn () => $pawn->days2pay),

            'term' => (int) $pawn->term,
            'auction' => (int) $pawn->auction,
            'items_count' => (int) $pawn->items_count,
            'transactions_count' => (int) $pawn->transactions_count,
            'active_countersigns_count' => (int) ($pawn->active_countersigns_count ?? 0),

            'date_expiration' => $this->formatDate($pawn->date_expiration, 'd/m/Y'),
            'date_auction' => $this->formatDate($pawn->date_auction, 'd/m/Y'),
            'created_at' => $this->formatDate($pawn->created_at),
            'paid_at' => $this->formatDate($pawn->paid_at),
            'canceled_at' => $this->formatDate($pawn->canceled_at),
            'auction_at' => $this->formatDate($pawn->auction_at),

            'has_photos' => $pawn->hasPhotos(),
            'can_be_auctioned' => $pawn->canBeAuctioned(),
            'may_be_cancelled' => $pawn->mayBeCanceled(),

            'customer' => $pawn->customer ? [
                'id' => $pawn->customer->id,
                'name' => $pawn->customer->name,
                'phone' => $pawn->customer->display_phone ?? ($pawn->customer->mobile ?: $pawn->customer->phone),
                'email' => $pawn->customer->email,
                'rfc' => $pawn->customer->rfc,
                'code_id' => $pawn->customer->code_id,
                'type_label' => $pawn->customer->identification_type_label ?? null,
                'city' => $pawn->customer->city,
                'state' => $pawn->customer->state,
            ] : null,

            'office' => $pawn->office ? [
                'id' => $pawn->office->id,
                'name' => $pawn->office->name,
                'serie' => $pawn->office->serie,
            ] : null,

            'creator' => $pawn->creator ? [
                'id' => $pawn->creator->id,
                'name' => $pawn->creator->name,
            ] : null,

            'items_preview' => $pawn->items
                ->take(2)
                ->map(fn ($item) => [
                    'id' => $item->id,
                    'quantity' => (float) $item->quantity,
                    'description' => $item->description,
                    'value' => (float) $item->value,
                    'product' => $item->product ? [
                        'id' => $item->product->id,
                        'code' => $item->product->code,
                        'description' => $item->product->description,
                        'unit' => $item->product->unit,
                    ] : null,
                ])
                ->values(),
        ];
    }

    private function status(Pawn $pawn): string
    {
        if ($pawn->canceled_at) {
            return 'cancelled';
        }

        if ($pawn->paid_at) {
            return 'paid';
        }

        if ($pawn->auction_at) {
            return 'auctioned';
        }

        if ((int) ($pawn->active_countersigns_count ?? 0) > 0) {
            return 'countersigned';
        }

        if ($pawn->date_expiration && Carbon::parse($pawn->date_expiration)->isPast()) {
            return 'expired';
        }

        return 'active';
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'cancelled' => 'Cancelado',
            'paid' => 'Liquidado',
            'auctioned' => 'En remate',
            'countersigned' => 'Refrendado',
            'expired' => 'Vencido',
            default => 'Pendiente de pago',
        };
    }

    private function formattedFolio(Pawn $pawn): string
    {
        $serie = $pawn->office?->serie ?: 'A';

        return $serie . str_pad((string) $pawn->folio, 6, '0', STR_PAD_LEFT);
    }

    private function formatDate(mixed $value, string $format = 'd/m/Y H:i'): ?string
    {
        if (! $value) {
            return null;
        }

        if ($value instanceof CarbonInterface) {
            return $value->format($format);
        }

        try {
            return Carbon::parse($value)->format($format);
        } catch (Throwable) {
            return (string) $value;
        }
    }

    private function safeFloat(callable $callback): float
    {
        try {
            return (float) $callback();
        } catch (Throwable) {
            return 0.0;
        }
    }

    private function safeInt(callable $callback): int
    {
        try {
            return (int) $callback();
        } catch (Throwable) {
            return 0;
        }
    }
}