<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // ✅ Si venías de branches.manage, lo “migramos” en caliente:
        $old = Permission::query()->where('guard_name', 'web')->where('name', 'branches.manage')->first();
        if ($old && !Permission::query()->where('guard_name', 'web')->where('name', 'companies.manage')->exists()) {
            $old->name = 'companies.manage';
            $old->save();
        }

        $perms = [
            'companies.manage',
            'offices.manage',

            'pawn.manage',
            'cash.manage',
            'sales.manage',
            'reports.view',

            'users.manage',
            'roles.manage',
        ];

        foreach ($perms as $p) {
            Permission::findOrCreate($p, 'web');
        }

        $admin = Role::findOrCreate('admin', 'web');
        $caja = Role::findOrCreate('caja', 'web');
        $gerencia = Role::findOrCreate('gerencia', 'web');

        $admin->syncPermissions(Permission::where('guard_name', 'web')->get());

        $caja->syncPermissions(['cash.manage', 'sales.manage']);
        $gerencia->syncPermissions([
            'reports.view', 'pawn.manage', 'cash.manage', 'sales.manage',
            'companies.manage', 'offices.manage',
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}