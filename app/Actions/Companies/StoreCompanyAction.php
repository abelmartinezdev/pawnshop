<?php

namespace App\Actions\Companies;

use App\Http\Requests\Companies\StoreCompanyRequest;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;

class StoreCompanyAction
{
    public function __invoke(StoreCompanyRequest $request): RedirectResponse
    {
        $company = Company::query()->create($request->validated());

        return redirect()
            ->route('companies.show', $company->id)
            ->with('success', 'Empresa creada correctamente.');
    }

    public function execute(array $validated): Company
    {
        return Company::query()->create($validated);
    }
}