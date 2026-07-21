<?php

namespace App\GameAuth\Tickets;

use Illuminate\Support\Carbon;

final readonly class RedeemedGameLoginTicket
{
    public function __construct(
        public int $identityId,
        public int $canaryAccountId,
        public int $securityGeneration,
        public Carbon $redeemedAt,
    ) {}
}
