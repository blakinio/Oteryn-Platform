# Oteryn Platform Module Catalog

This catalog defines intended module responsibilities and dependency boundaries. Modules marked `PLANNED` do not yet exist in source code.

## Status legend

- `PLANNED` — architecture decision only; no implementation proven.
- `DISCOVERY` — contract/research work required before implementation.
- `IMPLEMENTING` — active source implementation exists in an active task.
- `AVAILABLE` — implemented and validated on main.

At architecture bootstrap, all product modules are `PLANNED` or `DISCOVERY`.

| Module | Status | Owns | Must not own |
|---|---|---|---|
| Identity | DISCOVERY | Web authentication policy, credentials lifecycle, sessions, MFA, verification, recovery | Payments, game runtime, arbitrary character mutations |
| Accounts | DISCOVERY | Account profile/settings and account-level business operations | Password verification logic, game runtime |
| Characters | DISCOVERY | Authorized web-triggered character lifecycle operations allowed by contract | Direct undocumented writes to Canary tables |
| PublicGameData | DISCOVERY | Read models/queries for characters, guilds, highscores, online/status | Privileged mutations |
| CMS | PLANNED | News, pages, public managed content | Identity policy, game state |
| Admin | PLANNED | Admin UI, privileged use cases, RBAC integration | Bypassing domain/application invariants |
| Audit | PLANNED | Security/admin audit events and query surface | Secrets, raw credentials, business logic decisions |
| Integration | DISCOVERY | Canary/login-server adapters, schema translation, contract enforcement | Product policy that belongs in domain modules |
| Notifications | PLANNED | Email and asynchronous user notifications | Core auth decisions, payment settlement |
| PlatformAPI | PLANNED | Stable first-party API endpoints and API-specific auth/limits | Duplicating business logic from modules |
| Payments | PLANNED-LATER | Provider adapters, payments, webhook handling, ledger/coins/shop when approved | Identity core, direct dependency from basic account creation/login |

## Identity

### Responsibilities

- login/logout;
- credential hashing/migration strategy;
- session creation, rotation and revocation;
- password reset;
- email verification if enabled by product policy;
- MFA/TOTP;
- remember-me behavior if enabled;
- authentication rate limiting;
- security-sensitive identity audit events.

### Invariants

- one authoritative web identity policy;
- credentials never stored reversibly;
- security-sensitive changes may revoke existing sessions;
- administrator MFA is mandatory before production readiness;
- compatibility with game login remains contract-driven.

## Accounts

### Responsibilities

- account profile and preferences;
- account state visible to the user;
- account lifecycle actions that do not belong to Identity;
- mapping an authenticated identity to the game account contract.

### Invariants

- every account mutation requires authorization;
- account IDs supplied by clients are never trusted as ownership proof;
- bans/status flags shared with Canary are handled only through explicit contracts.

## Characters

### Responsibilities

Potential operations include create, rename, delete/soft-delete and other web account-management operations, but exact allowed operations remain `UNKNOWN` until Canary schema/rules are verified.

### Invariants

- character ownership is checked server-side;
- names and vocations/classes follow verified Canary/product rules;
- concurrency-sensitive changes are transactional;
- no raw undocumented mutation of shared game tables.

## PublicGameData

### Responsibilities

- character profiles;
- guild pages;
- highscores;
- online list;
- server/world status;
- public search.

### Direction

Prefer dedicated query/read-model services. Cache may be introduced after correctness is established. Staleness expectations must be explicit for each view.

## CMS

### Responsibilities

- news/articles;
- managed pages;
- publication state;
- media references when upload security is implemented.

### Security

- output escaped by default;
- rich text sanitized with a maintained allowlist solution;
- uploads require explicit MIME/content/size/storage controls;
- admin authorization required for mutations.

## Admin

### Responsibilities

- administration UI;
- RBAC/policies;
- security-sensitive account actions approved by product policy;
- CMS administration;
- operational visibility that is safe for the assigned role.

### Invariants

- deny by default;
- no implicit "admin can do everything" shortcut;
- privileged actions audited;
- no direct arbitrary PHP/code/plugin execution feature;
- admin access protected by MFA and preferably Cloudflare Access in production.

## Audit

### Responsibilities

- append-oriented security events;
- administrator action audit;
- authentication anomalies and important account security events;
- references to actors/targets without leaking secrets.

Audit storage is not a replacement for infrastructure/application logs.

## Integration

### Responsibilities

- explicit interfaces to Canary/login-server/shared schema;
- mapping/translation between platform domain models and external schema;
- compatibility assertions;
- integration tests/fixtures based on verified contracts.

### Invariants

- external schema assumptions documented in `docs/contracts/**`;
- no hidden shared-table usage outside agreed integration/read boundaries;
- breaking changes require contract updates and cross-repository coordination.

## Notifications

Initial use cases:

- email verification;
- password reset;
- security alerts.

Mail delivery should be asynchronous once queue infrastructure exists, while security tokens remain generated/validated by the owning Identity use case.

## PlatformAPI

Expose API endpoints only when there is a concrete client/use case. Do not create a broad public API prematurely.

API endpoints reuse module services/policies and must not implement a second business-rule path.

## Payments — deferred

No payment implementation belongs in the initial bootstrap.

Future module requirements include:

- provider abstraction;
- signed/authenticated webhook verification;
- idempotency;
- transactional immutable ledger;
- reconciliation;
- refund/chargeback model;
- strict separation from account authentication;
- dedicated threat model and security review.

## Adding a new module

Before adding a module:

1. prove existing modules cannot own the responsibility cleanly;
2. document responsibility and dependencies here;
3. create an ADR for a durable new architectural boundary when material;
4. add task ownership and tests;
5. avoid cyclic dependencies.
