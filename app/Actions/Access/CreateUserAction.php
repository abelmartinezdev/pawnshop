<?php

namespace App\Actions\Access;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateUserAction
{
    /**
     * @return array{user: User, temp_password: string|null}
     */
    public function execute(array $data): array
    {
        $temp = null;

        $password = $data['password'] ?? null;
        if (!is_string($password) || trim($password) === '') {
            // temporal para que el admin la comparta (luego podemos meter “enviar enlace”)
            $temp = Str::password(12);
            $password = $temp;
            $mustChange = true;
        }

        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'branch_id' => (int) $data['branch_id'],
            'password' => Hash::make($password),

            // importantísimo por tu middleware ['verified'] en dashboard:
            'email_verified_at' => now(),
        ]);

        // Roles / permisos
        $roles = $data['roles'] ?? [];
        $perms = $data['permissions'] ?? [];

        if (!empty($roles)) {
            $user->syncRoles($roles);
        }

        if (!empty($perms)) {
            $user->syncPermissions($perms);
        }

        return ['user' => $user, 'temp_password' => $temp];
    }
}