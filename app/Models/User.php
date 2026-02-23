<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, TwoFactorAuthenticatable, HasRoles;

    public function currentOffice()
    {
        return $this->belongsTo(Office::class, 'office_id');
    }

    // opcional: acceso directo a company vía office
    public function company()
    {
        return $this->hasOneThrough(
            Company::class,
            Office::class,
            'id',        // FK de offices hacia company? (ojo: esto depende del esquema)
            'id',
            'office_id',
            'company_id'
        );
    }

    protected $fillable = [
        'name',
        'email',
        'password',
        'office_id', // 👈 importante si lo vas a setear
    ];

    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }
}