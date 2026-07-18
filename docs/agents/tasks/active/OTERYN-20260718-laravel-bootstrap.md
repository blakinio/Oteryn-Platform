# OTERYN-20260718 Laravel application bootstrap

## Goal

Create the initial maintained Laravel/PHP application foundation for Oteryn Platform with Blade, tests, CI and safe environment configuration, without implementing speculative Canary/login-server shared auth/data behavior.

## Status

In progress.

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

- Laravel scaffold/application paths
- `composer.json`
- `composer.lock`
- `artisan`
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
- `.editorconfig`
- `.gitattributes`
- `.gitignore`
- `phpunit.xml`
- `.github/workflows/**`
- `README.md`
- `docs/agents/tasks/active/OTERYN-20260718-laravel-bootstrap.md`
- bootstrap-related narrow updates under `docs/agents/**`

No other active task currently claims overlapping paths.

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
updated_at: 2026-07-18T21:15:00+02:00
head: 99e6893e0eed0e4716ef4474a73a2273fca07a93
branch: task/OTERYN-20260718-laravel-bootstrap
pr: none
status: implementing
context_routes:
  - architecture
  - testing
  - security
owned_paths:
  - composer.json
  - composer.lock
  - artisan
  - app/**
  - bootstrap/**
  - config/**
  - database/**
  - public/**
  - resources/**
  - routes/**
  - storage/**
  - tests/**
  - .env.example
  - .editorconfig
  - .gitattributes
  - .gitignore
  - phpunit.xml
  - .github/workflows/**
  - README.md
  - docs/agents/tasks/active/OTERYN-20260718-laravel-bootstrap.md
proven:
  - Phase 0 architecture and agent bootstrap is complete on main.
  - ADR 0001 selects a Laravel modular monolith with Blade as the initial direction.
  - Canary and login-server integration require separate evidence-backed contracts.
  - Payments are deferred.
  - No open pull request was found for this task or another overlapping OTERYN task at startup.
  - docs/agents/ACTIVE_WORK.md lists only this Laravel bootstrap task as active.
  - Official Laravel 13 release notes list Laravel 13 as released on 2026-03-17, requiring PHP 8.3 or newer and supporting PHP 8.3-8.5.
  - Official PHP supported-versions information lists PHP 8.5 under active support through 2027-12-31 and security support through 2029-12-31.
  - The target stack selected for this bootstrap is Laravel 13 on PHP 8.5.
derived:
  - The bootstrap can remain independent of Canary and login-server schema/auth decisions.
  - Laravel Pint plus Composer validation and PHP syntax checks provide a useful baseline without introducing a separate static-analysis dependency before domain code exists.
unknown:
  - Final resolved Composer dependency graph and lockfile until generated on PHP 8.5.
  - CI result on the task branch until the workflow runs.
conflicts: []
first_failure:
  marker: LOCAL_RUNTIME_PHP_VERSION
  evidence: The available sandbox runtime is PHP 8.4.16 and has no Composer binary, so PHP 8.5 dependency resolution and full Laravel execution must be validated in GitHub Actions.
rejected_hypotheses:
  - Use Laravel 12: rejected because Laravel 13 is the current maintained major release and has a longer support window.
  - Add Larastan immediately: deferred because the bootstrap has no substantive domain code yet; formatter, syntax lint, Composer validation and PHPUnit provide the initial baseline.
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260718-laravel-bootstrap.md
validation:
  - command: official Laravel/PHP support verification
    result: PASS
    evidence: Laravel 13 support policy and PHP supported-versions pages checked on 2026-07-18.
  - command: php -v
    result: PASS_WITH_LIMITATION
    evidence: sandbox provides PHP 8.4.16, below selected project target PHP 8.5.
  - command: composer --version
    result: NOT_AVAILABLE
    evidence: Composer is not installed in the sandbox.
blockers: []
next_action: Add the minimal Laravel 13 application scaffold, Blade home/status routes, safe environment template, baseline tests and temporary lock-generation CI on the dedicated task branch.
```
