<?php

namespace App\GameAuth\Context;

use App\GameAuth\Worlds\DatabaseWorldRegistry;
use App\PublicGameData\CanaryGameDataRepository;
use stdClass;

final class GameLoginContextProvider
{
    public function __construct(
        private readonly DatabaseWorldRegistry $worlds,
        private readonly CanaryGameDataRepository $canary,
    ) {}

    public function forAccount(int $canaryAccountId): GameLoginContext
    {
        if ($canaryAccountId < 1) {
            throw new GameLoginContextUnavailable('invalid_account', 422);
        }

        $worlds = $this->worlds->forAccount($canaryAccountId);

        if ($worlds === []) {
            throw new GameLoginContextUnavailable('world_unavailable', 503);
        }

        if (count($worlds) !== 1) {
            throw new GameLoginContextUnavailable('world_mapping_ambiguous', 409);
        }

        $world = $worlds[0];
        $characters = [];

        foreach ($this->canary->activeCharactersForAccount($canaryAccountId) as $row) {
            $characters[] = $this->characterFromRow($row, $world->id);
        }

        return new GameLoginContext($world, $characters);
    }

    private function characterFromRow(stdClass $row, int $worldId): GameLoginCharacter
    {
        $id = $row->id ?? null;
        $name = $row->name ?? null;
        $level = $row->level ?? null;
        $vocation = $row->vocation ?? null;

        if ((! is_int($id) && ! is_string($id))
            || ! is_string($name)
            || (! is_int($level) && ! is_string($level))
            || (! is_int($vocation) && ! is_string($vocation))
            || ! ctype_digit((string) $id)
            || ! ctype_digit((string) $level)
            || ! ctype_digit((string) $vocation)
        ) {
            throw new GameLoginContextUnavailable('character_data_invalid', 503);
        }

        return new GameLoginCharacter(
            id: (int) $id,
            name: $name,
            level: (int) $level,
            vocation: (int) $vocation,
            worldId: $worldId,
        );
    }
}
