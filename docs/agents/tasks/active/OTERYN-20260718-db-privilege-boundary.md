# OTERYN-20260718 Database privilege boundary

## Goal

Turn the Oteryn Platform -> Canary database connection from an application-level read-only convention into a reviewable, database-enforced least-privilege boundary, with deterministic non-destructive privilege verification.

## Acceptance criteria

- [ ] Add a reviewable MySQL/MariaDB-compatible provisioning artifact for a dedicated Oteryn Canary database user with direct table-level `SELECT` grants only.
- [ ] Derive the granted table allowlist from current Oteryn Platform code and the current Canary data contract; do not grant unused future surfaces.
- [ ] Update `docs/contracts/CANARY_DATA_CONTRACT.md` to require a separate Oteryn credential, DB-level SELECT-only enforcement, no Canary-server credential reuse, no root/admin credential, least privilege, and grant updates when the read surface changes.
- [ ] Add a non-destructive privilege verifier that inspects the current Canary connection grants, rejects write/DDL/admin/global/schema-wide/unrecognized grants, and requires the exact current table allowlist.
- [ ] Do not log or expose passwords/secrets and do not perform production write tests.
- [ ] Add regression tests for accepted least-privilege grants and rejection of excessive or incomplete grants.
- [ ] Run the relevant PublicGameData tests, Composer validation, formatting, full tests, and static analysis only if the T1 gate is present on this task's current base/head.
- [ ] Verify current-head GitHub Actions CI, complete the checkpoint, and leave exactly one concrete `next_action`.

## Ownership

```yaml
owned_paths:
  - database/provisioning/**
  - app/CanaryIntegration/**
  - routes/console.php
  - tests/Unit/CanaryIntegration/**
  - tests/Feature/CanaryIntegration/**
  - docs/contracts/CANARY_DATA_CONTRACT.md
  - docs/agents/tasks/active/OTERYN-20260718-db-privilege-boundary.md
  - docs/agents/tasks/archive/OTERYN-20260718-db-privilege-boundary.md
modules:
  - Canary integration security boundary
  - database deployment/provisioning
  - deployment diagnostics
  - testing
dependencies:
  - docs/contracts/CANARY_DATA_CONTRACT.md
  - app/PublicGameData/CanaryGameDataRepository.php
  - OTERYN-20260718-static-analysis-gate (optional validation only; no implementation dependency)
blockers:
  - none
cross_repository_tasks:
  - blakinio/canary is read-only evidence only; no writes authorized
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-18T23:49:40+02:00
head: 9353f7bd4f08acf5e3905199020ec72b86f12c26
branch: task/OTERYN-20260718-db-privilege-boundary
pr: 7
status: validating
context_routes:
  - agent-governance
  - security
  - database
  - canary-integration
  - public-game-data
  - testing
owned_paths:
  - database/provisioning/**
  - app/CanaryIntegration/**
  - routes/console.php
  - tests/Unit/CanaryIntegration/**
  - tests/Feature/CanaryIntegration/**
  - docs/contracts/CANARY_DATA_CONTRACT.md
  - docs/agents/tasks/active/OTERYN-20260718-db-privilege-boundary.md
  - docs/agents/tasks/archive/OTERYN-20260718-db-privilege-boundary.md
proven:
  - main HEAD after merged PR #5 is d94915064e64b7cd6c02dcd91268743224289f76.
  - Open PR #6 implements OTERYN-20260718-static-analysis-gate and owns Composer/PHPStan/CI/test-strategy paths; it does not overlap this task's owned paths and is not present on this task base/head.
  - The current `canary` Laravel connection is a MySQL-driver connection whose default username is only nominally `oteryn_readonly`; no repository provisioning or runtime DB-level privilege enforcement existed on main when this task started.
  - Current PublicGameData code reads exactly five Canary tables: players, guilds, guild_membership, guild_ranks, and channels.
  - The current contract approves `cluster_sessions` for a future bounded online-list read, but current main application code does not read that table; least privilege therefore does not justify granting it yet.
  - Current PublicGameData integration tests use SQLite `PRAGMA query_only = ON`, which protects the test connection but does not prove production MySQL/MariaDB credentials are read-only.
  - Canary is built with `libmariadb`; the Platform uses Laravel's MySQL driver, so the target privilege mechanism stays within common direct MySQL/MariaDB grant semantics.
  - Draft PR #7 exists from the dedicated task branch against main.
  - Provisioning template grants direct SELECT only on players, guilds, guild_membership, guild_ranks and channels after explicitly revoking historical excess privileges from the dedicated account; it is a placeholder-only review artifact and is not auto-executed.
  - The verifier inspects SHOW GRANTS FOR CURRENT_USER without performing writes and does not emit raw grant statements.
  - The verifier accepts only account-level USAGE plus direct table-level SELECT on the exact five-table allowlist and fails closed on write/admin/DDL/global/schema-wide/extra-table/role/unrecognized grant shapes.
  - Focused unit tests cover the exact valid grant set and rejection of write privilege, schema-wide SELECT, extra tables, missing required grants, role grants and GRANT OPTION.
  - The Canary data contract now requires a dedicated Oteryn credential, forbids Canary-server/root/admin credential reuse, requires DB-level table SELECT-only least privilege, and requires contract/provisioning/verifier updates when the implemented read surface changes.
  - Temporary contract-patch and formatting workflows self-removed and are absent from the PR changed-file list.
derived:
  - The current production read credential table allowlist is exactly: players, guilds, guild_membership, guild_ranks, channels.
  - A future feature that starts reading `cluster_sessions` must update provisioning grants and privilege-verifier allowlist in the same reviewed change.
  - Deterministic fail-closed verification accepts only direct `USAGE` plus direct table-level `SELECT` grants on the exact allowlist and rejects role-based or otherwise unrecognized grant forms rather than claiming they are safe.
unknown:
  - Exact production MySQL/MariaDB server product and version are not proven by the repository; provisioning avoids version-specific privilege features and the verifier rejects unsupported privilege models.
conflicts: []
first_failure:
  marker: PINT_FORMATTING
  evidence: GitHub Actions CI run 29662208119 on head 00344682a81162a6f1f9e94a55771862a12b03e0 passed Composer validation/install and failed only at Pint; a repository-Pint formatting pass produced head 9353f7bd4f08acf5e3905199020ec72b86f12c26
rejected_hypotheses:
  - Treat the username `oteryn_readonly` as an enforced security control: rejected because the current configuration proves only a name, not database privileges.
  - Treat SQLite `PRAGMA query_only` tests as production enforcement: rejected because production uses a MySQL-driver Canary connection.
  - Pre-grant `cluster_sessions` because the contract approves a future read: rejected because no current main code reads it and least privilege requires grants to follow the implemented read surface.
changed_paths:
  - app/CanaryIntegration/CanaryDatabasePrivilegeVerifier.php
  - database/provisioning/canary-readonly.sql.template
  - docs/agents/tasks/active/OTERYN-20260718-db-privilege-boundary.md
  - docs/contracts/CANARY_DATA_CONTRACT.md
  - routes/console.php
  - tests/Unit/CanaryIntegration/CanaryDatabasePrivilegeVerifierTest.php
validation:
  - command: startup repository/task/PR/ownership verification
    result: PASS
    evidence: root governance, required architecture/contracts, merged PR #5, open PR #6, current main, Canary connection, PublicGameData repository and tests inspected
  - command: provisioning artifact logical review
    result: PASS_WITH_UNKNOWN_RUNTIME_VERSION
    evidence: placeholder-only direct account/REVOKE/direct table SELECT syntax is intentionally limited to common MySQL/MariaDB semantics; exact production server product/version is UNKNOWN and no production execution was performed
  - command: local checkout validation
    result: UNAVAILABLE
    evidence: git access to github.com cannot resolve DNS in the execution sandbox; local Git/worktree commands cannot be claimed
  - command: GitHub Actions CI run 29662208119
    result: FAIL
    evidence: Composer metadata/lockfile validation and dependency installation passed; Pint failed; tests were skipped; formatting was then corrected with repository Pint
  - command: static analysis
    result: NOT_AVAILABLE_ON_BASE
    evidence: OTERYN-20260718-static-analysis-gate remains in separate open PR #6 and is not present on this task base/head; this task introduces no dependency on T1
blockers:
  - none
next_action: Inspect fresh PR #7 CI on the connector-authored post-formatting head and fix any remaining test or validation failure.
```

## Notes

The verifier is a deployment/security diagnostic. It must inspect grants only and must never prove read-only status by attempting an INSERT/UPDATE/DELETE against the production Canary database.
