<?php

namespace App\GameAuth\Context;

final readonly class GameLoginCharacter
{
    public function __construct(
        public int $id,
        public string $name,
        public int $level,
        public int $vocation,
        public int $worldId,
    ) {}
}
