<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'rfc',
        'phone',
        'email',
        'address',
        'website',
        'is_active',

        // si luego los necesitas:
        'storage_commission',
        'marketing_commission',
        'delayed_payment_commission',
        'replacement_contract_commission',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'storage_commission' => 'decimal:2',
        'marketing_commission' => 'decimal:2',
        'delayed_payment_commission' => 'decimal:2',
        'replacement_contract_commission' => 'decimal:2',
    ];

    public function offices(): HasMany
    {
        return $this->hasMany(Office::class);
    }

    // scope útil
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}