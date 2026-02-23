<?php

namespace App\Http\Controllers\Access;

use App\Http\Controllers\Controller;
use App\Http\Requests\Access\StorePermissionRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        $permissions = Permission::query()
            ->where('guard_name', 'web')
            ->when($q !== '', fn ($x) => $x->where('name', 'like', "%{$q}%"))
            ->orderBy('name')
            ->get(['id', 'name', 'guard_name']);

        $grouped = $permissions->groupBy(function ($p) {
            $name = (string) $p->name;
            return str_contains($name, '.') ? explode('.', $name, 2)[0] : 'misc';
        })->map(fn ($items) => $items->values());

        return Inertia::render('Access/Permissions/Index', [
            'permissions' => $permissions,
            'grouped' => $grouped,
            'filters' => ['q' => $q],
        ]);
    }

    public function store(StorePermissionRequest $request)
    {
        $name = $request->validated()['name'];

        Permission::findOrCreate($name, 'web');

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('access.permissions.index')
            ->with('flash', ['type' => 'success', 'message' => 'Permiso creado.']);
    }

    public function destroy(Permission $permission)
    {
        abort_unless($permission->guard_name === 'web', 404);
        abort_unless(auth()->user()?->can('roles.manage'), 403);

        // ⚠️ Esto elimina y limpia pivotes (role_has_permissions / model_has_permissions)
        // En la migración de Spatie normalmente hay FKs/cascade.
        $permission->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('access.permissions.index')
            ->with('flash', ['type' => 'success', 'message' => 'Permiso eliminado.']);
    }
}