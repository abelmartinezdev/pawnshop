<?php

namespace App\Actions\Auth;

use App\Models\Office;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class RedirectAuthenticatedUserAction
{
    public function __invoke(Request $request): RedirectResponse
    {
        $user = $request->user();

        $request->session()->forget([
            'office_id',
            'company_id',
        ]);

        if (! $user) {
            return redirect()->route('login');
        }

        if ($this->mustChangePassword($user)) {
            return redirect()
                ->route('account.password.edit')
                ->with('warning', 'Por seguridad, primero actualiza tu contraseña.');
        }

        if ($this->canSelectAnyOffice($user)) {
            return redirect()
                ->route('offices.select')
                ->with('success', 'Bienvenido. Selecciona la sucursal con la que vas a trabajar.');
        }

        if (! $user->office_id) {
            return redirect()
                ->route('offices.select')
                ->with('error', 'Tu usuario no tiene una sucursal asignada. Contacta al administrador.');
        }

        $office = Office::query()
            ->with('company')
            ->find($user->office_id);

        if (! $office) {
            return redirect()
                ->route('offices.select')
                ->with('error', 'La sucursal asignada a tu usuario no existe o fue eliminada.');
        }

        $request->session()->put('office_id', $office->id);

        if ($office->company_id) {
            $request->session()->put('company_id', $office->company_id);
        }

        return redirect()->intended(route('dashboard'));
    }

    private function mustChangePassword($user): bool
    {
        return Schema::hasColumn($user->getTable(), 'must_change_password')
            && (bool) $user->must_change_password;
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