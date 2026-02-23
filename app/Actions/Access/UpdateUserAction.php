<?php

namespace App\Actions\Access;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class UpdateUserAction
{
    public function execute(User $user, array $data): void
    {
        DB::transaction(function () use ($user, $data) {

            $roles = collect($data['roles'] ?? [])
                ->filter()->unique()->values();

            $perms = collect($data['permissions'] ?? [])
                ->filter()->unique()->values();

            // Validar que existan (guard web)
            $validRoles = Role::query()
                ->whereIn('name', $roles)
                ->where('guard_name', 'web')
                ->pluck('name');

            $validPerms = Permission::query()
                ->whereIn('name', $perms)
                ->where('guard_name', 'web')
                ->pluck('name');

            $user->syncRoles($validRoles);
            $user->syncPermissions($validPerms);

            if (array_key_exists('branch_id', $data)) {
                $user->branch_id = $data['branch_id'];
                $user->save();
            }

            app(PermissionRegistrar::class)->forgetCachedPermissions();
        });
    }
}