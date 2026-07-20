<?php

namespace App\Characters\Data;

final class CharacterCreationResult
{
    public function __construct(
        public readonly int $playerId,
        public readonly string $canonicalName,
        public readonly bool $created,
    ) {}
}
