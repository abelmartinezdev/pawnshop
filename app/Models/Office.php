<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Office extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'name',
        'code',
        'serie',
        'phone',
        'address',
        'schedule',
        'bank_account',
        'daily_interest_rate',
        'monthly_interest_rate',
        'cash',
    ];

    protected $casts = [
        'daily_interest_rate' => 'decimal:4',
        'monthly_interest_rate' => 'decimal:4',
        'cash' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function pawns(): HasMany
    {
        return $this->hasMany(Pawn::class);
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->trashed() ? 'Archivada' : 'Activa';
    }

    public function getDisplayNameAttribute(): string
    {
        return trim(($this->serie ? "{$this->serie} · " : '') . $this->name);
    }
}