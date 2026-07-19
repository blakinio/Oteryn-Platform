<?php

namespace App\Identity\Actions;

use App\Audit\SecurityEventRecorder;
use App\Identity\Models\Identity;
use Illuminate\Support\Facades\DB;

final class RevokeIdentityWebSessions
{
    public function __construct(
        private readonly SecurityEventRecorder $securityEvents,
    ) {}

    public function execute(Identity $identity): int
    {
        return DB::transaction(function () use ($identity): int {
            Identity::query()
                ->whereKey($identity->id)
                ->increment('web_session_generation');

            $identity->refresh();
            $this->securityEvents->recordIdentityWebSessionsRevoked($identity->id);

            return $identity->web_session_generation;
        });
    }
}
