# Oteryn Platform Production Topology Evidence Baseline

## Status

Phase 7 discovery baseline — 2026-07-20.

This document separates repository-proven capabilities from actual deployed production facts.

It must not contain production secrets, credentials, private keys, copied `.env` files, database dumps, private IP inventories or personal data.

`UNKNOWN` means the current repository does not prove the deployed value. It is not permission to guess.

## Repository-proven facts

### Application shape

`PROVEN`

- Oteryn Platform is one Laravel modular-monolith deployable.
- The logical target architecture places an edge layer in front of an origin/reverse proxy and Laravel web tier.
- The repository exposes Laravel's `/health` health route.
- The repository does not currently define a separate dependency-aware readiness endpoint.

### Edge and origin

`PROVEN`

- Architecture documents describe Cloudflare as a target/optional defense-in-depth edge.
- Cloudflare must not replace Laravel authentication, authorization, MFA or application rate limiting.
- Origin ingress should eventually be restricted to approved paths.

`UNKNOWN`

- whether a production Cloudflare zone currently proxies the application;
- actual DNS hostname(s), WAF policies, rate limits, Turnstile or Access policies;
- actual TLS termination point;
- actual origin provider/host/reverse proxy;
- whether direct-origin access is currently possible;
- actual ingress/firewall allowlists.

### Web runtime and deployment mechanism

`PROVEN`

- CI validates Composer metadata, installs locked dependencies, runs Composer advisory audit, Pint, PHPStan and the full test suite.
- The current GitHub Actions CI workflow does not deploy the application.
- Target architecture is provider-neutral.

`UNKNOWN`

- actual production hosting provider or orchestration model;
- PHP process model and web server/reverse-proxy implementation;
- number of web instances;
- deployment artifact/image strategy;
- release command sequence;
- migration execution strategy;
- zero-downtime behavior;
- rollback mechanism.

### Platform database

`PROVEN`

- Application configuration supports Platform-owned SQLite and MySQL connections.
- `.env.example` uses SQLite as a local-safe default.
- Platform-owned migrations exist for Identity, CMS, RBAC, audit and provisioning state.

`UNKNOWN`

- actual production Platform database engine and endpoint;
- network isolation and allowed source paths;
- TLS-in-transit configuration;
- credential injection/rotation mechanism;
- database HA/replication topology;
- backup owner, schedule, retention and restore procedure.

### Canary SQL boundaries

`PROVEN`

The repository defines separate configuration surfaces for:

- `canary` — generic read-only SQL access;
- `canary_provisioning` — operation-specific account provisioning;
- `canary_character_create` — operation-specific character creation.

The repository also contains least-privilege grant templates and effective-grant verifiers for approved Phase 5 write principals.

`UNKNOWN`

- actual production endpoints/network paths;
- whether production credentials have been provisioned;
- whether the effective-grant verifiers have been run successfully against production-equivalent credentials;
- production credential rotation/secret-management mechanism.

### Canary runtime Redis

`PROVEN`

- A dedicated `canary_runtime` Redis configuration surface exists.
- The intended boundary is read-only runtime data with a dedicated ACL/user, separate from Platform cache/session credentials.

`UNKNOWN`

- actual production Redis endpoint;
- ACL/user provisioning status;
- network isolation/TLS configuration;
- operational monitoring/failure alerting.

### Platform sessions

`PROVEN`

- Session storage is environment-configurable.
- The repository default is file-backed sessions.
- Secure cookies default to enabled when `APP_ENV=production`, unless explicitly overridden.
- HttpOnly defaults to true and SameSite defaults to `lax`.

`UNKNOWN`

- actual production session backend;
- whether multiple web instances require shared session storage;
- actual production cookie domain and explicit overrides;
- deployed proxy/TLS configuration needed to verify secure-cookie behavior end to end.

### Platform cache

`PROVEN`

- Current configured cache stores are `array`, `file` and `null`.
- `.env.example` uses the `file` cache store.
- No Platform Redis cache store is currently configured in `config/cache.php`.

`UNKNOWN`

- whether production currently runs one or multiple web instances;
- whether shared cache is operationally required;
- any intended production cache service/provider.

### Queue

`PROVEN`

- Current queue configuration implements only the synchronous queue connection.
- Failed-job storage defaults to `null`.
- No background queue worker process is required by the currently configured queue behavior.

`UNKNOWN`

- whether production requirements now need asynchronous work;
- future queue backend/provider;
- worker process supervision, retry policy and failed-job retention.

### Mail

`PROVEN`

- Mail configuration supports SMTP, log and array transports.
- `.env.example` intentionally uses the in-memory `array` transport and states that production must configure a real transport with credentials injected outside Git.
- `production:verify-configuration` rejects non-delivery default transports and invalid/reserved-test sender addresses for production configuration.

`UNKNOWN`

- actual production mail provider;
- sender domain/address;
- SPF/DKIM/DMARC status;
- credential injection/rotation mechanism;
- bounce/delivery monitoring.

### Logging and monitoring

`PROVEN`

- Application logging supports single-file and stderr output plus an optional JSON-to-stderr channel.
- Security-event and administrator-audit application primitives exist.
- Every Laravel-handled request receives a fresh server-generated UUID correlation identifier.
- Normal responses expose that identifier through `X-Request-ID`.
- The application does not trust inbound `X-Request-ID` as the authoritative request correlation identifier.
- Request-completion logging is bounded to request ID, HTTP method, route name, response status and duration.
- Request-completion logging intentionally excludes full URLs, query strings, request bodies, request headers and credential values.
- Architecture requires no credentials/secrets in logs.

`UNKNOWN`

- actual centralized log sink;
- whether the optional JSON stderr channel is selected in production;
- metrics backend;
- alerting/on-call destination;
- retention and access-control policy;
- whether request IDs are propagated through any external reverse proxy or downstream services.

### Backups and restore

`PROVEN`

- Phase 7 roadmap requires backups and a tested restore procedure.
- Test strategy requires an operational backup-restore test before a production-ready claim.

`UNKNOWN`

- backup technology, scope, schedule and retention;
- encryption and access controls;
- off-site/independent-copy policy;
- last successful restore test and measured recovery time/data loss;
- restore owner and runbook.

## Evidence required to move a boundary from UNKNOWN to PROVEN

Evidence must be non-secret and tied to an environment and date where practical.

| Boundary | Minimum acceptable evidence |
|---|---|
| Edge/DNS/TLS | Sanitized provider/export screenshots or configuration summary showing hostname, proxy mode, TLS mode and relevant WAF/rate-limit/Access policy identifiers without secrets |
| Origin ingress | Sanitized firewall/security-group/reverse-proxy rule summary proving intended ingress sources and whether direct-origin access is blocked |
| Web runtime | Deployment manifest/runbook or sanitized platform configuration proving runtime, process model, instance count, release and rollback procedure |
| Platform DB | Sanitized engine/network/backup topology summary; no passwords or connection strings |
| Canary SQL | Sanitized endpoint/network-boundary summary plus successful least-privilege verifier results for each production credential class |
| Runtime Redis | Sanitized endpoint/network/ACL summary plus evidence that the dedicated read-only ACL is provisioned |
| Sessions/cache | Sanitized effective runtime configuration and scaling model proving whether shared state is required |
| Queue | Effective queue configuration plus worker/supervision/retry evidence if asynchronous queues are enabled |
| Mail | Provider/transport and sender-domain readiness summary without credentials |
| Logs/metrics | Sink/retention/alerting configuration summary plus a sample redacted structured request event showing request ID propagation without query/body/credential data |
| Backups | Backup policy plus a dated restore-test record containing scope, result and recovery measurements |

A copied production `.env` file is never acceptable evidence because it exposes secrets and mixes configuration facts with credentials.

## Dependency order for Phase 7

The next Phase 7 work should follow this order unless new evidence changes the dependency graph:

1. **Runtime production-safety guardrails** — repository-owned invariant verification. **COMPLETE**.
2. **Dependency/security scanning and security headers/CSP** — repository-owned merge/runtime hardening. **COMPLETE**.
3. **Application request correlation and structured log shape** — provider-neutral application observability primitive. **IN PROGRESS** until PR #55 merges.
4. **Edge/origin/database exposure review** — requires actual deployment evidence; do not invent firewall or Cloudflare state.
5. **Backup/restore contract and operational test record** — requires the selected Platform database/storage topology.
6. **Bind logging/metrics/alerting to the actual production sink** — requires external deployment evidence; application correlation alone is insufficient.
7. **Queue/cache/mail production setup** — only introduce services that deployed scaling/use-case evidence proves are needed.
8. **Critical production E2E matrix** — run against exact deployed versions after topology, secrets, game-login bridge and operational dependencies are ready.

## Current blocker boundary

The repository alone cannot prove the actual deployed production topology.

Therefore Phase 7 may continue with provider-independent repository hardening and operational documentation, but it must not claim any of the following until external deployment evidence exists:

- Cloudflare/WAF/Access is enabled;
- origin bypass is blocked;
- databases or Redis are privately isolated;
- HSTS is safely deployed for the actual hostname/subdomain policy;
- backups are running or restorable;
- centralized monitoring/alerting exists;
- production mail delivery is functional;
- a production deployment/rollback procedure is operational.
