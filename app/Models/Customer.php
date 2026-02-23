<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'state',
        'city',
        'address',
        'phone',
        'mobile',
        'email',
        'rfc',
        'code_id',
        'type_id',
        'inapam_code',
    ];

    public function hasInapam(): bool
    {
        return (string) ($this->inapam_code ?? '') !== '';
    }

    public function pawns()
    {
        return $this->hasMany(Pawn::class);
    }

    public function transactions()
    {
        // Pawn tiene customer_id, Transaction tiene pawn_id
        return $this->hasManyThrough(Transaction::class, Pawn::class, 'customer_id', 'pawn_id');
    }

    protected ?int $pending = null;

    public function havePending(): int
    {
        if ($this->pending === null) {
            $this->pending = $this->pawns()
                ->whereNull('paid_at')
                ->whereNull('canceled_at')
                ->count();
        }

        return $this->pending;
    }
}