<?php

namespace App\Actions\Expenses;

use App\Models\Office;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class CreateExpenseAction
{
    public function __invoke(): Response
    {
        $officeId = session('office_id') ?: Auth::user()?->office_id;

        abort_unless($officeId, 404, 'No hay una sucursal activa.');

        $office = Office::query()
            ->select('id', 'name', 'cash')
            ->findOrFail($officeId);

        return Inertia::render('Expenses/Create', [
            'office' => [
                'id' => $office->id,
                'name' => $office->name,
                'cash' => (float) $office->cash,
            ],
            'paymentTypes' => [
                ['value' => 'cash', 'label' => 'Efectivo'],
                ['value' => 'card', 'label' => 'Tarjeta'],
            ],
        ]);
    }
}