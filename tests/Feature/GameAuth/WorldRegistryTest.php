<?php

namespace Tests\Feature\GameAuth;

use App\GameAuth\Worlds\DatabaseWorldRegistry;
use App\GameAuth\Worlds\GameWorld;
use App\GameAuth\Worlds\GameWorldStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class WorldRegistryTest extends TestCase
{
    use RefreshDatabase;

    public function test_registry_is_empty_by_default_and_does_not_invent_a_production_route(): void
    {
        self::assertSame([], (new DatabaseWorldRegistry)->forAccount(1001));
        self::assertSame(0, GameWorld::query()->count());
    }

    public function test_registry_returns_only_login_enabled_online_routable_worlds(): void
    {
        $eligible = $this->createWorld(
            slug: 'oteryn-test',
            status: GameWorldStatus::Online,
            loginEnabled: true,
            host: 'game.test',
            port: 7172,
        );
        $this->createWorld('maintenance', GameWorldStatus::Maintenance, true, 'maintenance.test', 7172);
        $this->createWorld('offline', GameWorldStatus::Offline, true, 'offline.test', 7172);
        $this->createWorld('disabled', GameWorldStatus::Online, false, 'disabled.test', 7172);
        $this->createWorld('invalid-host', GameWorldStatus::Online, true, 'not a host', 7172);
        $this->createWorld('invalid-port', GameWorldStatus::Online, true, 'port.test', 0);

        $routes = (new DatabaseWorldRegistry)->forAccount(1001);

        self::assertCount(1, $routes);
        self::assertSame($eligible->id, $routes[0]->id);
        self::assertSame('oteryn-test', $routes[0]->slug);
        self::assertSame('game.test', $routes[0]->host);
        self::assertSame(7172, $routes[0]->port);
    }

    public function test_registry_fails_closed_for_invalid_account_identifier(): void
    {
        $this->createWorld('oteryn-test', GameWorldStatus::Online, true, 'game.test', 7172);

        self::assertSame([], (new DatabaseWorldRegistry)->forAccount(0));
    }

    private function createWorld(
        string $slug,
        GameWorldStatus $status,
        bool $loginEnabled,
        string $host,
        int $port,
    ): GameWorld {
        return GameWorld::query()->create([
            'slug' => $slug,
            'name' => ucfirst($slug),
            'region' => 'TEST',
            'status' => $status,
            'login_enabled' => $loginEnabled,
            'game_host' => $host,
            'game_port' => $port,
        ]);
    }
}
