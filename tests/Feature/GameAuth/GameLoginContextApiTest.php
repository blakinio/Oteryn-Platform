<?php

namespace Tests\Feature\GameAuth;

use App\GameAuth\Worlds\GameWorld;
use App\GameAuth\Worlds\GameWorldStatus;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class GameLoginContextApiTest extends TestCase
{
    use RefreshDatabase;

    private const SERVICE_CREDENTIAL = 'gateway-login-context-test-credential';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'game-auth.gateway.service_token_sha256' => hash('sha256', self::SERVICE_CREDENTIAL),
            'database.connections.canary' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
        ]);
        DB::purge('canary');

        Schema::connection('canary')->create('players', function (Blueprint $table): void {
            $table->integer('id')->primary();
            $table->string('name')->unique();
            $table->unsignedInteger('account_id');
            $table->integer('level')->default(1);
            $table->integer('vocation')->default(0);
            $table->bigInteger('deletion')->default(0);
        });
    }

    public function test_login_context_returns_only_exact_account_active_characters_for_one_authorized_world(): void
    {
        $world = $this->createWorld('oteryn-test', true, GameWorldStatus::Online);
        DB::connection('canary')->table('players')->insert([
            ['id' => 1, 'name' => 'Alpha', 'account_id' => 1001, 'level' => 120, 'vocation' => 4, 'deletion' => 0],
            ['id' => 2, 'name' => 'Deleted', 'account_id' => 1001, 'level' => 130, 'vocation' => 3, 'deletion' => 1],
            ['id' => 3, 'name' => 'Foreign', 'account_id' => 2002, 'level' => 140, 'vocation' => 2, 'deletion' => 0],
        ]);

        $response = $this->withToken(self::SERVICE_CREDENTIAL)
            ->getJson('/internal/v1/game-auth/accounts/1001/login-context');

        $response->assertOk()
            ->assertJsonPath('protocol_version', 1)
            ->assertJsonPath('worlds.0.id', $world->id)
            ->assertJsonPath('worlds.0.slug', 'oteryn-test')
            ->assertJsonPath('characters.0.name', 'Alpha')
            ->assertJsonPath('characters.0.world_id', $world->id)
            ->assertJsonCount(1, 'characters');

        $payload = $response->json();
        self::assertIsArray($payload);
        self::assertStringNotContainsString('Deleted', json_encode($payload, JSON_THROW_ON_ERROR));
        self::assertStringNotContainsString('Foreign', json_encode($payload, JSON_THROW_ON_ERROR));
        self::assertStringNotContainsString('account_id', json_encode($payload, JSON_THROW_ON_ERROR));
    }

    public function test_zero_worlds_fail_closed_without_character_data(): void
    {
        DB::connection('canary')->table('players')->insert([
            'id' => 1,
            'name' => 'Alpha',
            'account_id' => 1001,
            'level' => 120,
            'vocation' => 4,
            'deletion' => 0,
        ]);

        $this->withToken(self::SERVICE_CREDENTIAL)
            ->getJson('/internal/v1/game-auth/accounts/1001/login-context')
            ->assertStatus(503)
            ->assertJsonPath('error', 'world_unavailable')
            ->assertJsonMissing(['characters']);
    }

    public function test_multiple_worlds_fail_closed_until_character_world_mapping_exists(): void
    {
        $this->createWorld('world-one', true, GameWorldStatus::Online);
        $this->createWorld('world-two', true, GameWorldStatus::Online);

        $this->withToken(self::SERVICE_CREDENTIAL)
            ->getJson('/internal/v1/game-auth/accounts/1001/login-context')
            ->assertStatus(409)
            ->assertJsonPath('error', 'world_mapping_ambiguous');
    }

    public function test_disabled_or_non_online_world_is_not_login_eligible(): void
    {
        $this->createWorld('maintenance', true, GameWorldStatus::Maintenance);
        $this->createWorld('disabled', false, GameWorldStatus::Online);

        $this->withToken(self::SERVICE_CREDENTIAL)
            ->getJson('/internal/v1/game-auth/accounts/1001/login-context')
            ->assertStatus(503)
            ->assertJsonPath('error', 'world_unavailable');
    }

    public function test_login_context_requires_gateway_service_authentication(): void
    {
        $this->getJson('/internal/v1/game-auth/accounts/1001/login-context')
            ->assertStatus(401)
            ->assertJsonPath('error', 'unauthorized_service');
    }

    private function createWorld(string $slug, bool $loginEnabled, GameWorldStatus $status): GameWorld
    {
        return GameWorld::query()->create([
            'slug' => $slug,
            'name' => ucfirst($slug),
            'region' => 'TEST',
            'status' => $status,
            'login_enabled' => $loginEnabled,
            'game_host' => $slug.'.example.test',
            'game_port' => 7172,
        ]);
    }
}
