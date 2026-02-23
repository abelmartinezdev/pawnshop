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

        if ($user && $user->office_id) {
            $currentOffice = Office::query()
                ->select('id', 'name', 'company_id')
                ->with('company:id,name')
                ->find($user->office_id);

            $currentCompany = $currentOffice?->company;
        }

        return [
            ...parent::share($request),

            'name' => config('app.name'),
            'flash' => fn () => session('flash'),

            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'office_id' => $user->office_id ?? null,
                ] : null,

                'roles' => fn () => $user ? $user->getRoleNames()->values() : [],
                'permissions' => fn () => $user ? $user->getAllPermissions()->pluck('name')->values() : [],

                'can' => fn () => $user ? [
                    'companies.manage' => $user->can('companies.manage'),
                    'offices.manage'   => $user->can('offices.manage'),
                    'users.manage'     => $user->can('users.manage'),
                    'roles.manage'     => $user->can('roles.manage'),

                    'reports.view'     => $user->can('reports.view'),
                    'cash.manage'      => $user->can('cash.manage'),
                    'sales.manage'     => $user->can('sales.manage'),
                    'pawn.manage'      => $user->can('pawn.manage'),
                ] : [],

                'office' => $currentOffice ? [
                    'id' => $currentOffice->id,
                    'name' => $currentOffice->name,
                    'code' => $currentOffice->code,
                    'company_id' => $currentOffice->company_id,
                ] : null,

                'company' => $currentCompany ? [
                    'id' => $currentCompany->id,
                    'name' => $currentCompany->name,
                    'code' => $currentCompany->code,
                ] : null,
            ],

            'sidebarOpen' => ! $request->hasCookie('sidebar_state')
                || $request->cookie('sidebar_state') === 'true',
        ];
    }
}