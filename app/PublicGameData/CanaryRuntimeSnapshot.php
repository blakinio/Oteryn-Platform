<?php

namespace App\PublicGameData;

final readonly class CanaryRuntimeSnapshot
{
    /**
     * @param array<int, CanaryRuntimeStatus|null> $statuses
     */
    private function __construct(
        public bool $available,
        private array $statuses,
    ) {}

    /**
     * @param array<int, CanaryRuntimeStatus|null> $statuses
     */
    public static function available(array $statuses): self
    {
        return new self(true, $statuses);
    }

    public static function unavailable(): self
    {
        return new self(false, []);
    }

    public function forChannel(int $channelId): ?CanaryRuntimeStatus
    {
        return $this->statuses[$channelId] ?? null;
    }
}
