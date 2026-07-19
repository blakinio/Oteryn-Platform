# OTERYN-20260719 Platform Web MFA

## Goal

Implement the complete safe Phase 3 T3.4c Oteryn Platform web MFA lifecycle on top of the merged encrypted MFA state foundation and maintained `pragmarx/google2fa` provider: secure enrollment confirmation, second-factor login challenge, replay-resistant TOTP verification, single-use recovery codes, rate limiting, audit, session revocation/rotation and authenticated MFA disable.

This task is strictly a Platform web-authentication control. It does not change Canary/login-server authentication and must not be described as global game-login MFA enforcement.

## Acceptance criteria

- [x] Archive merged `OTERYN-20260719-mfa-totp-provider-resolution` without changing its contents and update `ACTIVE_WORK.md` to this task.
- [x] Add reversible Platform-owned persistence for the last successfully consumed TOTP timestep so a confirmed TOTP cannot be replayed.
- [x] Add authenticated MFA enrollment that generates a secret only through the maintained `pragmarx/google2fa` provider, protects it with the existing encrypted model cast, and does not mark MFA confirmed until a valid TOTP is submitted.
- [x] Generate recovery codes only after successful enrollment confirmation; return plaintext codes only in that one response, store only framework-hashed recovery-code values inside the encrypted recovery-code state, and never log them.
- [x] Revoke other Platform web sessions on successful MFA enrollment while preserving the current authenticated browser by rotating/re-establishing its session generation marker.
- [x] Change password login so identities with confirmed MFA remain unauthenticated in a short-lived server-side pending-login state until a valid second factor is consumed; identities without confirmed MFA keep the current login behavior.
- [x] Bind pending MFA login to identity ID, current web-session generation and a bounded lifetime; fail closed if the identity is disabled, MFA state changes, session generation changes or the pending state expires.
- [x] Verify TOTP with `Google2FA::verifyKeyNewer()` under row locking and persist the returned timestep atomically so concurrent or repeated reuse of the same accepted timestep fails.
- [x] Support single-use recovery-code login under row locking and atomically remove the consumed recovery-code hash.
- [x] Apply application-level rate limiting to MFA challenge, enrollment confirmation and MFA disable/recovery attempts.
- [x] Add minimal audit events for MFA enrollment, recovery-code use and MFA disable without recording secrets, TOTP values or recovery codes.
- [x] Add authenticated MFA disable that requires the current password plus a valid TOTP/recovery code, clears MFA state transactionally, revokes all Platform web sessions and leaves the current browser logged out.
- [x] Preserve CSRF protection and regenerate session identifiers on password-to-MFA and MFA-to-authenticated security transitions.
- [x] Add security regression coverage for unconfirmed enrollment, challenge gating, pending-state expiry/revocation, TOTP replay, persisted timestep semantics, recovery-code single use, rate limiting, enrollment session revocation, disable/logout and non-MFA login compatibility.
- [x] Run Composer validation, lockfile installation, Pint, PHPStan/Larastan level 10 and the full test suite on the exact code-validation head; inspect current-head Agent Governance before readiness.
- [x] Do not add Admin/RBAC semantics, an administrator boolean, custom TOTP/HOTP cryptography, Canary/login-server writes or any global game-login MFA claim.

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
  - .github/workflows/mfa-static-diagnostics.yml
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260719-platform-web-mfa.md
  - docs/agents/tasks/active/OTERYN-20260719-mfa-totp-provider-resolution.md
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

This task enforces MFA only for Oteryn Platform web identities that have confirmed MFA state. It does not claim that Canary native login, external login-server, old-protocol password login or game-session authorization is MFA-gated.

Enrollment confirmation, TOTP/recovery verification and disable are security-sensitive state transitions. They fail closed, use transactions/row locking where replay or single-use semantics are involved, preserve CSRF and do not expose secret material through audit/logging.

Administrator MFA remains a production-readiness requirement, but no Admin/RBAC authority exists yet. This task provides the web MFA mechanism without creating an authorization shortcut or self-assignable administrator marker.

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-19T14:30:00+02:00
head: 29be1190dc9de47a6f727b4811c6afb5d17ff8d1
branch: task/OTERYN-20260719-platform-web-mfa
pr: 16
status: ready
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
  - .github/workflows/mfa-static-diagnostics.yml
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260719-platform-web-mfa.md
  - docs/agents/tasks/active/OTERYN-20260719-mfa-totp-provider-resolution.md
  - docs/agents/tasks/archive/OTERYN-20260719-mfa-totp-provider-resolution.md
proven:
  - Current task started from main d4cc4189cbc99f47b4cec69ce198bd5ded43d719 after PR #15 merged the real Composer-resolved pragmarx/google2fa v9.0.0 dependency.
  - The merged T3.4b task record was moved from active to archive using its exact original blob 8a8db9be0d0ecb40728a45226c0b822020c9d704; ACTIVE_WORK points to PR #16.
  - Password authentication for a confirmed-MFA Identity now stops before Auth::login, regenerates the session and stores only a bounded pending challenge bound to identity ID, current web_session_generation, MFA confirmation timestamp and issue time.
  - Pending challenge state expires after five minutes and second-factor completion fails closed if the identity is missing, disabled, MFA state changed or web_session_generation changed.
  - TOTP consumption uses maintained Google2FA verifyKeyNewer under an Identity row lock and persists two_factor_last_used_timestep atomically; the migration is reversible.
  - Recovery codes are generated only after successful enrollment confirmation, returned in plaintext only in the direct no-store response, framework-hashed before encrypted persistence, consumed once under row locking and removed atomically.
  - Enrollment confirmation requires the current password plus a fresh provider-verified TOTP, records identity.mfa_enrolled, revokes Platform web sessions and re-establishes the current browser with the incremented generation marker.
  - MFA challenge has per-identity-and-source, identity-only and source-only rate limits; enrollment confirmation and disable have authenticated identity/source rate limits.
  - MFA settings, challenge and one-time recovery-code responses send Cache-Control no-store/private and Pragma no-cache.
  - MFA disable requires the current password plus a valid TOTP or recovery code, clears all MFA state, revokes all Platform web sessions, records identity.mfa_disabled and logs out the current browser.
  - MFA audit records only event type, identity ID and timestamp; no TOTP value, secret or recovery code is passed to SecurityEventRecorder.
  - Security regression tests cover enrollment confirmation, encrypted/hash-only recovery state behavior, unconfirmed enrollment behavior, challenge gating, persisted TOTP replay rejection, recovery-code single use, pending expiry, generation invalidation, challenge rate limiting, enrollment session generation rotation, disable/logout and wrong-password rejection.
  - Temporary task-owned MFA Static Diagnostics workflow run 29686474343 produced artifact 8442244658 to expose the truncated PHPStan report; every exact reported static-analysis issue was fixed and the workflow was deleted before code validation.
  - Final code-validation head 29be1190dc9de47a6f727b4811c6afb5d17ff8d1 contains no mfa-static-diagnostics workflow in its tree or intended final diff.
  - CI run 29686674274 (#241) passed Composer validation, lockfile-backed install, Pint, PHPStan/Larastan level 10 and the full test suite on exact code-validation head 29be1190dc9de47a6f727b4811c6afb5d17ff8d1.
  - Agent Governance run 29686674301 (#162) passed on exact code-validation head 29be1190dc9de47a6f727b4811c6afb5d17ff8d1.
  - No Admin/RBAC semantic, administrator boolean, custom TOTP/HOTP implementation, Canary/login-server write, game-login policy change or global game-login MFA claim is introduced.
derived:
  - Deferring Auth::login until successful second-factor consumption prevents middleware/application code from treating a password-only MFA session as authenticated.
  - Persisting the last accepted TOTP timestep and updating it under a database row lock makes replay protection durable across requests and serializes concurrent consumption for the same Identity.
  - Hashing recovery codes in addition to encrypting the containing state limits plaintext recovery-code exposure even if decrypted application state is inspected.
unknown:
  - Final product policy for requiring MFA from normal users remains outside this task; this implementation enforces MFA for identities that have confirmed enrollment.
  - Mandatory administrator MFA policy still requires future explicit Admin/RBAC identity classification.
conflicts:
  - Platform web MFA cannot be claimed as global game-login MFA while alternate Canary/login-server auth paths remain available.
first_failure:
  marker: CI run 29686245679 (#238) / Run static analysis
  evidence: Composer validation, lockfile install and Pint passed on e868e278257126952544dceb2ac1a1d12dfc60d3, but PHPStan failed and tests were skipped; CI run 29686349648 (#239) still failed at PHPStan after an initial boundary-type narrowing.
rejected_hypotheses:
  - Add enrollment without login challenge enforcement: rejected because it creates configured MFA state without an enforced authentication path.
  - Authenticate with Auth::login before MFA challenge: rejected because middleware and application code would treat the session as fully authenticated during the second-factor step.
  - Implement a custom TOTP/HOTP algorithm: rejected because a maintained provider is installed and repository security policy prefers maintained/framework mechanisms.
  - Add an admin boolean solely to force MFA: rejected because Admin/RBAC is not implemented and a boolean must not become an authorization shortcut.
  - Keep guessing PHPStan failures from truncated logs: rejected after two exact-head failures; a temporary task-owned diagnostic artifact was used and removed before validation.
changed_paths:
  - app/Audit/SecurityEventRecorder.php
  - app/Http/Controllers/Identity/SessionController.php
  - app/Http/Controllers/Identity/Mfa/MfaChallengeController.php
  - app/Http/Controllers/Identity/Mfa/MfaEnrollmentController.php
  - app/Http/Requests/Identity/Mfa/ConfirmMfaEnrollmentRequest.php
  - app/Http/Requests/Identity/Mfa/DisableMfaRequest.php
  - app/Http/Requests/Identity/Mfa/MfaChallengeRequest.php
  - app/Identity/Mfa/ConfirmIdentityMfaEnrollment.php
  - app/Identity/Mfa/DisableIdentityMfa.php
  - app/Identity/Mfa/MfaCodeConsumer.php
  - app/Identity/Mfa/MfaCodeRejected.php
  - app/Identity/Mfa/MfaEnrollmentConfirmation.php
  - app/Identity/Mfa/MfaFactor.php
  - app/Identity/Mfa/MfaProvisioningUri.php
  - app/Identity/Mfa/MfaRecoveryCodes.php
  - app/Identity/Mfa/MfaStateRejected.php
  - app/Identity/Mfa/PendingMfaLogin.php
  - app/Identity/Mfa/ResetIdentityMfa.php
  - app/Identity/Mfa/StartIdentityMfaEnrollment.php
  - app/Identity/Models/Identity.php
  - app/Providers/AppServiceProvider.php
  - database/migrations/2026_07_19_135000_add_mfa_replay_state_to_identities_table.php
  - resources/views/identity/mfa/challenge.blade.php
  - resources/views/identity/mfa/recovery-codes.blade.php
  - resources/views/identity/mfa/settings.blade.php
  - routes/web.php
  - tests/Feature/Identity/Mfa/MfaStateFoundationTest.php
  - tests/Feature/Identity/Mfa/MfaWebFlowTest.php
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260719-platform-web-mfa.md
  - docs/agents/tasks/active/OTERYN-20260719-mfa-totp-provider-resolution.md
  - docs/agents/tasks/archive/OTERYN-20260719-mfa-totp-provider-resolution.md
validation:
  - command: composer validate --strict; composer install --no-interaction --prefer-dist --no-progress; composer format:check; composer analyse; composer test
    result: PASS
    evidence: CI run 29686674274 (#241) completed every required step successfully on exact code-validation head 29be1190dc9de47a6f727b4811c6afb5d17ff8d1
  - command: temporary MFA Static Diagnostics / phpstan --error-format=raw artifact
    result: PASS
    evidence: workflow run 29686474343 uploaded artifact 8442244658; its exact findings were fixed, and the temporary workflow was deleted before code-validation head 29be1190dc9de47a6f727b4811c6afb5d17ff8d1
  - command: python tools/agents/test_checkpoint.py; python tools/agents/checkpoint.py --tasks docs/agents/tasks/active --require-checkpoint
    result: PASS
    evidence: Agent Governance run 29686674301 (#162) passed on exact code-validation head 29be1190dc9de47a6f727b4811c6afb5d17ff8d1
blockers:
  - none for the complete optional/user-enabled Platform web MFA lifecycle implemented by T3.4c
  - mandatory admin-MFA policy awaits explicit Admin/RBAC classification
  - global game-login MFA remains outside Platform-only authority
next_action: Revalidate normal CI and Agent Governance on the final documentation/checkpoint head, then inspect PR #16 divergence, final diff, reviews/comments/threads, mergeability and security boundary before squash-merging with the exact expected head SHA.
```

## Notes

T3.4c is one complete Platform web-authentication slice: enrollment is considered complete only because the same PR also enforces the second factor for confirmed-MFA web identities and provides replay-resistant TOTP, single-use recovery codes, rate limiting, audit and session revocation semantics.
