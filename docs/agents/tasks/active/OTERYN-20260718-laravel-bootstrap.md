# OTERYN-20260718 Laravel application bootstrap

## Goal

Create the initial maintained Laravel/PHP application foundation for Oteryn Platform with Blade, tests, CI and safe environment configuration, without implementing speculative Canary/login-server shared auth/data behavior.

## Status

Ready for a future agent to start.

## Required startup context

- `AGENTS.md`
- `docs/agents/PROJECT_STATE.md`
- `docs/architecture/SYSTEM_ARCHITECTURE.md`
- `docs/architecture/MODULE_CATALOG.md`
- `docs/architecture/SECURITY_ARCHITECTURE.md`
- `docs/architecture/TEST_STRATEGY.md`
- ADR 0001

Load Canary/auth contracts only if the bootstrap task reaches an integration decision. The default bootstrap must remain independent of speculative shared schema/auth assumptions.

## Acceptance criteria

- Select a currently maintained Laravel and PHP version using official upstream support information at implementation time.
- Bootstrap a clean Laravel application in this repository.
- Use Blade as the initial UI layer unless a new ADR supersedes ADR 0001.
- Commit `.env.example` placeholders only; never commit `.env` or secrets.
- Establish a reproducible dependency install using Composer lockfile.
- Add a baseline application/health route suitable for testing, without leaking sensitive environment data.
- Establish unit/feature test baseline.
- Establish formatter/linter/static-analysis choices appropriate to the selected Laravel/PHP version.
- Add GitHub CI that installs dependencies and runs the selected required checks.
- Document local setup commands from actual project files.
- Keep payments out of scope.
- Keep Canary/login-server integration out of scope except for non-binding configuration placeholders when clearly necessary.

## Owned paths

Expected ownership after task begins:

- Laravel scaffold/application paths
- `composer.json`
- `composer.lock`
- `app/**`
- `bootstrap/**`
- `config/**`
- `database/**`
- `public/**`
- `resources/**`
- `routes/**`
- `storage/**` tracked placeholders only
- `tests/**`
- `.env.example`
- `.github/workflows/**`
- bootstrap-related documentation

Before editing, verify no other active task owns overlapping paths.

## Explicit non-goals

- real account login against Canary;
- password/hash migration;
- character creation/deletion;
- guild/highscore implementation;
- payment/shop implementation;
- production Cloudflare configuration;
- production deployment.

## Security constraints

- no secrets in Git;
- no debug endpoint exposing environment/configuration;
- no speculative custom authentication implementation;
- use framework defaults/security primitives;
- dependency versions selected from maintained official releases at task execution time.

## Validation target

Exact commands must come from the bootstrapped repository. Expected categories:

- dependency install;
- PHP/framework version check;
- syntax/lint/format/static analysis as selected;
- unit/feature tests;
- CI run on current head.

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-18T19:30:00Z
head: 95430f1e257daf7fd7c230983e9a3fc0bcee3705
branch: main
pr: none
status: investigating
context_routes:
  - architecture
  - testing
  - security
owned_paths: []
proven:
  - Phase 0 architecture and agent bootstrap is complete on main.
  - ADR 0001 selects a Laravel modular monolith with Blade as the initial direction.
  - Canary and login-server integration require separate evidence-backed contracts.
  - Payments are deferred.
derived:
  - The next safe implementation step is framework/application bootstrap without shared auth/data integration.
unknown:
  - Current maintained Laravel/PHP version to select at task execution time.
  - Exact CI toolchain choices for linting/static analysis.
conflicts: []
first_failure:
  marker: none
  evidence: none
rejected_hypotheses: []
changed_paths: []
validation:
  - command: not started
    result: NOT_RUN
    evidence: task prepared for future execution
blockers: []
next_action: Verify current repository and official Laravel/PHP support state, claim paths, create a dedicated task branch, then bootstrap the minimal Laravel application and CI baseline.
```
