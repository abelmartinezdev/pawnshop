<?php

namespace App\Actions\Offices;

use App\Http\Requests\Offices\StoreOfficeRequest;
use App\Models\Office;
use Illuminate\Http\RedirectResponse;

class StoreOfficeAction
{
    public function __invoke(StoreOfficeRequest $request): RedirectResponse
    {
        $office = Office::query()->create($request->validated());

        return redirect()
            ->route('offices.show', $office->id)
            ->with('success', 'Sucursal creada correctamente.');
    }
}