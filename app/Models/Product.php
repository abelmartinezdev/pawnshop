<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'department_id',
        'code',
        'description',
        'unit',
        'min_price',
        'max_price',
        'is_active',
    ];

    protected $casts = [
        'department_id' => 'integer',
        'min_price' => 'decimal:3',
        'max_price' => 'decimal:3',
        'is_active' => 'boolean',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function pawnItems(): HasMany
    {
        return $this->hasMany(PawnItem::class);
    }

    public function getPriceRangeAttribute(): string
    {
        return '$' . number_format((float) $this->min_price, 3)
            . ' - $'
            . number_format((float) $this->max_price, 3);
    }

    public function getDisplayNameAttribute(): string
    {
        return trim($this->code . ' - ' . $this->description);
    }
}