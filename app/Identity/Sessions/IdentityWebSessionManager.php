<?php

namespace App\Identity\Sessions;

use App\Identity\Models\Identity;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class IdentityWebSessionManager
{
    public function login(Identity $identity): void
    {
        Auth::login($identity, false);
    }

    public function user(): ?Authenticatable
    {
        return Auth::user();
    }

    public function establish(Request $request, Identity $identity): void
    {
        $request->session()->regenerate();
        $request->session()->put(WebSessionState::GENERATION_KEY, $identity->web_session_generation);
    }

    public function invalidate(Request $request): void
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
