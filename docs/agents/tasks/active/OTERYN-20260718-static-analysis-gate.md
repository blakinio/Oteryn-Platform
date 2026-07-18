# OTERYN-20260718 Static analysis gate

## Goal

Add a mandatory PHP/Laravel static-analysis gate before Phase 3 Identity/Auth implementation, using maintained PHPStan/Larastan tooling without weakening existing validation.

## Acceptance criteria

- [ ] Add maintained PHPStan/Larastan dependencies compatible with the repository's Laravel 13 / PHP 8.5 stack.
- [ ] Configure the strictest practical analysis level and analyse application code without suppressing new findings.
- [ ] Add a Composer static-analysis script.
- [ ] Make static analysis a mandatory CI step while preserving Composer validation, lockfile install, Pint and tests.
- [ ] Keep `composer.lock` consistent with `composer.json`.
- [ ] Run `composer validate --strict`, install from lockfile, format check, static analysis and full tests.
- [ ] Record current-head GitHub Actions evidence and leave a complete handover with exactly one `next_action`.
- [ ] Do not implement Identity/Auth, MFA, user sessions, password migration, payments, public-WWW features or Canary changes.

## Ownership

```yaml
owned_paths:
  - composer.json
  - composer.lock
  - phpstan.neon.dist
  - phpstan-baseline.neon
  - .github/workflows/ci.yml
  - .github/workflows/static-analysis-lockfile.yml
  - docs/architecture/TEST_STRATEGY.md
  - docs/agents/tasks/active/OTERYN-20260718-static-analysis-gate.md
modules:
  - CI
  - test infrastructure
  - PHP static analysis
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
updated_at: 2026-07-18T23:36:59+02:00
head: 874215a0f962e8e8efd8873a2b3e58802ea141ce
branch: task/OTERYN-20260718-static-analysis-gate
pr: none
status: implementing
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
  - docs/architecture/TEST_STRATEGY.md
  - docs/agents/tasks/active/OTERYN-20260718-static-analysis-gate.md
proven:
  - main HEAD at task creation is 874215a0f962e8e8efd8873a2b3e58802ea141ce.
  - The repository uses Laravel ^13.8 and PHP ^8.5, has Pint and PHPUnit, and has no PHPStan/Larastan dependency or static-analysis Composer script on main.
  - Main CI validates Composer metadata/lockfile, installs from lockfile, runs Pint format check and runs the full Composer test script, but has no static-analysis step.
  - Open PR #5 owns only online-status documentation/contract paths and does not overlap this task's Composer, PHPStan, CI or test-strategy paths.
  - PR #5 owns docs/agents/ACTIVE_WORK.md and docs/agents/PROJECT_STATE.md, so this task will not edit those shared files.
  - Larastan 3.x supports Laravel 11.16+ and PHP 8.2+, which includes this Laravel 13 / PHP 8.5 repository.
derived:
  - A dedicated PHPStan 2.x plus Larastan 3.x gate is compatible with the current framework/runtime direction.
  - Analysis should start at PHPStan level 10 with no baseline; a baseline is permitted only if actual legacy findings make it technically necessary.
unknown:
  - Exact findings produced by level 10 analysis on the current application code.
  - Exact compatible dependency versions Composer will resolve into the lockfile.
conflicts: []
first_failure:
  marker: local-checkout-unavailable
  evidence: sandbox git clone failed because github.com DNS resolution is unavailable; GitHub API and GitHub Actions remain available for repository writes and validation
rejected_hypotheses:
  - Static analysis was already added after the prompt was written: rejected by current main composer.json, CI workflow and repository search.
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260718-static-analysis-gate.md
validation:
  - command: startup repository/task/PR/ownership verification
    result: PASS
    evidence: main HEAD, root governance, project state, repository map, context routing, security architecture, test strategy, active work, open PR #5, composer.json and CI inspected
  - command: local git checkout verification
    result: BLOCKED
    evidence: execution sandbox cannot resolve github.com for git clone; no local working tree exists to inspect
blockers:
  - none
next_action: Open the draft PR, then add PHPStan/Larastan configuration and use GitHub Actions to resolve and capture the updated Composer lockfile.
```

## Notes

The temporary `static-analysis-lockfile.yml` path is owned only as a bootstrap mechanism if needed to resolve the lockfile in GitHub Actions; it must not remain in the final PR unless it has an independently justified permanent purpose.
