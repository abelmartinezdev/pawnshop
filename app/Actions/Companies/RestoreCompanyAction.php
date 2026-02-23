<?php

namespace App\Actions\Companies;

use App\Models\Company;

class RestoreCompanyAction
{
    public function execute(int $id): void
    {
        $company = Company::withTrashed()->findOrFail($id);

        if ($company->trashed()) {
            $company->restore();
        }
    }
}