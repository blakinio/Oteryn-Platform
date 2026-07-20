<?php

namespace App\Characters\Actions;

use App\Accounts\Models\IdentityCanaryAccount;
use App\Characters\Contracts\CanaryCharacterCreationGateway;
use App\Characters\Data\CharacterCreationResult;
use App\Characters\Exceptions\CharacterBindingNotReady;
use App\Characters\Exceptions\CharacterInputInvalid;
use App\Characters\Policies\CharacterNamePolicy;
use App\Identity\Models\Identity;

final class CreateCharacter
{
    /** @var list<int> */
    private const ALLOWED_VOCATIONS = [1, 2, 3, 4, 9];

    /** @var list<int> */
    private const ALLOWED_SEXES = [0, 1];

    public function __construct(
        private readonly CharacterNamePolicy $names,
        private readonly CanaryCharacterCreationGateway $gateway,
    ) {}

    public function execute(Identity $identity, string $name, int $vocation, int $sex): CharacterCreationResult
    {
        if (! in_array($vocation, self::ALLOWED_VOCATIONS, true)) {
            throw new CharacterInputInvalid('The selected vocation is not available for character creation.');
        }

        if (! in_array($sex, self::ALLOWED_SEXES, true)) {
            throw new CharacterInputInvalid('The selected sex value is not available for character creation.');
        }

        $canonicalName = $this->names->canonicalize($name);
        $binding = IdentityCanaryAccount::query()->whereKey($identity->id)->first();

        if ($binding === null || ! $binding->isReady() || $binding->canary_account_id === null) {
            throw new CharacterBindingNotReady('Your game account is not ready for character creation.');
        }

        return $this->gateway->create(
            $binding->canary_account_id,
            $canonicalName,
            $vocation,
            $sex,
        );
    }
}
