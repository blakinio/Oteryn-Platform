<?php

namespace App\CanaryIntegration;

use App\Accounts\Contracts\CanaryAccountProvisioningGateway;
use App\Accounts\Exceptions\CanaryAccountProvisioningConflict;
use App\Accounts\Exceptions\CanaryAccountProvisioningException;
use App\Accounts\Exceptions\CanaryAccountProvisioningUnavailable;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Throwable;

final class CanaryAccountProvisioner implements CanaryAccountProvisioningGateway
{
    public const CONNECTION = 'canary_provisioning';

    private const PROVISIONING_NAME_PATTERN = '/^op[0-9a-f]{30}$/';

    public function provision(string $provisioningName, int $creationEpoch): int
    {
        if (preg_match(self::PROVISIONING_NAME_PATTERN, $provisioningName) !== 1 || $creationEpoch <= 0) {
            throw new CanaryAccountProvisioningConflict('Invalid persisted Canary account provisioning identity.');
        }

        try {
            return DB::connection(self::CONNECTION)->transaction(function () use ($provisioningName, $creationEpoch): int {
                $connection = DB::connection(self::CONNECTION);
                $passwordHash = $this->generateSinkPasswordHash();

                try {
                    $connection->table('accounts')->insert([
                        'name' => $provisioningName,
                        'password' => $passwordHash,
                        'email' => '',
                        'creation' => $creationEpoch,
                    ]);
                } catch (QueryException $exception) {
                    if (! $this->isIntegrityViolation($exception)) {
                        throw $exception;
                    }
                } finally {
                    $this->erase($passwordHash);
                }

                $account = $connection->table('accounts')
                    ->select(['id', 'name', 'creation'])
                    ->where('name', $provisioningName)
                    ->first();

                if ($account === null) {
                    throw new CanaryAccountProvisioningUnavailable('Canary account provisioning did not produce a recoverable account row.');
                }

                $values = (array) $account;
                $accountId = $values['id'] ?? null;
                $accountName = $values['name'] ?? null;
                $accountCreation = $values['creation'] ?? null;

                if ((! is_int($accountId) && ! is_string($accountId)) || (int) $accountId <= 0) {
                    throw new CanaryAccountProvisioningUnavailable('Canary account provisioning returned an invalid account identifier.');
                }

                if (! is_string($accountName) || $accountName !== $provisioningName) {
                    throw new CanaryAccountProvisioningConflict('Recovered Canary account name does not match the provisioning intent.');
                }

                if ((! is_int($accountCreation) && ! is_string($accountCreation)) || (int) $accountCreation !== $creationEpoch) {
                    throw new CanaryAccountProvisioningConflict('Recovered Canary account creation marker does not match the provisioning intent.');
                }

                return (int) $accountId;
            });
        } catch (CanaryAccountProvisioningException $exception) {
            throw $exception;
        } catch (Throwable) {
            // Do not attach the underlying database exception: QueryException
            // messages may contain bound values, including the sink hash.
            throw new CanaryAccountProvisioningUnavailable('Canary account provisioning dependency is unavailable.');
        }
    }

    private function generateSinkPasswordHash(): string
    {
        $secret = bin2hex(random_bytes(32));

        try {
            return sha1($secret);
        } finally {
            $this->erase($secret);
        }
    }

    private function isIntegrityViolation(QueryException $exception): bool
    {
        return (string) $exception->getCode() === '23000';
    }

    private function erase(?string &$value): void
    {
        if ($value === null) {
            return;
        }

        if (function_exists('sodium_memzero')) {
            sodium_memzero($value);

            return;
        }

        $value = str_repeat("\0", strlen($value));
    }
}
