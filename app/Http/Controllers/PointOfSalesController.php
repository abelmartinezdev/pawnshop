<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RendersComingSoon;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;

class PointOfSalesController extends Controller
{
    use RendersComingSoon;

    public function index(): Response
    {
        return $this->comingSoon('Punto de venta');
    }

    public function inventory(): Response
    {
        return $this->comingSoon('Inventario para venta');
    }

    public function deletedInventory(): Response
    {
        return $this->comingSoon('Inventario eliminado');
    }

    public function restoreInventory($saleItem): RedirectResponse
    {
        return $this->pendingAction('La restauración de inventario está pendiente de implementar.');
    }

    public function destroyInventory($saleItem): RedirectResponse
    {
        return $this->pendingAction('La eliminación de inventario está pendiente de implementar.');
    }

    public function sales(): Response
    {
        return $this->comingSoon('Ventas');
    }

    public function storeSale(): RedirectResponse
    {
        return $this->pendingAction('El registro de ventas está pendiente de implementar.');
    }

    public function showSale($sale): Response
    {
        return $this->comingSoon('Detalle de venta');
    }

    public function ticket($sale): Response
    {
        return $this->comingSoon('Ticket de venta');
    }

    public function cancelSale($sale): RedirectResponse
    {
        return $this->pendingAction('La cancelación de venta está pendiente de implementar.');
    }

    public function reports(): Response
    {
        return $this->comingSoon('Reportes de punto de venta');
    }

    public function renderReports(): Response
    {
        return $this->comingSoon('Resultado de reportes de punto de venta');
    }

    public function move($auction): Response
    {
        return $this->comingSoon('Mover prenda a inventario de venta');
    }

    public function moveStore($auction): RedirectResponse
    {
        return $this->pendingAction('El movimiento a inventario de venta está pendiente de implementar.');
    }

    public function suggestions($saleItem): Response
    {
        return $this->comingSoon('Sugerencias de precio');
    }

    public function suggestionStore($saleItem): RedirectResponse
    {
        return $this->pendingAction('El registro de sugerencias está pendiente de implementar.');
    }
}