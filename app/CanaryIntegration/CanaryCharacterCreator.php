<?php

namespace App\CanaryIntegration;

use App\Characters\Contracts\CanaryCharacterCreationGateway;
use App\Characters\Data\CharacterCreationResult;
use App\Characters\Exceptions\CharacterAccountMissing;
use App\Characters\Exceptions\CharacterCreationException;
use App\Characters\Exceptions\CharacterCreationUnavailable;
use App\Characters\Exceptions\CharacterLimitReached;
use App\Characters\Exceptions\CharacterNameConflict;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Throwable;

final class CanaryCharacterCreator implements CanaryCharacterCreationGateway
{
    public const CONNECTION = 'canary_character_create';

    private const MAX_TRANSACTION_ATTEMPTS = 3;

    private const CHARACTER_LIMIT = 10;

    public function create(int $accountId, string $canonicalName, int $vocation, int $sex): CharacterCreationResult
    {
        for ($attempt = 1; $attempt <= self::MAX_TRANSACTION_ATTEMPTS; $attempt++) {
            try {
                return $this->attempt($accountId, $canonicalName, $vocation, $sex);
            } catch (CharacterCreationException $exception) {
                throw $exception;
            } catch (QueryException $exception) {
                if ($this->isDuplicateKey($exception)) {
                    return $this->recoverAfterDuplicate($accountId, $canonicalName);
                }

                if ($this->isTransientConcurrencyFailure($exception) && $attempt < self::MAX_TRANSACTION_ATTEMPTS) {
                    continue;
                }

                throw new CharacterCreationUnavailable('Character creation dependency is unavailable.');
            } catch (Throwable) {
                throw new CharacterCreationUnavailable('Character creation dependency is unavailable.');
            }
        }

        throw new CharacterCreationUnavailable('Character creation dependency is unavailable.');
    }

    private function attempt(int $accountId, string $canonicalName, int $vocation, int $sex): CharacterCreationResult
    {
        $connection = DB::connection(self::CONNECTION);

        return $connection->transaction(function () use ($connection, $accountId, $canonicalName, $vocation, $sex): CharacterCreationResult {
            $account = $connection->table('accounts')
                ->select('id')
                ->where('id', $accountId)
                ->lockForUpdate()
                ->first();

            if ($account === null) {
                throw new CharacterAccountMissing('The bound Canary account no longer exists.');
            }

            $existing = $this->findByCanonicalName($connection, $canonicalName);

            if ($existing !== null) {
                return $this->classifyExisting($existing, $accountId, $canonicalName);
            }

            $activeCount = $connection->table('players')
                ->where('account_id', $accountId)
                ->where('deletion', 0)
                ->count('id');

            if ($activeCount >= self::CHARACTER_LIMIT) {
                throw new CharacterLimitReached('This account already has the maximum number of active characters.');
            }

            $playerId = $connection->table('players')->insertGetId(
                $this->starterRow($accountId, $canonicalName, $vocation, $sex),
            );

            if ((! is_int($playerId) && ! is_string($playerId)) || (int) $playerId <= 0) {
                throw new CharacterCreationUnavailable('Character creation did not return a valid player identifier.');
            }

            return new CharacterCreationResult((int) $playerId, $canonicalName, true);
        }, 1);
    }

    private function recoverAfterDuplicate(int $accountId, string $canonicalName): CharacterCreationResult
    {
        try {
            $connection = DB::connection(self::CONNECTION);

            return $connection->transaction(function () use ($connection, $accountId, $canonicalName): CharacterCreationResult {
                $account = $connection->table('accounts')
                    ->select('id')
                    ->where('id', $accountId)
                    ->lockForUpdate()
                    ->first();

                if ($account === null) {
                    throw new CharacterAccountMissing('The bound Canary account no longer exists.');
                }

                $existing = $this->findByCanonicalName($connection, $canonicalName);

                if ($existing === null) {
                    throw new CharacterCreationUnavailable('A character-name race could not be recovered deterministically.');
                }

                return $this->classifyExisting($existing, $accountId, $canonicalName);
            }, 1);
        } catch (CharacterCreationException $exception) {
            throw $exception;
        } catch (Throwable) {
            throw new CharacterCreationUnavailable('Character creation dependency is unavailable.');
        }
    }

    private function findByCanonicalName(ConnectionInterface $connection, string $canonicalName): ?object
    {
        return $connection->table('players')
            ->select(['id', 'name', 'account_id', 'deletion'])
            ->where('name', $canonicalName)
            ->first();
    }

    private function classifyExisting(object $existing, int $accountId, string $canonicalName): CharacterCreationResult
    {
        $values = (array) $existing;
        $playerId = $values['id'] ?? null;
        $existingName = $values['name'] ?? null;
        $existingAccountId = $values['account_id'] ?? null;
        $deletion = $values['deletion'] ?? null;

        if ((! is_int($playerId) && ! is_string($playerId)) || (int) $playerId <= 0) {
            throw new CharacterCreationUnavailable('Character recovery returned an invalid player identifier.');
        }

        if (! is_string($existingName) || $existingName !== $canonicalName) {
            throw new CharacterCreationUnavailable('Character recovery returned an invalid canonical name.');
        }

        if ((! is_int($existingAccountId) && ! is_string($existingAccountId)) || (int) $existingAccountId !== $accountId) {
            throw new CharacterNameConflict('That character name is already in use.');
        }

        if ((! is_int($deletion) && ! is_string($deletion)) || (int) $deletion !== 0) {
            throw new CharacterNameConflict('That character name is not available.');
        }

        return new CharacterCreationResult((int) $playerId, $canonicalName, false);
    }

    /**
     * @return array<string, int|string>
     */
    private function starterRow(int $accountId, string $canonicalName, int $vocation, int $sex): array
    {
        return [
            'name' => $canonicalName,
            'group_id' => 1,
            'account_id' => $accountId,
            'level' => 8,
            'vocation' => $vocation,
            'health' => 185,
            'healthmax' => 185,
            'experience' => 4200,
            'lookbody' => 120,
            'lookfeet' => 115,
            'lookhead' => 114,
            'looklegs' => 132,
            'looktype' => $sex === 0 ? 136 : 128,
            'lookaddons' => 0,
            'maglevel' => 0,
            'mana' => 90,
            'manamax' => 90,
            'manaspent' => 0,
            'soul' => 100,
            'town_id' => 8,
            'posx' => 0,
            'posy' => 0,
            'posz' => 0,
            'conditions' => '',
            'cap' => 470,
            'sex' => $sex,
            'pronoun' => 0,
            'istutorial' => 0,
            'skill_fist' => 10,
            'skill_fist_tries' => 0,
            'skill_club' => 10,
            'skill_club_tries' => 0,
            'skill_sword' => 10,
            'skill_sword_tries' => 0,
            'skill_axe' => 10,
            'skill_axe_tries' => 0,
            'skill_dist' => 10,
            'skill_dist_tries' => 0,
            'skill_shielding' => 10,
            'skill_shielding_tries' => 0,
            'skill_fishing' => 10,
            'skill_fishing_tries' => 0,
        ];
    }

    private function isDuplicateKey(QueryException $exception): bool
    {
        return (string) $exception->getCode() === '23000'
            && $this->driverErrorCode($exception) === 1062;
    }

    private function isTransientConcurrencyFailure(QueryException $exception): bool
    {
        return (string) $exception->getCode() === '40001'
            || $this->driverErrorCode($exception) === 1213;
    }

    private function driverErrorCode(QueryException $exception): ?int
    {
        $driverCode = $exception->errorInfo[1] ?? null;

        if (! is_int($driverCode) && ! is_string($driverCode)) {
            return null;
        }

        return (int) $driverCode;
    }
}
