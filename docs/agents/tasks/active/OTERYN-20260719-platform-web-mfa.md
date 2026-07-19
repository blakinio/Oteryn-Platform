# OTERYN-20260719 Platform Web MFA

## Goal

Implement the complete safe Phase 3 T3.4c Oteryn Platform web MFA lifecycle on top of the merged encrypted MFA state foundation and maintained `pragmarx/google2fa` provider: secure enrollment confirmation, second-factor login challenge, replay-resistant TOTP verification, single-use recovery codes, rate limiting, audit, session revocation/rotation and authenticated MFA disable.

This task is strictly a Platform web-authentication control. It does not change Canary/login-server authentication and must not be described as global game-login MFA enforcement.

## Acceptance criteria

- [ ] Archive merged `OTERYN-20260719-mfa-totp-provider-resolution` without changing its contents and update `ACTIVE_WORK.md` to this task.
- [ ] Add reversible Platform-owned persistence for the last successfully consumed TOTP timestep so a confirmed TOTP cannot be replayed.
- [ ] Add authenticated MFA enrollment that generates a secret only through the maintained `pragmarx/google2fa` provider, protects it with the existing encrypted model cast, and does not mark MFA confirmed until a valid TOTP is submitted.
- [ ] Generate recovery codes only after successful enrollment confirmation; return plaintext codes only in that one response, store only framework-hashed recovery-code values inside the encrypted recovery-code state, and never log them.
- [ ] Revoke other Platform web sessions on successful MFA enrollment while preserving the current authenticated browser by rotating/re-establishing its session generation marker.
- [ ] Change password login so identities with confirmed MFA remain unauthenticated in a short-lived server-side pending-login state until a valid second factor is consumed; identities without confirmed MFA keep the current login behavior.
- [ ] Bind pending MFA login to identity ID, current web-session generation and a bounded lifetime; fail closed if the identity is disabled, MFA state changes, session generation changes or the pending state expires.
- [ ] Verify TOTP with `Google2FA::verifyKeyNewer()` under row locking and persist the returned timestep atomically so concurrent or repeated reuse of the same accepted timestep fails.
- [ ] Support single-use recovery-code login under row locking and atomically remove the consumed recovery-code hash.
- [ ] Apply application-level rate limiting to MFA challenge, enrollment confirmation and MFA disable/recovery attempts.
- [ ] Add minimal audit events for MFA enrollment, recovery-code use and MFA disable without recording secrets, TOTP values or recovery codes.
- [ ] Add authenticated MFA disable that requires the current password plus a valid TOTP/recovery code, clears MFA state transactionally, revokes all Platform web sessions and leaves the current browser logged out.
- [ ] Preserve CSRF protection and regenerate session identifiers on password-to-MFA and MFA-to-authenticated security transitions.
- [ ] Add security regression coverage for unconfirmed enrollment, challenge gating, pending-state expiry/revocation, TOTP replay, concurrent-safe persisted timestep semantics, recovery-code single use, rate limiting, enrollment session revocation, disable/logout and non-MFA login compatibility.
- [ ] Run Composer validation, lockfile installation, Pint, PHPStan/Larastan level 10 and the full test suite on the exact final head; inspect current-head Agent Governance before readiness.
- [ ] Do not add Admin/RBAC semantics, an administrator boolean, custom TOTP/HOTP cryptography, Canary/login-server writes or any global game-login MFA claim.

## Ownership

```yaml
owned_paths:
  - app/Audit/SecurityEventRecorder.php
  - app/Http/Controllers/Identity/SessionController.php
  - app/Http/Controllers/Identity/Mfa/**
  - app/Http/Requests/Identity/Mfa/**
  - app/Identity/Mfa/**
  - app/Identity/Models/Identity.php
  - app/Providers/AppServiceProvider.php
  - database/migrations/*add_mfa_replay_state_to_identities_table.php
  - resources/views/identity/mfa/**
  - routes/web.php
  - tests/Feature/Identity/Mfa/**
  - tests/Unit/Identity/Mfa/**
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260719-platform-web-mfa.md
  - docs/agents/tasks/archive/OTERYN-20260719-mfa-totp-provider-resolution.md
modules:
  - Identity MFA
  - Identity web sessions
  - Audit (MFA security events only)
  - security
  - database
  - testing
  - agent-governance
dependencies:
  - OTERYN-20260719-mfa-state-foundation
  - OTERYN-20260719-mfa-totp-provider-resolution
  - OTERYN-20260719-web-login-sessions
  - docs/architecture/SECURITY_ARCHITECTURE.md
  - docs/contracts/AUTH_GAME_LOGIN_CONTRACT.md
  - pragmarx/google2fa v9.0.0
blockers:
  - none for optional/user-enabled Platform web MFA lifecycle
  - mandatory administrator MFA policy remains coupled to future explicit Admin/RBAC identity classification and must not be invented as a boolean in this task
  - global game-login MFA enforcement remains blocked by alternate Canary/login-server authentication paths
cross_repository_tasks:
  - blakinio/canary and opentibiabr/login-server remain read-only evidence only; no writes are authorized
```

## Security boundary

This task may enforce MFA only for Oteryn Platform web identities that have confirmed MFA state. It must not claim that Canary native login, external login-server, old-protocol password login or game-session authorization is MFA-gated.

Enrollment confirmation, TOTP/recovery verification and disable are security-sensitive state transitions. They must fail closed, use transactions/locking where replay or single-use semantics are involved, preserve CSRF and never expose secret material through audit/logging.

Administrator MFA remains a production-readiness requirement, but no Admin/RBAC authority exists yet. This task provides the web MFA mechanism without creating an authorization shortcut or self-assignable administrator marker.

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-19T13:49:26+02:00
head: d4cc4189cbc99f47b4cec69ce198bd5ded43d719
branch: task/OTERYN-20260719-platform-web-mfa
pr: none
status: investigating
context_routes:
  - agent-governance
  - auth-identity
  - security
  - database
  - testing
owned_paths:
  - app/Audit/SecurityEventRecorder.php
  - app/Http/Controllers/Identity/SessionController.php
  - app/Http/Controllers/Identity/Mfa/**
  - app/Http/Requests/Identity/Mfa/**
  - app/Identity/Mfa/**
  - app/Identity/Models/Identity.php
  - app/Providers/AppServiceProvider.php
  - database/migrations/*add_mfa_replay_state_to_identities_table.php
  - resources/views/identity/mfa/**
  - routes/web.php
  - tests/Feature/Identity/Mfa/**
  - tests/Unit/Identity/Mfa/**
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260719-platform-web-mfa.md
  - docs/agents/tasks/archive/OTERYN-20260719-mfa-totp-provider-resolution.md
proven:
  - Current main is d4cc4189cbc99f47b4cec69ce198bd5ded43d719 from merged PR #15.
  - PR #15 resolved pragmarx/google2fa v9.0.0 and paragonie/constant_time_encoding v3.1.3 through real Composer resolution and passed final CI and Agent Governance.
  - No open Oteryn Platform PR overlaps this task at task start.
  - Existing SessionController authenticates the Identity by password and immediately calls Auth::login through IdentityWebSessionManager before establishing the web-session generation marker.
  - Existing Identity MFA state uses encrypted two_factor_secret and encrypted:array two_factor_recovery_codes plus two_factor_confirmed_at; hasConfirmedMfa requires both secret and confirmation timestamp.
  - Existing ResetIdentityMfa clears MFA state, revokes Platform web sessions and records identity.mfa_reset, but no public MFA route exists.
  - Existing EnsureIdentitySessionIsCurrent fails closed for missing/stale web_session_generation and disabled identities.
  - Google2FA v9.0.0 source exposes generateSecretKey() and verifyKeyNewer(), where verifyKeyNewer prevents accepting a timestep at or before the supplied old timestamp.
  - SECURITY_ARCHITECTURE requires secure enrollment confirmation, MFA verification/recovery rate limiting, audit, secret protection at rest and administrator MFA before production readiness.
  - AUTH_GAME_LOGIN_CONTRACT proves alternate Canary/login-server password/session paths, so Platform MFA cannot be represented as global game-login enforcement.
derived:
  - Confirmed-MFA password login must stop before Auth::login and use a server-side pending state so the browser remains a guest until the second factor succeeds.
  - Persisting the last accepted TOTP timestep and consuming it under a database row lock is required to make verifyKeyNewer replay protection durable across requests and concurrent workers.
  - Enrollment should revoke other web sessions but re-establish the current session after generation increment so the security transition does not unexpectedly log out the browser performing enrollment.
  - A direct provider integration is narrower and safer than introducing Fortify routes into the existing custom authentication stack.
unknown:
  - Final product UX/policy for requiring MFA from normal users remains outside this task; implementation will be opt-in for identities that enroll.
conflicts:
  - Platform web MFA cannot be claimed as global game-login MFA while alternate Canary/login-server auth paths remain available.
first_failure:
  marker: none
  evidence: implementation validation has not run yet
rejected_hypotheses:
  - Add enrollment without login challenge enforcement: rejected because it creates configured MFA state without an enforced authentication path.
  - Authenticate with Auth::login before MFA challenge: rejected because middleware and application code would treat the session as fully authenticated during the second-factor step.
  - Implement a custom TOTP/HOTP algorithm: rejected because a maintained provider is now installed and repository security policy prefers maintained/framework mechanisms.
  - Add an admin boolean solely to force MFA: rejected because Admin/RBAC is not implemented and a boolean must not become an authorization shortcut.
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260719-platform-web-mfa.md
validation:
  - command: implementation validation
    result: NOT_RUN
    evidence: task branch has only the initial task record
blockers:
  - none for optional/user-enabled Platform web MFA lifecycle
  - mandatory admin-MFA policy awaits explicit Admin/RBAC classification
  - global game-login MFA remains outside Platform-only authority
next_action: Archive the merged T3.4b task record, update ACTIVE_WORK, open a draft PR, then implement the complete Platform web MFA lifecycle only within declared owned_paths.
```

## Notes

The task is deliberately one complete web-authentication slice: enrollment is not considered complete unless the same PR also enforces the second factor at login for confirmed-MFA identities and provides safe replay/recovery/session semantics.
