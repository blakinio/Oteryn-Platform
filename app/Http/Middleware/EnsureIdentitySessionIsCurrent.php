<?php

namespace App\Http\Middleware;

use App\Audit\SecurityEventRecorder;
use App\Identity\Models\Identity;
use App\Identity\Sessions\IdentityWebSessionManager;
use App\Identity\Sessions\WebSessionState;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureIdentitySessionIsCurrent
{
    public function __construct(
        private readonly IdentityWebSessionManager $sessions,
        private readonly SecurityEventRecorder $securityEvents,
    ) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $identity = $this->sessions->user();

        if ($identity === null) {
            return $next($request);
        }

        if (! $identity instanceof Identity) {
            $this->sessions->invalidate($request);

            return $next($request);
        }

        $sessionGeneration = $request->session()->get(WebSessionState::GENERATION_KEY);
        $sessionIsCurrent = is_int($sessionGeneration)
            && $sessionGeneration === $identity->web_session_generation
            && $identity->disabled_at === null;

        if (! $sessionIsCurrent) {
            $identityId = $identity->id;

            $this->sessions->invalidate($request);
            $this->securityEvents->recordIdentityWebSessionRejected($identityId);
        }

        return $next($request);
    }
}
