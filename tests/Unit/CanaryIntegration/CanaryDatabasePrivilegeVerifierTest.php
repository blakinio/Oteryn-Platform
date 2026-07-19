<?php

namespace Tests\Unit\CanaryIntegration;

use App\CanaryIntegration\CanaryDatabasePrivilegeVerifier;
use PHPUnit\Framework\TestCase;

class CanaryDatabasePrivilegeVerifierTest extends TestCase
{
    public function test_exact_direct_table_select_grants_are_accepted(): void
    {
        $violations = (new CanaryDatabasePrivilegeVerifier)->verify('canary', [
            "GRANT USAGE ON *.* TO `oteryn_readonly`@`%` IDENTIFIED BY PASSWORD '*REDACTED_TEST_HASH'",
            'GRANT SELECT ON `canary`.`players` TO `oteryn_readonly`@`%`',
            'GRANT SELECT ON `canary`.`guilds` TO `oteryn_readonly`@`%`',
            'GRANT SELECT ON `canary`.`guild_membership` TO `oteryn_readonly`@`%`',
            'GRANT SELECT ON `canary`.`guild_ranks` TO `oteryn_readonly`@`%`',
            'GRANT SELECT ON `canary`.`channels` TO `oteryn_readonly`@`%`',
            'GRANT SELECT ON `canary`.`cluster_sessions` TO `oteryn_readonly`@`%`',
        ]);

        $this->assertSame([], $violations);
    }

    public function test_write_privilege_is_rejected(): void
    {
        $violations = (new CanaryDatabasePrivilegeVerifier)->verify('canary', [
            'GRANT SELECT, INSERT ON `canary`.`players` TO `oteryn_readonly`@`%`',
            ...$this->remainingRequiredSelectGrants(except: 'players'),
        ]);

        $this->assertContains('Grant #1 includes a privilege other than direct SELECT.', $violations);
        $this->assertContains('Missing direct SELECT grant for required Canary table: players.', $violations);
    }

    public function test_schema_wide_select_is_rejected(): void
    {
        $violations = (new CanaryDatabasePrivilegeVerifier)->verify('canary', [
            'GRANT SELECT ON `canary`.* TO `oteryn_readonly`@`%`',
        ]);

        $this->assertContains('Grant #1 is global or schema-wide; only direct table SELECT grants are allowed.', $violations);
    }

    public function test_select_on_extra_table_is_rejected(): void
    {
        $violations = (new CanaryDatabasePrivilegeVerifier)->verify('canary', [
            ...$this->requiredSelectGrants(),
            'GRANT SELECT ON `canary`.`accounts` TO `oteryn_readonly`@`%`',
        ]);

        $this->assertContains('Grant #7 targets a table outside the approved Canary read allowlist.', $violations);
    }

    public function test_missing_cluster_sessions_grant_is_rejected(): void
    {
        $violations = (new CanaryDatabasePrivilegeVerifier)->verify(
            'canary',
            $this->remainingRequiredSelectGrants(except: 'cluster_sessions'),
        );

        $this->assertContains('Missing direct SELECT grant for required Canary table: cluster_sessions.', $violations);
    }

    public function test_role_based_grant_is_rejected_as_unverifiable(): void
    {
        $violations = (new CanaryDatabasePrivilegeVerifier)->verify('canary', [
            ...$this->requiredSelectGrants(),
            'GRANT `canary_reader`@`%` TO `oteryn_readonly`@`%`',
        ]);

        $this->assertContains('Grant #7 has an unsupported grant shape.', $violations);
    }

    public function test_grant_option_is_rejected(): void
    {
        $violations = (new CanaryDatabasePrivilegeVerifier)->verify('canary', [
            'GRANT SELECT ON `canary`.`players` TO `oteryn_readonly`@`%` WITH GRANT OPTION',
            ...$this->remainingRequiredSelectGrants(except: 'players'),
        ]);

        $this->assertContains('Grant #1 includes GRANT OPTION.', $violations);
        $this->assertContains('Missing direct SELECT grant for required Canary table: players.', $violations);
    }

    /**
     * @return list<string>
     */
    private function requiredSelectGrants(): array
    {
        return array_map(
            static fn (string $table): string => "GRANT SELECT ON `canary`.`{$table}` TO `oteryn_readonly`@`%`",
            CanaryDatabasePrivilegeVerifier::REQUIRED_TABLES,
        );
    }

    /**
     * @return list<string>
     */
    private function remainingRequiredSelectGrants(string $except): array
    {
        return array_values(array_filter(
            $this->requiredSelectGrants(),
            static fn (string $grant): bool => ! str_contains($grant, ".`{$except}` "),
        ));
    }
}
