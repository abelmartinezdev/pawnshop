<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForcePasswordChange
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // Si no hay sesión, no hacemos nada
        if (! $user) {
            return $next($request);
        }

        // Si no requiere cambio, seguimos normal
        if (! $user->must_change_password) {
            return $next($request);
        }

        // Rutas permitidas aunque esté bloqueado
        if (
            $request->routeIs('account.password.edit') ||
            $request->routeIs('account.password.update') ||
            $request->routeIs('logout')
        ) {
            return $next($request);
        }

        // Evita loop si el usuario intenta acceder a otras pantallas
        return redirect()->route('account.password.edit')
            ->with('flash', [
                'type' => 'error',
                'message' => 'Debes cambiar tu contraseña para continuar.',
            ]);
    }
}