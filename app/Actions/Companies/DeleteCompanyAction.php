<?php

namespace App\Actions\Companies;

use App\Models\Company;
use App\Models\Office;
use Illuminate\Validation\ValidationException;

class DeleteCompanyAction
{
    public function execute(Company $company): void
    {
        // ✅ No borrar si tienes sucursales activas
        if ($company->offices()->exists()) {
            throw ValidationException::withMessages([
                'company' => 'No puedes archivar una empresa que todavía tiene sucursales. Archiva primero sus sucursales.',
            ]);
        }

        // ✅ No permitir archivar si es la empresa actual (por la sucursal seleccionada)
        $officeId = session('office_id') ?? auth()->user()?->office_id;
        if ($officeId) {
            $office = Office::query()->select('id', 'company_id')->find($officeId);

            if ($office && (int) $office->company_id === (int) $company->id) {
                throw ValidationException::withMessages([
                    'company' => 'No puedes archivar tu empresa actual. Cambia de sucursal/empresa primero.',
                ]);
            }
        }

        $company->delete(); // soft delete
    }
}