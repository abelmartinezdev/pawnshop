<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Closure extends Model
{
    protected $table = 'closures';

    protected $fillable = [
        'company_id',
        'office_id',
        'user_id',
        'period_start_at',
        'period_end_at',
        'closed_at',
        'opening_cash',
        'cash_in',
        'cash_out',
        'expected_cash',
        'counted_cash',
        'difference',
        'total_transactions',
        'comments',
        'data',
    ];

    protected $casts = [
        'period_start_at' => 'datetime',
        'period_end_at' => 'datetime',
        'closed_at' => 'datetime',
        'opening_cash' => 'decimal:2',
        'cash_in' => 'decimal:2',
        'cash_out' => 'decimal:2',
        'expected_cash' => 'decimal:2',
        'counted_cash' => 'decimal:2',
        'difference' => 'decimal:2',
        'total_transactions' => 'integer',
        'data' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}