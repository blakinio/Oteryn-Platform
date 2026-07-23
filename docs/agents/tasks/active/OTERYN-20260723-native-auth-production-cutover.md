---
task_id: OTERYN-20260723-native-auth-production-cutover
required_reads:
  - AGENTS.md
  - docs/agents/CONTEXT_HANDOFF.md
  - docs/architecture/SECURITY_ARCHITECTURE.md
  - docs/contracts/AUTH_GAME_LOGIN_CONTRACT.md
  - docs/contracts/GAME_SESSION_CANARY_CONTRACT.md
search_first:
  - docs/agents/ACTIVE_WORK.md
  - open pull requests touching game-auth or game-gateway
  - existing game-auth middleware, controllers, tests and Gateway config/clients
optional_reads:
  - docs/architecture/adr/0009-authoritative-game-authentication.md
---

# OTERYN-20260723-native-auth-production-cutover

## Goal

Complete the repository-owned hardening and deployment-boundary prerequisites for Oteryn native authentication, prove the production-like cross-repository path where repository tooling permits, and keep irreversible production activation blocked until exact production environment evidence and secret/deployment access exist.

## Acceptance criteria

- [x] Unauthorized private game-ticket redeem attempts are throttled before service credential authentication.
- [x] Gateway service authentication supports bounded overlapping SHA-256 credential hashes for zero-downtime rotation.
- [x] Sensitive game-auth ticket issuance/redeem responses are consistently non-cacheable, including validation/authentication failures.
- [x] Gateway private service URLs fail closed on unsafe production transport configuration and rotation sequencing is documented without committing secrets.
- [ ] Focused PHP and Go regression coverage passes on the exact PR head.
- [ ] Canary-side issuer service authentication supports equivalent bounded credential rotation if required by the Gateway -> Canary boundary.
- [ ] Production-like OTClient -> Gateway -> Canary native-auth E2E is re-proven against the hardened exact component revisions where available.
- [ ] Production activation remains disabled until private/TLS routing, injected production credentials and exact deployed revisions are directly verified.

## Ownership

```yaml
owned_paths:
  - docs/agents/tasks/active/OTERYN-20260723-native-auth-production-cutover.md
  - app/Http/Middleware/GameAuth/**
  - app/Http/Controllers/GameAuth/**
  - config/game-auth.php
  - routes/api.php
  - routes/internal.php
  - tests/Feature/GameAuth/**
  - services/game-gateway/**
  - .env.example
  - docs/contracts/GAME_SESSION_CANARY_CONTRACT.md
  - .github/workflows/pint-diagnostic.yml
modules:
  - Game Auth HTTP boundary
  - Game Gateway private service boundary
  - Oteryn native-auth production rollout
dependencies:
  - Canary PR #722 merged as b8a88f073b2609b444fa15370aae30ac9f80b908
  - Canary credential-rotation PR #807
  - OTClient PR #17 merged as bb87346f6c516a19d19497d82bb01fb389334ff5
  - Platform Gateway PR #122 merged as 8006534108d835474dadd208b0ec934e4a12528b
blockers:
  - actual production deployment/secret injection and external network/TLS verification require environment access outside repository writes
cross_repository_tasks:
  - CAN-20260723-oteryn-native-auth-production-cutover
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-23T16:40:00+02:00
head: baf58ce450aa66ca3394ed8c5f647c9872638d16
branch: task/OTERYN-20260723-native-auth-production-cutover
pr: 124
status: validating
context_routes:
  - auth-identity
  - canary-integration
  - api
  - security
  - testing
owned_paths:
  - docs/agents/tasks/active/OTERYN-20260723-native-auth-production-cutover.md
  - app/Http/Middleware/GameAuth/**
  - app/Http/Controllers/GameAuth/**
  - config/game-auth.php
  - routes/api.php
  - routes/internal.php
  - tests/Feature/GameAuth/**
  - services/game-gateway/**
  - .env.example
  - docs/contracts/GAME_SESSION_CANARY_CONTRACT.md
  - .github/workflows/pint-diagnostic.yml
proven:
  - Platform PR #124 implements pre-auth source throttling before Gateway service authentication for private ticket redeem.
  - Platform PR #124 accepts one required current and one optional previous Gateway service credential SHA-256 hash for bounded overlap rotation.
  - Sensitive ticket issue/redeem responses and Gateway POST /v1/login responses are marked no-store/no-cache on success and bounded error paths.
  - Gateway configuration rejects plain HTTP dependency URLs outside loopback while retaining standard verified HTTPS behavior.
  - Gateway CI, Game Auth Ticket Concurrency, Platform DB Outage Validation, Phase 7 Production-Like Validation, Acceptance E2E and Agent Governance pass on baf58ce450aa66ca3394ed8c5f647c9872638d16.
  - Main CI on baf58ce450aa66ca3394ed8c5f647c9872638d16 fails only at vendor/bin/pint --test before static analysis/tests; a temporary diagnostic workflow is authorized to obtain the exact formatter patch and will be removed before finalization.
  - Canary PR #807 implements the equivalent current/previous issuer credential hash overlap and remains disabled by default.
derived:
  - Repository-owned HTTP/TLS/rotation hardening is implemented; current Platform validation blocker is deterministic formatting only.
  - Repository code remains deploy-first-safe while native-auth production activation stays disabled.
unknown:
  - exact production private-network ingress/firewall topology for Gateway -> Canary
  - exact production TLS certificate/hostname and secret-manager deployment state
  - final merged Platform #124 and Canary #807 revisions
  - hardened cross-repository native-auth E2E result
conflicts:
  - prior handoff claimed Platform PR #123 merged; live PR state proves it closed unmerged with zero changes
first_failure:
  marker: platform-pint-formatting
  evidence: CI run 30015408062 fails only at Check formatting; Phase 7 production-like validation run 30015407683 is green
rejected_hypotheses:
  - reuse PR #123 implementation: PR #123 contains zero commits and zero changed files
  - Treat initial Phase 7 failure as runtime hardening failure: rerun on baf58ce450aa66ca3394ed8c5f647c9872638d16 passes after cache-control assertion normalization
changed_paths:
  - .env.example
  - app/Http/Middleware/GameAuth/PreventSensitiveGameAuthResponseCaching.php
  - app/Http/Middleware/GameAuth/RequireGatewayServiceCredential.php
  - app/Providers/AppServiceProvider.php
  - bootstrap/app.php
  - config/game-auth.php
  - docs/agents/tasks/active/OTERYN-20260723-native-auth-production-cutover.md
  - routes/api.php
  - routes/internal.php
  - services/game-gateway/README.md
  - services/game-gateway/internal/config/config.go
  - services/game-gateway/internal/config/config_test.go
  - services/game-gateway/internal/httpapi/server.go
  - services/game-gateway/internal/httpapi/server_test.go
  - tests/Feature/GameAuth/GameLoginTicketApiTest.php
  - tests/Feature/GameAuth/GameLoginTicketRedeemApiTest.php
validation:
  - command: Game Gateway CI 30015407675
    result: PASS
    evidence: hardened Gateway config, TLS URL policy and HTTP API tests passed
  - command: Game Auth Ticket Concurrency 30015407709
    result: PASS
    evidence: ticket single-use concurrency validation passed
  - command: Platform DB Outage Validation 30015408087
    result: PASS
    evidence: database outage fail-closed validation passed
  - command: Phase 7 Production-Like Validation 30015407683
    result: PASS
    evidence: production-like critical regression validation passed
  - command: Acceptance E2E and Visual UX 30015408010
    result: PASS
    evidence: acceptance workflow passed
  - command: CI 30015408062
    result: FAIL
    evidence: first failure is vendor/bin/pint --test; static analysis and tests were skipped
blockers:
  - exact Platform formatting validation must be fixed
  - Canary PR #807 exact-head validation remains pending
  - hardened cross-repository E2E is not yet re-proven
  - irreversible production activation and direct production network/secret verification are outside repository-only evidence until exact environment access is available
next_action: Apply the exact Pint formatter patch, remove the temporary diagnostic workflow, and revalidate Platform PR #124 before hardened cross-repository E2E.
```
