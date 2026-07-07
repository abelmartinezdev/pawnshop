<?php

namespace App\Actions\Departments;

use Inertia\Inertia;
use Inertia\Response;

class CreateDepartmentAction
{
    public function __invoke(): Response
    {
        return Inertia::render('Departments/Create', [
            'dayOptions' => range(1, 360),
        ]);
    }
}