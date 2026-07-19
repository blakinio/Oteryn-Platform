<?php

namespace Tests\Feature\PublicGameData;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Redis\Connections\Connection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Mockery\CompositeExpectation;
use Mockery\MockInterface;
use RuntimeException;
use Tests\TestCase;

final class ServerRuntimeAvailabilityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.connections.canary', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);

        DB::purge('canary');
        DB::connection('canary')->getPdo();

        Schema::connection('canary')->create('channels', function (Blueprint $table): void {
            $table->unsignedInteger('id')->primary();
            $table->string('name');
            $table->string('pvp_type');
            $table->string('external_host');
            $table->unsignedSmallInteger('game_port');
            $table->unsignedSmallInteger('status_port');
            $table->unsignedInteger('max_players');
            $table->boolean('enabled');
            $table->integer('sort_order');
            $table->boolean('maintenance');
            $table->string('maintenance_message')->nullable();
        });
    }

    protected function tearDown(): void
    {
        DB::purge('canary');

        parent::tearDown();
    }

    public function test_server_page_renders_explicit_runtime_states_counts_and_full_status(): void
    {
        $this->insertChannel(1, 'Alpha', 100, 1);
        $this->insertChannel(2, 'Beta', 200, 2);

        $connection = Mockery::mock(Connection::class);
        Redis::shouldReceive('connection')->twice()->with('canary_runtime')->andReturn($connection);
        $this->commandExpectation($connection)
            ->andReturnUsing(function (string $command, array $arguments): mixed {
                $key = $arguments[0] ?? null;

                if ($command === 'hmget' && $key === 'cluster:channel:1:runtime') {
                    return [
                        'channel_id' => '1',
                        'status' => 'ONLINE',
                        'players_online' => '100',
                    ];
                }

                if ($command === 'pttl' && $key === 'cluster:channel:1:runtime') {
                    return 25000;
                }

                if ($command === 'hmget' && $key === 'cluster:channel:2:runtime') {
                    return [
                        'channel_id' => '2',
                        'status' => 'MAINTENANCE',
                        'players_online' => '3',
                    ];
                }

                if ($command === 'pttl' && $key === 'cluster:channel:2:runtime') {
                    return 25000;
                }

                throw new RuntimeException('Unexpected Redis command in test.');
            });

        $this->get(route('game.servers.index'))
            ->assertOk()
            ->assertSee('Alpha')
            ->assertSee('Beta')
            ->assertSee('Runtime:</strong> ONLINE', false)
            ->assertSee('Players online:</strong> 100', false)
            ->assertSee('Full')
            ->assertSee('Runtime:</strong> MAINTENANCE', false)
            ->assertSee('Players online:</strong> 3', false)
            ->assertDontSee('instance_id')
            ->assertDontSee('build_sha')
            ->assertDontSee('map_hash')
            ->assertDontSee('data_hash');
    }

    public function test_missing_or_expired_runtime_key_is_rendered_as_unknown_without_synthetic_count(): void
    {
        $this->insertChannel(1, 'Alpha', 100, 1);

        $connection = Mockery::mock(Connection::class);
        Redis::shouldReceive('connection')->once()->with('canary_runtime')->andReturn($connection);
        $this->commandExpectation($connection)
            ->once()
            ->with('hmget', [
                'cluster:channel:1:runtime',
                ['channel_id', 'status', 'players_online'],
            ])
            ->andReturn([]);
        $this->commandExpectation($connection)
            ->once()
            ->with('pttl', ['cluster:channel:1:runtime'])
            ->andReturn(-2);

        $this->get(route('game.servers.index'))
            ->assertOk()
            ->assertSee('Alpha')
            ->assertSee('Runtime:</strong> Unknown', false)
            ->assertDontSee('Players online:</strong>', false)
            ->assertDontSee('Runtime:</strong> OFFLINE', false);
    }

    public function test_runtime_transport_failure_keeps_static_channels_but_marks_runtime_unavailable(): void
    {
        $this->insertChannel(1, 'Alpha', 100, 1);

        $connection = Mockery::mock(Connection::class);
        Redis::shouldReceive('connection')->once()->with('canary_runtime')->andReturn($connection);
        $this->commandExpectation($connection)
            ->once()
            ->with('hmget', [
                'cluster:channel:1:runtime',
                ['channel_id', 'status', 'players_online'],
            ])
            ->andThrow(new RuntimeException('Redis transport unavailable.'));

        $this->get(route('game.servers.index'))
            ->assertOk()
            ->assertSee('Alpha')
            ->assertSee('Configured max players:</strong> 100', false)
            ->assertSee('live player availability is intentionally not shown')
            ->assertSee('Runtime:</strong> Unavailable', false)
            ->assertDontSee('Players online:</strong>', false)
            ->assertDontSee('Runtime:</strong> OFFLINE', false);
    }

    public function test_failure_after_a_valid_channel_discards_the_entire_runtime_snapshot(): void
    {
        $this->insertChannel(1, 'Alpha', 100, 1);
        $this->insertChannel(2, 'Beta', 100, 2);

        $connection = Mockery::mock(Connection::class);
        Redis::shouldReceive('connection')->twice()->with('canary_runtime')->andReturn($connection);
        $this->commandExpectation($connection)
            ->andReturnUsing(function (string $command, array $arguments): mixed {
                $key = $arguments[0] ?? null;

                if ($command === 'hmget' && $key === 'cluster:channel:1:runtime') {
                    return [
                        'channel_id' => '1',
                        'status' => 'ONLINE',
                        'players_online' => '25',
                    ];
                }

                if ($command === 'pttl' && $key === 'cluster:channel:1:runtime') {
                    return 25000;
                }

                if ($command === 'hmget' && $key === 'cluster:channel:2:runtime') {
                    throw new RuntimeException('Redis transport unavailable.');
                }

                throw new RuntimeException('Unexpected Redis command in test.');
            });

        $this->get(route('game.servers.index'))
            ->assertOk()
            ->assertSee('Alpha')
            ->assertSee('Beta')
            ->assertSee('Runtime:</strong> Unavailable', false)
            ->assertDontSee('Players online:</strong> 25', false);
    }

    private function commandExpectation(MockInterface $connection): CompositeExpectation
    {
        /** @var CompositeExpectation */
        $expectation = $connection->shouldReceive('command');

        return $expectation;
    }

    private function insertChannel(int $id, string $name, int $maxPlayers, int $sortOrder): void
    {
        DB::connection('canary')->table('channels')->insert([
            'id' => $id,
            'name' => $name,
            'pvp_type' => 'pvp',
            'external_host' => strtolower($name).'.example.test',
            'game_port' => 7172 + $id,
            'status_port' => 7170 + $id,
            'max_players' => $maxPlayers,
            'enabled' => 1,
            'sort_order' => $sortOrder,
            'maintenance' => 0,
            'maintenance_message' => null,
        ]);
    }
}
