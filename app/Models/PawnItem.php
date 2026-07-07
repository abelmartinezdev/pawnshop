<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PawnItem extends Model
{
    protected $fillable = [
        'pawn_id',
        'product_id',
        'quantity',
        'description',
        'value',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'value' => 'decimal:2',
    ];

    public function pawn(): BelongsTo
    {
        return $this->belongsTo(Pawn::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}