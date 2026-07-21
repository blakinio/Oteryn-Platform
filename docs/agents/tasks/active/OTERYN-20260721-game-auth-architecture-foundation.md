---
task_id: OTERYN-20260721-game-auth-architecture-foundation
required_reads:
  - AGENTS.md
  - docs/agents/CONTEXT_HANDOFF.md
  - docs/architecture/SECURITY_ARCHITECTURE.md
  - docs/contracts/AUTH_GAME_LOGIN_CONTRACT.md
  - docs/contracts/CANARY_DATA_CONTRACT.md
  - docs/contracts/IDENTITY_CANARY_ACCOUNT_BINDING_CONTRACT.md
search_first:
  - open PRs and active tasks overlapping game authentication, Identity authorization, session credentials, world registry, or gateway contracts
  - current blakinio/otclient login flow and credential handling
  - current blakinio/canary authentication/session behavior since the pinned auth discovery revision
  - current opentibiabr/login-server main revision
optional_reads:
  - docs/architecture/SYSTEM_ARCHITECTURE.md
  - docs/architecture/DATA_OWNERSHIP.md
  - docs/architecture/TEST_STRATEGY.md
---

# OTERYN-20260721-game-auth-architecture-foundation

## Goal

Complete Phase 0 discovery and the durable architecture/contract foundation for authoritative Oteryn game authentication without changing OTClient, Canary, or upstream login-server runtime code.

## Acceptance criteria

- [ ] Fresh repository and cross-repository auth preflight is recorded with exact relevant revisions.
- [ ] A durable ADR defines Oteryn Identity as the sole reusable-credential authority, native-client Authorization Code + PKCE, one-time Game Login Tickets, a separately deployable Game Gateway, independent Game Sessions, World Registry, and migration boundaries.
- [ ] A threat model covers credential theft, authorization-code interception, CSRF/state, PKCE, ticket replay/races, token leakage/logging, confused deputy/audience, service impersonation, direct legacy bypass, revocation, dependency outages, and multi-instance storage semantics.
- [ ] OTClient, Gateway↔Identity, Gateway↔Canary/Game Session, and World Registry contracts are documented with versioned request/response semantics and explicit UNKNOWNs.
- [ ] Sequence diagrams and rollout ordering preserve legacy login until the new path is proven end to end, while preventing it from being called globally authoritative before bypass paths are fenced.
- [ ] No runtime or external-repository mutation is performed in this architecture-foundation task.

## Ownership

```yaml
owned_paths:
  - docs/agents/tasks/active/OTERYN-20260721-game-auth-architecture-foundation.md
  - docs/architecture/adr/0009-oteryn-game-authentication-architecture.md
  - docs/architecture/GAME_AUTH_THREAT_MODEL.md
  - docs/architecture/GAME_AUTH_SEQUENCE_DIAGRAMS.md
  - docs/architecture/GAME_AUTH_ROLLOUT_PLAN.md
  - docs/contracts/OTCLIENT_GAME_AUTH_CONTRACT.md
  - docs/contracts/GAME_GATEWAY_IDENTITY_CONTRACT.md
  - docs/contracts/GAME_SESSION_CANARY_CONTRACT.md
  - docs/contracts/WORLD_REGISTRY_CONTRACT.md
modules:
  - architecture
  - auth-identity
  - canary-integration
  - api
  - security
dependencies:
  - docs/contracts/AUTH_GAME_LOGIN_CONTRACT.md
  - docs/contracts/IDENTITY_CANARY_ACCOUNT_BINDING_CONTRACT.md
  - docs/contracts/CANARY_DATA_CONTRACT.md
blockers:
  - none
cross_repository_tasks:
  - future OTClient implementation task required
  - future Canary/login-server compatibility implementation task required only after explicit authorization
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-21T21:41:16Z
head: 09450e6f96638ae2dbf6d18c9585e7ccc5f6d24f
branch: task/OTERYN-20260721-game-auth-architecture-foundation
pr: none
status: investigating
context_routes:
  - architecture
  - auth-identity
  - canary-integration
  - api
  - security
  - testing
owned_paths:
  - docs/agents/tasks/active/OTERYN-20260721-game-auth-architecture-foundation.md
  - docs/architecture/adr/0009-oteryn-game-authentication-architecture.md
  - docs/architecture/GAME_AUTH_THREAT_MODEL.md
  - docs/architecture/GAME_AUTH_SEQUENCE_DIAGRAMS.md
  - docs/architecture/GAME_AUTH_ROLLOUT_PLAN.md
  - docs/contracts/OTCLIENT_GAME_AUTH_CONTRACT.md
  - docs/contracts/GAME_GATEWAY_IDENTITY_CONTRACT.md
  - docs/contracts/GAME_SESSION_CANARY_CONTRACT.md
  - docs/contracts/WORLD_REGISTRY_CONTRACT.md
proven:
  - Oteryn Platform main preflight started from 09450e6f96638ae2dbf6d18c9585e7ccc5f6d24f.
  - Platform Identity, revocable web sessions, password recovery/change, TOTP MFA, immutable ready Identity-to-Canary account binding, and character creation are implemented.
  - The authoritative Platform-originated game-login bridge is not implemented.
  - opentibiabr/login-server main remains 2612930de4d97123a397f8f2cd0d5f784094af40, matching the existing auth-contract pin.
  - blakinio/canary advanced 122 commits beyond the auth-contract pin to d760ce44c55b9aa6f01e80d2d407f6833938bdce, with no authentication/runtime source file listed in the compare delta.
  - Current OTClient main still reads account/password into global login state, can persist encrypted password material, sends password to HTTP login, and passes password to ProtocolLogin.
  - Laravel Passport 13.x supports Laravel 13 and Authorization Code with PKCE for public clients; its League OAuth2 Server dependency supports RFC 8252 loopback redirect matching with dynamic ports.
derived:
  - The target native authorization layer should use Laravel Passport rather than a custom OAuth protocol, with a narrowly scoped short-lived access token used only to request a Game Login Ticket.
  - Game Login Ticket issuance and Game Session creation must be separate lifecycles; neither should reuse Platform web sessions or Canary account sink credentials.
  - Existing legacy/native/password paths must remain compatibility-only during rollout and must be fenced before claiming Identity is globally authoritative for game login.
unknown:
  - Exact production deployment image/digest and external exposure of native Canary login and upstream login-server remain unproven.
  - Final Canary-facing credential shape that minimizes or eliminates Canary changes remains to be selected after the contract is finalized and external implementation is explicitly authorized.
conflicts:
  - Existing broad auth contract documents parallel password/session paths whose semantics are incompatible with the target single authoritative Identity policy.
first_failure:
  marker: none
  evidence: none
rejected_hypotheses:
  - Custom OAuth authorization protocol is unnecessary because the current Laravel ecosystem provides a standards-based PKCE-capable authorization server.
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260721-game-auth-architecture-foundation.md
validation:
  - command: not-run
    result: NOT_RUN
    evidence: architecture documents not yet completed
blockers:
  - none
next_action: Write ADR 0009 and the threat model from the proven current-state evidence.
```

## Notes

This task is intentionally documentation/contracts only. Runtime work begins in a later bounded task after this architecture foundation is reviewed and merged.
