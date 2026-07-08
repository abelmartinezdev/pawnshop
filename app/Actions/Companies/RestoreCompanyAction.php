<?php

namespace App\Actions\Companies;

use App\Models\Company;
use Illuminate\Http\RedirectResponse;

class RestoreCompanyAction
{
    public function __invoke(int $id): RedirectResponse
    {
        $company = Company::onlyTrashed()->findOrFail($id);
        $company->restore();

        return redirect()
            ->route('companies.show', $company->id)
            ->with('success', 'Empresa restaurada correctamente.');
    }

    public function execute(int $id): void
    {
        Company::onlyTrashed()->findOrFail($id)->restore();
    }
}