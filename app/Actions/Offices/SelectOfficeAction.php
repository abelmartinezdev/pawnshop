<?php

namespace App\Actions\Offices;

use App\Models\Office;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SelectOfficeAction
{
    public function __invoke(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'office_id' => ['required', 'integer', 'exists:offices,id'],
        ]);

        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $office = Office::query()
            ->with('company')
            ->findOrFail($validated['office_id']);

        if (! $this->canSelectOffice($user, $office)) {
            throw ValidationException::withMessages([
                'office_id' => 'No tienes permiso para entrar a esta sucursal.',
            ]);
        }

        session([
            'office_id' => $office->id,
            'company_id' => $office->company_id,
        ]);

        if (! $this->canSelectAnyOffice($user) && (int) $user->office_id !== (int) $office->id) {
            $user->forceFill([
                'office_id' => $office->id,
            ])->save();
        }

        return redirect()
            ->route('dashboard')
            ->with('success', "Entraste a la sucursal {$office->name}.");
    }

    private function canSelectOffice($user, Office $office): bool
    {
        if ($this->canSelectAnyOffice($user)) {
            return true;
        }

        return (int) $user->office_id === (int) $office->id;
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