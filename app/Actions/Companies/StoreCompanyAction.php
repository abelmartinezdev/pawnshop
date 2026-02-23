<?php

namespace App\Actions\Companies;

use App\Models\Company;

class StoreCompanyAction
{
    public function execute(array $data): Company
    {
        // Si mantienes "code" como antes:
        if (isset($data['code'])) {
            $data['code'] = strtoupper(trim((string) $data['code']));
        }

        return Company::create($data);
    }
}