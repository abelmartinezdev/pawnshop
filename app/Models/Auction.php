<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Auction extends Model
{
    use SoftDeletes;

    public const MODE_SELLABLE = 'sellable';
    public const MODE_GROUPED = 'grouped';
    public const MODE_NOT_SELL = 'not_sell';

    protected $fillable = [
        'company_id',
        'office_id',
        'pawn_id',
        'pawn_item_id',
        'product_id',
        'user_id',
        'created_by',
        'unit_number',
        'quantity',
        'auction_mode',
        'description',
        'source_value',
        'value',
        'interest_amount',
        'total',
        'commission',
        'active',
        'not_sell',
        'sold_at',
        'move_at',
    ];

    protected $casts = [
        'company_id' => 'integer',
        'office_id' => 'integer',
        'pawn_id' => 'integer',
        'pawn_item_id' => 'integer',
        'product_id' => 'integer',
        'user_id' => 'integer',
        'created_by' => 'integer',
        'unit_number' => 'integer',

        'quantity' => 'decimal:3',
        'source_value' => 'decimal:3',
        'value' => 'decimal:3',
        'interest_amount' => 'decimal:3',
        'total' => 'decimal:3',
        'commission' => 'decimal:3',

        'active' => 'boolean',
        'not_sell' => 'boolean',

        'sold_at' => 'datetime',
        'move_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function pawn(): BelongsTo
    {
        return $this->belongsTo(Pawn::class);
    }

    public function pawnItem(): BelongsTo
    {
        return $this->belongsTo(PawnItem::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}