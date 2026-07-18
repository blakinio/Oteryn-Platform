# OTERYN-20260718 Laravel application bootstrap

## Status

Completed on 2026-07-18. PR #1 is the delivery PR for the validated bootstrap.

## Goal

Create the initial maintained Laravel/PHP application foundation for Oteryn Platform with Blade, tests, CI and safe environment configuration, without implementing speculative Canary/login-server shared auth/data behavior.

## Completed deliverables

- Laravel 13 application foundation targeting PHP 8.5;
- server-rendered Blade home page;
- safe `.env.example` with placeholders/local defaults only;
- committed `composer.lock` generated on PHP 8.5;
- Laravel built-in health route exposed at `GET /health`;
- baseline unit and feature tests;
- Laravel Pint formatting checks;
- lockfile-backed GitHub Actions CI with read-only repository permissions;
- documented local setup and validation commands;
- no account/user model or auth/account migration assumptions;
- no Canary/login-server write integration, payments, MFA, character creation or production Cloudflare changes.

## Acceptance criteria result

- PASS — current maintained framework/runtime selected from official upstream support information: Laravel 13 and PHP 8.5.
- PASS — clean Laravel application scaffold created.
- PASS — Blade is the initial UI layer.
- PASS — `.env.example` contains no committed application key, credentials or production secrets.
- PASS — Composer lockfile is committed and final CI installs with `composer install`.
- PASS — `GET /health` is configured without an environment/configuration dump and is covered by a feature test.
- PASS — unit and feature test baselines exist.
- PASS — Composer validation and Laravel Pint are required checks; separate static analysis is deliberately deferred until substantive domain code exists.
- PASS — GitHub Actions CI runs on PHP 8.5 with `contents: read`.
- PASS — local setup is documented in `README.md`.
- PASS — explicit non-goals remained out of scope.

## CI repair note

Final CI run `29659305851` first failed at `composer validate --strict` after package license metadata had been removed from `composer.json`; subsequent install/format/test steps were skipped. Commit `9973a11940f55b7dcf70939b94b67f999535f6ce` restored the prior `license: proprietary` package metadata. Run `29659338430` then passed Composer validation, lockfile installation, Pint and the test suite without weakening the check.

## Final context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-18T22:30:00+02:00
head: 9973a11940f55b7dcf70939b94b67f999535f6ce
branch: task/OTERYN-20260718-laravel-bootstrap
pr: 1
status: ready
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
  - docs/agents/**
proven:
  - Phase 0 architecture and agent bootstrap was complete before this task began.
  - ADR 0001 selects a Laravel modular monolith with Blade as the initial direction.
  - Official Laravel 13 support information requires PHP 8.3 or newer and supports PHP 8.3-8.5.
  - Official PHP support information lists PHP 8.5 as actively supported at task execution time.
  - Oteryn Platform now has a Laravel 13 application foundation targeting PHP 8.5.
  - composer.lock is committed and resolves laravel/framework v13.20.0, laravel/pint v1.29.3, phpunit/phpunit 12.5.31, nunomaduro/collision v8.9.5 and mockery/mockery 1.6.12.
  - The scaffold contains no account/user model and no auth/account migrations.
  - Laravel's built-in health route is configured at /health and feature-tested for HTTP 200 without environment-key labels.
  - GitHub Actions CI uses PHP 8.5, composer install from the lockfile, Composer strict validation, Pint and the Laravel/PHPUnit test suite.
  - Final lockfile-backed CI run 29659338430 passed every step on implementation head 9973a11940f55b7dcf70939b94b67f999535f6ce.
  - PR #1 has no review submissions or unresolved review threads at handover time.
derived:
  - The Laravel application foundation is ready for merge without introducing speculative Canary/login-server schema or authentication behavior.
  - Separate static analysis can be reconsidered when substantive domain/application code exists; it is not required to make the empty-domain bootstrap internally consistent.
unknown:
  - Exact Oteryn Canary account/player/guild schema and permitted shared-data writes.
  - Exact login-server authentication, password and game-session contract.
  - Final production hosting/network topology and production cache/queue/mail providers.
conflicts: []
first_failure:
  marker: LOCAL_RUNTIME_PHP_VERSION
  evidence: The available sandbox runtime was PHP 8.4.16 and had no Composer binary, below the selected PHP 8.5 target; PHP 8.5 dependency and application validation therefore ran in GitHub Actions.
rejected_hypotheses:
  - Use Laravel 12: rejected because Laravel 13 is the current maintained major release with the selected support window.
  - Add Larastan immediately: deferred because the bootstrap contains no substantive domain code; Composer validation, Pint and PHPUnit form the initial baseline.
  - Keep Laravel's default users/auth migration scaffold: rejected to avoid implying a shared account schema before Canary/Auth discovery.
  - Keep a write-capable lock-generation workflow: rejected after composer.lock generation; final CI uses contents: read and composer install.
  - Remove Composer package license metadata: rejected because strict Composer validation failed; restoring the prior proprietary metadata returned the same check to green.
changed_paths:
  - .editorconfig
  - .env.example
  - .gitattributes
  - .gitignore
  - .github/workflows/ci.yml
  - README.md
  - artisan
  - composer.json
  - composer.lock
  - phpunit.xml
  - app/**
  - bootstrap/**
  - config/**
  - database/.gitignore
  - public/**
  - resources/views/**
  - routes/**
  - storage/**
  - tests/**
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/tasks/archive/OTERYN-20260718-laravel-bootstrap.md
validation:
  - command: official Laravel/PHP support verification
    result: PASS
    evidence: Official Laravel 13 release/support documentation and PHP supported-versions documentation checked on 2026-07-18.
  - command: composer update --no-interaction --prefer-dist --no-progress
    result: PASS
    evidence: GitHub Actions run 29659175593 on PHP 8.5 generated composer.lock and completed bootstrap validation.
  - command: composer validate --strict
    result: PASS
    evidence: GitHub Actions run 29659338430.
  - command: composer install --no-interaction --prefer-dist --no-progress
    result: PASS
    evidence: GitHub Actions run 29659338430 installed from committed composer.lock.
  - command: composer format:check
    result: PASS
    evidence: GitHub Actions run 29659338430.
  - command: composer test
    result: PASS
    evidence: GitHub Actions run 29659338430.
blockers: []
security_handoff:
  trust_boundary: Bootstrap only; no Canary, login-server or external authentication trust boundary was implemented.
  authentication_authorization_invariant: No authentication or authorization policy was implemented or changed.
  canary_login_compatibility: Unchanged and intentionally UNKNOWN; no shared schema or session compatibility write path was added.
  rollback_required: false
  secrets_production_config: No secrets or production-only credentials were added; .env.example contains placeholders/local defaults only.
next_action: Create OTERYN-20260718-canary-schema-discovery from the task template and begin evidence-backed read-only Canary schema discovery before implementing any shared-data write path.
```
