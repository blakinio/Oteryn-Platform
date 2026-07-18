# OTERYN-20260718 Static analysis gate

## Goal

Add a mandatory PHP/Laravel static-analysis gate before Phase 3 Identity/Auth implementation, using maintained PHPStan/Larastan tooling without weakening existing validation.

## Acceptance criteria

- [x] Add maintained PHPStan/Larastan dependencies compatible with the repository's Laravel 13 / PHP 8.5 stack.
- [x] Configure the strictest practical analysis level and analyse application code without suppressing new findings.
- [x] Add a Composer static-analysis script.
- [x] Make static analysis a mandatory CI step while preserving Composer validation, lockfile install, Pint and tests.
- [x] Keep `composer.lock` consistent with `composer.json`.
- [x] Run `composer validate --strict`, install from lockfile, format check, static analysis and full tests.
- [x] Record current-head GitHub Actions evidence and leave a complete handover with exactly one `next_action`.
- [x] Do not implement Identity/Auth, MFA, user sessions, password migration, payments, public-WWW features or Canary changes.

## Ownership

```yaml
owned_paths:
  - composer.json
  - composer.lock
  - phpstan.neon.dist
  - phpstan-baseline.neon
  - .github/workflows/ci.yml
  - .github/workflows/static-analysis-lockfile.yml
  - app/PublicGameData/CanaryGameDataRepository.php
  - tests/Unit/BootstrapTest.php
  - docs/architecture/TEST_STRATEGY.md
  - docs/agents/tasks/active/OTERYN-20260718-static-analysis-gate.md
modules:
  - CI
  - test infrastructure
  - PHP static analysis
  - PublicGameData type annotations only
dependencies:
  - none
blockers:
  - none
cross_repository_tasks:
  - none
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-18T23:48:54+02:00
head: 0f9c3a65730198555727cf95a0972269ada65892
branch: task/OTERYN-20260718-static-analysis-gate
pr: 6
status: ready
context_routes:
  - agent-governance
  - testing
  - security
owned_paths:
  - composer.json
  - composer.lock
  - phpstan.neon.dist
  - phpstan-baseline.neon
  - .github/workflows/ci.yml
  - .github/workflows/static-analysis-lockfile.yml
  - app/PublicGameData/CanaryGameDataRepository.php
  - tests/Unit/BootstrapTest.php
  - docs/architecture/TEST_STRATEGY.md
  - docs/agents/tasks/active/OTERYN-20260718-static-analysis-gate.md
proven:
  - main HEAD at task creation was 874215a0f962e8e8efd8873a2b3e58802ea141ce.
  - The repository uses Laravel ^13.8 and PHP ^8.5; main had Pint and PHPUnit but no PHPStan/Larastan dependency, Composer analysis script or static-analysis CI step.
  - PR #5 initially owned online-status documentation/contract paths only and did not overlap this task's final Composer, PHPStan, CI, PublicGameData code, unit-test or test-strategy paths.
  - PR #5 merged during this task and advanced main to d94915064e64b7cd6c02dcd91268743224289f76.
  - This task branch merged the updated main and compare state after synchronization is ahead_by=18, behind_by=0 with merge base d94915064e64b7cd6c02dcd91268743224289f76.
  - Composer resolved larastan/larastan v3.10.0 and phpstan/phpstan 2.2.5 into the committed lockfile under the declared compatible constraints.
  - phpstan.neon.dist runs Larastan/PHPStan at level 10 across app, bootstrap, config, database, routes and tests with no ignoreErrors and no baseline.
  - The first level-10 run found exactly four actionable findings: three missing Laravel generic return types in CanaryGameDataRepository and one always-true placeholder BootstrapTest assertion.
  - The three PublicGameData findings were fixed with precise stdClass paginator/collection PHPDoc generics without changing query behavior.
  - The placeholder BootstrapTest was replaced with a runtime assertion that the PDO SQLite driver required by the repository's local/test database strategy is available.
  - A second level-10 run rejected the initial phpversion assertion as statically always true; the PDO SQLite assertion removed the final finding without weakening PHPStan configuration.
  - GitHub Actions CI run 29662265833 on synchronized head 0f9c3a65730198555727cf95a0972269ada65892 completed successfully through Composer validation, lockfile install, Pint, level-10 static analysis and full tests.
  - Temporary workflows used for lockfile generation, diagnostics and branch synchronization were removed; only the existing CI workflow remains in the final diff.
  - No Identity/Auth, MFA, user-session, password-migration, payment, public-WWW or Canary changes were made.
derived:
  - Level 10 is practical for the current codebase and does not require a baseline.
  - New static-analysis findings will fail the mandatory CI job before tests can report success.
  - The four initial findings were small enough to fix directly, so introducing a baseline would have hidden fixable debt without technical justification.
unknown: []
conflicts: []
first_failure:
  marker: static-analysis-level-10
  evidence: GitHub Actions CI run 29662027410 failed at Run static analysis with four findings; Composer validation, lockfile install and Pint had already passed
rejected_hypotheses:
  - Static analysis was already added after the prompt was written: rejected by current main composer.json, CI workflow and repository search at task start.
  - A PHPStan baseline is required for legacy findings: rejected because all four initial findings and the one follow-up test finding were fixed directly and the level-10 gate passes with no baseline.
  - PHPStan strictness needed to be reduced for PHPUnit PHPDoc certainty: rejected by replacing the placeholder assertion with a meaningful PDO SQLite runtime check.
changed_paths:
  - composer.json
  - composer.lock
  - phpstan.neon.dist
  - .github/workflows/ci.yml
  - app/PublicGameData/CanaryGameDataRepository.php
  - tests/Unit/BootstrapTest.php
  - docs/architecture/TEST_STRATEGY.md
  - docs/agents/tasks/active/OTERYN-20260718-static-analysis-gate.md
validation:
  - command: startup repository/task/PR/ownership verification
    result: PASS
    evidence: main HEAD, root governance, project state, repository map, context routing, security architecture, test strategy, active work, open PR #5, composer.json, composer.lock intent and CI inspected before writes
  - command: local git checkout verification
    result: BLOCKED
    evidence: execution sandbox could not resolve github.com for git clone; repository writes used GitHub API and executable PHP/Composer validation used GitHub Actions
  - command: composer validate --strict
    result: PASS
    evidence: GitHub Actions run 29662265833 job 88126825110 on synchronized head 0f9c3a65730198555727cf95a0972269ada65892
  - command: composer install --no-interaction --prefer-dist --no-progress
    result: PASS
    evidence: GitHub Actions run 29662265833 job 88126825110 on synchronized head 0f9c3a65730198555727cf95a0972269ada65892
  - command: composer format:check
    result: PASS
    evidence: GitHub Actions run 29662265833 job 88126825110 on synchronized head 0f9c3a65730198555727cf95a0972269ada65892
  - command: composer analyse
    result: PASS
    evidence: GitHub Actions run 29662265833 job 88126825110 on synchronized head 0f9c3a65730198555727cf95a0972269ada65892; PHPStan/Larastan level 10, no baseline
  - command: composer test
    result: PASS
    evidence: GitHub Actions run 29662265833 job 88126825110 on synchronized head 0f9c3a65730198555727cf95a0972269ada65892
blockers:
  - none
next_action: Squash-merge PR #6 after confirming the final CI check is green on the live PR HEAD.
```

## Notes

The execution sandbox could not create a local checkout because outbound DNS for `github.com` was unavailable. The same required commands were executed by the repository's GitHub Actions runner against the PR code. Temporary workflows were used only to generate the Composer lockfile, capture complete PHPStan diagnostics and synchronize the branch after main advanced; all were removed before final delivery. The checkpoint `head` records the synchronized code/documentation head validated before this handover-only task-record commit; the live PR remains authoritative for the exact final HEAD and its CI result.
