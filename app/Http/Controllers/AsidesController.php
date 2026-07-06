<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RendersComingSoon;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;

class AsidesController extends Controller
{
    use RendersComingSoon;

    public function index(): Response
    {
        return $this->comingSoon('Apartados');
    }

    public function create(): Response
    {
        return $this->comingSoon('Nuevo apartado');
    }

    public function store(): RedirectResponse
    {
        return $this->pendingAction('La creación de apartados está pendiente de implementar.');
    }

    public function finished(): Response
    {
        return $this->comingSoon('Apartados finalizados');
    }

    public function cancelled(): Response
    {
        return $this->comingSoon('Apartados cancelados');
    }

    public function show($aside): Response
    {
        return $this->comingSoon('Detalle de apartado');
    }

    public function payment($aside): RedirectResponse
    {
        return $this->pendingAction('El abono a apartado está pendiente de implementar.');
    }

    public function finish($aside): RedirectResponse
    {
        return $this->pendingAction('La finalización de apartados está pendiente de implementar.');
    }

    public function cancel($aside): RedirectResponse
    {
        return $this->pendingAction('La cancelación de apartados está pendiente de implementar.');
    }

    public function ticket($aside): Response
    {
        return $this->comingSoon('Ticket de apartado');
    }

    public function historyTicket($aside): Response
    {
        return $this->comingSoon('Historial de apartado');
    }
}