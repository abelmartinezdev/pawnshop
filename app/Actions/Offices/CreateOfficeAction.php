<?php

namespace App\Actions\Offices;

use App\Models\Company;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CreateOfficeAction
{
    public function __invoke(Request $request): Response
    {
        $companies = Company::query()
            ->orderBy('name')
            ->get(['id', 'name', 'code'])
            ->map(fn (Company $company) => [
                'id' => $company->id,
                'name' => $company->name,
                'code' => $company->code,
            ]);

        return Inertia::render('Offices/Create', [
            'companies' => $companies,
            'selected_company_id' => $request->integer('company') ?: null,
        ]);
    }
}