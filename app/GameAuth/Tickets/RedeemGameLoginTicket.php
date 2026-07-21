<?php

namespace App\GameAuth\Tickets;

use App\Accounts\Models\IdentityCanaryAccount;
use App\Audit\SecurityEventRecorder;
use App\Identity\Models\Identity;
use Illuminate\Support\Facades\DB;

final class RedeemGameLoginTicket
{
    public function __construct(
        private readonly GameLoginTicketSecrets $secrets,
        private readonly SecurityEventRecorder $securityEvents,
    ) {}

    public function execute(string $ticket, string $audience): RedeemedGameLoginTicket
    {
        return DB::transaction(function () use ($ticket, $audience): RedeemedGameLoginTicket {
            $storedTicket = GameLoginTicket::query()
                ->where('ticket_hash', $this->secrets->hash($ticket))
                ->lockForUpdate()
                ->first();

            if (! $storedTicket instanceof GameLoginTicket
                || ! hash_equals($storedTicket->audience, $audience)
                || $storedTicket->used_at !== null
                || $storedTicket->expires_at->lte(now())
            ) {
                throw new GameLoginTicketDenied;
            }

            $identity = Identity::query()
                ->lockForUpdate()
                ->find($storedTicket->identity_id);

            if (! $identity instanceof Identity
                || $identity->disabled_at !== null
                || $identity->game_auth_generation !== $storedTicket->security_generation
            ) {
                throw new GameLoginTicketDenied;
            }

            $binding = IdentityCanaryAccount::query()
                ->lockForUpdate()
                ->find($identity->id);

            if (! $binding instanceof IdentityCanaryAccount
                || ! $binding->isReady()
                || $binding->canary_account_id !== $storedTicket->canary_account_id
            ) {
                throw new GameLoginTicketDenied;
            }

            $redeemedAt = now();
            $storedTicket->forceFill(['used_at' => $redeemedAt])->save();

            $this->securityEvents->recordGameLoginTicketRedeemed($identity->id);

            return new RedeemedGameLoginTicket(
                identityId: $identity->id,
                canaryAccountId: $storedTicket->canary_account_id,
                securityGeneration: $storedTicket->security_generation,
                redeemedAt: $redeemedAt,
            );
        });
    }
}
