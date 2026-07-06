<?php

namespace App\Http\Middleware;

use App\Models\Office;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCurrentOffice
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return redirect()->route('login');
        }

        if ($request->routeIs([
            'offices.select',
            'offices.select.store',
            'logout',
            'account.password.edit',
            'account.password.update',
            'profile.edit',
            'profile.update',
            'profile.destroy',
            'user-password.edit',
            'user-password.update',
            'two-factor.show',
            'appearance.edit',
        ])) {
            return $next($request);
        }

        if (session('office_id')) {
            return $next($request);
        }

        $user = $request->user();

        if ($this->canSelectAnyOffice($user)) {
            return redirect()
                ->route('offices.select')
                ->with('warning', 'Selecciona una sucursal para continuar.');
        }

        if (! $user->office_id) {
            return redirect()
                ->route('offices.select')
                ->with('error', 'Tu usuario no tiene una sucursal asignada. Contacta al administrador.');
        }

        $office = Office::query()->find($user->office_id);

        if (! $office) {
            return redirect()
                ->route('offices.select')
                ->with('error', 'La sucursal asignada a tu usuario no está disponible.');
        }

        session([
            'office_id' => $office->id,
            'company_id' => $office->company_id,
        ]);

        return $next($request);
    }

    private function canSelectAnyOffice($user): bool
    {
        if (method_exists($user, 'hasAnyRole') && $user->hasAnyRole([
            'admin',
            'administrator',
            'super-admin',
            'super_admin',
        ])) {
            return true;
        }

        return $user->can('offices.manage')
            || $user->can('companies.manage')
            || $user->can('roles.manage');
    }
}