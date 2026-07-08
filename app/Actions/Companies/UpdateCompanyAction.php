<?php

namespace App\Actions\Companies;

use App\Http\Requests\Companies\UpdateCompanyRequest;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;

class UpdateCompanyAction
{
    public function __invoke(UpdateCompanyRequest $request, Company $company): RedirectResponse
    {
        $company->update($request->validated());

        return redirect()
            ->route('companies.show', $company->id)
            ->with('success', 'Empresa actualizada correctamente.');
    }

    public function execute(Company $company, array $validated): Company
    {
        $company->update($validated);

        return $company;
    }
}