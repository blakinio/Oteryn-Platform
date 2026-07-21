<?php

namespace App\GameAuth\Worlds;

final class DatabaseWorldRegistry implements WorldRegistry
{
    /**
     * @return list<GameWorldRoute>
     */
    public function forAccount(int $canaryAccountId): array
    {
        if ($canaryAccountId < 1) {
            return [];
        }

        $routes = [];
        $worlds = GameWorld::query()
            ->where('login_enabled', true)
            ->where('status', GameWorldStatus::Online->value)
            ->orderBy('id')
            ->get();

        foreach ($worlds as $world) {
            if (! $this->isRoutable($world)) {
                continue;
            }

            $routes[] = new GameWorldRoute(
                id: $world->id,
                slug: $world->slug,
                name: $world->name,
                region: $world->region,
                host: $world->game_host,
                port: $world->game_port,
            );
        }

        return $routes;
    }

    private function isRoutable(GameWorld $world): bool
    {
        $host = trim($world->game_host);

        if ($world->slug === '' || $world->name === '' || $world->region === '' || $host === '') {
            return false;
        }

        $validHost = filter_var($host, FILTER_VALIDATE_IP) !== false
            || filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) !== false;

        return $validHost && $world->game_port >= 1 && $world->game_port <= 65535;
    }
}
