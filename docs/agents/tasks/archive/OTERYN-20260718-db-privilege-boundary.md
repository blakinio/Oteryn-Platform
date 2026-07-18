# OTERYN-20260718 Database privilege boundary

## Goal

Turn the Oteryn Platform -> Canary database connection from an application-level read-only convention into a reviewable, database-enforced least-privilege boundary, with deterministic non-destructive privilege verification.

## Acceptance criteria

- [x] Add a reviewable MySQL/MariaDB-compatible provisioning artifact for a dedicated Oteryn Canary database user with direct table-level `SELECT` grants only.
- [x] Derive the granted table allowlist from current Oteryn Platform code and the current Canary data contract; do not grant unused future surfaces.
- [x] Update `docs/contracts/CANARY_DATA_CONTRACT.md` to require a separate Oteryn credential, DB-level SELECT-only enforcement, no Canary-server credential reuse, no root/admin credential, least privilege, and grant updates when the read surface changes.
- [x] Add a non-destructive privilege verifier that inspects the current Canary connection grants, rejects write/DDL/admin/global/schema-wide/unrecognized grants, and requires the exact current table allowlist.
- [x] Do not log or expose passwords/secrets and do not perform production write tests.
- [x] Add regression tests for accepted least-privilege grants and rejection of excessive or incomplete grants.
- [x] Run the relevant PublicGameData tests, Composer validation, formatting and full tests; static analysis was not present on this task's base/head and was not made an implementation dependency.
- [x] Verify current-head GitHub Actions CI, complete the checkpoint, and leave exactly one concrete `next_action`.

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
  - blakinio/canary was read-only evidence only; no writes were performed
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-18T23:52:10+02:00
head: eddbfa501b3c699deae99348077fcf329cff32b7
branch: task/OTERYN-20260718-db-privilege-boundary
pr: 7
status: completed
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
  - main HEAD after merged PR #5 and before this task was d94915064e64b7cd6c02dcd91268743224289f76.
  - Open PR #6 implements OTERYN-20260718-static-analysis-gate and owns Composer/PHPStan/CI/test-strategy paths; it does not overlap this task's owned paths and is not present on this task base/head.
  - The pre-task `canary` Laravel connection used the MySQL driver and a nominal default username `oteryn_readonly`, but main had no repository provisioning or runtime DB-level privilege enforcement.
  - Current PublicGameData code reads exactly five Canary tables: players, guilds, guild_membership, guild_ranks, and channels.
  - The contract approves `cluster_sessions` for a future bounded online-list read, but current application code does not read that table; least privilege therefore does not justify granting it yet.
  - Existing PublicGameData integration tests use SQLite PRAGMA query_only = ON; that remains useful test isolation but is not treated as production MySQL/MariaDB enforcement.
  - Canary is built with libmariadb while Platform uses Laravel's MySQL driver; the committed control uses common direct MySQL/MariaDB grant semantics and fails closed on privilege models it cannot deterministically validate.
  - database/provisioning/canary-readonly.sql.template is placeholder-only, is not automatically executed, and configures a dedicated account by revoking historical excess privileges before granting direct SELECT only on players, guilds, guild_membership, guild_ranks and channels.
  - app/CanaryIntegration/CanaryDatabasePrivilegeVerifier.php inspects SHOW GRANTS FOR CURRENT_USER without a destructive write test and never emits raw grant statements.
  - The verifier accepts account-level USAGE plus direct table-level SELECT on the exact five-table allowlist and fails on write/admin/DDL/global/schema-wide/extra-table/role/unrecognized grant shapes, GRANT OPTION, or missing required table grants.
  - routes/console.php exposes php artisan canary:verify-db-privileges as the non-destructive deployment diagnostic and returns a non-zero status on inspection failure or policy violation.
  - Focused unit tests cover the exact valid grant set and rejection of write privilege, schema-wide SELECT, extra tables, missing required grants, role grants and GRANT OPTION.
  - The Canary data contract requires a dedicated Oteryn credential, forbids Canary-server/root/admin credential reuse, requires DB-level table SELECT-only least privilege, and requires contract/provisioning/verifier updates whenever the implemented read surface changes.
  - Temporary contract-patch and formatting workflows self-removed and are absent from the PR changed-file list.
  - GitHub Actions CI run 29662310084 on implementation/checkpoint head eddbfa501b3c699deae99348077fcf329cff32b7 passed Composer metadata/lockfile validation, install from lockfile, Pint format check and the full test suite.
derived:
  - The current production read credential table allowlist is exactly: players, guilds, guild_membership, guild_ranks, channels.
  - A future feature that starts reading cluster_sessions must update provisioning grants and the privilege-verifier allowlist/tests in the same reviewed change.
  - Direct grant inspection is intentionally conservative: role-based or otherwise unrecognized privilege models are rejected rather than assumed safe.
unknown:
  - Exact production MySQL/MariaDB server product and version are not proven by repository evidence; the provisioning template has been logically reviewed but not executed against the unknown production server version.
conflicts: []
first_failure:
  marker: PINT_FORMATTING
  evidence: GitHub Actions CI run 29662208119 on head 00344682a81162a6f1f9e94a55771862a12b03e0 passed Composer validation/install and failed only at Pint; repository Pint formatted the changed PHP files and subsequent CI run 29662310084 passed
rejected_hypotheses:
  - Treat the username oteryn_readonly as an enforced security control: rejected because configuration proved only a name, not database privileges.
  - Treat SQLite PRAGMA query_only tests as production enforcement: rejected because production uses a MySQL-driver Canary connection.
  - Pre-grant cluster_sessions because the contract approves a future read: rejected because current code does not read it and least privilege requires grants to follow the implemented surface.
changed_paths:
  - app/CanaryIntegration/CanaryDatabasePrivilegeVerifier.php
  - database/provisioning/canary-readonly.sql.template
  - docs/agents/tasks/archive/OTERYN-20260718-db-privilege-boundary.md
  - docs/contracts/CANARY_DATA_CONTRACT.md
  - routes/console.php
  - tests/Unit/CanaryIntegration/CanaryDatabasePrivilegeVerifierTest.php
validation:
  - command: startup repository/task/PR/ownership verification
    result: PASS
    evidence: root governance, required architecture/contracts, merged PR #5, open PR #6, current main, Canary connection, PublicGameData repository and tests inspected
  - command: provisioning artifact logical review
    result: PASS_WITH_UNKNOWN_RUNTIME_VERSION
    evidence: placeholder-only account/revoke/direct table SELECT syntax is limited to common MySQL/MariaDB semantics; exact production server product/version is UNKNOWN and no production action was performed
  - command: local checkout validation
    result: UNAVAILABLE
    evidence: git access to github.com could not resolve DNS in the execution sandbox; local Git/worktree commands cannot be claimed
  - command: static analysis
    result: NOT_AVAILABLE_ON_BASE
    evidence: OTERYN-20260718-static-analysis-gate remains in separate open PR #6 and is not present on this task base/head; T2 has no dependency on T1
  - command: GitHub Actions CI run 29662208119
    result: FAIL
    evidence: Composer metadata/lockfile validation and dependency installation passed; Pint failed; tests were skipped; repository Pint was then applied
  - command: GitHub Actions CI run 29662310084
    result: PASS
    evidence: Composer metadata/lockfile validation, dependency installation from lockfile, Pint format check and full tests all passed; the existing PublicGameData suite is part of the full test command
blockers:
  - none
next_action: Provision the dedicated Oteryn Canary database credential in the target deployment from the reviewed template and require php artisan canary:verify-db-privileges to pass before enabling Canary-backed reads.
```

## Notes

The task is repository-complete. The production privilege boundary becomes operational only when deployment provisions the dedicated credential and the non-destructive verifier passes against that actual account. No production credential or database privilege change was performed by this task.
