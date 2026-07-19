<?php

namespace App\PublicGameData;

final readonly class CanaryRuntimeStatus
{
    public function __construct(
        public int $channelId,
        public string $status,
        public int $playersOnline,
    ) {}

    public function isFull(int $maxPlayers): bool
    {
        return $this->status === 'ONLINE'
            && $maxPlayers > 0
            && $this->playersOnline >= $maxPlayers;
    }
}
