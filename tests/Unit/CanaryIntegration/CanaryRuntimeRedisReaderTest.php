<?php

namespace Tests\Unit\CanaryIntegration;

use App\CanaryIntegration\CanaryRuntimeRedisReader;
use Illuminate\Redis\Connections\Connection;
use Illuminate\Support\Facades\Redis;
use Mockery;
use Tests\TestCase;
use UnexpectedValueException;

final class CanaryRuntimeRedisReaderTest extends TestCase
{
    public function test_it_reads_only_public_runtime_fields_from_the_deterministic_channel_key(): void
    {
        $connection = Mockery::mock(Connection::class);

        Redis::shouldReceive('connection')
            ->once()
            ->with('canary_runtime')
            ->andReturn($connection);

        $connection->shouldReceive('command')
            ->once()
            ->with('hmget', [
                'cluster:channel:7:runtime',
                ['channel_id', 'status', 'players_online'],
            ])
            ->andReturn([
                'channel_id' => '7',
                'status' => 'ONLINE',
                'players_online' => '42',
            ]);
        $connection->shouldReceive('command')
            ->once()
            ->with('pttl', ['cluster:channel:7:runtime'])
            ->andReturn(25000);

        $status = (new CanaryRuntimeRedisReader())->read(7);

        self::assertNotNull($status);
        self::assertSame(7, $status->channelId);
        self::assertSame('ONLINE', $status->status);
        self::assertSame(42, $status->playersOnline);
    }

    public function test_missing_or_expired_runtime_key_is_unknown_not_offline_or_zero(): void
    {
        $connection = Mockery::mock(Connection::class);

        Redis::shouldReceive('connection')->once()->with('canary_runtime')->andReturn($connection);
        $connection->shouldReceive('command')
            ->once()
            ->with('hmget', [
                'cluster:channel:7:runtime',
                ['channel_id', 'status', 'players_online'],
            ])
            ->andReturn([]);
        $connection->shouldReceive('command')
            ->once()
            ->with('pttl', ['cluster:channel:7:runtime'])
            ->andReturn(-2);

        self::assertNull((new CanaryRuntimeRedisReader())->read(7));
    }

    public function test_positive_ttl_with_malformed_runtime_data_fails_closed(): void
    {
        $connection = Mockery::mock(Connection::class);

        Redis::shouldReceive('connection')->once()->with('canary_runtime')->andReturn($connection);
        $connection->shouldReceive('command')
            ->once()
            ->with('hmget', [
                'cluster:channel:7:runtime',
                ['channel_id', 'status', 'players_online'],
            ])
            ->andReturn([
                'channel_id' => '7',
                'status' => 'ONLINE',
                'players_online' => '-1',
            ]);
        $connection->shouldReceive('command')
            ->once()
            ->with('pttl', ['cluster:channel:7:runtime'])
            ->andReturn(25000);

        $this->expectException(UnexpectedValueException::class);

        (new CanaryRuntimeRedisReader())->read(7);
    }

    public function test_channel_id_must_match_the_deterministic_key(): void
    {
        $connection = Mockery::mock(Connection::class);

        Redis::shouldReceive('connection')->once()->with('canary_runtime')->andReturn($connection);
        $connection->shouldReceive('command')
            ->once()
            ->with('hmget', [
                'cluster:channel:7:runtime',
                ['channel_id', 'status', 'players_online'],
            ])
            ->andReturn([
                'channel_id' => '8',
                'status' => 'ONLINE',
                'players_online' => '10',
            ]);
        $connection->shouldReceive('command')
            ->once()
            ->with('pttl', ['cluster:channel:7:runtime'])
            ->andReturn(25000);

        $this->expectException(UnexpectedValueException::class);

        (new CanaryRuntimeRedisReader())->read(7);
    }

    public function test_runtime_state_must_be_from_the_canary_allowlist(): void
    {
        $connection = Mockery::mock(Connection::class);

        Redis::shouldReceive('connection')->once()->with('canary_runtime')->andReturn($connection);
        $connection->shouldReceive('command')
            ->once()
            ->with('hmget', [
                'cluster:channel:7:runtime',
                ['channel_id', 'status', 'players_online'],
            ])
            ->andReturn([
                'channel_id' => '7',
                'status' => 'DEGRADED',
                'players_online' => '10',
            ]);
        $connection->shouldReceive('command')
            ->once()
            ->with('pttl', ['cluster:channel:7:runtime'])
            ->andReturn(25000);

        $this->expectException(UnexpectedValueException::class);

        (new CanaryRuntimeRedisReader())->read(7);
    }
}
