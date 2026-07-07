<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'description',
        'auction',
        'term',
        'loan_rate',
        'daily_interest_rate',
        'monthly_interest_rate',
        'iva_rate',
        'cat_annual',
        'cat_annual_noiva',
        'color',
        'icon',
        'is_active',
    ];

    protected $casts = [
        'auction' => 'integer',
        'term' => 'integer',
        'loan_rate' => 'decimal:3',
        'daily_interest_rate' => 'decimal:3',
        'monthly_interest_rate' => 'decimal:3',
        'iva_rate' => 'decimal:3',
        'cat_annual' => 'decimal:3',
        'cat_annual_noiva' => 'decimal:3',
        'is_active' => 'boolean',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function getDisplayNameAttribute(): string
    {
        return trim($this->code . ' - ' . $this->description);
    }
}