<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RendersComingSoon;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;

class AuctionsController extends Controller
{
    use RendersComingSoon;

    public function index(): Response
    {
        return $this->comingSoon('Subastas');
    }

    public function create($pawn): Response
    {
        return $this->comingSoon('Enviar empeño a subasta');
    }

    public function store($pawn): RedirectResponse
    {
        return $this->pendingAction('El envío a subasta está pendiente de implementar.');
    }

    public function show($auction): Response
    {
        return $this->comingSoon('Detalle de subasta');
    }

    public function cancel($auction): RedirectResponse
    {
        return $this->pendingAction('La cancelación de subasta está pendiente de implementar.');
    }

    public function moveToSale($auction): RedirectResponse
    {
        return $this->pendingAction('El movimiento de subasta a venta está pendiente de implementar.');
    }
}