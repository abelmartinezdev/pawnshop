<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    public const TYPE_PAWN = 'pawn';
    public const TYPE_COUNTERSIGN = 'countersign';
    public const TYPE_LIQUIDATION = 'liquidation';
    public const TYPE_PAYMENT = 'payment';
    public const TYPE_MANUAL_INCOME = 'manual_income';
    public const TYPE_MANUAL_EXPENSE = 'manual_expense';
    public const TYPE_SALE = 'sale';
    public const TYPE_ASIDE = 'aside';
    public const TYPE_ASIDE_PAYMENT = 'aside_payment';
    public const TYPE_ADJUSTMENT = 'adjustment';

    public const PAYMENT_CASH = 'cash';
    public const PAYMENT_CARD = 'card';

    protected $fillable = [
        'office_id',
        'user_id',
        'pawn_id',
        'reference_id',
        'type',
        'comments',
        'data',
        'amount',
        'balance',
        'discount_amount',
        'discount_rate',
        'payment_type',
        'canceled_at',
        'comments_cancel',
    ];

    protected $casts = [
        'data' => 'array',
        'amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'discount_rate' => 'decimal:4',
        'canceled_at' => 'datetime',
    ];

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pawn(): BelongsTo
    {
        return $this->belongsTo(Pawn::class);
    }

    public function getIsCancelledAttribute(): bool
    {
        return $this->canceled_at !== null;
    }

    public function getFormattedAmountAttribute(): string
    {
        return '$' . number_format((float) $this->amount, 2);
    }

    public function getFormattedBalanceAttribute(): string
    {
        return '$' . number_format((float) $this->balance, 2);
    }
}