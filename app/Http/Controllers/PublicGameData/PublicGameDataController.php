<?php

namespace App\Http\Controllers\PublicGameData;

use App\PublicGameData\CanaryGameDataRepository;
use Illuminate\Contracts\View\View;

final class PublicGameDataController
{
    public function __construct(private readonly CanaryGameDataRepository $gameData)
    {
    }

    public function highscores(): View
    {
        return view('game.highscores', [
            'players' => $this->gameData->levelHighscores(),
        ]);
    }

    public function character(string $name): View
    {
        $character = $this->gameData->findActiveCharacter($name);

        abort_if($character === null, 404);

        return view('game.character', ['character' => $character]);
    }

    public function guild(string $name): View
    {
        $result = $this->gameData->findGuild($name);

        abort_if($result === null, 404);

        return view('game.guild', $result);
    }

    public function servers(): View
    {
        return view('game.servers', [
            'channels' => $this->gameData->configuredChannels(),
        ]);
    }
}
