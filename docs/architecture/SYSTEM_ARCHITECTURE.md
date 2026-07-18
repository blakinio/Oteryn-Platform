# Oteryn Platform System Architecture

## Status

Target architecture for the first implementation phase. It is authoritative for direction, but implementation claims remain `UNKNOWN` until source code and tests exist.

## Architectural style

Oteryn Platform starts as a **Laravel modular monolith**.

This means one deployable Laravel application with explicit internal modules and dependency boundaries. The goal is to gain the operational simplicity of one application while avoiding a tightly coupled "everything calls everything" codebase.

Microservices are not the default. A module may be extracted later only when there is proven need for independent scaling, security isolation, lifecycle or ownership.

## System context

```text
                         Internet
                            |
                            v
                    Cloudflare / Edge
              DNS | TLS | WAF | Rate limits
                 Turnstile | Access (admin)
                            |
                            v
                 Reverse proxy / web tier
                            |
                            v
+---------------------------------------------------------+
|                 Oteryn Platform (Laravel)               |
|                                                         |
|  Public Web/CMS     Identity/Auth      Accounts          |
|  Characters         Guilds             Highscores        |
|  Server Status      Admin/RBAC         Audit             |
|  API/Integration    Notifications      Future Payments   |
|                                                         |
+----------------------+----------------------+-------------+
                       |                      |
              explicit contracts      app-owned storage
                       |                      |
        +--------------+--------------+       |
        |                             |       |
        v                             v       v
 login-server / auth path      Canary-compatible DB   cache/queue/mail
        |                             |
        +--------------+--------------+
                       |
                       v
                  Canary server
               (separate repository)
```

## Trust boundaries

### Boundary A — Internet to edge

Cloudflare may provide TLS termination, WAF, bot mitigation, rate limiting and administrative access controls. Origin access should eventually be restricted so public traffic cannot trivially bypass the edge.

Cloudflare is defense in depth only. The application must remain secure if a request reaches Laravel directly.

### Boundary B — Edge to Oteryn Platform

Laravel owns HTTP validation, authentication, authorization, CSRF protection, session security, output escaping and application rate limits.

Never trust client-provided identity, role, account ID, character ownership or privilege claims.

### Boundary C — Oteryn Platform to shared game data

Any table also read or written by Canary/login-server is a cross-repository contract.

Before implementation, every shared write path must define:

- owning component;
- allowed writer(s);
- schema fields used;
- transaction/locking behavior;
- compatibility assumptions;
- rollback or migration strategy.

Read-only queries may be optimized independently but still require a documented schema contract.

### Boundary D — Identity to game login

The final login path is not yet proven. Oteryn Platform must not invent password/session compatibility.

The target principle is one authoritative identity/security policy with an explicit contract to whichever component creates or validates game login sessions.

## Initial modules

See `MODULE_CATALOG.md` for detailed ownership.

High-level modules:

1. `Identity` — credentials, sessions, verification, MFA, recovery.
2. `Accounts` — player account profile and account-level settings.
3. `Characters` — allowed character lifecycle operations.
4. `PublicGameData` — characters, guilds, highscores, online/status read models.
5. `CMS` — news and managed public content.
6. `Admin` — privileged application operations and RBAC.
7. `Audit` — immutable/security-relevant audit events.
8. `Integration` — Canary/login-server adapters and contract enforcement.
9. `Notifications` — mail and asynchronous user notifications.
10. `Payments` — deferred future module; no initial dependency from core auth/account flows.

## Dependency rules

- HTTP controllers depend on application/domain services, not directly on arbitrary shared tables.
- Security-critical authorization lives in policies/gates/domain rules, not only in UI visibility.
- `Identity` must not depend on `Payments`.
- `Accounts` may depend on `Identity` identity references, but must not implement authentication itself.
- `Characters` may mutate Canary-owned/shared data only through a documented integration boundary.
- `PublicGameData` should prefer read-only models/query services and must not become a hidden mutation path.
- `Admin` invokes the same domain/application services as normal flows with stronger authorization; it must not bypass invariants with raw SQL.
- `Integration` contains compatibility translation, not core business policy.

## Data access strategy

Two categories are expected:

### Application-owned data

Examples:

- web sessions when not using framework default storage;
- MFA metadata;
- password recovery/verification metadata where appropriate;
- CMS content;
- RBAC metadata;
- audit records;
- future platform-specific preferences.

Oteryn Platform owns migrations and lifecycle for these tables.

### Shared/Canary-compatible data

Examples may include accounts, players, guilds and game-specific state, but exact ownership is **UNKNOWN** until contract discovery.

No agent may assume shared table names or columns from MyAAC conventions without verifying the actual Oteryn Canary schema.

## Authentication direction

Target capabilities:

- framework-backed secure password hashing;
- password migration strategy compatible with the actual game login path;
- email verification where product policy requires it;
- MFA/TOTP for security-sensitive users, mandatory for administrators;
- secure password reset;
- server-side authorization;
- session revocation after security-sensitive changes;
- rate limiting and abuse controls;
- auditable privileged actions.

The exact compatibility model with login-server/Canary is intentionally unresolved until evidence is collected.

## Frontend direction

Initial preference: Laravel Blade/server-rendered pages.

Rationale:

- fewer moving parts during first release;
- one application and one authorization model;
- no requirement to design a public SPA API before it is needed;
- easier replacement of MyAAC with a coherent first-party platform.

A separate React/Vue frontend may be introduced later through an ADR if product needs justify it.

## Deployment direction

Logical target, not a provider-specific implementation:

```text
Cloudflare
   |
Origin firewall / reverse proxy
   |
Laravel web instances
   |---- queue workers (when introduced)
   |---- cache/session service (when introduced)
   |---- mail provider
   |
Database reachable only from approved private/application paths
   |
Canary / login-server on explicitly allowed network paths
```

Production database, cache and game services should not be publicly exposed unless technically unavoidable and explicitly secured.

## Observability direction

The platform should eventually provide:

- structured application logs;
- request/error correlation IDs;
- authentication/security event logs;
- admin audit trail;
- health/readiness endpoints suitable for infrastructure monitoring;
- metrics for login failure, rate limits, queue failures and critical integrations.

Do not log passwords, session tokens, MFA secrets, reset tokens or other credentials.

## Non-goals for initial release

- payment processing;
- marketplace/auction systems;
- microservice decomposition;
- complex real-time frontend architecture;
- replacing Canary gameplay/runtime responsibilities;
- silent modifications to Canary or login-server repositories.

## Required discovery before coding shared auth/data

Before implementing Identity/Accounts/Characters against real Canary data, create bounded tasks to prove:

1. actual Oteryn Canary account/player/guild schema;
2. actual password hashing expectations;
3. actual login-server and/or Canary session authentication path;
4. which component creates/revokes game sessions;
5. required account flags/status fields;
6. transaction/concurrency expectations for character/account mutations;
7. single-world versus multi-world requirements.

Until proven, each item remains `UNKNOWN`.
