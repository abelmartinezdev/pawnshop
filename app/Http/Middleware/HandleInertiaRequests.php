<?php

namespace App\Http\Middleware;

use App\Models\Office;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();

        $currentOffice = null;
        $currentCompany = null;

        if ($user) {
            $officeId = session('office_id') ?: $user->office_id;

            if ($officeId) {
                $currentOffice = Office::query()
                    ->select([
                        'id',
                        'company_id',
                        'name',
                        'code',
                        'serie',
                        'phone',
                        'address',
                        'cash',
                    ])
                    ->with('company:id,name')
                    ->find($officeId);

                $currentCompany = $currentOffice?->company;
            }
        }

        return [
            ...parent::share($request),

            'name' => config('app.name'),

            'flash' => fn () => [
                'success' => session('success'),
                'error' => session('error'),
                'warning' => session('warning'),
                'status' => session('status'),
            ],

            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'office_id' => $user->office_id ?? null,
                ] : null,

                'roles' => fn () => $user && method_exists($user, 'getRoleNames')
                    ? $user->getRoleNames()->values()
                    : [],

                'permissions' => fn () => $user && method_exists($user, 'getAllPermissions')
                    ? $user->getAllPermissions()->pluck('name')->values()
                    : [],

                'can' => fn () => $user ? [
                    'companies.manage' => $user->can('companies.manage'),
                    'offices.manage' => $user->can('offices.manage'),
                    'users.manage' => $user->can('users.manage'),
                    'roles.manage' => $user->can('roles.manage'),

                    'reports.view' => $user->can('reports.view'),
                    'cash.manage' => $user->can('cash.manage'),
                    'sales.manage' => $user->can('sales.manage'),
                    'pawn.manage' => $user->can('pawn.manage'),
                ] : [],
            ],

            'activeOffice' => $currentOffice ? [
                'id' => $currentOffice->id,
                'name' => $currentOffice->name,
                'code' => $currentOffice->code,
                'serie' => $currentOffice->serie,
                'phone' => $currentOffice->phone,
                'address' => $currentOffice->address,
                'cash' => (float) $currentOffice->cash,
                'company_id' => $currentOffice->company_id,
            ] : null,

            'activeCompany' => $currentCompany ? [
                'id' => $currentCompany->id,
                'name' => $currentCompany->name,
            ] : null,

            'sidebarOpen' => ! $request->hasCookie('sidebar_state')
                || $request->cookie('sidebar_state') === 'true',
        ];
    }
}