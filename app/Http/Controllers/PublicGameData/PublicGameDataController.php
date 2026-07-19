<?php

namespace App\Http\Controllers\PublicGameData;

use App\PublicGameData\CanaryChannelRuntimeService;
use App\PublicGameData\CanaryGameDataRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use UnexpectedValueException;

final class PublicGameDataController
{
    public function __construct(
        private readonly CanaryGameDataRepository $gameData,
        private readonly CanaryChannelRuntimeService $runtime,
    ) {}

    public function highscores(): View
    {
        return view('game.highscores', [
            'players' => $this->gameData->levelHighscores(),
        ]);
    }

    public function characterSearch(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);
        $name = $request->input('name');

        if (! is_string($name)) {
            abort(422);
        }

        return redirect()->route('game.characters.show', [
            'name' => $name,
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
        $channels = $this->gameData->configuredChannels();

        /** @var list<int> */
        $channelIds = $channels
            ->pluck('id')
            ->map(static function (mixed $channelId): int {
                if (is_int($channelId)) {
                    return $channelId;
                }

                if (is_string($channelId) && ctype_digit($channelId)) {
                    $parsedChannelId = (int) $channelId;

                    if ($parsedChannelId > 0) {
                        return $parsedChannelId;
                    }
                }

                throw new UnexpectedValueException('Configured Canary channel ID is invalid.');
            })
            ->values()
            ->all();

        return view('game.servers', [
            'channels' => $channels,
            'runtimeSnapshot' => $this->runtime->snapshot($channelIds),
        ]);
    }

    public function online(): View
    {
        try {
            $characters = $this->gameData->onlineCharacters();
        } catch (QueryException $exception) {
            throw new ServiceUnavailableHttpException(
                null,
                'Online character data is temporarily unavailable.',
                $exception,
            );
        }

        return view('game.online', ['characters' => $characters]);
    }
}
