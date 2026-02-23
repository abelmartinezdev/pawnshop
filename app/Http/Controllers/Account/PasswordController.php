<?php

namespace App\Http\Controllers\Account;

use App\Actions\Account\UpdatePasswordAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Account\UpdatePasswordRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PasswordController extends Controller
{
    public function edit(Request $request)
    {
        return Inertia::render('Account/Password', [
            'mustChange' => (bool) $request->user()->must_change_password,
        ]);
    }

    public function update(UpdatePasswordRequest $request, UpdatePasswordAction $action)
    {
        $action->execute($request->user(), $request->validated());

        // Re-login opcional (recomendado)
        Auth::login($request->user());

        return redirect()->route('dashboard')
            ->with('flash', ['type' => 'success', 'message' => 'Contraseña actualizada.']);
    }
}