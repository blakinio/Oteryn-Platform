---
task_id: OTERYN-20260722-game-ticket-hardening
required_reads:
  - AGENTS.md
  - docs/agents/CONTEXT_HANDOFF.md
  - docs/architecture/GAME_AUTH_THREAT_MODEL.md
  - docs/contracts/OTCLIENT_GAME_AUTH_CONTRACT.md
  - docs/contracts/GAME_GATEWAY_IDENTITY_CONTRACT.md
  - docs/agents/tasks/archive/OTERYN-20260722-game-ticket-api.md
  - docs/agents/tasks/archive/OTERYN-20260722-game-gateway-mvp.md
search_first:
  - open PRs and active tasks overlapping game-auth service credentials, ticket APIs, login-context or Gateway HTTP boundaries
  - merged Phase 3 PR 121 and Phase 4 PR 122 workflow evidence
  - current private route middleware order and named rate limiters
optional_reads:
  - docs/architecture/adr/0009-oteryn-game-authentication-architecture.md
---

# OTERYN-20260722-game-ticket-hardening

## Goal

Harden the merged Phase 3 and Phase 4 game-auth HTTP boundaries without changing ticket, login-context or Gateway orchestration semantics. Rate-limit unauthorized private requests before service authentication, support overlapping Gateway credential hashes for no-downtime rotation, prevent credential-bearing responses from being cached, document deployment variables, and reconcile contract implementation status.

## Acceptance criteria

- [ ] Private ticket redeem and login-context throttling execute before Gateway credential validation, including invalid or missing credentials.
- [ ] Gateway service authentication accepts one or more externally injected SHA-256 hashes and fails closed on empty or malformed configuration.
- [ ] The previous single-hash configuration remains accepted during migration.
- [ ] Credential rotation can overlap old and new hashes without committing plaintext credentials.
- [ ] Public ticket issuance plus private redeem and login-context responses carry `Cache-Control: no-store, private` and `Pragma: no-cache`, including framework authentication, validation and throttle failures.
- [ ] `.env.example` documents OAuth, ticket and Gateway hash-list deployment variables without secrets.
- [ ] Focused tests cover rotation, legacy compatibility, invalid-attempt throttling and no-store headers across all three HTTP boundaries.
- [ ] Contracts distinguish implemented Platform and Gateway MVP components from the unimplemented OTClient and production Canary session adapter.
- [ ] No ticket lifecycle, concurrency, Gateway routing, World Registry or SessionIssuer semantics change.
- [ ] Required repository workflows pass on the final clean checkpoint head.

## Ownership

```yaml
owned_paths:
  - docs/agents/tasks/active/OTERYN-20260722-game-ticket-hardening.md
  - docs/contracts/OTCLIENT_GAME_AUTH_CONTRACT.md
  - docs/contracts/GAME_GATEWAY_IDENTITY_CONTRACT.md
  - app/Http/Middleware/GameAuth/PreventCredentialResponseCaching.php
  - app/Http/Middleware/GameAuth/RequireGatewayServiceCredential.php
  - bootstrap/app.php
  - routes/internal.php
  - config/game-auth.php
  - .env.example
  - tests/Feature/GameAuth/GameAuthHttpHardeningTest.php
modules:
  - auth-identity
  - api
  - security
  - game-gateway
  - testing
dependencies:
  - Phase 3 merged as cab00c140ce200e3cd51b7eafe2c1659842c2b90
  - Phase 4 Gateway MVP merged as 8006534108d835474dadd208b0ec934e4a12528b
blockers:
  - none
cross_repository_tasks:
  - OTClient and Canary runtime changes remain out of scope
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-22T08:48:00Z
head: 8006534108d835474dadd208b0ec934e4a12528b
branch: task/OTERYN-20260722-game-ticket-hardening
pr: 123
status: implementing
context_routes:
  - auth-identity
  - api
  - security
  - game-gateway
  - testing
owned_paths:
  - docs/agents/tasks/active/OTERYN-20260722-game-ticket-hardening.md
  - docs/contracts/OTCLIENT_GAME_AUTH_CONTRACT.md
  - docs/contracts/GAME_GATEWAY_IDENTITY_CONTRACT.md
  - app/Http/Middleware/GameAuth/PreventCredentialResponseCaching.php
  - app/Http/Middleware/GameAuth/RequireGatewayServiceCredential.php
  - bootstrap/app.php
  - routes/internal.php
  - config/game-auth.php
  - .env.example
  - tests/Feature/GameAuth/GameAuthHttpHardeningTest.php
proven:
  - Phase 3 PR 121 and Phase 4 PR 122 are merged and passed their required workflows.
  - Current main exposes public ticket issuance, private atomic redeem and private login-context resolution.
  - The merged private routes validate the Gateway service credential before their named throttle middleware.
  - The merged service authentication accepts one configured SHA-256 hash only.
  - The rebased hardening branch places both private rate limiters before service authentication, supports plural and legacy hash configuration and applies a shared path-scoped cache policy to all three game-auth HTTP boundaries.
derived:
  - Phase 4 introduced another service-authenticated endpoint, so the same hardening policy must cover login-context as well as ticket redeem.
  - Laravel exception rendering requires a response-finalization hook in addition to outer middleware for deterministic no-store headers.
unknown:
  - Exact production Gateway credentials and secret-manager delivery remain deployment state.
  - Post-rebase static-analysis and runtime results are not yet known.
conflicts:
  - PR 123 was rebased onto merged Phase 4 because its earlier base became stale.
first_failure:
  marker: none
  evidence: post-rebase validation has not started.
rejected_hypotheses:
  - Merging the stale pre-Phase-4 hardening branch is rejected because it would remove the login-context route and stale the contracts.
  - Changing ticket, Gateway routing or SessionIssuer semantics in this task is rejected.
changed_paths:
  - .env.example
  - app/Http/Middleware/GameAuth/PreventCredentialResponseCaching.php
  - app/Http/Middleware/GameAuth/RequireGatewayServiceCredential.php
  - bootstrap/app.php
  - config/game-auth.php
  - docs/agents/tasks/active/OTERYN-20260722-game-ticket-hardening.md
  - routes/internal.php
  - tests/Feature/GameAuth/GameAuthHttpHardeningTest.php
validation:
  - command: post-rebase validation
    result: NOT_RUN
    evidence: implementation is being reconstructed on current main.
blockers:
  - none
next_action: Reconcile contract status with the merged Gateway MVP, then run governance, CI, concurrency, outage, production-like and acceptance validation.
```
