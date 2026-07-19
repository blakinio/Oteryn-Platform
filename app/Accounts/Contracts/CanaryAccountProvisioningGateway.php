<?php

namespace App\Accounts\Contracts;

interface CanaryAccountProvisioningGateway
{
    /**
     * Create or recover the exact Canary account represented by the persisted
     * provisioning name and creation epoch.
     *
     * @throws \App\Accounts\Exceptions\CanaryAccountProvisioningException
     */
    public function provision(string $provisioningName, int $creationEpoch): int;
}
