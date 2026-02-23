<?php

namespace App\Actions\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginAction
{
    public function execute(string $email, string $password, bool $remember, string $ip): void
    {
        $this->ensureIsNotRateLimited($email, $ip);

        $ok = Auth::attempt(
            ['email' => $email, 'password' => $password],
            $remember
        );

        if (! $ok) {
            RateLimiter::hit($this->throttleKey($email, $ip));

            throw ValidationException::withMessages([
                'email' => 'Las credenciales no coinciden con nuestros registros.',
            ]);
        }

        RateLimiter::clear($this->throttleKey($email, $ip));
    }

    private function ensureIsNotRateLimited(string $email, string $ip): void
    {
        $key = $this->throttleKey($email, $ip);

        if (! RateLimiter::tooManyAttempts($key, 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($key);

        throw ValidationException::withMessages([
            'email' => "Demasiados intentos. Intenta de nuevo en {$seconds} segundos.",
        ]);
    }

    private function throttleKey(string $email, string $ip): string
    {
        return Str::lower($email).'|'.$ip;
    }
}