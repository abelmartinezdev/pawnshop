<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Office extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'company_id',
        'address',
        'phone',
        'email',
        'serie',
        'schedule',
        'bank_account',
        'daily_interest_rate',
        'monthly_interest_rate',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'daily_interest_rate' => 'decimal:2',
        'monthly_interest_rate' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    // Si ya tienes estos modelos, descomenta:
    public function pawns(): HasMany
    {
        return $this->hasMany(Pawn::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function closures(): HasMany
    {
        return $this->hasMany(Closure::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}