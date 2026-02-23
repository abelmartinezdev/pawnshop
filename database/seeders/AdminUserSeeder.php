<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Office;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Busca la sucursal "MATRIZ" por serie (equivalente al code anterior)
        $office = Office::query()
            ->where('serie', 'MATRIZ')
            ->first();

        // Fallback: si por alguna razón no existe, toma la primera
        if (! $office) {
            $office = Office::query()->orderBy('id')->first();
        }

        $user = User::updateOrCreate(
            ['email' => 'admin@demo.com'],
            [
                'name' => 'Abel Emmanuel Martinez Torres',
                'password' => Hash::make('123456'),
                'email_verified_at' => now(),
                'office_id' => $office?->id, // ✅ asigna sucursal (Office)
            ]
        );

        $user->syncRoles(['admin']);
    }
}