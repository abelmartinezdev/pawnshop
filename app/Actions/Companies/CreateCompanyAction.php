<?php

namespace App\Actions\Companies;

use Inertia\Inertia;
use Inertia\Response;

class CreateCompanyAction
{
    public function __invoke(): Response
    {
        return Inertia::render('Companies/Create');
    }
}