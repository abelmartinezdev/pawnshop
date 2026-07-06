<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

trait RendersComingSoon
{
    protected function comingSoon(string $title, ?string $description = null): Response
    {
        return Inertia::render('ComingSoon', [
            'title' => $title,
            'description' => $description ?? 'Este módulo ya está registrado en rutas y será implementado en la siguiente etapa.',
        ]);
    }

    protected function pendingAction(string $message = 'Acción registrada como pendiente de implementar.'): RedirectResponse
    {
        return back()->with('success', $message);
    }
}