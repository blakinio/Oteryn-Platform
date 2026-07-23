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

- [ ] Unauthorized private game-ticket redeem attempts are throttled before service credential authentication.
- [ ] Gateway service authentication supports bounded overlapping SHA-256 credential hashes for zero-downtime rotation.
- [ ] Sensitive game-auth ticket issuance/redeem responses are consistently non-cacheable, including validation/authentication failures.
- [ ] Gateway private service URLs fail closed on unsafe production transport configuration and rotation sequencing is documented without committing secrets.
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
modules:
  - Game Auth HTTP boundary
  - Game Gateway private service boundary
  - Oteryn native-auth production rollout
dependencies:
  - Canary PR #722 merged as b8a88f073b2609b444fa15370aae30ac9f80b908
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
updated_at: 2026-07-23T16:10:00+02:00
head: 8006534108d835474dadd208b0ec934e4a12528b
branch: task/OTERYN-20260723-native-auth-production-cutover
pr: none
status: implementing
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
  - Platform main is 8006534108d835474dadd208b0ec934e4a12528b, merged Gateway PR #122.
  - Closed PR #123 has zero commits and zero changed files, so its advertised hardening was not delivered.
  - Private ticket redeem currently applies RequireGatewayServiceCredential before throttle:game-auth-ticket-redeem.
  - Platform Gateway service authentication currently accepts one configured SHA-256 credential hash.
  - Ticket issuance/redeem controllers do not currently guarantee no-store/no-cache on every success and failure path.
  - Canary PR #722 is merged and the Game Session issuer remains disabled by default unless explicitly configured.
derived:
  - Repository code can be merged deploy-first-safe while native-auth production activation remains disabled.
  - Zero-downtime service credential rotation requires an overlap window accepting old and new credential hashes while callers roll to the new secret.
unknown:
  - exact production private-network/TLS Gateway -> Canary route and origin exposure
  - exact production secret-management/credential rotation mechanism
  - exact final deployed Platform, Gateway, Canary and OTClient revisions
conflicts:
  - prior handoff claimed Platform PR #123 merged; live PR state proves it closed unmerged with zero changes
first_failure:
  marker: platform-game-auth-http-hardening-incomplete
  evidence: route middleware order and single-hash service credential configuration on current main
rejected_hypotheses:
  - reuse PR #123 implementation: PR #123 contains zero commits and zero changed files
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260723-native-auth-production-cutover.md
validation:
  - command: live repository/PR preflight
    result: PASS
    evidence: current main and overlapping PR/task state verified before implementation
blockers:
  - irreversible production activation and direct production network/secret verification are outside repository-only evidence until exact environment access is available
next_action: Implement Platform ticket-boundary throttling, overlapping service credential hash acceptance and no-store/no-cache regression coverage on the task branch.
```
