<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\LoginAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\Route;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    public function store(LoginRequest $request, LoginAction $action)
    {
        $action->execute(
            $request->email(),
            $request->password(),
            $request->remember(),
            $request->ipAddress(),
        );

        $request->session()->regenerate();

        // Flash para toast global si quieres
        return redirect()->intended(route('dashboard'))
            ->with('flash', ['type' => 'success', 'message' => 'Bienvenido.']);
    }

    public function destroy(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}