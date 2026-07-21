<?php

namespace App\GameAuth\Sessions;

interface GameSessionIssuer
{
    public function issue(GameSessionRequest $request): IssuedGameSession;
}
