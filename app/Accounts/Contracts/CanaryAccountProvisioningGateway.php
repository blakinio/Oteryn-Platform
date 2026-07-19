<?php

namespace App\Accounts\Contracts;

use App\Accounts\Exceptions\CanaryAccountProvisioningException;

interface CanaryAccountProvisioningGateway
{
    /**
     * Create or recover the exact Canary account represented by the persisted
     * provisioning name and creation epoch.
     *
     * @throws CanaryAccountProvisioningException
     */
    public function provision(string $provisioningName, int $creationEpoch): int;
}
