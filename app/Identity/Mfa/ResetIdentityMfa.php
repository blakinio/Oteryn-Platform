<?php

namespace App\Identity\Mfa;

use App\Audit\SecurityEventRecorder;
use App\Identity\Actions\RevokeIdentityWebSessions;
use App\Identity\Models\Identity;
use Illuminate\Support\Facades\DB;

final class ResetIdentityMfa
{
    public function __construct(
        private readonly RevokeIdentityWebSessions $webSessions,
        private readonly SecurityEventRecorder $securityEvents,
    ) {}

    public function execute(Identity $identity): void
    {
        DB::transaction(function () use ($identity): void {
            $identity->forceFill([
                'mfa_secret' => null,
                'mfa_recovery_codes' => null,
                'mfa_confirmed_at' => null,
            ])->save();

            $this->webSessions->execute($identity);
            $this->securityEvents->recordIdentityMfaReset($identity->id);
        });
    }
}
