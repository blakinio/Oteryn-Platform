<?php

namespace App\Identity\Sessions;

use App\Identity\Models\Identity;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

final class IdentityWebSessionManager
{
    public function login(Identity $identity): void
    {
        $this->guard()->login($identity, false);
    }

    public function user(): ?Authenticatable
    {
        return $this->guard()->user();
    }

    public function establish(Request $request, Identity $identity): void
    {
        $request->session()->regenerate();
        $request->session()->put(WebSessionState::GENERATION_KEY, $identity->web_session_generation);
    }

    public function invalidate(Request $request): void
    {
        $this->guard()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    private function guard(): StatefulGuard
    {
        $guard = Auth::guard('web');

        if (! $guard instanceof StatefulGuard) {
            throw new RuntimeException('The configured web authentication guard must be stateful.');
        }

        return $guard;
    }
}
