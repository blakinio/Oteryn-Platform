<?php

namespace App\Characters\Contracts;

use App\Characters\Data\CharacterCreationResult;
use App\Characters\Exceptions\CharacterCreationException;

interface CanaryCharacterCreationGateway
{
    /**
     * @throws CharacterCreationException
     */
    public function create(int $accountId, string $canonicalName, int $vocation, int $sex): CharacterCreationResult;
}
