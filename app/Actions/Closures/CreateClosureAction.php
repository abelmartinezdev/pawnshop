<?php

namespace App\Actions\Closures;

use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class CreateClosureAction
{
    public function __construct(
        private readonly CalculateClosurePreviewAction $calculateClosurePreview
    ) {
    }

    public function __invoke(): Response
    {
        $officeId = session('office_id') ?: Auth::user()?->office_id;

        abort_unless($officeId, 404, 'No hay una sucursal activa.');

        return Inertia::render('Closures/Create', [
            'preview' => ($this->calculateClosurePreview)((int) $officeId),
        ]);
    }
}