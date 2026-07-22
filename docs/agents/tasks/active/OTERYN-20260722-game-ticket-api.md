---
task_id: OTERYN-20260722-game-ticket-api
required_reads:
  - AGENTS.md
  - docs/agents/CONTEXT_HANDOFF.md
  - docs/architecture/adr/0009-oteryn-game-authentication-architecture.md
  - docs/architecture/GAME_AUTH_THREAT_MODEL.md
  - docs/architecture/GAME_AUTH_ROLLOUT_PLAN.md
  - docs/contracts/GAME_GATEWAY_IDENTITY_CONTRACT.md
  - docs/contracts/OTCLIENT_GAME_AUTH_CONTRACT.md
search_first:
  - open PRs and active tasks overlapping Game Login Ticket issuance, private redeem, Passport token lifecycle, API routes, or Gateway service authentication
  - current Game Login Ticket domain services and Passport 13.7.5 token models
  - current application routing/bootstrap and rate-limiter patterns
  - MariaDB-capable CI patterns for true concurrent consume proof
optional_reads:
  - docs/architecture/SECURITY_ARCHITECTURE.md
  - docs/architecture/TEST_STRATEGY.md
---

# OTERYN-20260722-game-ticket-api

## Goal

Implement Phase 3 of ADR 0009: public OAuth-scoped Game Login Ticket issuance and private service-authenticated atomic redeem, including deterministic bootstrap OAuth token-family revocation and an executable independent-connection MariaDB proof that exactly one concurrent redeem succeeds.

## Acceptance criteria

- [ ] `POST /api/v1/game-auth/tickets` requires Passport authentication, the `game:ticket` scope, and the exact first-party public native client contract.
- [ ] Ticket issuance accepts no client-authoritative Identity or Canary account ID.
- [ ] Successful issuance returns protocol v1, the opaque ticket once, and bounded expiry information.
- [ ] Ticket issuance and revocation of the current OAuth access token plus associated refresh-token family commit atomically; a successful bootstrap credential cannot mint a second ticket.
- [ ] Concurrent issuance attempts using the same OAuth access token cannot both succeed.
- [ ] `POST /internal/v1/game-auth/tickets/redeem` is outside the public `/api` prefix and requires a dedicated Gateway service credential verified against a configured SHA-256 hash.
- [ ] Missing/invalid service authentication fails closed and raw service credentials are never committed or logged.
- [ ] Private redeem validates protocol version and configured `oteryn-game-gateway` audience without trusting client-supplied account ownership.
- [ ] Successful redeem returns only bounded authorization data required by Gateway; no password, MFA, OAuth or web-session material is exposed.
- [ ] Expired/reused/revoked/wrong-audience/stale-generation/binding-drift tickets fail closed without password or longer-lived-session fallback.
- [ ] A real MariaDB test with independent concurrent connections/processes proves exactly one redeem winner for one ticket.
- [ ] Public/private endpoints have bounded rate limiting and no credential-bearing query-string transport.
- [ ] No Game Gateway runtime, OTClient runtime change, Canary adapter or legacy-login removal is included in this task.
- [ ] Governance, CI, concurrency proof, DB-outage, production-like and acceptance workflows pass on final head.

## Ownership

```yaml
owned_paths:
  - docs/agents/tasks/active/OTERYN-20260722-game-ticket-api.md
  - docs/agents/tasks/archive/OTERYN-20260722-native-oauth-pkce.md
  - docs/agents/tasks/active/OTERYN-20260722-native-oauth-pkce.md
  - app/GameAuth/OAuth/
  - app/GameAuth/Tickets/
  - app/Http/Controllers/GameAuth/
  - app/Http/Middleware/GameAuth/
  - app/Providers/AppServiceProvider.php
  - bootstrap/app.php
  - config/game-auth.php
  - routes/api.php
  - routes/internal.php
  - tests/Feature/GameAuth/
  - .github/workflows/game-auth-ticket-concurrency.yml
modules:
  - auth-identity
  - api
  - security
  - testing
  - canary-integration
dependencies:
  - Phase 2 merged on main as 27fa277c5def0e151d7ee013acef188dbfd6f463
  - Phase 1 Game Login Ticket domain and revocation generation
  - Passport 13.7.5 native public-client OAuth bootstrap
blockers:
  - none
cross_repository_tasks:
  - Game Gateway consumes the private redeem contract in Phase 4
  - OTClient consumes public ticket issuance and Gateway login in its coordinated future task
  - Canary remains untouched in Phase 3
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-22T07:15:00Z
head: 27fa277c5def0e151d7ee013acef188dbfd6f463
branch: task/OTERYN-20260722-game-ticket-api
pr: none
status: investigating
context_routes:
  - auth-identity
  - api
  - security
  - testing
  - canary-integration
owned_paths:
  - docs/agents/tasks/active/OTERYN-20260722-game-ticket-api.md
  - docs/agents/tasks/archive/OTERYN-20260722-native-oauth-pkce.md
  - docs/agents/tasks/active/OTERYN-20260722-native-oauth-pkce.md
  - app/GameAuth/OAuth/
  - app/GameAuth/Tickets/
  - app/Http/Controllers/GameAuth/
  - app/Http/Middleware/GameAuth/
  - app/Providers/AppServiceProvider.php
  - bootstrap/app.php
  - config/game-auth.php
  - routes/api.php
  - routes/internal.php
  - tests/Feature/GameAuth/
  - .github/workflows/game-auth-ticket-concurrency.yml
proven:
  - Phase 2 final head faa47af04be135e8e518f10eda1bae167114b888 passed all required exact-final-head workflows and PR 119 merged to main as 27fa277c5def0e151d7ee013acef188dbfd6f463.
  - Main is identical to 27fa277c5def0e151d7ee013acef188dbfd6f463 at task start.
  - No open Oteryn Platform PR overlaps ticket issue/redeem scope at task start.
  - Existing IssueGameLoginTicket and RedeemGameLoginTicket domain services persist only ticket hashes and use database transactions with lockForUpdate.
  - Standard PHPUnit uses in-memory SQLite, so true concurrent replay proof requires a separate MariaDB-backed validation path.
derived:
  - Public issuance should wrap ticket creation and OAuth access/refresh revocation in one Platform database transaction and re-lock the current access-token row to prevent concurrent double issuance.
  - Private Gateway service authentication should compare SHA-256 of a presented bearer credential against an injected configured hash so Identity does not store the raw service credential.
  - The private redeem route should be loaded under API middleware without Laravel's public `/api` prefix to preserve `/internal/v1/...`.
unknown:
  - Exact production Gateway service credential value and secret-manager delivery remain deployment state and will not be committed.
  - Production private-network/mTLS topology remains a later deployment verification gate; this task implements application-level dedicated service authentication.
conflicts:
  - none
first_failure:
  marker: none
  evidence: none
rejected_hypotheses:
  - Reusing the OAuth access token itself as the Game Login Ticket remains rejected.
  - Persisting the raw Gateway service credential in repository configuration remains rejected.
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260722-game-ticket-api.md
validation:
  - command: not-run
    result: NOT_RUN
    evidence: implementation has not started
blockers:
  - none
next_action: Archive Phase 2 task state, open the draft PR, then implement route boundaries, OAuth-bound issuance, service-authenticated redeem and MariaDB concurrency proof.
```

## Notes

This phase remains Platform-only. Game Gateway runtime starts in Phase 4.
