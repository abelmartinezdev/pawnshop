<?php

namespace App\Actions\Offices;

use App\Models\Office;
use Illuminate\Support\Collection;

class GetSelectableOfficesAction
{
    public function __invoke($user): Collection
    {
        if (! $user) {
            return collect();
        }

        $query = Office::query()
            ->with('company')
            ->orderBy('name');

        if (! $this->canSelectAnyOffice($user)) {
            if (! $user->office_id) {
                return collect();
            }

            $query->whereKey($user->office_id);
        }

        return $query
            ->get()
            ->map(fn (Office $office) => [
                'id' => $office->id,
                'name' => $office->name,
                'code' => $office->code,
                'serie' => $office->serie,
                'phone' => $office->phone,
                'address' => $office->address,
                'cash' => (float) $office->cash,
                'company' => $office->company ? [
                    'id' => $office->company->id,
                    'name' => $office->company->name,
                ] : null,
            ]);
    }

    private function canSelectAnyOffice($user): bool
    {
        if (method_exists($user, 'hasAnyRole') && $user->hasAnyRole([
            'admin',
            'administrator',
            'super-admin',
            'super_admin',
        ])) {
            return true;
        }

        return $user->can('offices.manage')
            || $user->can('companies.manage')
            || $user->can('roles.manage');
    }
}