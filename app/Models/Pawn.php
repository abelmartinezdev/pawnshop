<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Pawn extends Model
{
    protected $fillable = [
        'folio','customer_id','company_id','comments','total','date_expiration','date_auction',
        'estimated_value','loan_value','loan_rate','iva_rate','created_by','canceled_by','canceled_at',
        'term','auction','monthly_interest_rate','daily_interest_rate','pay_extra','office_id','photos',
        'beneficiary','date_settlement','bag','previous_pawn','paid_at','paid_by',
    ];

    protected $casts = [
        'canceled_at' => 'datetime',
        'paid_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    protected $appends = ['interest2payminus1day','amount2liquidateminus1day'];
    
    public function items(): HasMany
    {
        return $this->hasMany(PawnItem::class);
    }

    public function isPaid(): bool
    {
        return !is_null($this->paid_at);
    }

    public function markPaid(?int $userId): void
    {
        $this->paid_by = $userId;
        $this->paid_at = now();
        $this->save();
    }

    public function makeCountersign(float $payExtra, int $userId): self
    {
        $now = now();

        $new = $this->replicate([
            'folio', 'created_by', 'date_expiration', 'date_auction', 'total', 'pay_extra',
            'previous_pawn', 'paid_at', 'paid_by', 'canceled_by', 'canceled_at', 'created_at', 'updated_at', 'id'
        ]);

        $new->total = $this->total - $payExtra;
        $new->pay_extra = $payExtra;
        $new->folio = $this->nextFolioForOffice(); // tú defines
        $new->created_by = $userId;

        $new->date_expiration = $now->copy()->addDays($this->term)->toDateString();
        $new->date_auction = $now->copy()->addDays($this->term + $this->auction)->toDateString();
        $new->date_settlement = $now->copy()->addDays($this->term + $this->auction + config('core.days_to_settlement'))->toDateString();

        $new->previous_pawn = $this->id;
        $new->save();

        foreach ($this->items as $item) {
            $new->items()->create($item->only(['product_id','quantity','description','value']));
        }

        return $new;
    }

    // 🔥 aquí migras tus cálculos “interest2payminus1day”
    public function interestToPayMinus1Day(bool $withIva = true, float $discount = 0): float
    {
        $days = $this->daysToPayMinus1();
        $daily = $this->dailyInterest(false);
        $interest = $daily * $days;

        if ($withIva) {
            $interest += $interest * config('core.iva_rate');
        }

        $interest -= $this->paidInterestAmount();

        if ($discount > 0 && auth()->user()?->can('apply-discount')) {
            $interest -= ($interest * $discount);
        }

        return max(0, $interest);
    }

    public function dailyInterest(bool $withIva = true): float
    {
        $daily = ($this->daily_interest_rate / 100) * $this->total;

        if ($withIva) {
            $daily += $daily * config('core.iva_rate');
        }

        return $daily;
    }

    public function daysToPayMinus1(): int
    {
        $start = Carbon::parse($this->created_at->toDateString());
        $days = $start->diffInDays(Carbon::today()) - 1;

        // tu regla de mínimo 5, y el hack product_id 34 lo puedes mejorar después
        $days = max(5, $days);

        return $days;
    }

    public function paidInterestAmount(): float
    {
        return (float) $this->transactions()
            ->where('type','payment_to_interest')
            ->whereNull('canceled_at')
            ->sum('amount');
    }

    // relación transactions la defines igual (depende tu módulo nuevo)
    public function transactions(): HasMany
    {
        return $this->hasMany(\App\Domain\Transactions\Models\Transaction::class);
    }

    private function nextFolioForOffice(): int
    {
        // En vez de repo getFolio(), esto lo puedes mover a un service
        $last = self::query()
            ->where('office_id', $this->office_id)
            ->orderByDesc('id')
            ->value('folio');

        return ((int) $last) + 1 ?: 1;
    }
}