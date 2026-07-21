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

- [x] `Identity` has a monotonic `game_auth_generation` independent from `web_session_generation`.
- [x] Password change/reset and MFA reset/disable security paths advance game-auth generation within existing domain transaction boundaries.
- [x] Game Login Ticket plaintext is generated from at least 256 bits of CSPRNG entropy, returned only as a value object, and only SHA-256 lookup material is persisted.
- [x] Ticket persistence includes exact Identity, ready Canary account ID, audience, security generation, expiry, used timestamp, and creation timestamp.
- [x] Domain issue/redeem lifecycle is fail-closed for disabled Identity, missing/pending/conflict binding, wrong audience, expiry, reuse, generation change, and binding drift.
- [x] Redeem uses transaction/`lockForUpdate` semantics for a single stored ticket; true independent-connection concurrent exactly-once proof remains an explicit Phase 3 gate and is not claimed by this task.
- [x] World Registry uses a Platform-owned database model with non-singleton schema, validated status/login-enabled/routing projection, and no invented production endpoint data.
- [x] Game Session remains an interface/value contract with no Canary persistence implementation in this phase.
- [x] Config defaults define protocol v1, audience `oteryn-game-gateway`, and 60-second ticket TTL without secrets.
- [x] Audit events contain no raw ticket/session/OAuth/password material.
- [x] No new public route/controller is added.
- [x] Relevant focused tests, full CI, governance, DB-outage, production-like and acceptance workflows pass on the validated checkpoint head before the final checkpoint commit.

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
updated_at: 2026-07-21T22:42:35Z
head: 32af1ecdf800c570a2ddda3408e0b52ee1e1cb4d
branch: task/OTERYN-20260722-game-auth-domain-foundation
pr: 118
status: ready
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
  - No open Oteryn Platform PR overlapped game-auth ticket, World Registry, or game-auth generation scope at Phase 1 start.
  - IdentityCredentialUpdater revokes independent game authorization generation for password change and password reset inside existing domain transactions.
  - ResetIdentityMfa and DisableIdentityMfa revoke independent game authorization generation inside their security-sensitive transaction boundaries.
  - Game Login Ticket storage contains only SHA-256 lookup material plus bounded authorization metadata; there is no plaintext-ticket column.
  - Ticket generation uses 32 bytes from random_bytes before base64url encoding.
  - Ticket issue requires an enabled Identity and exact ready Identity-to-Canary binding.
  - Ticket redeem uses a database transaction and lockForUpdate on the exact stored ticket before audience, expiry, reuse, current Identity generation/disabled state, and exact ready binding are revalidated.
  - World Registry storage is the Platform-owned game_worlds database table and remains empty by default; no production hostname/port is seeded.
  - DatabaseWorldRegistry returns only online, login-enabled, syntactically routable worlds for a positive Canary account ID behind an account-aware interface.
  - Game Session remains an interface/value boundary only; no Canary account_sessions write or other Canary adapter exists in this task.
  - Temporary diagnostic workflow exposed exact PHPStan/test failures and was removed after diagnosis; it is absent from the PR changed-file inventory.
  - CI run 29874507061 succeeded on checkpoint head 32af1ecdf800c570a2ddda3408e0b52ee1e1cb4d.
  - Agent Governance run 29874507173 succeeded on checkpoint head 32af1ecdf800c570a2ddda3408e0b52ee1e1cb4d.
  - Platform DB Outage Validation run 29874507079 succeeded on checkpoint head 32af1ecdf800c570a2ddda3408e0b52ee1e1cb4d.
  - Phase 7 Production-Like Validation run 29874507065 succeeded on checkpoint head 32af1ecdf800c570a2ddda3408e0b52ee1e1cb4d, including the exact-SHA critical regression suite.
  - Acceptance E2E and Visual UX run 29874507100 succeeded on checkpoint head 32af1ecdf800c570a2ddda3408e0b52ee1e1cb4d.
derived:
  - Platform MariaDB is the first authoritative ticket store because Identity, binding, generation and ticket state can participate in controlled transactional locking while plaintext credentials remain absent from storage.
  - The initial World Registry account policy is universal for positive redeemed Canary account IDs, while future account-specific entitlements can replace this behind the existing account-aware interface.
unknown:
  - Exact production world hostname and port remain unproven and are not seeded by this task.
  - Final Game Session-to-Canary persistence adapter, Game Session TTL, replay/revocation/world-scope behavior and active-session revocation remain Phase 6 UNKNOWNs.
  - A real independent-connection concurrent ticket consume test has not been executed; true concurrent exactly-once evidence remains required before Phase 3 ticket redeem is production-ready.
conflicts:
  - none
first_failure:
  marker: CI formatting failure on initial Phase 1 implementation head
  evidence: Pint required single-line formatting for the empty GameLoginTicketDenied exception body; corrected before deeper validation
rejected_hypotheses:
  - Reusing web_session_generation for game authorization is rejected because web and game credential lifecycles have different revocation semantics.
  - Seeding an illustrative game-eu.oteryn.com endpoint is rejected because production routing is UNKNOWN.
  - Treating lockForUpdate implementation alone as proof of concurrent exactly-once behavior is rejected; concurrency requires executable multi-connection evidence.
  - PHPStan/test failures were not caused by ticket lifecycle or migrations; exact diagnostics identified two mixed config casts, one list inference issue, and one stale manual ResetIdentityMfa constructor in an existing test.
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
  - docs/contracts/WORLD_REGISTRY_CONTRACT.md
  - tests/Feature/GameAuth/
  - tests/Feature/Identity/Mfa/MfaStateFoundationTest.php
  - tests/Feature/Identity/Recovery/PasswordChangeTest.php
  - tests/Feature/Identity/Recovery/PasswordRecoveryTest.php
validation:
  - command: GitHub list PR 118 changed filenames
    result: PASS
    evidence: changed paths are within declared ownership and the temporary diagnostic workflow is absent from the diff
  - command: diagnostic PHPStan/test capture on run 29874155874
    result: PASS
    evidence: diagnostic artifact provided exact actionable errors; no failure was hidden or guessed
  - command: GitHub Actions CI run 29874507061
    result: PASS
    evidence: Composer validation/audit, Pint, PHPStan and full composer test completed successfully on 32af1ecdf800c570a2ddda3408e0b52ee1e1cb4d
  - command: GitHub Actions Agent Governance run 29874507173
    result: PASS
    evidence: checkpoint/ownership governance completed successfully on 32af1ecdf800c570a2ddda3408e0b52ee1e1cb4d
  - command: GitHub Actions Platform DB Outage Validation run 29874507079
    result: PASS
    evidence: fail-closed database outage and recovery validation completed successfully on 32af1ecdf800c570a2ddda3408e0b52ee1e1cb4d
  - command: GitHub Actions Phase 7 Production-Like Validation run 29874507065
    result: PASS
    evidence: production-like migration, privilege, outage, Redis, SMTP, configuration, critical regression, runtime, backup/restore and upgrade/rollback validation completed successfully on 32af1ecdf800c570a2ddda3408e0b52ee1e1cb4d
  - command: GitHub Actions Acceptance E2E and Visual UX run 29874507100
    result: PASS
    evidence: required bounded acceptance profiles and durable evidence completed successfully on 32af1ecdf800c570a2ddda3408e0b52ee1e1cb4d
  - command: true independent-connection concurrent ticket consume proof
    result: NOT_RUN
    evidence: intentionally deferred to the Phase 3 atomic redeem production gate; this task does not claim concurrent replay proof
blockers:
  - none
next_action: Verify the same required workflows pass on the final checkpoint commit, then merge PR 118 if review/merge gates remain satisfied.
```

## Notes

This task is intentionally Platform-only and non-public. Phase 2 (Passport/OAuth) and Phase 3 (ticket HTTP issuance/redeem) remain separate bounded slices.
