<?php

namespace App\GameAuth\Worlds;

interface WorldRegistry
{
    /**
     * @return list<GameWorldRoute>
     */
    public function forAccount(int $canaryAccountId): array;
}
