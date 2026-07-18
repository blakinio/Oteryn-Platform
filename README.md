# Oteryn Platform

Oteryn Platform is the first-party web/application platform for the Oteryn Open Tibia Server ecosystem. It is intended to replace MyAAC as the long-term web platform while Canary remains a separate game-server project.

## Current implementation

The repository now contains the Phase 1 Laravel application foundation:

- Laravel 13 on PHP 8.5;
- server-rendered Blade UI;
- safe local `.env.example` defaults with no committed secrets;
- SQLite as the default local/test database connection;
- `GET /health` using Laravel's health route;
- baseline unit and feature tests;
- Laravel Pint formatting checks;
- lockfile-backed Composer installs;
- GitHub Actions CI.

The bootstrap intentionally does not implement Canary authentication, credential migration, MFA, account/character mutations, payments, or other shared-data write paths.

## Local setup

Requirements: PHP 8.5 with Laravel's required extensions, Composer 2, and PDO SQLite for the default local database.

```sh
cp .env.example .env
composer install
php artisan key:generate
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan serve
```

The application is then available at `http://localhost:8000`.

The health endpoint is available at `GET /health`. It reports only application availability and does not expose environment variables, configuration, version details, or secrets.

## Validation

```sh
composer validate --strict
composer format:check
composer test
```

To apply formatting:

```sh
composer format
```

GitHub Actions installs dependencies from `composer.lock`, validates Composer metadata/lock consistency, checks formatting, and runs the Laravel/PHPUnit test suite on PHP 8.5.

A separate static-analysis dependency is intentionally deferred until substantive application/domain code exists. The bootstrap baseline uses Composer validation, Laravel Pint, PHP parsing/runtime through the test suite, and PHPUnit unit/feature tests.

## Data and integration boundaries

Canary and login-server behavior are external contracts. Do not add shared account/player/authentication write paths until the matching evidence-backed contract task proves the required schema and session semantics.

## Authoritative project documentation

- `AGENTS.md` — mandatory operating rules for agents.
- `docs/agents/PROJECT_STATE.md` — current project phase and next work.
- `docs/agents/REPOSITORY_MAP.md` — repository navigation and ownership map.
- `docs/architecture/SYSTEM_ARCHITECTURE.md` — system boundaries and target topology.
- `docs/architecture/SECURITY_ARCHITECTURE.md` — mandatory security invariants.
- `docs/architecture/DATA_OWNERSHIP.md` — persistent-data ownership rules.
- `docs/contracts/` — Canary/login-server integration contracts.
- `docs/agents/tasks/active/` — active implementation task records.

Repository state, task records, Git and live PR/CI state are authoritative over chat history.
