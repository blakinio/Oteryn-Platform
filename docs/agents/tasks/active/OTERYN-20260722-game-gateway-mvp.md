---
task_id: OTERYN-20260722-game-gateway-mvp
required_reads:
  - AGENTS.md
  - docs/agents/CONTEXT_HANDOFF.md
  - docs/architecture/adr/0009-oteryn-game-authentication-architecture.md
  - docs/architecture/GAME_AUTH_ROLLOUT_PLAN.md
  - docs/architecture/GAME_AUTH_THREAT_MODEL.md
  - docs/contracts/GAME_GATEWAY_IDENTITY_CONTRACT.md
  - docs/contracts/GAME_SESSION_CANARY_CONTRACT.md
  - docs/contracts/WORLD_REGISTRY_CONTRACT.md
search_first:
  - open PRs and active tasks overlapping Game Gateway, login context, World Registry, character-list reads, or session issuance
  - current main and merged Phase 3 ticket/redeem API
  - existing Canary read-only repository and database privilege boundary
  - existing World Registry implementation
optional_reads:
  - docs/architecture/SYSTEM_ARCHITECTURE.md
  - docs/architecture/DATA_OWNERSHIP.md
  - docs/architecture/TEST_STRATEGY.md
---

# OTERYN-20260722-game-gateway-mvp

## Goal

Implement Phase 4 of ADR 0009 as a separately deployable Go Game Gateway plus the minimum narrow Platform internal login-context API it requires. The Gateway must redeem one-time Game Login Tickets through Identity, obtain authorized single-world-ready routing and account-scoped characters through a service-authenticated Platform boundary, invoke an abstract Game Session issuer, and return a versioned login response. No direct Platform/Identity/Canary database credentials are granted to the Gateway.

## Acceptance criteria

- [ ] `services/game-gateway` is an independently buildable/testable Go service with no dependency on Laravel runtime internals.
- [ ] Gateway exposes `GET /health`, `GET /ready`, `GET /version`, and `POST /v1/login`.
- [ ] `POST /v1/login` accepts protocol v1 plus one Game Login Ticket and never accepts password/OAuth/account-ownership fields.
- [ ] Gateway redeems the ticket through `POST /internal/v1/game-auth/tickets/redeem` using the dedicated Platform service credential.
- [ ] Gateway obtains World Registry and account-scoped active character data only through a narrow service-authenticated Platform login-context API; it has no Platform or Canary DB credentials.
- [ ] Platform login context uses the existing Platform-owned `WorldRegistry` boundary and existing SELECT-only Canary connection.
- [ ] The single-world MVP succeeds only when exactly one authorized online/login-enabled world exists; zero or ambiguous multiworld state fails closed rather than guessing character/world ownership.
- [ ] Character list is filtered by exact redeemed `canary_account_id` and `deletion=0`; client input cannot change ownership.
- [ ] Gateway creates a server-generated login-attempt identifier and invokes a `SessionIssuer` abstraction; the concrete HTTP session-issuer adapter is configurable and tested against contract fixtures but no Canary implementation is claimed in Phase 4.
- [ ] Dependency failures (Identity redeem, login context, session issuer) fail closed with bounded public errors and no password/legacy fallback.
- [ ] Gateway never logs Game Login Tickets, OAuth credentials, passwords, service credentials, or Game Session secrets.
- [ ] Structured logs contain only bounded request/correlation identifiers, route/status/duration and safe failure categories.
- [ ] Gateway configuration is environment-driven; repository contains no service secrets or invented production world route.
- [ ] Contract tests cover success and dependency outage/error mappings using `httptest` fake dependencies.
- [ ] A durable Gateway CI workflow runs `go test`, `go vet`, and `go build` independently.
- [ ] No OTClient or Canary runtime write occurs in this task; no full game E2E is claimed before Phase 5/6.
- [ ] Platform governance/CI/DB-outage/production-like/acceptance plus Gateway CI pass on final head.

## Ownership

```yaml
owned_paths:
  - docs/agents/tasks/active/OTERYN-20260722-game-gateway-mvp.md
  - docs/agents/tasks/archive/OTERYN-20260722-game-ticket-api.md
  - docs/agents/tasks/active/OTERYN-20260722-game-ticket-api.md
  - services/game-gateway/
  - .github/workflows/game-gateway-ci.yml
  - app/GameAuth/Context/
  - app/Http/Controllers/GameAuth/GameLoginContextController.php
  - app/PublicGameData/CanaryGameDataRepository.php
  - routes/internal.php
  - tests/Feature/GameAuth/GameLoginContextApiTest.php
modules:
  - architecture
  - api
  - security
  - canary-integration
  - testing
dependencies:
  - Phase 3 merged to main as cab00c140ce200e3cd51b7eafe2c1659842c2b90
  - service-authenticated private ticket redeem API
  - Platform-owned DatabaseWorldRegistry
  - existing SELECT-only Canary database connection
blockers:
  - none
cross_repository_tasks:
  - OTClient implementation remains Phase 5 in blakinio/otclient
  - concrete Game Session/Canary adapter remains Phase 6 and may require blakinio/canary changes
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-22T07:55:00Z
head: cab00c140ce200e3cd51b7eafe2c1659842c2b90
branch: task/OTERYN-20260722-game-gateway-mvp
pr: none
status: investigating
context_routes:
  - architecture
  - api
  - security
  - canary-integration
  - testing
owned_paths:
  - docs/agents/tasks/active/OTERYN-20260722-game-gateway-mvp.md
  - docs/agents/tasks/archive/OTERYN-20260722-game-ticket-api.md
  - docs/agents/tasks/active/OTERYN-20260722-game-ticket-api.md
  - services/game-gateway/
  - .github/workflows/game-gateway-ci.yml
  - app/GameAuth/Context/
  - app/Http/Controllers/GameAuth/GameLoginContextController.php
  - app/PublicGameData/CanaryGameDataRepository.php
  - routes/internal.php
  - tests/Feature/GameAuth/GameLoginContextApiTest.php
proven:
  - Phase 3 PR 121 passed all exact-final-head gates and merged to main as cab00c140ce200e3cd51b7eafe2c1659842c2b90.
  - No open Oteryn Platform PR overlaps Game Gateway scope at task start.
  - The merged private ticket redeem endpoint returns exact canary_account_id after atomic one-time consume and requires the dedicated Gateway service credential.
  - Platform already owns a database-backed WorldRegistry abstraction.
  - Platform already has a separate `canary` connection whose deployment contract is SELECT-only and a query-builder-only CanaryGameDataRepository.
  - Existing CanaryGameDataRepository currently supports public character/guild/highscore/channel/online reads but not account-scoped character-list reads.
derived:
  - The Gateway can remain database-credential-free by calling a new narrow Platform login-context endpoint that composes WorldRegistry plus account-scoped character reads.
  - True multiworld character association is not yet proven; the Phase 4 endpoint should therefore support only the explicit single-world MVP and fail closed when world count is not exactly one.
  - The Game Session concrete adapter remains intentionally deferred; Gateway should depend on a SessionIssuer interface and an HTTP adapter contract so Phase 6 can supply the implementation without changing the public Gateway API.
unknown:
  - Exact production Gateway listen address/base URL and service credentials remain deployment state and are not committed.
  - Exact Phase 6 Game Session issuer endpoint/service identity and Canary semantics remain unresolved.
conflicts:
  - none
first_failure:
  marker: none
  evidence: none
rejected_hypotheses:
  - Giving the Gateway direct generic Platform or Canary database credentials is rejected.
  - Guessing character-to-world ownership when more than one world exists is rejected.
  - Claiming a live Canary Game Session implementation in Phase 4 is rejected; only the Gateway interface/HTTP adapter contract will be implemented.
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260722-game-gateway-mvp.md
validation:
  - command: not-run
    result: NOT_RUN
    evidence: implementation has not started
blockers:
  - none
next_action: Archive the completed Phase 3 task, open the draft PR, then implement the narrow Platform login-context endpoint and standalone Go Gateway with contract tests and independent CI.
```

## Notes

Phase 4 delivers the separately deployable Gateway runtime and its orchestration boundary, but deliberately does not claim world-entry E2E until OTClient and a concrete Game Session/Canary adapter are implemented.
