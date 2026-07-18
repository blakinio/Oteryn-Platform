# Oteryn Platform Data Ownership

## Purpose

Prevent accidental coupling and unsafe shared-database writes between Oteryn Platform, login-server and Canary.

## Rule

Every persistent data set has exactly one documented **primary owner**. Other components may read or write only through an explicitly documented contract.

At bootstrap, exact Canary table ownership is not yet proven. Do not infer ownership from MyAAC or generic TFS conventions.

## Ownership categories

### Platform-owned

Oteryn Platform controls schema, migrations and lifecycle.

Expected examples:

- CMS/news content;
- platform roles/permissions if not mapped to a shared account field;
- audit events;
- MFA metadata;
- platform-specific user preferences;
- platform notification metadata;
- future payment ledger and provider records.

### Canary-owned

Canary controls schema/semantics. Oteryn Platform is read-only unless a write contract explicitly permits operations.

Potential examples may include game runtime/world state and gameplay-owned player data. Exact tables must be discovered from the actual Oteryn Canary repository/database schema.

### Shared-contract data

Both components require access, but one component remains the semantic owner and the contract defines allowed operations.

Likely candidates requiring discovery:

- accounts;
- players/characters;
- guild membership;
- bans/account status;
- game login sessions/tokens.

No candidate is confirmed solely by this document.

## Write policy

For every shared write path document:

1. primary owner;
2. caller/component;
3. exact table/model/fields;
4. validation rules;
5. authorization rule;
6. transaction boundary;
7. concurrency/locking behavior;
8. side effects/cache/session invalidation;
9. compatibility/version assumptions;
10. rollback/migration implications.

## Read policy

Public read features such as highscores or character profiles may query Canary-compatible data through dedicated read/query services.

Rules:

- use explicit selected columns rather than depending on arbitrary whole-row shapes when practical;
- handle missing/deprecated fields deliberately;
- document freshness/cache expectations;
- avoid N+1/mass-query patterns;
- do not accidentally turn read models into mutation-capable domain models.

## Database credentials

Target production direction:

- platform migration owner: allowed only for platform-owned schema and approved migrations;
- application runtime credential: least privileges required by the platform;
- read-only game-data credential where architecture permits separation;
- Canary uses its own credential;
- no shared root/admin database credential in application configuration.

Exact credential split depends on final deployment/database topology.

## Migrations

### Platform-owned schema

Laravel migrations are authoritative.

### Shared/Canary schema

Oteryn Platform does not silently migrate Canary-owned tables.

A required shared schema change must:

- be documented in `docs/contracts/**`;
- identify the owning repository;
- define compatibility order;
- define rollback/backward compatibility;
- coordinate both repositories when atomic behavior is required.

## Identity data special rule

Credentials and game-login compatibility require explicit ownership discovery before implementation.

Questions that must be answered:

- Which table/system is authoritative for account credentials?
- Which hashing formats are accepted by login-server/Canary?
- Can the platform migrate hashes transparently?
- Which component creates game sessions/tokens?
- Which component revokes them?
- What happens to active sessions after password/MFA/account-state changes?

Until answered, agents must not implement a speculative credential migration.

## Character data special rule

Before web character creation/deletion/rename, verify:

- required columns/defaults;
- name uniqueness and normalization rules;
- vocation/sex/town/world rules;
- starter state creation requirements;
- deletion semantics;
- online-character restrictions;
- foreign keys and dependent rows;
- Canary caches/runtime assumptions.

## Future financial data

Future coins/payment balances are platform-owned business data unless a later ADR says otherwise.

Use an immutable/append-oriented ledger as the source of financial history. A cached balance may exist, but mutation must remain transactional and auditable.

Do not reuse a generic mutable `premium_points` field as the sole financial source of truth without a deliberate ADR and threat/concurrency analysis.

## Data classification

### Secret

Passwords, password hashes where exposure increases attack value, session tokens, reset tokens, MFA secrets, private keys, provider secrets.

### Sensitive personal/security data

Email addresses, IP/security history, account security events.

### Internal operational

Admin audit records, integration errors, deployment metadata.

### Public game data

Character/guild/highscore data explicitly intended for public display.

Classification affects logging, access, retention and export behavior.
