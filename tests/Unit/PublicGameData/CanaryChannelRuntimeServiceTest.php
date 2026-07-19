<?php

namespace Tests\Unit\PublicGameData;

use App\CanaryIntegration\CanaryRuntimeRedisReader;
use App\PublicGameData\CanaryChannelRuntimeService;
use Illuminate\Redis\Connections\Connection;
use Illuminate\Support\Facades\Redis;
use Mockery;
use Mockery\CompositeExpectation;
use Mockery\MockInterface;
use RuntimeException;
use Tests\TestCase;

final class CanaryChannelRuntimeServiceTest extends TestCase
{
    public function test_snapshot_preserves_healthy_missing_channel_as_unknown(): void
    {
        $connection = Mockery::mock(Connection::class);

        Redis::shouldReceive('connection')
            ->twice()
            ->with('canary_runtime')
            ->andReturn($connection);

        $this->commandExpectation($connection)->__call('andReturnUsing', [
            function (string $command, array $arguments): mixed {
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
                    return [];
                }

                if ($command === 'pttl' && $key === 'cluster:channel:2:runtime') {
                    return -2;
                }

                throw new RuntimeException('Unexpected Redis command in test.');
            },
        ]);

        $snapshot = (new CanaryChannelRuntimeService(new CanaryRuntimeRedisReader))->snapshot([1, 2]);
        $channelOneStatus = $snapshot->forChannel(1);

        self::assertTrue($snapshot->available);
        self::assertNotNull($channelOneStatus);
        self::assertTrue($channelOneStatus->isFull(100));
        self::assertNull($snapshot->forChannel(2));
    }

    public function test_transport_failure_discards_the_whole_runtime_snapshot(): void
    {
        $connection = Mockery::mock(Connection::class);

        Redis::shouldReceive('connection')
            ->twice()
            ->with('canary_runtime')
            ->andReturn($connection);

        $this->commandExpectation($connection)->__call('andReturnUsing', [
            function (string $command, array $arguments): mixed {
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
            },
        ]);

        $snapshot = (new CanaryChannelRuntimeService(new CanaryRuntimeRedisReader))->snapshot([1, 2]);

        self::assertFalse($snapshot->available);
        self::assertNull($snapshot->forChannel(1));
        self::assertNull($snapshot->forChannel(2));
    }

    public function test_malformed_channel_data_discards_the_whole_runtime_snapshot(): void
    {
        $connection = Mockery::mock(Connection::class);

        Redis::shouldReceive('connection')
            ->twice()
            ->with('canary_runtime')
            ->andReturn($connection);

        $this->commandExpectation($connection)->__call('andReturnUsing', [
            function (string $command, array $arguments): mixed {
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
                    return [
                        'channel_id' => '2',
                        'status' => 'BROKEN',
                        'players_online' => '5',
                    ];
                }

                if ($command === 'pttl' && $key === 'cluster:channel:2:runtime') {
                    return 25000;
                }

                throw new RuntimeException('Unexpected Redis command in test.');
            },
        ]);

        $snapshot = (new CanaryChannelRuntimeService(new CanaryRuntimeRedisReader))->snapshot([1, 2]);

        self::assertFalse($snapshot->available);
        self::assertNull($snapshot->forChannel(1));
        self::assertNull($snapshot->forChannel(2));
    }

    private function commandExpectation(MockInterface $connection): CompositeExpectation
    {
        /** @var CompositeExpectation */
        $expectation = $connection->shouldReceive('command');

        return $expectation;
    }
}
