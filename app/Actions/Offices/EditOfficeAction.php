<?php

namespace App\Actions\Offices;

use App\Models\Company;
use App\Models\Office;
use Inertia\Inertia;
use Inertia\Response;

class EditOfficeAction
{
    public function __invoke(Office $office): Response
    {
        $companies = Company::query()
            ->orderBy('name')
            ->get(['id', 'name', 'code'])
            ->map(fn (Company $company) => [
                'id' => $company->id,
                'name' => $company->name,
                'code' => $company->code,
            ]);

        return Inertia::render('Offices/Edit', [
            'office' => [
                'id' => $office->id,
                'company_id' => $office->company_id,
                'name' => $office->name,
                'code' => $office->code,
                'serie' => $office->serie,
                'phone' => $office->phone,
                'address' => $office->address,
                'schedule' => $office->schedule,
                'bank_account' => $office->bank_account,
                'daily_interest_rate' => (float) $office->daily_interest_rate,
                'monthly_interest_rate' => (float) $office->monthly_interest_rate,
                'cash' => (float) $office->cash,
                'is_deleted' => $office->trashed(),
            ],
            'companies' => $companies,
        ]);
    }
}