<?php

namespace App\Http\Controllers\Identity;

use App\Audit\SecurityEventRecorder;
use App\Http\Requests\Identity\LoginIdentityRequest;
use App\Identity\Models\Identity;
use App\Identity\Sessions\IdentityWebSessionManager;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class SessionController
{
    public function create(): View
    {
        return view('identity.login');
    }

    public function store(
        LoginIdentityRequest $request,
        IdentityWebSessionManager $sessions,
        SecurityEventRecorder $securityEvents,
    ): RedirectResponse {
        $identity = $request->authenticate();

        $sessions->login($identity);
        $sessions->establish($request, $identity);
        $securityEvents->recordIdentityLoginSucceeded($identity->id);

        return redirect()->intended(route('home'));
    }

    public function destroy(
        Request $request,
        IdentityWebSessionManager $sessions,
        SecurityEventRecorder $securityEvents,
    ): RedirectResponse {
        $identity = $sessions->user();

        $sessions->invalidate($request);

        if ($identity instanceof Identity) {
            $securityEvents->recordIdentityLoggedOut($identity->id);
        }

        return redirect()->route('home');
    }
}
