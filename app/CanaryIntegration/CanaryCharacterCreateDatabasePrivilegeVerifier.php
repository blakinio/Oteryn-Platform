<?php

namespace App\CanaryIntegration;

use Illuminate\Support\Facades\DB;
use RuntimeException;

final class CanaryCharacterCreateDatabasePrivilegeVerifier
{
    /** @var list<string> */
    public const ACCOUNT_SELECT_COLUMNS = ['id'];

    /** @var list<string> */
    public const PLAYER_SELECT_COLUMNS = ['id', 'name', 'account_id', 'deletion'];

    /** @var list<string> */
    public const PLAYER_INSERT_COLUMNS = [
        'name',
        'group_id',
        'account_id',
        'level',
        'vocation',
        'health',
        'healthmax',
        'experience',
        'lookbody',
        'lookfeet',
        'lookhead',
        'looklegs',
        'looktype',
        'lookaddons',
        'maglevel',
        'mana',
        'manamax',
        'manaspent',
        'soul',
        'town_id',
        'posx',
        'posy',
        'posz',
        'conditions',
        'cap',
        'sex',
        'pronoun',
        'istutorial',
        'skill_fist',
        'skill_fist_tries',
        'skill_club',
        'skill_club_tries',
        'skill_sword',
        'skill_sword_tries',
        'skill_axe',
        'skill_axe_tries',
        'skill_dist',
        'skill_dist_tries',
        'skill_shielding',
        'skill_shielding_tries',
        'skill_fishing',
        'skill_fishing_tries',
    ];

    /**
     * @return list<string>
     */
    public function inspect(): array
    {
        $connection = DB::connection(CanaryCharacterCreator::CONNECTION);
        $database = $connection->getDatabaseName();

        if ($database === '') {
            throw new RuntimeException('The Canary character-create database name is unavailable.');
        }

        $rows = $connection->select('SHOW GRANTS FOR CURRENT_USER');
        $grants = [];

        foreach ($rows as $row) {
            $values = array_values((array) $row);
            $grant = $values[0] ?? null;

            if (! is_string($grant) || $grant === '') {
                throw new RuntimeException('The database returned an unreadable character-create grant row.');
            }

            $grants[] = $grant;
        }

        return $this->verify($database, $grants);
    }

    /**
     * @param  list<string>  $grants
     * @return list<string>
     */
    public function verify(string $database, array $grants): array
    {
        if ($grants === []) {
            return ['No grants were returned for the current Canary character-create credential.'];
        }

        $violations = [];
        $foundAccountSelect = [];
        $foundPlayerSelect = [];
        $foundPlayerInsert = [];

        foreach ($grants as $index => $grant) {
            $grantNumber = $index + 1;
            $normalized = trim(rtrim($grant, ';'));

            if (preg_match('/\bWITH\s+GRANT\s+OPTION\b/i', $normalized) === 1) {
                $violations[] = "Grant #{$grantNumber} includes GRANT OPTION.";

                continue;
            }

            if (preg_match('/^GRANT\s+USAGE\s+ON\s+\*\.\*\s+TO\s+/i', $normalized) === 1) {
                continue;
            }

            if (preg_match('/^GRANT\s+(.+?)\s+ON\s+(.+?)\s+TO\s+/i', $normalized, $matches) !== 1) {
                $violations[] = "Grant #{$grantNumber} has an unsupported grant shape.";

                continue;
            }

            $target = $this->parseQualifiedTarget(trim($matches[2]));

            if ($target === null) {
                $violations[] = "Grant #{$grantNumber} has an unsupported privilege target.";

                continue;
            }

            [$grantDatabase, $table] = $target;

            if ($grantDatabase !== $database || ! in_array($table, ['accounts', 'players'], true)) {
                $violations[] = "Grant #{$grantNumber} targets data outside the approved Canary character-create surface.";

                continue;
            }

            $privileges = trim($matches[1]);
            $selectColumns = $this->extractColumnPrivilege($privileges, 'SELECT');
            $insertColumns = $this->extractColumnPrivilege($privileges, 'INSERT');

            $remaining = preg_replace('/\bSELECT\s*\([^)]*\)/i', '', $privileges, 1);
            $remaining = is_string($remaining)
                ? preg_replace('/\bINSERT\s*\([^)]*\)/i', '', $remaining, 1)
                : null;
            $remaining = is_string($remaining) ? trim(str_replace(',', '', $remaining)) : null;

            if ($remaining === null || $remaining !== '' || ($selectColumns === null && $insertColumns === null)) {
                $violations[] = "Grant #{$grantNumber} includes an unsupported or non-column-level privilege.";

                continue;
            }

            if ($table === 'accounts') {
                if ($insertColumns !== null) {
                    $violations[] = "Grant #{$grantNumber} grants INSERT on accounts.";

                    continue;
                }

                if ($selectColumns === null) {
                    $violations[] = "Grant #{$grantNumber} does not contain the approved accounts SELECT privilege.";

                    continue;
                }

                foreach ($selectColumns as $column) {
                    if (! in_array($column, self::ACCOUNT_SELECT_COLUMNS, true)) {
                        $violations[] = "Grant #{$grantNumber} grants SELECT on an unapproved accounts column.";

                        continue 2;
                    }

                    $foundAccountSelect[$column] = true;
                }

                continue;
            }

            if ($selectColumns !== null) {
                foreach ($selectColumns as $column) {
                    if (! in_array($column, self::PLAYER_SELECT_COLUMNS, true)) {
                        $violations[] = "Grant #{$grantNumber} grants SELECT on an unapproved players column.";

                        continue 2;
                    }

                    $foundPlayerSelect[$column] = true;
                }
            }

            if ($insertColumns !== null) {
                foreach ($insertColumns as $column) {
                    if (! in_array($column, self::PLAYER_INSERT_COLUMNS, true)) {
                        $violations[] = "Grant #{$grantNumber} grants INSERT on an unapproved players column.";

                        continue 2;
                    }

                    $foundPlayerInsert[$column] = true;
                }
            }
        }

        foreach (self::ACCOUNT_SELECT_COLUMNS as $column) {
            if (! isset($foundAccountSelect[$column])) {
                $violations[] = "Missing approved SELECT privilege for accounts.{$column}.";
            }
        }

        foreach (self::PLAYER_SELECT_COLUMNS as $column) {
            if (! isset($foundPlayerSelect[$column])) {
                $violations[] = "Missing approved SELECT privilege for players.{$column}.";
            }
        }

        foreach (self::PLAYER_INSERT_COLUMNS as $column) {
            if (! isset($foundPlayerInsert[$column])) {
                $violations[] = "Missing approved INSERT privilege for players.{$column}.";
            }
        }

        return array_values(array_unique($violations));
    }

    /**
     * @return list<string>|null
     */
    private function extractColumnPrivilege(string $privileges, string $privilege): ?array
    {
        if (preg_match('/\b'.preg_quote($privilege, '/').'\s*\(([^)]*)\)/i', $privileges, $matches) !== 1) {
            return null;
        }

        $columns = [];

        foreach (explode(',', $matches[1]) as $column) {
            $column = trim($column);

            if (preg_match('/^`(?:``|[^`])+`$|^[A-Za-z0-9_$]+$/', $column) !== 1) {
                return null;
            }

            $columns[] = $this->unquoteIdentifier($column);
        }

        return array_values(array_unique($columns));
    }

    /**
     * @return array{0: string, 1: string}|null
     */
    private function parseQualifiedTarget(string $target): ?array
    {
        if (preg_match(
            '/^(?<database>`(?:``|[^`])+`|[A-Za-z0-9_$]+|\*)\.(?<table>`(?:``|[^`])+`|[A-Za-z0-9_$]+|\*)$/',
            $target,
            $matches,
        ) !== 1) {
            return null;
        }

        return [
            $this->unquoteIdentifier($matches['database']),
            $this->unquoteIdentifier($matches['table']),
        ];
    }

    private function unquoteIdentifier(string $identifier): string
    {
        if (str_starts_with($identifier, '`') && str_ends_with($identifier, '`')) {
            return str_replace('``', '`', substr($identifier, 1, -1));
        }

        return $identifier;
    }
}
