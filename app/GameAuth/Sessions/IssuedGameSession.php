<?php

namespace App\GameAuth\Sessions;

use Illuminate\Support\Carbon;

final readonly class IssuedGameSession
{
    public function __construct(
        public string $sessionId,
        public string $credential,
        public int $canaryAccountId,
        public int $worldId,
        public Carbon $createdAt,
        public Carbon $expiresAt,
    ) {}
}
