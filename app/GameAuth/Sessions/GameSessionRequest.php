<?php

namespace App\GameAuth\Sessions;

final readonly class GameSessionRequest
{
    public function __construct(
        public int $canaryAccountId,
        public int $worldId,
        public string $loginAttemptId,
    ) {}
}
