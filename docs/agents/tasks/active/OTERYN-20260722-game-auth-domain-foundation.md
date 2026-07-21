---
task_id: OTERYN-20260722-game-auth-domain-foundation
required_reads:
  - AGENTS.md
  - docs/agents/CONTEXT_HANDOFF.md
  - docs/architecture/adr/0009-oteryn-game-authentication-architecture.md
  - docs/architecture/GAME_AUTH_THREAT_MODEL.md
  - docs/architecture/GAME_AUTH_ROLLOUT_PLAN.md
  - docs/contracts/GAME_GATEWAY_IDENTITY_CONTRACT.md
  - docs/contracts/GAME_SESSION_CANARY_CONTRACT.md
  - docs/contracts/WORLD_REGISTRY_CONTRACT.md
  - docs/contracts/IDENTITY_CANARY_ACCOUNT_BINDING_CONTRACT.md
search_first:
  - open PRs and active tasks overlapping game auth, Identity revocation, tickets, world registry, or shared migrations
  - existing web_session_generation revocation pattern
  - existing password reset/change and MFA reset transaction boundaries
  - existing audit event patterns and model/migration conventions
optional_reads:
  - docs/architecture/SECURITY_ARCHITECTURE.md
  - docs/architecture/DATA_OWNERSHIP.md
  - docs/architecture/TEST_STRATEGY.md
---

# OTERYN-20260722-game-auth-domain-foundation

## Goal

Implement Phase 1 of ADR 0009 as a Platform-only, non-public foundation: game-auth revocation generation, opaque Game Login Ticket lifecycle/storage, database-backed World Registry domain, Game Session interface/value contracts, protocol/configuration constants, and focused security tests. Do not expose OAuth/ticket HTTP endpoints and do not modify OTClient, Canary, or upstream login-server.

## Acceptance criteria

- [ ] `Identity` has a monotonic `game_auth_generation` independent from `web_session_generation`.
- [ ] Password change/reset and MFA reset/disable security paths advance game-auth generation within existing domain transaction boundaries.
- [ ] Game Login Ticket plaintext is generated from at least 256 bits of CSPRNG entropy, returned only as a value object, and only SHA-256 (or stronger equivalent) lookup material is persisted.
- [ ] Ticket persistence includes exact Identity, ready Canary account ID, audience, security generation, expiry, used timestamp, and creation timestamp.
- [ ] Domain issue/redeem lifecycle is fail-closed for disabled Identity, missing/pending/conflict binding, wrong audience, expiry, reuse, generation change, and binding drift.
- [ ] Redeem uses transaction/locking semantics that allow exactly one successful consume of one stored ticket; concurrent proof may be deferred to Phase 3 only if current test harness cannot reliably exercise independent DB connections, but the storage primitive must be designed for it.
- [ ] World Registry uses a Platform-owned database model with non-singleton schema, validated status/login-enabled/routing fields, and no invented production endpoint data.
- [ ] Game Session remains an interface/value contract with no Canary persistence implementation in this phase.
- [ ] Config defaults define protocol v1, audience `oteryn-game-gateway`, and 60-second ticket TTL without secrets.
- [ ] Audit events contain no raw ticket/session/OAuth/password material.
- [ ] No new public route/controller is added.
- [ ] Relevant focused tests, full CI, governance and required production-like workflows pass on final head.

## Ownership

```yaml
owned_paths:
  - docs/agents/tasks/active/OTERYN-20260722-game-auth-domain-foundation.md
  - docs/agents/tasks/archive/OTERYN-20260721-game-auth-architecture-foundation.md
  - docs/agents/tasks/active/OTERYN-20260721-game-auth-architecture-foundation.md
  - app/Audit/SecurityEventRecorder.php
  - app/GameAuth/
  - app/Identity/Actions/RevokeIdentityGameAuthorizations.php
  - app/Identity/Credentials/IdentityCredentialUpdater.php
  - app/Identity/Mfa/DisableIdentityMfa.php
  - app/Identity/Mfa/ResetIdentityMfa.php
  - app/Identity/Models/Identity.php
  - config/game-auth.php
  - database/migrations/
  - docs/contracts/WORLD_REGISTRY_CONTRACT.md
  - tests/Feature/GameAuth/
  - tests/Unit/GameAuth/
  - tests/Feature/Identity/
modules:
  - auth-identity
  - architecture
  - api
  - security
  - canary-integration
dependencies:
  - ADR 0009 merged as 78bc9f839b98b96ff9e5e3fcf43680104a5e27fa
  - ready immutable Identity-to-Canary account binding
  - existing Platform Identity credential and MFA transaction boundaries
blockers:
  - none
cross_repository_tasks:
  - OTClient implementation remains a separate future task
  - Canary Game Session compatibility remains a separate future task unless explicitly authorized
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-21T22:18:00Z
head: 0cd655322bacdf82e32200f273d134682042733d
branch: task/OTERYN-20260722-game-auth-domain-foundation
pr: 118
status: implementing
context_routes:
  - auth-identity
  - architecture
  - api
  - security
  - canary-integration
  - testing
owned_paths:
  - docs/agents/tasks/active/OTERYN-20260722-game-auth-domain-foundation.md
  - docs/agents/tasks/archive/OTERYN-20260721-game-auth-architecture-foundation.md
  - docs/agents/tasks/active/OTERYN-20260721-game-auth-architecture-foundation.md
  - app/Audit/SecurityEventRecorder.php
  - app/GameAuth/
  - app/Identity/Actions/RevokeIdentityGameAuthorizations.php
  - app/Identity/Credentials/IdentityCredentialUpdater.php
  - app/Identity/Mfa/DisableIdentityMfa.php
  - app/Identity/Mfa/ResetIdentityMfa.php
  - app/Identity/Models/Identity.php
  - config/game-auth.php
  - database/migrations/
  - docs/contracts/WORLD_REGISTRY_CONTRACT.md
  - tests/Feature/GameAuth/
  - tests/Unit/GameAuth/
  - tests/Feature/Identity/
proven:
  - Phase 0 architecture PR 117 was squash-merged to main as 78bc9f839b98b96ff9e5e3fcf43680104a5e27fa.
  - No open Oteryn Platform PR overlaps game auth ticket, world registry, or game-auth generation scope at Phase 1 start.
  - Existing web-session revocation uses a dedicated unsigned bigint generation and transactional atomic increment.
  - IdentityCredentialUpdater owns password change/reset domain transactions and now invokes independent game-auth revocation there.
  - ResetIdentityMfa and DisableIdentityMfa own their security-sensitive MFA transaction boundaries and now invoke independent game-auth revocation there.
  - Platform SecurityEventRecorder writes bounded event type plus identity ID and timestamp only; new game-auth audit methods preserve that shape.
  - Game Login Ticket storage contains only SHA-256 lookup material plus bounded authorization metadata; there is no plaintext-ticket column.
  - Ticket generation uses 32 bytes from random_bytes before base64url encoding.
  - Ticket redeem uses a database transaction and lockForUpdate on the exact stored ticket before current Identity generation/disabled state and ready binding are revalidated.
  - World Registry is represented by a non-singleton Platform database table and a fail-closed database-backed registry implementation; no world row or production endpoint is seeded.
  - Game Session remains an interface/value boundary only; no Canary account_sessions write or other Canary adapter exists in this task.
derived:
  - game_auth_generation mirrors the independent generation pattern rather than reusing web_session_generation.
  - Password and MFA security events call game-auth revocation from domain services, not controllers.
  - Platform MariaDB is the first authoritative ticket store because Identity and binding state already live transactionally in the same Platform database; ticket plaintext is never persisted.
  - The initial World Registry account policy is universal for positive redeemed Canary account IDs, while only online, login-enabled, syntactically routable worlds are returned; future account-specific entitlements can replace this behind the existing account-aware interface.
unknown:
  - Exact production world hostname and port remain unproven and are not seeded by this task.
  - Final Game Session-to-Canary persistence adapter and active-session revocation remain Phase 6 UNKNOWNs.
  - A real independent-connection concurrent consume test has not yet been executed; lockForUpdate is implemented, while concurrent proof remains a Phase 3 gate unless a reliable MariaDB test can be added in this task.
conflicts:
  - none
first_failure:
  marker: none
  evidence: none
rejected_hypotheses:
  - Reusing web_session_generation for game authorization is rejected because web and game credential lifecycles have different revocation semantics.
  - Seeding an illustrative game-eu.oteryn.com endpoint is rejected because production routing is UNKNOWN.
  - Treating lockForUpdate implementation alone as proof of concurrent exactly-once behavior is rejected; concurrency requires executable multi-connection evidence.
changed_paths:
  - app/Audit/SecurityEventRecorder.php
  - app/GameAuth/
  - app/Identity/Actions/RevokeIdentityGameAuthorizations.php
  - app/Identity/Credentials/IdentityCredentialUpdater.php
  - app/Identity/Mfa/DisableIdentityMfa.php
  - app/Identity/Mfa/ResetIdentityMfa.php
  - app/Identity/Models/Identity.php
  - config/game-auth.php
  - database/migrations/2026_07_22_000100_add_game_auth_generation_to_identities_table.php
  - database/migrations/2026_07_22_000200_create_game_login_tickets_table.php
  - database/migrations/2026_07_22_000300_create_game_worlds_table.php
  - docs/agents/tasks/active/OTERYN-20260722-game-auth-domain-foundation.md
  - docs/agents/tasks/archive/OTERYN-20260721-game-auth-architecture-foundation.md
  - tests/Feature/GameAuth/
  - tests/Feature/Identity/Recovery/PasswordChangeTest.php
  - tests/Feature/Identity/Recovery/PasswordRecoveryTest.php
validation:
  - command: GitHub list PR 118 changed filenames
    result: PASS
    evidence: all current changed paths are within declared ownership after adding the MFA disable domain path
  - command: focused and repository test workflows
    result: NOT_RUN
    evidence: first implementation validation has not been triggered yet
blockers:
  - none
next_action: Trigger first PR validation, inspect formatting/static-analysis/test failures, repair, then update World Registry contract implementation status and final checkpoint.
```

## Notes

This task is intentionally Platform-only and non-public. Phase 2 (Passport/OAuth) and Phase 3 (ticket HTTP issuance/redeem) remain separate bounded slices.
