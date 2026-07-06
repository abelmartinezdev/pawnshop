<?php

namespace App\Http\Controllers\Offices;

use App\Actions\Offices\GetSelectableOfficesAction;
use App\Actions\Offices\SelectOfficeAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OfficeSelectController extends Controller
{
    public function show(Request $request, GetSelectableOfficesAction $getSelectableOffices): Response
    {
        $user = $request->user();

        return Inertia::render('Offices/Select', [
            'offices' => $getSelectableOffices($user),
            'canSelectAnyOffice' => $this->canSelectAnyOffice($user),
            'hasAssignedOffice' => (bool) $user?->office_id,
            'user' => [
                'name' => $user?->name,
                'email' => $user?->email,
            ],
        ]);
    }

    public function store(Request $request, SelectOfficeAction $selectOffice): RedirectResponse
    {
        return $selectOffice($request);
    }

    private function canSelectAnyOffice($user): bool
    {
        if (! $user) {
            return false;
        }

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