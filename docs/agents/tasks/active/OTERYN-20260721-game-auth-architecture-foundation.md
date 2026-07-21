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

- [x] Fresh repository and cross-repository auth preflight is recorded with exact relevant revisions.
- [x] A durable ADR defines Oteryn Identity as the sole reusable-credential authority, native-client Authorization Code + PKCE, one-time Game Login Tickets, a separately deployable Game Gateway, independent Game Sessions, World Registry, and migration boundaries.
- [x] A threat model covers credential theft, authorization-code interception, CSRF/state, PKCE, ticket replay/races, token leakage/logging, confused deputy/audience, service impersonation, direct legacy bypass, revocation, dependency outages, and multi-instance storage semantics.
- [x] OTClient, Gateway↔Identity, Gateway↔Canary/Game Session, and World Registry contracts are documented with versioned request/response semantics and explicit UNKNOWNs.
- [x] Sequence diagrams and rollout ordering preserve legacy login until the new path is proven end to end, while preventing it from being called globally authoritative before bypass paths are fenced.
- [x] No runtime or external-repository mutation is performed in this architecture-foundation task.

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
updated_at: 2026-07-21T21:54:31Z
head: c30760195dee451ec78e2b5a6b5cd1e3c3ee9cb6
branch: task/OTERYN-20260721-game-auth-architecture-foundation
pr: 117
status: validating
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
  - docs/contracts/OTCLIENT_GAME_AUTH_CONTRACT.md
  - docs/contracts/WORLD_REGISTRY_CONTRACT.md
proven:
  - Oteryn Platform Phase 0 branch started from main 09450e6f96638ae2dbf6d18c9585e7ccc5f6d24f.
  - Platform Identity, revocable web sessions, password recovery/change, TOTP MFA, immutable ready Identity-to-Canary account binding, and character creation are implemented.
  - The authoritative Platform-originated game-login bridge is not implemented.
  - opentibiabr/login-server main remains 2612930de4d97123a397f8f2cd0d5f784094af40, matching the existing auth-contract pin.
  - blakinio/canary advanced 122 commits beyond the auth-contract pin to d760ce44c55b9aa6f01e80d2d407f6833938bdce, with no authentication/runtime source file listed in the compare delta.
  - Current OTClient main a6868920443dc285656bd016acdb2c1ea566e511 still reads account/password into global login state, can persist encrypted password material, sends password to HTTP login, and passes password to ProtocolLogin.
  - Laravel Passport 13.x supports the current Laravel 13 dependency line and Authorization Code with PKCE for public clients; its League OAuth2 Server dependency supports RFC 8252 loopback redirect matching with dynamic ports.
  - ADR 0009 selects Oteryn Identity as sole reusable-credential authority, Passport-based Authorization Code plus PKCE, opaque one-time Game Login Tickets, a separately deployable Go Game Gateway, independent Game Sessions, and World Registry.
  - Threat model, sequence diagrams, rollout plan, and four cross-component contracts are present on PR 117.
  - PR 117 changed-file inventory contains exactly the nine declared documentation/task owned paths and no runtime or external-repository changes.
derived:
  - The target native authorization layer uses Laravel Passport rather than a custom OAuth protocol, with a narrowly scoped short-lived access token used only to request a Game Login Ticket.
  - Game Login Ticket issuance and Game Session creation are separate lifecycles; neither reuses Platform web sessions or Canary account sink credentials.
  - Existing legacy/native/password paths remain compatibility-only during rollout and must be fenced before claiming Identity is globally authoritative for game login.
  - Current DB account_sessions remains only a candidate Game Session adapter until exact Canary/schema/replay/revocation/least-privilege behavior is revalidated and accepted.
unknown:
  - Exact production deployment image/digest and external exposure of native Canary login and upstream login-server remain unproven.
  - Final Canary-facing Game Session adapter, TTL, world scoping, active-session revocation and ambiguous-commit recovery remain to be selected/proven in Phase 6.
  - Exact production service identity, secret management and shared atomic ticket storage remain unproven.
  - Exact Passport configuration used to prevent long-lived refresh semantics for the first-party native client remains a Phase 2 implementation gate.
conflicts:
  - Existing broad auth contract documents parallel password/session paths whose semantics are incompatible with the target single authoritative Identity policy until legacy bypass closure.
first_failure:
  marker: none
  evidence: none
rejected_hypotheses:
  - Custom OAuth authorization protocol is unnecessary because the current Laravel ecosystem provides a standards-based PKCE-capable authorization server.
  - Current 24-hour replayable account_sessions is not accepted as the Game Login Ticket.
  - Process-local Canary LoginSessionManager state is not assumed sufficient for horizontally scaled Gateway/multiworld routing.
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260721-game-auth-architecture-foundation.md
  - docs/architecture/adr/0009-oteryn-game-authentication-architecture.md
  - docs/architecture/GAME_AUTH_THREAT_MODEL.md
  - docs/architecture/GAME_AUTH_SEQUENCE_DIAGRAMS.md
  - docs/architecture/GAME_AUTH_ROLLOUT_PLAN.md
  - docs/contracts/OTCLIENT_GAME_AUTH_CONTRACT.md
  - docs/contracts/GAME_GATEWAY_IDENTITY_CONTRACT.md
  - docs/contracts/GAME_SESSION_CANARY_CONTRACT.md
  - docs/contracts/WORLD_REGISTRY_CONTRACT.md
validation:
  - command: GitHub list PR 117 changed filenames
    result: PASS
    evidence: exactly nine changed paths, all within declared owned_paths; no runtime/cross-repository writes
  - command: GitHub commit status on c30760195dee451ec78e2b5a6b5cd1e3c3ee9cb6
    result: PENDING
    evidence: no commit statuses reported yet; do not claim CI success
blockers:
  - none
next_action: Verify PR 117 workflow runs on the checkpoint commit, inspect any failures, and repair before readiness.
```

## Notes

This task is intentionally documentation/contracts only. Runtime work begins in a later bounded task after this architecture foundation is reviewed and merged.
