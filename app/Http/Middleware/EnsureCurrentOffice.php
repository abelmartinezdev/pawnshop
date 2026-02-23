<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureCurrentOffice
{
    public function handle(Request $request, Closure $next)
    {
        $officeId = $request->session()->get('office_id') ?? $request->user()?->office_id;

        if (! $officeId) {
            return redirect()->route('offices.select') // 👈 cambia esta ruta a tu selector real
                ->with('flash', ['type' => 'error', 'message' => 'Selecciona una sucursal para continuar.']);
        }

        $request->attributes->set('office_id', (int) $officeId);

        return $next($request);
    }
}