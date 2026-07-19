<?php

namespace App\Audit;

use Illuminate\Support\Facades\DB;

final class SecurityEventRecorder
{
    public const IDENTITY_REGISTERED = 'identity.registered';

    public function recordIdentityRegistered(int $identityId): void
    {
        DB::table('identity_security_events')->insert([
            'identity_id' => $identityId,
            'event_type' => self::IDENTITY_REGISTERED,
            'occurred_at' => now(),
        ]);
    }
}
