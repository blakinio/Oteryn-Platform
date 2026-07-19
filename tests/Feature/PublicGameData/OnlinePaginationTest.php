<?php

namespace Tests\Feature\PublicGameData;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OnlinePaginationTest extends TestCase
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

        Schema::connection('canary')->create('players', function (Blueprint $table): void {
            $table->integer('id')->primary();
            $table->string('name')->unique();
            $table->unsignedInteger('account_id')->default(0);
            $table->integer('group_id')->default(1);
            $table->integer('level')->default(1);
            $table->unsignedBigInteger('experience')->default(0);
            $table->integer('vocation')->default(0);
            $table->bigInteger('deletion')->default(0);
        });

        Schema::connection('canary')->create('channels', function (Blueprint $table): void {
            $table->integer('id')->primary();
            $table->string('name')->unique();
            $table->string('pvp_type');
            $table->integer('max_players')->default(0);
            $table->boolean('enabled')->default(true);
            $table->integer('sort_order')->default(0);
            $table->boolean('maintenance')->default(false);
            $table->text('maintenance_message')->nullable();
        });

        Schema::connection('canary')->create('cluster_sessions', function (Blueprint $table): void {
            $table->integer('player_id')->primary();
            $table->unsignedInteger('account_id');
            $table->integer('channel_id');
            $table->string('instance_id');
            $table->string('session_id');
            $table->unsignedBigInteger('fencing_token');
            $table->string('status');
            $table->bigInteger('last_heartbeat');
            $table->bigInteger('expires_at');
        });
    }

    public function test_online_list_is_bounded_to_one_hundred_characters_per_page(): void
    {
        $readTime = (int) floor(microtime(true) * 1000);

        DB::connection('canary')->table('channels')->insert([
            'id' => 1,
            'name' => 'Main',
            'pvp_type' => 'pvp',
            'max_players' => 1000,
            'enabled' => 1,
            'sort_order' => 1,
            'maintenance' => 0,
            'maintenance_message' => null,
        ]);

        $players = [];
        $sessions = [];
        for ($index = 1; $index <= 101; $index++) {
            $players[] = [
                'id' => $index,
                'name' => sprintf('Online%03d', $index),
                'account_id' => 1000 + $index,
                'level' => 100,
                'vocation' => 1,
                'deletion' => 0,
            ];
            $sessions[] = [
                'player_id' => $index,
                'account_id' => 1000 + $index,
                'channel_id' => 1,
                'instance_id' => 'instance',
                'session_id' => sprintf('session-%03d', $index),
                'fencing_token' => $index,
                'status' => 'ONLINE',
                'last_heartbeat' => $readTime,
                'expires_at' => $readTime + 60_000,
            ];
        }

        DB::connection('canary')->table('players')->insert($players);
        DB::connection('canary')->table('cluster_sessions')->insert($sessions);
        DB::connection('canary')->statement('PRAGMA query_only = ON');

        $this->get(route('game.online.index'))
            ->assertOk()
            ->assertSee('Online001')
            ->assertSee('Online100')
            ->assertDontSee('Online101')
            ->assertSee('Page 1 of 2');

        $this->get(route('game.online.index', ['page' => 2]))
            ->assertOk()
            ->assertSee('Online101')
            ->assertDontSee('Online001')
            ->assertSee('Page 2 of 2');
    }
}
