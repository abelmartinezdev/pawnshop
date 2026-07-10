<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Throwable;

class Pawn extends Model
{
    protected $fillable = [
        'folio',
        'customer_id',
        'company_id',
        'office_id',
        'created_by',
        'canceled_by',
        'previous_pawn',

        'canceled_at',
        'paid_at',
        'auction_at',

        'date_expiration',
        'date_auction',
        'date_settlement',

        'total',
        'estimated_value',
        'loan_value',
        'loan_rate',
        'iva_rate',
        'monthly_interest_rate',
        'daily_interest_rate',

        'term',
        'auction',
        'pay_extra',

        'comments',
        'photos',
        'beneficiary',
        'bag',
        'inapam_rate',

        'cancellation_type',
        'number_investigation',
        'paid_by',
    ];

    protected $casts = [
        'folio' => 'integer',

        'canceled_at' => 'datetime',
        'paid_at' => 'datetime',
        'auction_at' => 'datetime',

        'date_expiration' => 'date',
        'date_auction' => 'date',
        'date_settlement' => 'date',

        'total' => 'decimal:2',
        'estimated_value' => 'decimal:2',
        'loan_value' => 'decimal:2',
        'loan_rate' => 'decimal:4',
        'iva_rate' => 'decimal:4',
        'monthly_interest_rate' => 'decimal:4',
        'daily_interest_rate' => 'decimal:4',
        'pay_extra' => 'decimal:2',
        'inapam_rate' => 'decimal:4',

        'term' => 'integer',
        'auction' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function items(): HasMany
    {
        return $this->hasMany(PawnItem::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function latestTransactions(): HasMany
    {
        return $this->transactions()->latest('id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function canceledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'canceled_by');
    }

    public function paidBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function previousPawn(): BelongsTo
    {
        return $this->belongsTo(Pawn::class, 'previous_pawn');
    }

    public function countersigns(): HasMany
    {
        return $this->hasMany(Pawn::class, 'previous_pawn');
    }

    public function activeCountersignQuery(): HasMany
    {
        return $this->countersigns()->whereNull('canceled_at');
    }

    public function interestDaysDiscounts(): HasMany
    {
        return $this->transactions()
            ->where('type', 'interest_days_discount')
            ->whereNull('canceled_at');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getFormattedFolioAttribute(): string
    {
        $serie = $this->office?->serie ?: 'A';

        return $serie . str_pad((string) $this->folio, 6, '0', STR_PAD_LEFT);
    }

    public function getRawFolioAttribute(): int
    {
        return (int) $this->getRawOriginal('folio');
    }

    public function getStatusAttribute(): string
    {
        if ($this->isCanceled()) {
            return 'cancelled';
        }

        if ($this->isPaid()) {
            return 'paid';
        }

        if ($this->hasCountersign()) {
            return 'countersigned';
        }

        if ($this->date_expiration && Carbon::parse($this->date_expiration)->isPast()) {
            return 'expired';
        }

        return 'active';
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'cancelled' => 'Cancelada',
            'paid' => 'Liquidada',
            'countersigned' => 'Refrendada',
            'expired' => 'Vencida',
            default => 'Pendiente de pago',
        };
    }

    public function getPhotosListAttribute(): array
    {
        if (! $this->photos) {
            return [];
        }

        if (is_array($this->photos)) {
            return collect($this->photos)
                ->map(fn ($photo) => trim((string) $photo))
                ->filter()
                ->values()
                ->all();
        }

        $value = trim((string) $this->photos);

        if ($value === '' || $value === '[]') {
            return [];
        }

        $decoded = json_decode($value, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return collect($decoded)
                ->map(fn ($photo) => trim((string) $photo))
                ->filter()
                ->values()
                ->all();
        }

        return collect(preg_split('/[;,|]/', $value))
            ->map(fn ($photo) => trim((string) $photo))
            ->filter()
            ->values()
            ->all();
    }

    public function getStartDayAttribute(): CarbonInterface
    {
        return Carbon::parse($this->created_at?->toDateString() ?: now()->toDateString());
    }

    public function getAmount2LiquidateAttribute(): float
    {
        return round((float) $this->total + (float) $this->interest2pay, 2);
    }

    public function getAmount2LiquidateMinus1DayAttribute(): float
    {
        return round((float) $this->total + (float) $this->interest2payminus1day, 2);
    }

    public function getInterest2PayAttribute(): float
    {
        return $this->getInterest2pay();
    }

    public function getInterest2PayMinus1DayAttribute(): float
    {
        return $this->getInterest2payLess1day();
    }

    public function getDailyInterestAttribute(): float
    {
        return $this->getDailyInterest();
    }

    public function getDays2PayAttribute(): int
    {
        $days = $this->start_day->diffInDays(Carbon::parse(now()->toDateString()));

        if ($this->firstItemProductId() === 34) {
            return 15;
        }

        return max((int) $days, 5);
    }

    public function getDays2PayMinus1Attribute(): int
    {
        $days = $this->start_day->diffInDays(Carbon::parse(now()->toDateString())) - 1;

        if ($this->firstItemProductId() === 34 && $days < 15) {
            return 15;
        }

        return max((int) $days, 5);
    }

    public function getDailyInterestWithoutIvaAttribute(): string
    {
        $ivaFactor = 1 + $this->ivaRate();

        if ($ivaFactor <= 0) {
            return number_format((float) $this->daily_interest_rate, 1);
        }

        return number_format(((float) $this->daily_interest_rate / $ivaFactor), 1);
    }

    public function getInterestDiscountDaysAttribute(): int
    {
        return $this->interestDiscountDays();
    }

    public function getInterestDiscountAmountAttribute(): float
    {
        return $this->interestDiscountAmount(true);
    }

    /*
    |--------------------------------------------------------------------------
    | Business helpers
    |--------------------------------------------------------------------------
    */

    public function activeCountersign(): ?self
    {
        return $this->activeCountersignQuery()->first();
    }

    public function hasCountersign(): bool
    {
        return $this->activeCountersignQuery()->exists();
    }

    public function getMainTransaction(): ?Transaction
    {
        return $this->transactions()->orderBy('id')->first();
    }

    public function getLastTransaction(): ?Transaction
    {
        return $this->transactions()->latest('id')->first();
    }

    public function isCountersign(): bool
    {
        return $this->previous_pawn !== null;
    }

    public function getCountersignTransaction(): ?Transaction
    {
        return $this->transactions()
            ->where('type', 'countersign')
            ->whereNull('canceled_at')
            ->first();
    }

    public function isCanceled(): bool
    {
        return $this->canceled_at !== null;
    }

    public function isPaid(): bool
    {
        return $this->paid_at !== null;
    }

    public function hasPhotos(): bool
    {
        return count($this->photos_list) > 0;
    }

    public function hasExpirationDateChange(): bool
    {
        return $this->transactions()
            ->where('type', 'expiration_date_change')
            ->exists();
    }

    public function getLastChangeExpiration(): ?Transaction
    {
        return $this->transactions()
            ->where('type', 'expiration_date_change')
            ->latest('id')
            ->first();
    }

    public function canBeAuctioned(): bool
    {
        if (! $this->date_auction) {
            return false;
        }

        $auctionDate = Carbon::parse($this->date_auction);

        if (
            now()->diffInDays($auctionDate, false) > 0 ||
            $this->auction_at !== null ||
            $this->isPaid() ||
            $this->hasCountersign() ||
            $this->isCanceled()
        ) {
            return false;
        }

        return true;
    }

    public function mayBeCanceled(): bool
    {
        if (
            $this->auction_at !== null ||
            $this->isPaid() ||
            $this->hasCountersign() ||
            $this->isCanceled() ||
            $this->transactions()->count() > 1
        ) {
            return false;
        }

        $user = auth()->user();

        $isAdmin = $user
            && (
                (method_exists($user, 'isAdmin') && $user->isAdmin())
                || (method_exists($user, 'hasRole') && $user->hasRole('admin'))
                || ($user->type ?? null) === 'admin'
                || ($user->type ?? null) === 'suadmin'
                || $user->can('pawn.cancel.any')
            );

        if (! $this->created_at?->isToday() && ! $isAdmin) {
            return false;
        }

        return true;
    }

    public function canCancel(): bool
    {
        $user = auth()->user();

        if (($user->type ?? null) === 'suadmin') {
            return true;
        }

        if (($user->type ?? null) === 'admin') {
            return Transaction::query()
                ->where('pawn_id', $this->id)
                ->where('type', 'liquidation')
                ->whereDate('created_at', now()->toDateString())
                ->exists();
        }

        return false;
    }

    public function hasLiquidation(): bool
    {
        return Transaction::query()
            ->where('pawn_id', $this->id)
            ->where('type', 'liquidation')
            ->exists();
    }

    public function interestDiscountDays(): int
    {
        return (int) $this->interestDaysDiscounts()
            ->get()
            ->sum(function (Transaction $transaction) {
                $data = $this->transactionData($transaction);

                return (int) ($data['discount_days'] ?? 0);
            });
    }

    public function interestDiscountAmount(bool $withIva = true): float
    {
        return round((float) $this->interestDaysDiscounts()
            ->get()
            ->sum(function (Transaction $transaction) use ($withIva) {
                $data = $this->transactionData($transaction);

                if ($withIva) {
                    return (float) ($data['discount_amount'] ?? 0);
                }

                return (float) ($data['discount_amount_without_iva'] ?? 0);
            }), 2);
    }

    public function getInterest2pay(bool $withIva = true): float
    {
        $interest = $this->getDailyInterest(false) * $this->days2pay;

        if ($withIva) {
            $interest += $interest * $this->ivaRate();

            if ($this->inapam_rate) {
                $interest -= $interest * (float) $this->inapam_rate;
            }

            $interest -= $this->interestDiscountAmount(true);
            $interest -= $this->paidAmount();
        } else {
            $interest -= $this->interestDiscountAmount(false);
            $interest -= ($this->paidAmount() / (1 + $this->ivaRate()));
        }

        return round(max($interest, 0), 2);
    }

    public function getInterest2payLess1day(bool $withIva = true, float $discount = 0): float
    {
        $interest = $this->getDailyInterest(false) * $this->days2payminus1;

        if ($withIva) {
            $interest += $interest * $this->ivaRate();

            if ($this->inapam_rate) {
                $interest -= $interest * (float) $this->inapam_rate;
            }

            $interest -= $this->interestDiscountAmount(true);
            $interest -= $this->paidAmount();
        } else {
            $interest -= $this->interestDiscountAmount(false);
            $interest -= ($this->paidAmount() / (1 + $this->ivaRate()));
        }

        if ($discount > 0 && auth()->user()?->can('apply-discount')) {
            $interest -= ($interest * $discount);
        }

        return round(max($interest, 0), 2);
    }

    public function getDailyInterest(bool $withIva = true): float
    {
        $dailyInterest = ((float) $this->daily_interest_rate / 100) * (float) $this->total;

        if ($withIva) {
            $dailyInterest += $dailyInterest * $this->ivaRate();
        }

        if ($this->inapam_rate) {
            $dailyInterest *= (1 - (float) $this->inapam_rate);
        }

        return round(max($dailyInterest, 0), 2);
    }

    public function getInterestIVA(): float
    {
        return round($this->getInterest2pay(false) * $this->ivaRate(), 2);
    }

    public function getInterestIVALess1day(float $discount = 0): float
    {
        return round($this->getInterest2payLess1day(false, $discount) * $this->ivaRate(), 2);
    }

    public function paidAmount(): float
    {
        return (float) $this->transactionsPaidAmount()->sum('amount');
    }

    public function transactionsPaidAmount(): HasMany
    {
        return $this->transactions()
            ->whereIn('type', [
                'payment_to_interest',
                'interest_payment',
                'abono_interes',
                'payment',
            ])
            ->whereNull('canceled_at');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeSearch(Builder $query, ?string $search = null): Builder
    {
        $search = trim((string) ($search ?? request('search', '')));

        if ($search === '') {
            return $query;
        }

        return $query->where(function (Builder $builder) use ($search) {
            $builder
                ->where('folio', 'like', "%{$search}%")
                ->orWhereHas('customer', fn (Builder $customerQuery) => $customerQuery
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('rfc', 'like', "%{$search}%")
                    ->orWhere('code_id', 'like', "%{$search}%"));
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Private helpers
    |--------------------------------------------------------------------------
    */

    private function ivaRate(): float
    {
        if ($this->iva_rate === null) {
            return (float) config('core.iva_rate', 0.16);
        }

        $rate = (float) $this->iva_rate;

        return $rate > 1 ? $rate / 100 : $rate;
    }

    private function firstItemProductId(): ?int
    {
        try {
            return $this->items()->first()?->product_id;
        } catch (Throwable) {
            return null;
        }
    }

    private function transactionData(Transaction $transaction): array
    {
        $data = $transaction->data ?? null;

        if (is_array($data)) {
            return $data;
        }

        if (! $data) {
            return [];
        }

        $decoded = json_decode((string) $data, true);

        return is_array($decoded) ? $decoded : [];
    }
}