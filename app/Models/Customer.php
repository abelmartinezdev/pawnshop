<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;

    public const IDENTIFICATION_TYPES = [
        1 => 'INE',
        2 => 'Pasaporte',
        3 => 'Licencia de conducir',
        4 => 'Cartilla militar',
        5 => 'Cédula profesional',
        6 => 'INAPAM',
        7 => 'Otro',
    ];

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

    protected $casts = [
        'type_id' => 'integer',
    ];

    public static function identificationTypes(): array
    {
        return collect(self::IDENTIFICATION_TYPES)
            ->map(fn (string $label, int $id) => [
                'id' => $id,
                'name' => $label,
            ])
            ->values()
            ->all();
    }

    public function pawns(): HasMany
    {
        return $this->hasMany(Pawn::class);
    }

    public function getDisplayPhoneAttribute(): ?string
    {
        return $this->mobile ?: $this->phone;
    }

    public function getFullAddressAttribute(): string
    {
        return collect([
            $this->address,
            $this->city,
            $this->state,
        ])
            ->filter()
            ->join(', ');
    }

    public function getIdentificationTypeLabelAttribute(): ?string
    {
        if (! $this->type_id) {
            return null;
        }

        return self::IDENTIFICATION_TYPES[(int) $this->type_id] ?? 'No especificado';
    }
}