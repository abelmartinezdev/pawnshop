<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RendersComingSoon;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;

class ProductsController extends Controller
{
    use RendersComingSoon;

    public function index(): Response
    {
        return $this->comingSoon('Productos');
    }

    public function create(): Response
    {
        return $this->comingSoon('Nuevo producto');
    }

    public function store(): RedirectResponse
    {
        return $this->pendingAction('La creación de productos está pendiente de implementar.');
    }

    public function show($product): Response
    {
        return $this->comingSoon('Detalle de producto');
    }

    public function edit($product): Response
    {
        return $this->comingSoon('Editar producto');
    }

    public function update($product): RedirectResponse
    {
        return $this->pendingAction('La actualización de productos está pendiente de implementar.');
    }

    public function destroy($product): RedirectResponse
    {
        return $this->pendingAction('La eliminación de productos está pendiente de implementar.');
    }

    public function restore($id): RedirectResponse
    {
        return $this->pendingAction('La restauración de productos está pendiente de implementar.');
    }
}