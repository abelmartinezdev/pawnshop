<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\Office;

class CompanyOfficeSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Company (empresa)
        $company = Company::updateOrCreate(
            ['name' => 'Empresa Demo'], // cámbialo por "La Joya", "Prestabaja", etc.
            [
                'rfc' => null,
                'address' => null,
                'phone' => null,
                'email' => null,
                'website' => null,

                // si tu tabla companies tiene estas comisiones, pon defaults
                'storage_commission' => 0,
                'marketing_commission' => 0,
                'delayed_payment_commission' => 0,
                'replacement_contract_commission' => 0,
            ]
        );

        // 2) Office (sucursal)
        Office::updateOrCreate(
            [
                'company_id' => $company->id,
                'serie' => 'MATRIZ', // folio prefix / serie de la sucursal
            ],
            [
                'name' => 'Sucursal Matriz',
                'address' => null,
                'phone' => null,
                'schedule' => null,
                'bank_account' => null,

                // tasas por default (ajusta a tu negocio)
                'daily_interest_rate' => 0,
                'monthly_interest_rate' => 0,
            ]
        );
    }
}