# OTERYN-20260719 MFA State Foundation

## Goal

Implement the smallest safe Phase 3 T3.4 slice: Platform-owned MFA state storage and an internal MFA reset primitive for Oteryn Platform Identity, without exposing enrollment/challenge routes, implementing TOTP cryptography, introducing Admin/RBAC semantics, changing Canary/login-server authentication, or claiming global game-login MFA enforcement.

## Acceptance criteria

- [ ] Add a reversible Platform-owned migration for nullable MFA secret, recovery-code state and confirmation timestamp on `identities`.
- [ ] Protect MFA secret and recovery-code state at rest with Laravel application encryption casts; do not implement custom encryption.
- [ ] Hide MFA secret and recovery-code attributes from model serialization.
- [ ] Add a deterministic `Identity::hasConfirmedMfa()` state check that requires both protected secret material and confirmation timestamp.
- [ ] Add an internal `ResetIdentityMfa` action that clears Platform MFA state transactionally, revokes all Platform web sessions through the existing generation primitive and records minimal security audit events.
- [ ] Add security regression coverage proving raw database values do not equal plaintext secret/recovery values, serialization does not expose them, confirmed-state semantics are fail closed, and MFA reset clears state plus revokes Platform web sessions.
- [ ] Do not add public MFA enrollment, confirmation, challenge, recovery-code consumption or disable routes in this task.
- [ ] Do not add custom TOTP/HOTP implementation or hand-edit `composer.lock`; maintained TOTP provider integration belongs to a follow-up task that can run a real Composer dependency update.
- [ ] Do not add an administrator flag or treat MFA state as authorization/RBAC.
- [ ] Do not modify Canary/login-server repositories, shared credentials, game sessions or game-login policy.
- [ ] Run Composer validation, lockfile install, Pint, PHPStan/Larastan level 10 and the full test suite; inspect current-head CI before readiness.

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
updated_at: 2026-07-19T11:55:00+02:00
head: e1ec8fddd4aedbd847558f223be35212ea11c85f
branch: task/OTERYN-20260719-mfa-state-foundation
pr: none
status: investigating
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
  - Trust boundary affected: Platform Identity persisted MFA state and Platform web-session validity only; no public MFA request boundary is added.
  - Canary/login-server schema or session compatibility changes: none in this task.
  - Rollback requirement: Platform-owned MFA columns must be reversible; no shared-data rollback is involved.
  - Secrets or production-only configuration involved: no real MFA secret/recovery code may be committed, logged or included in task/PR text.
derived:
  - A useful bounded first MFA slice is encrypted state plus internal reset/revocation semantics, because it can be validated without exposing a half-enforced authentication path or inventing TOTP cryptography.
  - Laravel encrypted model casts satisfy the application-encryption-at-rest direction for MFA secret/recovery material without custom encryption code.
unknown:
  - Exact maintained TOTP provider/version to adopt in the follow-up implementation remains undecided until Composer dependency resolution can run against the current lockfile.
  - Exact product policy for optional normal-user MFA versus administrator-only mandatory MFA remains outside this state-foundation task.
  - Exact future global MFA enforcement behavior for Canary/login-server paths remains unknown/blocked.
conflicts:
  - Platform web MFA can be authoritative only for Platform web authentication today; alternate game authentication paths remain a bypass for any global claim.
first_failure:
  marker: none
  evidence: implementation validation has not run yet
rejected_hypotheses:
  - Implement TOTP manually with hash functions/random_bytes: rejected because repository security policy requires framework/maintained security mechanisms before custom cryptography.
  - Add Fortify/Google2FA by editing composer.json only: rejected because CI installs from composer.lock and the lockfile cannot be safely regenerated by hand.
  - Enable public MFA management before login challenge enforcement exists: rejected because that would create configured MFA state without a complete enforced authentication path.
  - Add an admin boolean to make MFA mandatory: rejected because Admin/RBAC is a later module and a single admin flag must not become an authorization shortcut.
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260719-mfa-state-foundation.md
validation:
  - command: PR #13 merge-gate verification and squash merge
    result: PASS
    evidence: PR head 87aec30b783b136e87989b741e787aa3939c4cf1 had CI and Agent Governance success, no review/comment/thread blockers and behind_by 0; squash merge produced e1ec8fddd4aedbd847558f223be35212ea11c85f
  - command: local git clone / Composer dependency update capability
    result: BLOCKED
    evidence: execution environment failed DNS resolution for github.com; no local checkout claim is made and composer.lock will not be hand-edited
  - command: composer validate --strict; composer install --no-interaction --prefer-dist --no-progress; composer format:check; composer analyse; composer test
    result: NOT_RUN
    evidence: T3.4a implementation has not started
blockers:
  - none for Platform-only MFA state/reset foundation
  - public MFA enrollment/challenge/enforcement is intentionally deferred to a follow-up task requiring a maintained TOTP provider and explicit login-pipeline integration
next_action: Archive the merged T3.3 task record, update ACTIVE_WORK, open a draft PR, then implement only the encrypted MFA state and internal reset/revocation primitive within the declared owned paths.
```

## Notes

This task must not produce a user-visible MFA feature. Its purpose is to establish safe durable state and reset semantics so a later complete enrollment/challenge/enforcement task can be implemented without custom cryptography or a half-enforced rollout.
