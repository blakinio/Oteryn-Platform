<?php

namespace App\Identity\Actions;

use App\Audit\SecurityEventRecorder;
use App\Identity\Models\Identity;
use Illuminate\Support\Facades\DB;

final class RevokeIdentityGameAuthorizations
{
    public function __construct(
        private readonly SecurityEventRecorder $securityEvents,
    ) {}

    public function execute(Identity $identity): int
    {
        return DB::transaction(function () use ($identity): int {
            Identity::query()
                ->whereKey($identity->id)
                ->increment('game_auth_generation');

            $identity->refresh();
            $this->securityEvents->recordIdentityGameAuthorizationsRevoked($identity->id);

            return $identity->game_auth_generation;
        });
    }
}
