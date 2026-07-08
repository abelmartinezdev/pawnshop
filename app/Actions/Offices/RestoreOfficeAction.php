<?php

namespace App\Actions\Offices;

use App\Models\Office;
use Illuminate\Http\RedirectResponse;

class RestoreOfficeAction
{
    public function __invoke(int $id): RedirectResponse
    {
        $office = Office::onlyTrashed()->findOrFail($id);
        $office->restore();

        return redirect()
            ->route('offices.show', $office->id)
            ->with('success', 'Sucursal restaurada correctamente.');
    }
}