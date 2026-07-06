<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\RedirectAuthenticatedUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => false,
            'status' => session('status'),
        ]);
    }

    public function store(LoginRequest $request, RedirectAuthenticatedUserAction $redirectAuthenticatedUser): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return $redirectAuthenticatedUser($request);
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->session()->forget([
            'office_id',
            'company_id',
        ]);

        auth()->guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}