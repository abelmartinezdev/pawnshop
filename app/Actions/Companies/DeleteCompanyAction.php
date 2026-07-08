<?php

namespace App\Actions\Companies;

use App\Models\Company;
use Illuminate\Http\RedirectResponse;

class DeleteCompanyAction
{
    public function __invoke(Company $company): RedirectResponse
    {
        $company->delete();

        return redirect()
            ->route('companies.index')
            ->with('success', 'Empresa archivada correctamente.');
    }

    public function execute(Company $company): void
    {
        $company->delete();
    }
}