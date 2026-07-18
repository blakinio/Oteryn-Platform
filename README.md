# Oteryn Platform

Oteryn Platform is the first-party web/application platform for the Oteryn Open Tibia Server ecosystem. It is intended to replace MyAAC as the long-term web platform while Canary remains a separate game-server project.

## Current implementation

The repository contains the Laravel application foundation plus initial evidence-backed read-only PublicGameData surfaces:

- Laravel 13 on PHP 8.5;
- server-rendered Blade UI;
- safe local `.env.example` defaults with no committed secrets;
- SQLite as the default local/test database connection;
- dedicated Canary read connection for public game-data queries;
- `GET /health` using Laravel's health route;
- public level highscores, character profiles, guild details and configured channel metadata;
- baseline unit/feature/integration tests;
- Laravel Pint formatting checks;
- lockfile-backed Composer installs;
- GitHub Actions CI.

Authentication, credential migration, MFA, account/character mutations, payments and all direct Canary/shared-data writes remain out of scope until their contracts and implementation tasks are explicitly approved.

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

## Read-only Canary game data

The public read routes use the dedicated `canary` database connection:

- `GET /highscores`
- `GET /characters/{name}`
- `GET /guilds/{name}`
- `GET /servers`

Configure `CANARY_DB_*` values in the local `.env`. The database user must be provisioned outside this repository with least-privilege `SELECT` access only to the required Canary tables. Do not reuse Canary's server credential or a database root/admin account.

The current public read model intentionally does **not** implement a cluster-wide online character list or claim live per-channel availability. The authoritative source/freshness contract for that data remains unresolved.

No application caching is used for these initial game-data reads because acceptable staleness semantics have not yet been defined.

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

The PublicGameData integration tests seed an isolated SQLite Canary connection and enable SQLite `query_only` mode before requests, so accidental writes from the read endpoints fail validation.

## Data and integration boundaries

Canary and login-server behavior are external contracts. Shared account/player/authentication mutations remain blocked unless a future operation-level contract explicitly approves them.

Public game-data queries use explicit selected columns and exclude deleted characters where required by `docs/contracts/CANARY_DATA_CONTRACT.md`.

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
