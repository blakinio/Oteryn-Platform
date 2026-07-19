<?php

namespace App\Audit;

use Illuminate\Support\Facades\DB;

final class SecurityEventRecorder
{
    public const IDENTITY_REGISTERED = 'identity.registered';

    public const IDENTITY_LOGIN_SUCCEEDED = 'identity.login_succeeded';

    public const IDENTITY_LOGGED_OUT = 'identity.logged_out';

    public const IDENTITY_WEB_SESSIONS_REVOKED = 'identity.web_sessions_revoked';

    public const IDENTITY_WEB_SESSION_REJECTED = 'identity.web_session_rejected';

    public function recordIdentityRegistered(int $identityId): void
    {
        $this->record($identityId, self::IDENTITY_REGISTERED);
    }

    public function recordIdentityLoginSucceeded(int $identityId): void
    {
        $this->record($identityId, self::IDENTITY_LOGIN_SUCCEEDED);
    }

    public function recordIdentityLoggedOut(int $identityId): void
    {
        $this->record($identityId, self::IDENTITY_LOGGED_OUT);
    }

    public function recordIdentityWebSessionsRevoked(int $identityId): void
    {
        $this->record($identityId, self::IDENTITY_WEB_SESSIONS_REVOKED);
    }

    public function recordIdentityWebSessionRejected(int $identityId): void
    {
        $this->record($identityId, self::IDENTITY_WEB_SESSION_REJECTED);
    }

    private function record(int $identityId, string $eventType): void
    {
        DB::table('identity_security_events')->insert([
            'identity_id' => $identityId,
            'event_type' => $eventType,
            'occurred_at' => now(),
        ]);
    }
}
