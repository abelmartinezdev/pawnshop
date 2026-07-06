<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RendersComingSoon;
use Inertia\Response;

class FoliosController extends Controller
{
    use RendersComingSoon;

    public function index(): Response
    {
        return $this->comingSoon('Folios');
    }

    public function reports(): Response
    {
        return $this->comingSoon('Reportes de folios');
    }

    public function renderReports(): Response
    {
        return $this->comingSoon('Resultado de reportes de folios');
    }
}