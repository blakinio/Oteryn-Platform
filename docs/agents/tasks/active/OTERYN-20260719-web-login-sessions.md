# OTERYN-20260719 Web Login and Session Management

## Goal

Implement the second bounded Phase 3 slice: secure Oteryn Platform web login/logout and revocable web sessions for Platform-owned Identity accounts, without changing Canary/login-server authentication, issuing game-login credentials, implementing password recovery/MFA, or claiming global game-login policy enforcement.

## Acceptance criteria

- [ ] Configure Laravel web authentication to use the Platform-owned `Identity` model and session guard.
- [ ] Add canonical-email web login with a generic invalid-credentials response and no remember-me support.
- [ ] Regenerate the session identifier after successful authentication to prevent session fixation.
- [ ] Add application-level login rate limiting using source IP plus a non-plaintext canonical-identity key.
- [ ] Add secure logout that invalidates the current session and regenerates the CSRF token.
- [ ] Add a Platform web-session revocation generation (or equivalent framework-compatible primitive) so security events can invalidate all existing Platform web sessions without depending on the session storage backend.
- [ ] Fail closed when an authenticated Identity is disabled or presents a stale/missing revocation generation.
- [ ] Record minimal security audit events for successful login, logout, explicit web-session revocation and rejected stale/disabled sessions without passwords, password hashes or session tokens.
- [ ] Preserve `HttpOnly` and `SameSite` cookie protections and keep the production `Secure` cookie requirement explicit; do not weaken current session configuration.
- [ ] Add negative/security regression coverage for invalid credentials, canonical email login, rate limiting, session ID regeneration, logout invalidation, stale-session rejection and disabled-identity rejection.
- [ ] Do not implement remember-me, password reset/change, MFA, admin/RBAC, Canary account writes, Canary/login-server credential migration or game-login authorization.
- [ ] Run the repository-required Composer validation, lockfile install, Pint, PHPStan/Larastan level 10 and full test suite; inspect current-head CI before readiness.

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
updated_at: 2026-07-19T07:56:24Z
head: f915a25c63d3cc1885b745bdf7fddc94bb89d8de
branch: task/OTERYN-20260719-web-login-sessions
pr: none
status: investigating
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
  - PR #11 had no submitted reviews, no inline review threads and no conversation comments at merge time.
  - The completed T3.1 task record was moved from docs/agents/tasks/active to docs/agents/tasks/archive on this branch without changing its blob contents.
  - No open pull request was returned by live repository PR search after PR #11 merged.
  - Platform Identity credentials are stored only in the Platform database and are not written to Canary accounts.password.
  - AUTH_GAME_LOGIN_CONTRACT.md still blocks global credential migration, global cross-session revocation and game-login policy claims because alternate Canary/login-server paths remain.
  - Trust boundary affected: browser/Internet to Oteryn Platform web authentication and Platform session state only.
  - Authentication invariant affected: successful web authentication must rotate session identity, logout must invalidate the current session, and stale/disabled Platform Identity sessions must fail closed.
  - Canary/login-server schema or session compatibility changes: none in this task.
  - Rollback requirement: Platform-owned schema changes must be reversible; no shared-data rollback is involved.
  - Secrets or production-only configuration involved: none; no real credentials or session tokens may be committed or logged.
derived:
  - A monotonically increasing Platform web-session generation can provide backend-independent logical revocation of Platform web sessions while leaving Canary/login-server revocation explicitly out of scope.
  - A Platform-only disabled state can deny web sessions but cannot be claimed to disable all game-login paths until the cross-repository auth contract is implemented.
unknown:
  - Exact deployed production authentication topology and login-server image digest remain unknown.
  - Exact future global revocation behavior for Canary one-time tokens, account_sessions and active game sessions remains unknown/blocked.
conflicts:
  - Native Canary and upstream external login-server retain incompatible credential verification capabilities documented by AUTH_GAME_LOGIN_CONTRACT.md.
first_failure:
  marker: none
  evidence: T3.2 implementation validation has not run yet
rejected_hypotheses:
  - Platform web logout can be described as global account logout: rejected because Canary/login-server credential/session revocation is not integrated or proven.
  - Remember-me may be enabled by default: rejected because the Phase 3 scope requires it only when explicitly approved, and no such approval is present.
changed_paths:
  - docs/agents/tasks/archive/OTERYN-20260719-identity-core-registration.md
  - docs/agents/tasks/active/OTERYN-20260719-web-login-sessions.md
validation:
  - command: PR #11 merge-gate verification
    result: PASS
    evidence: final PR head CI and Agent Governance passed; no review/comment blockers; squash merge produced main commit 6f48cf97288963c25b0ca97563865f5b3514de3b
  - command: git status; git remote -v; git worktree list in a local checkout
    result: BLOCKED
    evidence: no local checkout is exposed in this environment; live GitHub state is used and no local working-tree claim is made
  - command: composer validate --strict; composer install --no-interaction --prefer-dist --no-progress; composer format:check; composer analyse; composer test
    result: NOT_RUN
    evidence: T3.2 implementation has not started
blockers:
  - none for Platform-only T3.2 scope
next_action: Open the T3.2 draft PR, then implement the Platform web authentication/session slice strictly within the declared owned paths.
```

## Notes

A valid Platform web session does not imply a valid Canary game-login session. Revoking a Platform web session does not currently revoke Canary/login-server credentials or active game sessions.