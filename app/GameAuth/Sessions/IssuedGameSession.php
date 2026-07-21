<?php

namespace App\GameAuth\Sessions;

use Illuminate\Support\Carbon;

final readonly class IssuedGameSession
{
    public function __construct(
        public string $credential,
        public Carbon $expiresAt,
    ) {}
}
