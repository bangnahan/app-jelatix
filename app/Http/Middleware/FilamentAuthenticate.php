<?php

namespace App\Http\Middleware;

use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate as BaseAuthenticate;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FilamentAuthenticate extends BaseAuthenticate
{
    /**
     * @param  Request  $request
     * @param  array<string>  $guards
     */
    protected function authenticate($request, array $guards): void
    {
        $guard = Filament::auth();

        if (! $guard->check()) {
            $this->unauthenticated($request, $guards);

            return;
        }

        $this->auth->shouldUse(Filament::getAuthGuard());

        $user = $guard->user();
        $panel = Filament::getCurrentPanel();

        if ($user instanceof FilamentUser && ! $user->canAccessPanel($panel)) {
            // Jika user login dengan role yang tidak memiliki akses ke panel ini (contoh: EO membuka /admin),
            // lakukan logout otomatis agar user dialihkan ke halaman login tanpa terjebak error 403 Forbidden.
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw new AuthenticationException(
                'Akun Anda tidak memiliki izin untuk mengakses panel ini.',
                $guards,
                Filament::getLoginUrl()
            );
        }
    }
}
