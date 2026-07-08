<?php

namespace App\Actions\Offices;

use App\Models\Office;
use Illuminate\Http\RedirectResponse;

class DeleteOfficeAction
{
    public function __invoke(Office $office): RedirectResponse
    {
        $office->delete();

        return redirect()
            ->route('offices.index')
            ->with('success', 'Sucursal archivada correctamente.');
    }
}