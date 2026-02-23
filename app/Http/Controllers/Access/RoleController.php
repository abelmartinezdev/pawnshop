<?php

namespace App\Http\Controllers\Access;

use App\Http\Controllers\Controller;
use App\Http\Requests\Access\StoreRoleRequest;
use App\Http\Requests\Access\UpdateRoleRequest;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::query()
            ->where('guard_name', 'web')
            ->withCount('users')
            ->orderBy('name')
            ->get(['id', 'name', 'guard_name']);

        return Inertia::render('Access/Roles/Index', [
            'roles' => $roles,
        ]);
    }

    public function store(StoreRoleRequest $request)
    {
        $name = $request->validated()['name'];
        Role::findOrCreate($name, 'web');

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('access.roles.index')
            ->with('flash', ['type' => 'success', 'message' => 'Rol creado.']);
    }

    public function edit(Role $role)
    {
        abort_unless($role->guard_name === 'web', 404);

        $permissions = Permission::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Access/Roles/Edit', [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions()->pluck('name')->values(),
            ],
            'permissions' => $permissions,
        ]);
    }

    public function update(Role $role, UpdateRoleRequest $request)
    {
        abort_unless($role->guard_name === 'web', 404);

        $perms = collect($request->validated()['permissions'] ?? [])
            ->filter()
            ->unique()
            ->values();

        DB::transaction(function () use ($role, $perms) {
            $valid = Permission::query()
                ->where('guard_name', 'web')
                ->whereIn('name', $perms)
                ->pluck('name');

            $role->syncPermissions($valid);
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('access.roles.edit', $role->id)
            ->with('flash', ['type' => 'success', 'message' => 'Permisos actualizados.']);
    }
}