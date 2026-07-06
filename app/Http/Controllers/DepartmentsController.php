<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RendersComingSoon;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;

class DepartmentsController extends Controller
{
    use RendersComingSoon;

    public function index(): Response
    {
        return $this->comingSoon('Departamentos');
    }

    public function create(): Response
    {
        return $this->comingSoon('Nuevo departamento');
    }

    public function store(): RedirectResponse
    {
        return $this->pendingAction('La creación de departamentos está pendiente de implementar.');
    }

    public function show($department): Response
    {
        return $this->comingSoon('Detalle de departamento');
    }

    public function edit($department): Response
    {
        return $this->comingSoon('Editar departamento');
    }

    public function update($department): RedirectResponse
    {
        return $this->pendingAction('La actualización de departamentos está pendiente de implementar.');
    }

    public function destroy($department): RedirectResponse
    {
        return $this->pendingAction('La eliminación de departamentos está pendiente de implementar.');
    }

    public function restore($id): RedirectResponse
    {
        return $this->pendingAction('La restauración de departamentos está pendiente de implementar.');
    }
}