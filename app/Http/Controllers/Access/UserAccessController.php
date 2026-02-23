<?php

namespace App\Http\Controllers\Access;

use App\Actions\Access\CreateUserAction;
use App\Actions\Access\UpdateUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Access\StoreUserRequest;
use App\Http\Requests\Access\UpdateUserAccessRequest;
use App\Models\Office;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserAccessController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $perPage = (int) ($request->get('perPage', 10));

        $users = User::query()
            ->select('id', 'name', 'email', 'office_id')
            ->when($q !== '', fn ($x) => $x->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%");
            }))
            ->with(['roles:id,name'])
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Access/Users/Index', [
            'users' => $users,
            'filters' => ['q' => $q, 'perPage' => $perPage],
        ]);
    }

    public function create()
    {
        $roles = Role::query()->where('guard_name', 'web')->orderBy('name')->get(['id','name']);
        $permissions = Permission::query()->where('guard_name', 'web')->orderBy('name')->get(['id','name']);

        $offices = Office::query()
            ->select('id', 'name', 'code', 'company_id')
            ->with('company:id,name,code')
            ->orderBy('name')
            ->get();

        return Inertia::render('Access/Users/Create', [
            'roles' => $roles,
            'permissions' => $permissions,
            'offices' => $offices,
        ]);
    }

    public function store(StoreUserRequest $request, CreateUserAction $action)
    {
        $result = $action->execute($request->validated());

        Session::flash('flash', [
            'type' => 'success',
            'message' => $result['temp_password']
                ? "Usuario creado. Contraseña temporal: {$result['temp_password']}"
                : 'Usuario creado.',
        ]);

        return redirect()->route('access.users.edit', $result['user']->id);
    }

    public function edit(User $user)
    {
        $roles = Role::query()->where('guard_name', 'web')->orderBy('name')->get(['id','name']);
        $permissions = Permission::query()->where('guard_name', 'web')->orderBy('name')->get(['id','name']);

        $offices = Office::query()
            ->select('id', 'name', 'code', 'company_id')
            ->with('company:id,name,code')
            ->orderBy('name')
            ->get();

        return Inertia::render('Access/Users/Edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'office_id' => $user->office_id,
                'roles' => $user->getRoleNames()->values(),
                'permissions' => $user->getDirectPermissions()->pluck('name')->values(),
            ],
            'roles' => $roles,
            'permissions' => $permissions,
            'offices' => $offices,
        ]);
    }

    public function update(User $user, UpdateUserAccessRequest $request, UpdateUserAction $action)
    {
        $action->execute($user, $request->validated());

        return redirect()->route('access.users.edit', $user->id)
            ->with('flash', ['type' => 'success', 'message' => 'Accesos actualizados.']);
    }
}