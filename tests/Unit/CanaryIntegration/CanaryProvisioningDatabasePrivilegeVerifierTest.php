<?php

namespace Tests\Unit\CanaryIntegration;

use App\CanaryIntegration\CanaryProvisioningDatabasePrivilegeVerifier;
use PHPUnit\Framework\TestCase;

final class CanaryProvisioningDatabasePrivilegeVerifierTest extends TestCase
{
    public function test_exact_combined_column_level_grant_is_accepted(): void
    {
        $violations = (new CanaryProvisioningDatabasePrivilegeVerifier)->verify('canary', [
            "GRANT USAGE ON *.* TO `oteryn_provisioning`@`%` IDENTIFIED BY PASSWORD '*REDACTED_TEST_HASH'",
            'GRANT SELECT (`id`, `name`, `creation`), INSERT (`name`, `password`, `email`, `creation`) ON `canary`.`accounts` TO `oteryn_provisioning`@`%`',
        ]);

        self::assertSame([], $violations);
    }

    public function test_exact_separate_column_level_grants_are_accepted(): void
    {
        $violations = (new CanaryProvisioningDatabasePrivilegeVerifier)->verify('canary', [
            'GRANT INSERT (`name`, `password`, `email`, `creation`) ON `canary`.`accounts` TO `oteryn_provisioning`@`%`',
            'GRANT SELECT (`id`, `name`, `creation`) ON `canary`.`accounts` TO `oteryn_provisioning`@`%`',
        ]);

        self::assertSame([], $violations);
    }

    public function test_password_read_access_is_rejected(): void
    {
        $violations = (new CanaryProvisioningDatabasePrivilegeVerifier)->verify('canary', [
            'GRANT INSERT (`name`, `password`, `email`, `creation`) ON `canary`.`accounts` TO `oteryn_provisioning`@`%`',
            'GRANT SELECT (`id`, `name`, `creation`, `password`) ON `canary`.`accounts` TO `oteryn_provisioning`@`%`',
        ]);

        self::assertContains('Grant #2 grants SELECT on an unapproved accounts column.', $violations);
    }

    public function test_table_level_accounts_privilege_is_rejected(): void
    {
        $violations = (new CanaryProvisioningDatabasePrivilegeVerifier)->verify('canary', [
            'GRANT INSERT ON `canary`.`accounts` TO `oteryn_provisioning`@`%`',
            'GRANT SELECT (`id`, `name`, `creation`) ON `canary`.`accounts` TO `oteryn_provisioning`@`%`',
        ]);

        self::assertContains('Grant #1 includes an unsupported or non-column-level privilege.', $violations);
    }

    public function test_unrelated_table_access_is_rejected(): void
    {
        $violations = (new CanaryProvisioningDatabasePrivilegeVerifier)->verify('canary', [
            'GRANT INSERT (`name`, `password`, `email`, `creation`) ON `canary`.`accounts` TO `oteryn_provisioning`@`%`',
            'GRANT SELECT (`id`, `name`, `creation`) ON `canary`.`players` TO `oteryn_provisioning`@`%`',
        ]);

        self::assertContains('Grant #2 targets data outside the approved Canary accounts provisioning surface.', $violations);
    }

    public function test_missing_required_column_privilege_is_rejected(): void
    {
        $violations = (new CanaryProvisioningDatabasePrivilegeVerifier)->verify('canary', [
            'GRANT INSERT (`name`, `password`, `email`) ON `canary`.`accounts` TO `oteryn_provisioning`@`%`',
            'GRANT SELECT (`id`, `name`, `creation`) ON `canary`.`accounts` TO `oteryn_provisioning`@`%`',
        ]);

        self::assertContains('Missing approved INSERT privilege for accounts.creation.', $violations);
    }

    public function test_grant_option_is_rejected(): void
    {
        $violations = (new CanaryProvisioningDatabasePrivilegeVerifier)->verify('canary', [
            'GRANT INSERT (`name`, `password`, `email`, `creation`) ON `canary`.`accounts` TO `oteryn_provisioning`@`%` WITH GRANT OPTION',
            'GRANT SELECT (`id`, `name`, `creation`) ON `canary`.`accounts` TO `oteryn_provisioning`@`%`',
        ]);

        self::assertContains('Grant #1 includes GRANT OPTION.', $violations);
    }
}
