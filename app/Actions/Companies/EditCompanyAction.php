<?php

namespace App\Actions\Companies;

use App\Models\Company;
use Inertia\Inertia;
use Inertia\Response;

class EditCompanyAction
{
    public function __invoke(Company $company): Response
    {
        return Inertia::render('Companies/Edit', [
            'company' => [
                'id' => $company->id,
                'name' => $company->name,
                'code' => $company->code,
                'rfc' => $company->rfc,
                'phone' => $company->phone,
                'email' => $company->email,
                'address' => $company->address,
                'website' => $company->website,
                'is_active' => (bool) $company->is_active,

                'storage_commission' => (float) $company->storage_commission,
                'marketing_commission' => (float) $company->marketing_commission,
                'delayed_payment_commission' => (float) $company->delayed_payment_commission,
                'replacement_contract_commission' => (float) $company->replacement_contract_commission,
            ],
        ]);
    }
}