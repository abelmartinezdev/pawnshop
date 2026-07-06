<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RendersComingSoon;
use Inertia\Response;

class ReportsController extends Controller
{
    use RendersComingSoon;

    public function index(): Response
    {
        return $this->comingSoon('Reportes');
    }

    public function cash(): Response
    {
        return $this->comingSoon('Reporte de caja');
    }

    public function pawns(): Response
    {
        return $this->comingSoon('Reporte de empeños');
    }

    public function sales(): Response
    {
        return $this->comingSoon('Reporte de ventas');
    }

    public function inventory(): Response
    {
        return $this->comingSoon('Reporte de inventario');
    }

    public function folios(): Response
    {
        return $this->comingSoon('Reporte de folios');
    }

    public function generate(): Response
    {
        return $this->comingSoon('Generar reporte');
    }
}