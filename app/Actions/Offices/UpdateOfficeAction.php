<?php

namespace App\Actions\Offices;

use App\Http\Requests\Offices\UpdateOfficeRequest;
use App\Models\Office;
use Illuminate\Http\RedirectResponse;

class UpdateOfficeAction
{
    public function __invoke(UpdateOfficeRequest $request, Office $office): RedirectResponse
    {
        abort_if($office->trashed(), 422, 'No puedes editar una sucursal archivada.');

        $office->update($request->validated());

        return redirect()
            ->route('offices.show', $office->id)
            ->with('success', 'Sucursal actualizada correctamente.');
    }
}