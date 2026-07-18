# Oteryn Platform

Oteryn Platform is the first-party web/application platform for Oteryn. The application starts as a Laravel modular monolith with server-rendered Blade views.

## Runtime baseline

- Laravel 13
- PHP 8.5
- Composer 2
- Blade for the initial UI
- SQLite for the default local/test database connection

The bootstrap intentionally does not implement Canary authentication, credential migration, MFA, character mutations, payments, or other shared-data write paths.

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

GitHub Actions runs dependency installation from `composer.lock`, Composer validation, Pint in check mode, and the PHPUnit/Laravel test suite on PHP 8.5.

A separate static-analysis dependency is intentionally deferred until substantive application/domain code exists; the bootstrap baseline uses Composer validation, Laravel Pint, PHP's runtime/parser through the test suite, and PHPUnit feature/unit tests.

## Data and integration boundaries

Canary and login-server behavior are external contracts. Do not add shared account/player/authentication write paths until the matching evidence-backed contract task proves the required schema and session semantics.
