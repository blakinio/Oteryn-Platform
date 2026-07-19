<?php

namespace App\CanaryIntegration;

use Illuminate\Support\Facades\DB;
use RuntimeException;

final class CanaryProvisioningDatabasePrivilegeVerifier
{
    /** @var list<string> */
    public const INSERT_COLUMNS = ['name', 'password', 'email', 'creation'];

    /** @var list<string> */
    public const SELECT_COLUMNS = ['id', 'name', 'creation'];

    /**
     * @return list<string>
     */
    public function inspect(): array
    {
        $connection = DB::connection(CanaryAccountProvisioner::CONNECTION);
        $database = $connection->getDatabaseName();

        if ($database === '') {
            throw new RuntimeException('The Canary provisioning database name is unavailable.');
        }

        $rows = $connection->select('SHOW GRANTS FOR CURRENT_USER');
        $grants = [];

        foreach ($rows as $row) {
            $values = array_values((array) $row);
            $grant = $values[0] ?? null;

            if (! is_string($grant) || $grant === '') {
                throw new RuntimeException('The database returned an unreadable provisioning grant row.');
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
            return ['No grants were returned for the current Canary provisioning credential.'];
        }

        $violations = [];
        $foundInsertColumns = [];
        $foundSelectColumns = [];

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

            if ($grantDatabase !== $database || $table !== 'accounts') {
                $violations[] = "Grant #{$grantNumber} targets data outside the approved Canary accounts provisioning surface.";

                continue;
            }

            $privileges = trim($matches[1]);
            $insertColumns = $this->extractColumnPrivilege($privileges, 'INSERT');
            $selectColumns = $this->extractColumnPrivilege($privileges, 'SELECT');

            $remaining = preg_replace('/\bINSERT\s*\([^)]*\)/i', '', $privileges, 1);
            $remaining = is_string($remaining)
                ? preg_replace('/\bSELECT\s*\([^)]*\)/i', '', $remaining, 1)
                : null;
            $remaining = is_string($remaining) ? trim(str_replace(',', '', $remaining)) : null;

            if ($remaining === null || $remaining !== '' || ($insertColumns === null && $selectColumns === null)) {
                $violations[] = "Grant #{$grantNumber} includes an unsupported or non-column-level privilege.";

                continue;
            }

            if ($insertColumns !== null) {
                foreach ($insertColumns as $column) {
                    if (! in_array($column, self::INSERT_COLUMNS, true)) {
                        $violations[] = "Grant #{$grantNumber} grants INSERT on an unapproved accounts column.";

                        continue 2;
                    }

                    $foundInsertColumns[$column] = true;
                }
            }

            if ($selectColumns !== null) {
                foreach ($selectColumns as $column) {
                    if (! in_array($column, self::SELECT_COLUMNS, true)) {
                        $violations[] = "Grant #{$grantNumber} grants SELECT on an unapproved accounts column.";

                        continue 2;
                    }

                    $foundSelectColumns[$column] = true;
                }
            }
        }

        foreach (self::INSERT_COLUMNS as $column) {
            if (! isset($foundInsertColumns[$column])) {
                $violations[] = "Missing approved INSERT privilege for accounts.{$column}.";
            }
        }

        foreach (self::SELECT_COLUMNS as $column) {
            if (! isset($foundSelectColumns[$column])) {
                $violations[] = "Missing approved SELECT privilege for accounts.{$column}.";
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
