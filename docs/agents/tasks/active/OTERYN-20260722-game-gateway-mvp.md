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

- [x] `services/game-gateway` is an independently buildable/testable Go service with no dependency on Laravel runtime internals.
- [x] Gateway exposes `GET /health`, `GET /ready`, `GET /version`, and `POST /v1/login`.
- [x] `POST /v1/login` accepts protocol v1 plus one Game Login Ticket and rejects unknown/password/account-ownership fields, query-string credentials, trailing JSON and oversized bodies.
- [x] Gateway redeems the ticket through `POST /internal/v1/game-auth/tickets/redeem` using the dedicated Platform service credential.
- [x] Gateway obtains World Registry and account-scoped active character data only through a narrow service-authenticated Platform login-context API; it has no Platform or Canary DB credentials.
- [x] Platform login context uses the existing Platform-owned World Registry behavior and existing SELECT-only Canary connection.
- [x] The single-world MVP succeeds only when exactly one authorized online/login-enabled world exists; zero or ambiguous multiworld state fails closed rather than guessing character/world ownership.
- [x] Character list is filtered by exact redeemed `canary_account_id` and `deletion=0`; client input cannot change ownership.
- [x] Gateway creates a server-generated login-attempt identifier and invokes a `SessionIssuer` abstraction; the concrete HTTP session-issuer adapter is configurable and tested against contract fixtures but no Canary implementation is claimed in Phase 4.
- [x] Dependency failures (Identity redeem, login context, session issuer) fail closed with bounded public errors and no password/legacy fallback.
- [x] Gateway structured logs contain only bounded request/correlation identifiers, method, path, status and duration; regression tests prove ticket/session credential material is absent.
- [x] Gateway configuration is environment-driven; repository contains no service secrets or invented production world route.
- [x] Contract tests cover orchestration success, invalid ticket, multiworld ambiguity, character/world mismatch, dependency outage, Platform HTTP contract, Session Issuer HTTP contract, strict config and public HTTP behavior.
- [x] A durable Gateway CI workflow runs canonical formatting, `go test ./...`, `go vet ./...`, and standalone binary build independently.
- [x] A static standalone container build definition is present under `services/game-gateway`.
- [x] No OTClient or Canary runtime write occurs in this task; no full game E2E is claimed before Phase 5/6.
- [x] Platform governance/CI/DB-outage/production-like/acceptance, existing ticket concurrency, and Gateway CI pass on the validated implementation head.

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
updated_at: 2026-07-22T08:25:00Z
head: a19eb862c7e0630cf653b71dd26f91a921cea596
branch: task/OTERYN-20260722-game-gateway-mvp
pr: 122
status: ready
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
  - No open Oteryn Platform PR overlapped Game Gateway scope at task start.
  - The Gateway has its own Go module under services/game-gateway and imports only the Go standard library plus its own internal packages.
  - The Gateway has no SQL driver, Laravel dependency, Platform DB credential, or Canary DB credential.
  - Public Gateway routes are health, readiness, version and protocol-v1 login only.
  - Gateway login redeems the one-time ticket through the Phase 3 private Platform endpoint and then obtains account-scoped login context through a second narrow service-authenticated Platform endpoint.
  - Platform login context composes DatabaseWorldRegistry with CanaryGameDataRepository::activeCharactersForAccount using the existing separate Canary connection; the query selects id/name/level/vocation, filters exact account_id and deletion=0, and does not expose account_id.
  - Platform login context deliberately requires exactly one eligible world; zero worlds returns unavailable and more than one returns world_mapping_ambiguous.
  - Gateway validates that every returned character belongs to the single returned world before requesting a Game Session.
  - Gateway SessionIssuer is an interface with a configurable internal HTTP adapter; no concrete Canary/session persistence implementation is claimed in Phase 4.
  - Gateway public login uses a 4 KiB hard request-body limit, rejects query strings and unknown JSON fields, and never accepts password/OAuth/account ownership fields.
  - Gateway structured request logging omits bodies and headers; test coverage proves Game Login Ticket and Game Session credential values do not appear in logs.
  - Gateway configuration requires injected Platform and Session service credentials and validated dependency base URLs; credentials embedded in URLs are rejected.
  - The repository includes a static standalone container build definition that runs as an unprivileged numeric user and includes CA certificates for outbound TLS.
  - Game Gateway CI run 29903192333 succeeded on implementation head a19eb862c7e0630cf653b71dd26f91a921cea596 after canonical gofmt was applied, proving gofmt check, go test, go vet and standalone go build.
  - Agent Governance run 29903192443 succeeded on a19eb862c7e0630cf653b71dd26f91a921cea596.
  - CI run 29903192032 succeeded on a19eb862c7e0630cf653b71dd26f91a921cea596.
  - Platform DB Outage Validation run 29903192504 succeeded on a19eb862c7e0630cf653b71dd26f91a921cea596.
  - Phase 7 Production-Like Validation run 29903192347 succeeded on a19eb862c7e0630cf653b71dd26f91a921cea596.
  - Acceptance E2E and Visual UX run 29903192240 succeeded on a19eb862c7e0630cf653b71dd26f91a921cea596.
  - Existing Game Auth Ticket Concurrency run 29903192663 succeeded on a19eb862c7e0630cf653b71dd26f91a921cea596.
derived:
  - The Gateway remains horizontally deployable from an authentication-state perspective because authoritative ticket/session state is not stored in process memory.
  - Phase 4 can provide a stable OTClient-facing login response contract before the concrete Canary Game Session adapter exists; readiness correctly fails when the configured Session Issuer dependency is unavailable.
  - True multiworld remains blocked on persistent character-to-world ownership and world-scoped session enforcement, so failing closed at more than one world preserves the Phase 0 contract.
unknown:
  - Exact production Gateway listen address/base URL and service credential delivery remain deployment state and are intentionally not committed.
  - Exact Phase 6 Game Session issuer implementation, service identity, TTL, replay/revocation semantics and Canary world-entry mapping remain unresolved.
  - Full browser-to-player-world-entry E2E remains unproven until Phase 5 OTClient and Phase 6 Canary/session work are complete.
conflicts:
  - none
first_failure:
  marker: initial Game Gateway CI formatting check
  evidence: manually authored Go sources required canonical gofmt; a branch-only formatting bootstrap applied gofmt to services/game-gateway and the workflow was then restored to strict read-only check mode before implementation validation
rejected_hypotheses:
  - Giving the Gateway direct generic Platform or Canary database credentials is rejected.
  - Guessing character-to-world ownership when more than one world exists is rejected.
  - Claiming a live Canary Game Session implementation in Phase 4 is rejected; only the Gateway interface/HTTP adapter contract is implemented.
  - Logging dependency request bodies or headers for observability is rejected because they contain bearer credentials or tickets/session secrets.
changed_paths:
  - .github/workflows/game-gateway-ci.yml
  - app/GameAuth/Context/
  - app/Http/Controllers/GameAuth/GameLoginContextController.php
  - app/PublicGameData/CanaryGameDataRepository.php
  - docs/agents/tasks/active/OTERYN-20260722-game-gateway-mvp.md
  - docs/agents/tasks/archive/OTERYN-20260722-game-ticket-api.md
  - routes/internal.php
  - services/game-gateway/
  - tests/Feature/GameAuth/GameLoginContextApiTest.php
validation:
  - command: GitHub changed-file inventory for PR 122
    result: PASS
    evidence: 26 changed files, all within declared Phase 4 owned paths; no OTClient or Canary repository writes
  - command: GitHub Actions Game Gateway CI run 29903192333
    result: PASS
    evidence: canonical formatting, go test ./..., go vet ./..., and standalone binary build succeeded on a19eb862c7e0630cf653b71dd26f91a921cea596
  - command: GitHub Actions Agent Governance run 29903192443
    result: PASS
    evidence: governance and checkpoint ownership validation succeeded on a19eb862c7e0630cf653b71dd26f91a921cea596
  - command: GitHub Actions CI run 29903192032
    result: PASS
    evidence: Composer validation/audit, Pint, PHPStan and full Platform tests succeeded on a19eb862c7e0630cf653b71dd26f91a921cea596
  - command: GitHub Actions Platform DB Outage Validation run 29903192504
    result: PASS
    evidence: fail-closed Platform database outage/recovery validation succeeded on a19eb862c7e0630cf653b71dd26f91a921cea596
  - command: GitHub Actions Phase 7 Production-Like Validation run 29903192347
    result: PASS
    evidence: production-like migration, privilege, dependency, critical regression, runtime, backup/restore and upgrade/rollback validation succeeded on a19eb862c7e0630cf653b71dd26f91a921cea596
  - command: GitHub Actions Acceptance E2E and Visual UX run 29903192240
    result: PASS
    evidence: required browser portability, responsive, resilience, accessibility and durable acceptance evidence succeeded on a19eb862c7e0630cf653b71dd26f91a921cea596
  - command: GitHub Actions Game Auth Ticket Concurrency run 29903192663
    result: PASS
    evidence: existing independent-process MariaDB exactly-one ticket issue/redeem proof remained green on a19eb862c7e0630cf653b71dd26f91a921cea596
blockers:
  - none
next_action: Verify the same required workflows pass on the final checkpoint commit, then merge PR 122 if review and merge gates remain satisfied.
```

## Notes

Phase 4 delivers the separately deployable Gateway runtime and its orchestration boundary, but deliberately does not claim world-entry E2E until OTClient and a concrete Game Session/Canary adapter are implemented.
