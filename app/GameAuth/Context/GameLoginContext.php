<?php

namespace App\GameAuth\Context;

use App\GameAuth\Worlds\GameWorldRoute;

final readonly class GameLoginContext
{
    /**
     * @param  list<GameLoginCharacter>  $characters
     */
    public function __construct(
        public GameWorldRoute $world,
        public array $characters,
    ) {}
}
