# OTERYN-20260719 MFA State Foundation

## Goal

Implement the smallest safe Phase 3 T3.4 slice: Platform-owned MFA state storage and an internal MFA reset primitive for Oteryn Platform Identity, without exposing enrollment/challenge routes, implementing TOTP cryptography, introducing Admin/RBAC semantics, changing Canary/login-server authentication, or claiming global game-login MFA enforcement.

## Acceptance criteria

- [x] Add a reversible Platform-owned migration for nullable MFA secret, recovery-code state and confirmation timestamp on `identities`.
- [x] Protect MFA secret and recovery-code state at rest with Laravel application encryption casts; do not implement custom encryption.
- [x] Hide MFA secret and recovery-code attributes from model serialization.
- [x] Add a deterministic `Identity::hasConfirmedMfa()` state check that requires both protected secret material and confirmation timestamp.
- [x] Add an internal `ResetIdentityMfa` action that clears Platform MFA state transactionally, revokes all Platform web sessions through the existing generation primitive and records minimal security audit events.
- [x] Add security regression coverage proving raw database values do not equal plaintext secret/recovery values, serialization does not expose them, confirmed-state semantics are fail closed, and MFA reset clears state plus revokes Platform web sessions.
- [x] Do not add public MFA enrollment, confirmation, challenge, recovery-code consumption or disable routes in this task.
- [x] Do not add custom TOTP/HOTP implementation or hand-edit `composer.lock`; maintained TOTP provider integration belongs to a follow-up task that can run a real Composer dependency update.
- [x] Do not add an administrator flag or treat MFA state as authorization/RBAC.
- [x] Do not modify Canary/login-server repositories, shared credentials, game sessions or game-login policy.
- [x] Run Composer validation, lockfile install, Pint, PHPStan/Larastan level 10 and the full test suite; inspect current-head CI before readiness.

## Ownership

```yaml
owned_paths:
  - app/Identity/Mfa/**
  - app/Identity/Models/Identity.php
  - app/Audit/SecurityEventRecorder.php
  - database/migrations/*add_mfa_state_to_identities_table.php
  - tests/Feature/Identity/Mfa/**
  - tests/Unit/Identity/Mfa/**
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260719-mfa-state-foundation.md
  - docs/agents/tasks/archive/OTERYN-20260719-password-recovery-credentials.md
modules:
  - Identity MFA state
  - Audit (MFA reset event only)
  - security
  - database
  - testing
  - agent-governance
dependencies:
  - OTERYN-20260719-password-recovery-credentials
  - OTERYN-20260719-web-login-sessions
  - docs/architecture/SECURITY_ARCHITECTURE.md
  - docs/contracts/AUTH_GAME_LOGIN_CONTRACT.md
  - OTERYN-20260718-static-analysis-gate
blockers:
  - none for internal Platform-only MFA state/reset foundation
  - enrollment/challenge/enforcement remains outside this task until a maintained TOTP provider can be added with a deterministically generated Composer lockfile and the existing login stack is integrated deliberately
cross_repository_tasks:
  - blakinio/canary and opentibiabr/login-server remain read-only evidence only; no writes are authorized
```

## Security boundary

This task creates only Oteryn Platform MFA state primitives. It does not make MFA active for any user because it exposes no enrollment or verification path.

A later task may use a maintained TOTP provider and these protected fields to implement Platform web MFA. Even then, Platform-only MFA must not be described as a global game-login gate while native Canary and external login-server authentication paths can bypass Platform policy.

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-19T12:28:00+02:00
head: 1d1be8f8233b0d324e12fea9fe404bb6dde30bc9
branch: task/OTERYN-20260719-mfa-state-foundation
pr: 14
status: ready
context_routes:
  - agent-governance
  - auth-identity
  - security
  - database
  - testing
owned_paths:
  - app/Identity/Mfa/**
  - app/Identity/Models/Identity.php
  - app/Audit/SecurityEventRecorder.php
  - database/migrations/*add_mfa_state_to_identities_table.php
  - tests/Feature/Identity/Mfa/**
  - tests/Unit/Identity/Mfa/**
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260719-mfa-state-foundation.md
  - docs/agents/tasks/archive/OTERYN-20260719-password-recovery-credentials.md
proven:
  - PR #13 for OTERYN-20260719-password-recovery-credentials was squash-merged to main as e1ec8fddd4aedbd847558f223be35212ea11c85f after current-head CI and Agent Governance passed, the branch was behind main by zero commits, and no review/comment/thread blocker existed.
  - Current SECURITY_ARCHITECTURE.md requires administrator MFA before production readiness, secure enrollment confirmation, protected MFA secrets at rest, privileged recovery/reset handling and MFA security audit events.
  - Current AUTH_GAME_LOGIN_CONTRACT.md still proves alternate Canary/login-server authentication paths; Platform-only MFA cannot be claimed as global game-login enforcement.
  - Current composer.json contains Laravel framework only as a runtime dependency and repository search finds no installed Fortify/Google2FA package.
  - A local checkout cannot currently be cloned because the execution environment cannot resolve github.com; therefore a deterministic Composer dependency update/lockfile generation is unavailable in this environment.
  - T3.4a adds only Platform-owned nullable two_factor_secret, two_factor_recovery_codes and two_factor_confirmed_at fields; secret and recovery state use Laravel encrypted model casts and are hidden from serialization.
  - Identity::hasConfirmedMfa() fails closed unless both protected secret material and confirmation timestamp are present.
  - ResetIdentityMfa clears all Platform MFA state transactionally, invokes the existing RevokeIdentityWebSessions generation primitive and records identity.mfa_reset without logging MFA secret or recovery material.
  - Security regression coverage verifies encrypted-at-rest persistence, hidden serialization, fail-closed confirmed-state semantics, reset clearing, audit events and rejection of an already-authenticated stale Platform web session after MFA reset.
  - No public MFA enrollment, confirmation, challenge, recovery-code consumption or disable route is introduced; routes/web.php and the existing login pipeline are unchanged.
  - No TOTP/HOTP dependency or implementation is added and composer.json/composer.lock are unchanged.
  - No administrator flag, RBAC semantics, Canary/login-server write or global game-login MFA claim is introduced.
  - PR #14 final code-validation head 1d1be8f8233b0d324e12fea9fe404bb6dde30bc9 passed CI run 29683098224 and Agent Governance run 29683098223.
  - CI run 29683098224 passed Composer validation, lockfile-backed dependency installation, Pint format check, PHPStan/Larastan level 10 and the full test suite.
  - Trust boundary affected: Platform Identity persisted MFA state and Platform web-session validity only; no public MFA request boundary is added.
  - Canary/login-server schema or session compatibility changes: none in this task.
  - Rollback requirement: Platform-owned MFA columns are removed by the migration down path; no shared-data rollback is involved.
  - Secrets or production-only configuration involved: no real MFA secret/recovery code is committed, logged or included in task/PR text.
derived:
  - A useful bounded first MFA slice is encrypted state plus internal reset/revocation semantics, because it can be validated without exposing a half-enforced authentication path or inventing TOTP cryptography.
  - Laravel encrypted model casts satisfy the application-encryption-at-rest direction for MFA secret/recovery material without custom encryption code.
  - The two_factor_* field naming aligns with common Laravel MFA conventions without claiming drop-in compatibility with any not-yet-installed provider; the follow-up task must still integrate the chosen maintained provider deliberately.
unknown:
  - Exact maintained TOTP provider/version to adopt in the follow-up implementation remains undecided until Composer dependency resolution can run against the current lockfile.
  - Exact product policy for optional normal-user MFA versus administrator-only mandatory MFA remains outside this state-foundation task.
  - Exact future global MFA enforcement behavior for Canary/login-server paths remains unknown/blocked.
conflicts:
  - Platform web MFA can be authoritative only for Platform web authentication today; alternate game authentication paths remain a bypass for any global claim.
first_failure:
  marker: CI run 29682625537 / Check formatting
  evidence: composer format:check failed on code-validation predecessor 5280795aa6466b6bd5174a997fdf48346883c77c; Composer validation and lockfile install had already passed, while static analysis/tests were skipped after the formatter failure
rejected_hypotheses:
  - Implement TOTP manually with hash functions/random_bytes: rejected because repository security policy requires framework/maintained security mechanisms before custom cryptography.
  - Add Fortify/Google2FA by editing composer.json only: rejected because CI installs from composer.lock and the lockfile cannot be safely regenerated by hand.
  - Enable public MFA management before login challenge enforcement exists: rejected because that would create configured MFA state without a complete enforced authentication path.
  - Add an admin boolean to make MFA mandatory: rejected because Admin/RBAC is a later module and a single admin flag must not become an authorization shortcut.
changed_paths:
  - app/Audit/SecurityEventRecorder.php
  - app/Identity/Mfa/ResetIdentityMfa.php
  - app/Identity/Models/Identity.php
  - database/migrations/2026_07_19_095700_add_mfa_state_to_identities_table.php
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260719-mfa-state-foundation.md
  - docs/agents/tasks/archive/OTERYN-20260719-password-recovery-credentials.md
  - tests/Feature/Identity/Mfa/MfaStateFoundationTest.php
validation:
  - command: PR #13 merge-gate verification and squash merge
    result: PASS
    evidence: PR head 87aec30b783b136e87989b741e787aa3939c4cf1 had CI and Agent Governance success, no review/comment/thread blockers and behind_by 0; squash merge produced e1ec8fddd4aedbd847558f223be35212ea11c85f
  - command: local git clone / Composer dependency update capability
    result: BLOCKED
    evidence: execution environment failed DNS resolution for github.com; no local checkout claim is made and composer.lock was not hand-edited
  - command: composer validate --strict; composer install --no-interaction --prefer-dist --no-progress; composer format:check; composer analyse; composer test
    result: PASS
    evidence: GitHub Actions CI run 29683098224 passed every required step on code-validation head 1d1be8f8233b0d324e12fea9fe404bb6dde30bc9 after correcting the Pint named-class new-with-parentheses formatting failure from predecessor run 29682625537
  - command: python tools/agents/test_checkpoint.py; python tools/agents/checkpoint.py --tasks docs/agents/tasks/active --require-checkpoint
    result: PASS
    evidence: Agent Governance run 29683098223 passed on code-validation head 1d1be8f8233b0d324e12fea9fe404bb6dde30bc9
blockers:
  - none for Platform-only T3.4a scope
  - public MFA enrollment/challenge/enforcement is intentionally deferred to a follow-up task requiring a maintained TOTP provider and explicit login-pipeline integration
next_action: Revalidate CI and Agent Governance on the final documentation/checkpoint head, inspect PR #14 reviews/comments/threads and branch divergence, then squash-merge only if the full merge gate remains satisfied.
```

## Notes

This task must not produce a user-visible MFA feature. Its purpose is to establish safe durable state and reset semantics so a later complete enrollment/challenge/enforcement task can be implemented without custom cryptography or a half-enforced rollout.