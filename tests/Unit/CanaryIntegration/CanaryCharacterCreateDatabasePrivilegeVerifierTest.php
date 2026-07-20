<?php

namespace Tests\Unit\CanaryIntegration;

use App\CanaryIntegration\CanaryCharacterCreateDatabasePrivilegeVerifier;
use PHPUnit\Framework\TestCase;

final class CanaryCharacterCreateDatabasePrivilegeVerifierTest extends TestCase
{
    public function test_exact_column_level_grants_are_accepted(): void
    {
        $violations = (new CanaryCharacterCreateDatabasePrivilegeVerifier)->verify('canary', $this->validGrants());

        self::assertSame([], $violations);
    }

    public function test_table_level_or_extra_privileges_are_rejected(): void
    {
        $grants = $this->validGrants();
        $grants[] = "GRANT SELECT ON `canary`.`players` TO 'oteryn_character_create'@'%'";
        $grants[] = "GRANT UPDATE (`name`) ON `canary`.`players` TO 'oteryn_character_create'@'%'";
        $grants[] = "GRANT SELECT (`password`) ON `canary`.`accounts` TO 'oteryn_character_create'@'%'";

        $violations = (new CanaryCharacterCreateDatabasePrivilegeVerifier)->verify('canary', $grants);

        self::assertNotSame([], $violations);
    }

    public function test_unrelated_table_and_grant_option_are_rejected(): void
    {
        $grants = $this->validGrants();
        $grants[] = "GRANT SELECT (`id`) ON `canary`.`account_sessions` TO 'oteryn_character_create'@'%'";
        $grants[] = "GRANT SELECT (`id`) ON `canary`.`accounts` TO 'oteryn_character_create'@'%' WITH GRANT OPTION";

        $violations = (new CanaryCharacterCreateDatabasePrivilegeVerifier)->verify('canary', $grants);

        self::assertNotSame([], $violations);
    }

    public function test_missing_required_column_is_rejected(): void
    {
        $grants = $this->validGrants();
        $grants[2] = str_replace('`skill_fishing_tries`', '', $grants[2]);

        $violations = (new CanaryCharacterCreateDatabasePrivilegeVerifier)->verify('canary', $grants);

        self::assertContains('Missing approved INSERT privilege for players.skill_fishing_tries.', $violations);
    }

    /**
     * @return list<string>
     */
    private function validGrants(): array
    {
        $insertColumns = implode(', ', array_map(
            static fn (string $column): string => "`{$column}`",
            CanaryCharacterCreateDatabasePrivilegeVerifier::PLAYER_INSERT_COLUMNS,
        ));

        return [
            "GRANT USAGE ON *.* TO 'oteryn_character_create'@'%'",
            "GRANT SELECT (`id`) ON `canary`.`accounts` TO 'oteryn_character_create'@'%'",
            "GRANT SELECT (`id`, `name`, `account_id`, `deletion`), INSERT ({$insertColumns}) ON `canary`.`players` TO 'oteryn_character_create'@'%'",
        ];
    }
}
