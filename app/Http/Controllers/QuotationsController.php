<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RendersComingSoon;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;

class QuotationsController extends Controller
{
    use RendersComingSoon;

    public function index(): Response
    {
        return $this->comingSoon('Cotizaciones');
    }

    public function create(): Response
    {
        return $this->comingSoon('Nueva cotización');
    }

    public function store(): RedirectResponse
    {
        return $this->pendingAction('La creación de cotizaciones está pendiente de implementar.');
    }

    public function show($quotation): Response
    {
        return $this->comingSoon('Detalle de cotización');
    }

    public function convertToPawn($quotation): RedirectResponse
    {
        return $this->pendingAction('La conversión de cotización a empeño está pendiente de implementar.');
    }

    public function cancel($quotation): RedirectResponse
    {
        return $this->pendingAction('La cancelación de cotizaciones está pendiente de implementar.');
    }
}