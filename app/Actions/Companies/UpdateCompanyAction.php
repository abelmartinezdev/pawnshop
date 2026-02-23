<?php

namespace App\Actions\Companies;

use App\Models\Company;

class UpdateCompanyAction
{
    public function execute(Company $company, array $data): Company
    {
        if (isset($data['code'])) {
            $data['code'] = strtoupper(trim((string) $data['code']));
        }

        $company->update($data);

        return $company;
    }
}