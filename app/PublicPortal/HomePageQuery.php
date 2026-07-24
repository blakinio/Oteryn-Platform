<?php

namespace App\PublicPortal;

use App\Cms\PublicNewsQuery;
use App\PublicGameData\CanaryChannelRuntimeService;
use App\PublicGameData\CanaryGameDataRepository;
use App\PublicPortal\ViewModels\HomeNewsSummary;
use App\PublicPortal\ViewModels\HomePageViewModel;
use App\PublicPortal\ViewModels\HomeWorldChannel;
use App\PublicPortal\ViewModels\HomeWorldSummary;
use stdClass;
use Throwable;
use UnexpectedValueException;

final readonly class HomePageQuery
{
    public function __construct(
        private PublicNewsQuery $news,
        private CanaryGameDataRepository $gameData,
        private CanaryChannelRuntimeService $runtime,
    ) {}

    public function get(): HomePageViewModel
    {
        return new HomePageViewModel(
            world: $this->worldSummary(),
            news: $this->newsSummary(),
        );
    }

    private function newsSummary(): HomeNewsSummary
    {
        try {
            $posts = $this->news->latestPublished();
        } catch (Throwable $exception) {
            report($exception);

            return new HomeNewsSummary(PublicContentState::UNAVAILABLE, []);
        }

        if ($posts->isEmpty()) {
            return new HomeNewsSummary(PublicContentState::EMPTY, []);
        }

        return new HomeNewsSummary(PublicContentState::AVAILABLE, $posts->values()->all());
    }

    private function worldSummary(): HomeWorldSummary
    {
        try {
            $channels = $this->gameData->configuredChannels();

            if ($channels->isEmpty()) {
                return new HomeWorldSummary(PublicContentState::EMPTY, [], null);
            }

            /** @var list<int> $channelIds */
            $channelIds = $channels
                ->map(fn (stdClass $channel): int => $this->channelId($channel->id ?? null))
                ->values()
                ->all();
            $runtimeSnapshot = $this->runtime->snapshot($channelIds);
            $state = $runtimeSnapshot->available
                ? PublicContentState::AVAILABLE
                : PublicContentState::UNAVAILABLE;
            $playersOnline = $runtimeSnapshot->available ? 0 : null;
            $worldChannels = [];

            foreach ($channels as $channel) {
                $channelId = $this->channelId($channel->id ?? null);
                $runtime = $runtimeSnapshot->available
                    ? $runtimeSnapshot->forChannel($channelId)
                    : null;
                $maintenanceMessage = $channel->maintenance_message ?? null;

                if ($runtimeSnapshot->available && $runtime === null) {
                    $state = PublicContentState::STALE;
                    $playersOnline = null;
                } elseif ($playersOnline !== null && $runtime !== null) {
                    $playersOnline += $runtime->playersOnline;
                }

                $worldChannels[] = new HomeWorldChannel(
                    id: $channelId,
                    name: (string) ($channel->name ?? ''),
                    pvpType: (string) ($channel->pvp_type ?? ''),
                    maxPlayers: (int) ($channel->max_players ?? 0),
                    maintenance: (bool) ($channel->maintenance ?? false),
                    maintenanceMessage: is_string($maintenanceMessage) ? $maintenanceMessage : null,
                    runtimeStatus: $runtime?->status,
                    playersOnline: $runtime?->playersOnline,
                );
            }

            return new HomeWorldSummary($state, $worldChannels, $playersOnline);
        } catch (Throwable $exception) {
            report($exception);

            return new HomeWorldSummary(PublicContentState::UNAVAILABLE, [], null);
        }
    }

    private function channelId(mixed $channelId): int
    {
        if (is_int($channelId) && $channelId > 0) {
            return $channelId;
        }

        if (is_string($channelId) && ctype_digit($channelId) && (int) $channelId > 0) {
            return (int) $channelId;
        }

        throw new UnexpectedValueException('Configured Canary channel ID is invalid.');
    }
}
