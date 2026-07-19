# OTERYN-20260719 Web Login and Session Management

## Goal

Implement the second bounded Phase 3 slice: secure Oteryn Platform web login/logout and revocable web sessions for Platform-owned Identity accounts, without changing Canary/login-server authentication, issuing game-login credentials, implementing password recovery/MFA, or claiming global game-login policy enforcement.

## Acceptance criteria

- [x] Configure Laravel web authentication to use the Platform-owned `Identity` model and session guard.
- [x] Add canonical-email web login with a generic invalid-credentials response and no remember-me support.
- [x] Regenerate the session identifier after successful authentication to prevent session fixation.
- [x] Add application-level login rate limiting using source IP plus a non-plaintext canonical-identity key.
- [x] Add secure logout that invalidates the current session and regenerates the CSRF token.
- [x] Add a Platform web-session revocation generation so security events can invalidate all existing Platform web sessions without depending on the session storage backend.
- [x] Fail closed when an authenticated Identity is disabled or presents a stale/missing revocation generation.
- [x] Record minimal security audit events for successful login, logout, explicit web-session revocation and rejected stale/disabled sessions without passwords, password hashes or session tokens.
- [x] Preserve `HttpOnly` and `SameSite` cookie protections and make production `Secure` cookie behavior explicit without breaking local HTTP defaults.
- [x] Add negative/security regression coverage for invalid credentials, canonical email login, rate limiting, session ID regeneration, logout invalidation, stale-session rejection and disabled-identity rejection.
- [x] Do not implement remember-me, password reset/change, MFA, admin/RBAC, Canary account writes, Canary/login-server credential migration or game-login authorization.
- [x] Run the repository-required Composer validation, lockfile install, Pint, PHPStan/Larastan level 10 and full test suite; inspect current-head CI before readiness.

## Ownership

```yaml
owned_paths:
  - app/Identity/Sessions/**
  - app/Identity/Actions/RevokeIdentityWebSessions.php
  - app/Identity/Models/Identity.php
  - app/Audit/SecurityEventRecorder.php
  - app/Http/Controllers/Identity/SessionController.php
  - app/Http/Requests/Identity/LoginIdentityRequest.php
  - app/Http/Middleware/EnsureIdentitySessionIsCurrent.php
  - app/Providers/AppServiceProvider.php
  - bootstrap/app.php
  - config/auth.php
  - config/session.php
  - database/migrations/*add_web_session_state_to_identities_table.php
  - resources/views/identity/login.blade.php
  - routes/web.php
  - tests/Feature/Identity/Sessions/**
  - tests/Unit/Identity/Sessions/**
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260719-web-login-sessions.md
  - docs/agents/tasks/archive/OTERYN-20260719-identity-core-registration.md
modules:
  - Identity
  - Audit (web authentication/session events only)
  - HTTP authentication boundary
  - security
  - database
  - testing
  - agent-governance
dependencies:
  - OTERYN-20260719-identity-core-registration
  - docs/contracts/AUTH_GAME_LOGIN_CONTRACT.md
  - docs/architecture/SECURITY_ARCHITECTURE.md
  - OTERYN-20260718-static-analysis-gate
blockers:
  - none for Platform-only web login/logout and web-session revocation
cross_repository_tasks:
  - blakinio/canary and opentibiabr/login-server remain read-only evidence only; no writes are authorized
```

## Security boundary

This task governs only browser-to-Oteryn-Platform web authentication and Platform web-session validity.

It does not make Platform MFA, disabled state, logout or session revocation authoritative for Canary/login-server paths. `AUTH_GAME_LOGIN_CONTRACT.md` blockers remain in force for any global game-login claim.

The web-session revocation primitive introduced here is intended to be reused by later password recovery/MFA tasks for Platform sessions only. Cross-repository session/token classes require their own approved integration work.

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-19T08:20:00Z
head: 4019a33ddd3ddec38748a32203feba27528a0041
branch: task/OTERYN-20260719-web-login-sessions
pr: 12
status: ready
context_routes:
  - agent-governance
  - auth-identity
  - security
  - database
  - testing
owned_paths:
  - app/Identity/Sessions/**
  - app/Identity/Actions/RevokeIdentityWebSessions.php
  - app/Identity/Models/Identity.php
  - app/Audit/SecurityEventRecorder.php
  - app/Http/Controllers/Identity/SessionController.php
  - app/Http/Requests/Identity/LoginIdentityRequest.php
  - app/Http/Middleware/EnsureIdentitySessionIsCurrent.php
  - app/Providers/AppServiceProvider.php
  - bootstrap/app.php
  - config/auth.php
  - config/session.php
  - database/migrations/*add_web_session_state_to_identities_table.php
  - resources/views/identity/login.blade.php
  - routes/web.php
  - tests/Feature/Identity/Sessions/**
  - tests/Unit/Identity/Sessions/**
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260719-web-login-sessions.md
  - docs/agents/tasks/archive/OTERYN-20260719-identity-core-registration.md
proven:
  - PR #11 for OTERYN-20260719-identity-core-registration was squash-merged to main as 6f48cf97288963c25b0ca97563865f5b3514de3b after CI and Agent Governance passed on its final head.
  - PR #12 is the only open pull request returned by live repository PR search during final T3.2 validation; no overlapping live PR was found.
  - Laravel web authentication is configured with a session guard backed by the Platform-owned App Identity model; no Canary credential field is read or written by this task.
  - Canonical-email login verifies exactly one Argon2id hash for both existing and unknown identities by using a non-secret dummy hash for the unknown-identity path, then returns the same public invalid-credentials response for failed authentication.
  - Remember-me is not implemented; successful login explicitly uses remember false and then regenerates the Laravel session identifier before storing the current web-session generation marker.
  - Login is rate-limited both per hashed canonical-identity plus source IP and by a separate source-IP-wide limit to bound password spraying across distinct identity keys.
  - Logout logs out the web guard, invalidates the current session and regenerates the CSRF token before recording the logout audit event.
  - Platform identities have a monotonically increasing web_session_generation and nullable disabled_at security state in a reversible Platform-owned migration.
  - RevokeIdentityWebSessions increments web_session_generation atomically inside a Platform database transaction and records a minimal revocation audit event.
  - EnsureIdentitySessionIsCurrent loads fresh Platform identity security state from the database on authenticated web requests and invalidates the session when the identity is missing, disabled, or presents a stale or missing generation marker.
  - Security audit events added by this task contain identity references and event types only; no password, password hash, session token or game credential is recorded.
  - Session configuration preserves HttpOnly and SameSite lax defaults and defaults Secure cookies on in production when SESSION_SECURE_COOKIE is not explicitly configured.
  - Security regression tests cover generic invalid credentials, canonical login, per-identity and source-wide rate limiting, disabled login, logout invalidation, session revocation, stale/missing generation rejection, disabled-session rejection, cookie defaults and session identifier regeneration.
  - Code-validation head 4019a33ddd3ddec38748a32203feba27528a0041 passed CI run 29680030840 and Agent Governance run 29680030842; CI passed Composer validation, lockfile install, Pint, PHPStan/Larastan level 10 and the full test suite.
  - Trust boundary affected: browser/Internet to Oteryn Platform web authentication and Platform session state only.
  - Authentication invariant affected: successful web authentication rotates session identity; logout invalidates the current session; stale, missing-generation and disabled Platform Identity sessions fail closed.
  - Canary/login-server schema or session compatibility changes: none in this task.
  - Rollback requirement: the Platform-owned web-session-state migration is reversible; no shared-data rollback is involved.
  - Secrets or production-only configuration involved: none; no real credentials or session tokens were committed or logged.
derived:
  - The monotonically increasing Platform web-session generation provides storage-backend-independent logical revocation of Platform web sessions because validity is checked against fresh Platform database state rather than physical session-row deletion.
  - Platform web logout, disabled state and revocation cannot be described as global account logout or global game-session revocation while alternate Canary/login-server credential and session paths remain outside this integration.
unknown:
  - Exact deployed production authentication topology and login-server image digest remain unknown.
  - Exact future global revocation behavior for Canary one-time tokens, account_sessions and active game sessions remains unknown/blocked.
conflicts:
  - Native Canary and upstream external login-server retain incompatible credential verification capabilities documented by AUTH_GAME_LOGIN_CONTRACT.md.
first_failure:
  marker: PHPStan/Larastan level-10 failure on initial T3.2 implementation
  evidence: CI run 29679198662 passed Composer validation, lockfile install and Pint, then failed static analysis before tests; deterministic isolation identified LoginIdentityRequest typing plus an earlier auth-guard wrapper as first-party causes, both fixed without a baseline or ignore rule.
rejected_hypotheses:
  - Platform web logout can be described as global account logout: rejected because Canary/login-server credential/session revocation is not integrated or proven.
  - Remember-me may be enabled by default: rejected because the Phase 3 scope requires it only when explicitly approved, and no such approval is present.
  - New security tests caused the PHPStan failure: rejected because PHPStan still failed when the new test files were temporarily removed during deterministic isolation.
  - config/auth.php caused the PHPStan failure: rejected because the auth-config-only isolation head passed PHPStan and the full existing test suite.
  - A PHPStan baseline or ignore is required: rejected because the first-party typing findings were fixed directly and code-validation head 4019a33ddd3ddec38748a32203feba27528a0041 passes level 10 unchanged.
changed_paths:
  - app/Audit/SecurityEventRecorder.php
  - app/Http/Controllers/Identity/SessionController.php
  - app/Http/Middleware/EnsureIdentitySessionIsCurrent.php
  - app/Http/Requests/Identity/LoginIdentityRequest.php
  - app/Identity/Actions/RevokeIdentityWebSessions.php
  - app/Identity/Models/Identity.php
  - app/Identity/Sessions/IdentityWebSessionManager.php
  - app/Identity/Sessions/WebSessionState.php
  - app/Providers/AppServiceProvider.php
  - bootstrap/app.php
  - config/auth.php
  - config/session.php
  - database/migrations/2026_07_19_075800_add_web_session_state_to_identities_table.php
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260719-web-login-sessions.md
  - docs/agents/tasks/archive/OTERYN-20260719-identity-core-registration.md
  - resources/views/identity/login.blade.php
  - routes/web.php
  - tests/Feature/Identity/Sessions/WebSessionTest.php
  - tests/Unit/Identity/Sessions/IdentityWebSessionManagerTest.php
validation:
  - command: PR #11 merge-gate verification and squash merge
    result: PASS
    evidence: final PR #11 head CI and Agent Governance passed; no review/comment blockers; squash merge produced main commit 6f48cf97288963c25b0ca97563865f5b3514de3b
  - command: composer validate --strict; composer install --no-interaction --prefer-dist --no-progress; composer format:check; composer analyse; composer test
    result: PASS
    evidence: GitHub Actions CI run 29680030840 on code-validation head 4019a33ddd3ddec38748a32203feba27528a0041 passed every required step
  - command: Agent Governance
    result: PASS
    evidence: GitHub Actions run 29680030842 on code-validation head 4019a33ddd3ddec38748a32203feba27528a0041
  - command: git status; git remote -v; git worktree list in a local checkout
    result: BLOCKED
    evidence: no local checkout is exposed in this environment; live GitHub state is used and no local working-tree claim is made
blockers:
  - none for Platform-only T3.2 scope
  - global Canary/login-server credential and game-session revocation remains blocked and explicitly outside this task
next_action: Revalidate CI and Agent Governance on the checkpoint/documentation head, then inspect PR #12 reviews and squash-merge only if the merge gate remains fully satisfied.
```

## Notes

A valid Platform web session does not imply a valid Canary game-login session. Revoking a Platform web session does not currently revoke Canary/login-server credentials or active game sessions.
