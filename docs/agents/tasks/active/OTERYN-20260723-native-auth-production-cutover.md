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

Complete repository-owned native-auth production hardening and deployment-boundary prerequisites, re-prove the hardened cross-repository path, and keep irreversible production activation blocked until exact deployed network/TLS/secret evidence exists.

## Acceptance criteria

- [x] Unauthorized private game-ticket redeem attempts are throttled before service credential authentication.
- [x] Gateway service authentication supports bounded overlapping SHA-256 credential hashes for zero-downtime rotation.
- [x] Sensitive ticket issue/redeem and Gateway native-login responses are consistently non-cacheable on success and bounded failures.
- [x] Gateway non-loopback private dependency URLs require HTTPS and rotation sequencing is documented without committing secrets.
- [x] Focused PHP and Go regression coverage plus repository production-like validation pass on the validated hardened code head.
- [x] Canary PR #807 implements equivalent bounded current/previous service-credential hash overlap while the issuer remains disabled by default.
- [ ] Hardened OTClient -> Gateway -> Canary native-auth E2E is re-proven against exact merged hardened component revisions.
- [ ] Production activation remains blocked until private/TLS routing, injected production credentials and deployed revisions are directly verified.

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
  - exact production deployment, secret injection and network/TLS verification require environment access outside repository writes
cross_repository_tasks:
  - CAN-20260723-oteryn-native-auth-production-cutover
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-23T17:05:00+02:00
head: 3cec748335520b76428fdd2c7be38a91259e15ed
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
proven:
  - Platform PR #124 implements pre-auth source throttling before Gateway service authentication for private ticket redeem.
  - Platform PR #124 accepts one required current and one optional previous Gateway service credential SHA-256 hash for bounded overlap rotation.
  - Sensitive ticket issue/redeem responses and Gateway POST /v1/login responses are marked no-store/no-cache on success and bounded error paths.
  - Gateway configuration rejects plain HTTP dependency URLs outside loopback while retaining standard Go TLS certificate/hostname verification for HTTPS.
  - Exact hardened Platform code head 2e664c440379af45b6413a26c9c0ee968275d049 passed CI 30017547910, Game Gateway CI 30017547767, Agent Governance 30017547837, Game Auth Ticket Concurrency 30017547805, Platform DB Outage Validation 30017547759, Phase 7 Production-Like Validation 30017547664 and Acceptance E2E 30017547713.
  - Temporary Pint/PHPStan diagnostic workflows were removed; the exact formatter patch and PHPStan type fixes were applied to repository code/tests.
  - docs/contracts/GAME_SESSION_CANARY_CONTRACT.md now records Candidate B as selected/implemented, the delivered protocol-v1 semantics, overlap rotation, HTTPS boundary, prior bounded E2E and explicit production activation gates.
  - Canary PR #807 exact head c8503b7d35fe15015e89b9d0067a8614e7a9d7a9 passed runtime CI 30016554527 and Agent Task Ownership 30016555803 after archiving the completed PR #722 task lifecycle.
derived:
  - Repository-owned Platform hardening is implementation-complete and deploy-first-safe while production native-auth activation remains disabled.
  - The old PR #123 blocker is superseded by implemented PR #124 hardening rather than recovered from PR #123, which contained no changes.
  - The remaining cross-repository behavior gate should be run against merged hardened Platform and Canary revisions to avoid invalidation by later documentation-only commits.
unknown:
  - exact production private-network ingress/firewall topology for Gateway -> Canary
  - exact production TLS certificate/hostname and secret-manager deployment state
  - final merged Platform #124 and Canary #807 revision SHAs
  - hardened exact-revision OTClient -> Gateway -> Canary E2E result
conflicts:
  - prior handoff claimed Platform PR #123 merged; live PR state proved it closed unmerged with zero commits/changed files
first_failure:
  marker: hardened-cross-repo-e2e-not-yet-reproven
  evidence: successful native-auth runs 29988893301 and 29992417296 predate PR #124 and PR #807 hardening
rejected_hypotheses:
  - reuse PR #123 implementation: PR #123 contains zero commits and zero changed files
  - Treat earlier formatter/static-analysis failures as runtime hardening defects: exact Pint diff and PHPStan diagnostics identified only deterministic style/type issues; hardened code head subsequently passed all Platform validation workflows
changed_paths:
  - .env.example
  - app/Http/Middleware/GameAuth/PreventSensitiveGameAuthResponseCaching.php
  - app/Http/Middleware/GameAuth/RequireGatewayServiceCredential.php
  - app/Providers/AppServiceProvider.php
  - bootstrap/app.php
  - config/game-auth.php
  - docs/agents/tasks/active/OTERYN-20260723-native-auth-production-cutover.md
  - docs/contracts/GAME_SESSION_CANARY_CONTRACT.md
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
  - command: CI 30017547910 on 2e664c440379af45b6413a26c9c0ee968275d049
    result: PASS
    evidence: Composer validation/audit, Pint, PHPStan and PHPUnit all passed.
  - command: Game Gateway CI 30017547767 on 2e664c440379af45b6413a26c9c0ee968275d049
    result: PASS
    evidence: hardened Gateway Go formatting/tests/vet/build passed.
  - command: Game Auth Ticket Concurrency 30017547805 on 2e664c440379af45b6413a26c9c0ee968275d049
    result: PASS
    evidence: single-use ticket concurrency behavior passed.
  - command: Platform DB Outage Validation 30017547759 on 2e664c440379af45b6413a26c9c0ee968275d049
    result: PASS
    evidence: fail-closed database outage validation passed.
  - command: Phase 7 Production-Like Validation 30017547664 on 2e664c440379af45b6413a26c9c0ee968275d049
    result: PASS
    evidence: exact-SHA critical regression and production-like deployment validation passed.
  - command: Acceptance E2E and Visual UX 30017547713 on 2e664c440379af45b6413a26c9c0ee968275d049
    result: PASS
    evidence: repository acceptance workflow passed.
blockers:
  - final documentation/checkpoint head must complete PR validation before merge
  - Canary PR #807 must be rebased/final-gated and merged
  - hardened cross-repository native-auth E2E must be re-proven on exact merged revisions
  - irreversible production activation requires direct deployed network/TLS/secret evidence outside repository-only state
next_action: Validate and merge deploy-first-safe Platform PR #124 and Canary PR #807, then rerun login/oteryn-native-auth against their exact merge SHAs before any production activation.
```
