<?php

namespace App\CanaryIntegration;

use App\PublicGameData\CanaryRuntimeStatus;
use Illuminate\Support\Facades\Redis;
use InvalidArgumentException;
use UnexpectedValueException;

final class CanaryRuntimeRedisReader
{
    /**
     * @var list<string>
     */
    private const FIELDS = [
        'channel_id',
        'status',
        'players_online',
    ];

    /**
     * @var list<string>
     */
    private const VALID_STATUSES = [
        'STARTING',
        'ONLINE',
        'DRAINING',
        'MAINTENANCE',
        'OFFLINE',
    ];

    public function read(int $channelId): ?CanaryRuntimeStatus
    {
        if ($channelId <= 0) {
            throw new InvalidArgumentException('Channel ID must be positive.');
        }

        $connection = Redis::connection('canary_runtime');
        $key = "cluster:channel:{$channelId}:runtime";
        $values = $connection->command('hmget', [$key, self::FIELDS]);
        $ttlMilliseconds = $connection->command('pttl', [$key]);

        if (! is_int($ttlMilliseconds)) {
            throw new UnexpectedValueException('Canary runtime Redis TTL response is malformed.');
        }

        if ($ttlMilliseconds <= 0) {
            return null;
        }

        if (! is_array($values)) {
            throw new UnexpectedValueException('Canary runtime Redis hash response is malformed.');
        }

        foreach (self::FIELDS as $field) {
            if (! array_key_exists($field, $values)) {
                throw new UnexpectedValueException("Canary runtime field {$field} is missing.");
            }
        }

        $runtimeChannelId = $this->parseNonNegativeInteger($values['channel_id'], 'channel_id');
        $playersOnline = $this->parseNonNegativeInteger($values['players_online'], 'players_online');
        $status = $values['status'];

        if ($runtimeChannelId !== $channelId) {
            throw new UnexpectedValueException('Canary runtime channel ID does not match the requested key.');
        }

        if (! is_string($status) || ! in_array($status, self::VALID_STATUSES, true)) {
            throw new UnexpectedValueException('Canary runtime status is invalid.');
        }

        return new CanaryRuntimeStatus(
            channelId: $runtimeChannelId,
            status: $status,
            playersOnline: $playersOnline,
        );
    }

    private function parseNonNegativeInteger(mixed $value, string $field): int
    {
        if (is_int($value)) {
            if ($value >= 0) {
                return $value;
            }

            throw new UnexpectedValueException("Canary runtime field {$field} must be non-negative.");
        }

        if (! is_string($value) || preg_match('/^(0|[1-9][0-9]*)$/D', $value) !== 1) {
            throw new UnexpectedValueException("Canary runtime field {$field} is not a valid integer.");
        }

        $parsed = filter_var($value, FILTER_VALIDATE_INT, [
            'options' => [
                'min_range' => 0,
                'max_range' => PHP_INT_MAX,
            ],
        ]);

        if (! is_int($parsed)) {
            throw new UnexpectedValueException("Canary runtime field {$field} is outside the supported integer range.");
        }

        return $parsed;
    }
}
