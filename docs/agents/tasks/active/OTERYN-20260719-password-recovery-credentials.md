# OTERYN-20260719 Password Recovery and Credential Changes

## Goal

Implement the third bounded Phase 3 slice for Platform-owned Identity credentials: forgot/reset password and authenticated password change with framework-backed reset tokens, atomic single-use reset completion, Platform web-session revocation and security audit, without modifying Canary/login-server credentials or claiming global game-login revocation.

## Acceptance criteria

- [ ] Configure Laravel's maintained password broker for the Platform `Identity` provider and Platform-owned reset-token storage.
- [ ] Make `Identity` reset-capable through Laravel framework contracts/traits; do not implement custom token cryptography.
- [ ] Add forgot-password request flow with canonical email normalization, generic public response and application rate limits.
- [ ] Ensure the default development mail configuration does not log reset links/tokens; keep production mail transport externally configurable without committing secrets.
- [ ] Add reset-password flow using framework token validation with expiry, hashed token storage and single-use semantics.
- [ ] Wrap successful reset password mutation, Platform web-session generation revocation, audit and reset-token deletion in one Platform database transaction so reset completion is atomic on the shared Platform connection.
- [ ] Add authenticated password change requiring the current password and the authoritative Platform password policy.
- [ ] Revoke all Platform web sessions after successful password change/reset; invalidate the current browser session after password change.
- [ ] Centralize the Platform password policy so registration, password change and password reset use one rule definition.
- [ ] Add security audit events for password change and completed password reset without passwords, hashes or reset tokens.
- [ ] Add negative/security regression coverage for generic forgot-password response, rate limiting, invalid token, expired token, replayed token, single-use successful reset, old-password rejection, current-session invalidation, stale-session rejection after reset, weak password, confirmation mismatch and wrong-current-password denial.
- [ ] Do not implement credential migration, Canary `accounts.password` changes, `account_sessions` deletion, Canary LoginSessionManager token revocation, active-game disconnection, MFA, Admin/RBAC or game-login authorization.
- [ ] Run Composer validation, lockfile install, Pint, PHPStan/Larastan level 10 and the full test suite; inspect current-head CI before readiness.

## Ownership

```yaml
owned_paths:
  - app/Identity/Credentials/**
  - app/Identity/Support/IdentityPasswordPolicy.php
  - app/Identity/Models/Identity.php
  - app/Audit/SecurityEventRecorder.php
  - app/Http/Controllers/Identity/PasswordRecoveryController.php
  - app/Http/Controllers/Identity/PasswordResetController.php
  - app/Http/Controllers/Identity/PasswordChangeController.php
  - app/Http/Requests/Identity/ForgotPasswordRequest.php
  - app/Http/Requests/Identity/ResetPasswordRequest.php
  - app/Http/Requests/Identity/ChangePasswordRequest.php
  - app/Http/Requests/Identity/RegisterIdentityRequest.php
  - app/Providers/AppServiceProvider.php
  - config/auth.php
  - config/mail.php
  - .env.example
  - database/migrations/*create_password_reset_tokens_table.php
  - resources/views/identity/forgot-password.blade.php
  - resources/views/identity/reset-password.blade.php
  - resources/views/identity/change-password.blade.php
  - routes/web.php
  - tests/Feature/Identity/Recovery/**
  - tests/Unit/Identity/Credentials/**
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260719-password-recovery-credentials.md
  - docs/agents/tasks/archive/OTERYN-20260719-web-login-sessions.md
modules:
  - Identity credentials/recovery
  - Audit (password security events only)
  - Notifications/mail safety configuration
  - HTTP recovery/change boundary
  - security
  - database
  - testing
  - agent-governance
dependencies:
  - OTERYN-20260719-web-login-sessions
  - docs/contracts/AUTH_GAME_LOGIN_CONTRACT.md
  - docs/architecture/SECURITY_ARCHITECTURE.md
  - OTERYN-20260718-static-analysis-gate
blockers:
  - none for Platform-only password recovery/change and Platform web-session revocation
  - global Canary/login-server credential/session revocation remains blocked and outside this task
cross_repository_tasks:
  - blakinio/canary and opentibiabr/login-server remain read-only evidence only; no writes are authorized
```

## Security boundary

This task changes only the password owned by Oteryn Platform Identity. It does not change any reusable password still independently verified by Canary or the external login-server.

A successful Platform password reset/change revokes Platform web sessions through `web_session_generation`. It does **not** revoke current Canary `account_sessions`, pending process-local Canary login tokens or active game sessions; those global revocation classes remain blocked by `AUTH_GAME_LOGIN_CONTRACT.md` and must not be represented as solved by this PR.

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-19T08:45:00Z
head: f337332ab31204323eb84d497ee80e5f470fe464
branch: task/OTERYN-20260719-password-recovery-credentials
pr: none
status: investigating
context_routes:
  - agent-governance
  - auth-identity
  - security
  - database
  - testing
owned_paths:
  - app/Identity/Credentials/**
  - app/Identity/Support/IdentityPasswordPolicy.php
  - app/Identity/Models/Identity.php
  - app/Audit/SecurityEventRecorder.php
  - app/Http/Controllers/Identity/PasswordRecoveryController.php
  - app/Http/Controllers/Identity/PasswordResetController.php
  - app/Http/Controllers/Identity/PasswordChangeController.php
  - app/Http/Requests/Identity/ForgotPasswordRequest.php
  - app/Http/Requests/Identity/ResetPasswordRequest.php
  - app/Http/Requests/Identity/ChangePasswordRequest.php
  - app/Http/Requests/Identity/RegisterIdentityRequest.php
  - app/Providers/AppServiceProvider.php
  - config/auth.php
  - config/mail.php
  - .env.example
  - database/migrations/*create_password_reset_tokens_table.php
  - resources/views/identity/forgot-password.blade.php
  - resources/views/identity/reset-password.blade.php
  - resources/views/identity/change-password.blade.php
  - routes/web.php
  - tests/Feature/Identity/Recovery/**
  - tests/Unit/Identity/Credentials/**
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260719-password-recovery-credentials.md
  - docs/agents/tasks/archive/OTERYN-20260719-web-login-sessions.md
proven:
  - PR #12 for OTERYN-20260719-web-login-sessions was squash-merged to main as 74a72d4acc2f0228a147e3ce71a1542f43e97906 after current-head CI and Agent Governance passed and no review/comment/thread blocker existed.
  - The completed T3.2 task record was moved from docs/agents/tasks/active to docs/agents/tasks/archive on this branch without changing its blob contents.
  - No open pull request was returned by live repository PR search after PR #12 merged.
  - Platform Identity passwords are separate Platform-owned credentials; this task has no approved write path to Canary accounts.password or other shared credential/session tables.
  - AUTH_GAME_LOGIN_CONTRACT.md states that password change/reset is not proven to delete account_sessions, invalidate pending Canary LoginSessionManager tokens or disconnect active game sessions; changing a password alone is insufficient to revoke already-issued game-login credentials.
  - Laravel 13 PasswordBroker uses a timeboxed user lookup, delegates reset-token storage to a token repository and deletes the reset token after a successful reset callback.
  - Laravel 13 DatabaseTokenRepository creates a random HMAC-SHA256 reset token, stores only a hash through the configured application hasher, applies expiry/throttle checks and deletes existing tokens for the identity when issuing a new token.
  - Laravel password-broker database token storage uses the configured/default Laravel database connection; wrapping PasswordBroker reset in an outer Platform DB transaction can therefore make the password mutation and broker token deletion part of the same Platform transaction when the default Platform connection is used.
  - The repository currently defaults MAIL_MAILER to log, while Laravel ResetPassword notification embeds the reset token in the reset URL and Laravel LogTransport writes the complete rendered message to the logger; the current default would therefore expose reset tokens to application logs if used unchanged.
  - Trust boundary affected: browser/email recovery channel to Oteryn Platform Identity and Platform web-session validity only.
  - Authentication invariant affected: reset tokens must be time-limited, hashed at rest and single-use; successful password change/reset must revoke Platform web sessions.
  - Canary/login-server schema or session compatibility changes: none in this task.
  - Rollback requirement: Platform-owned reset-token schema/configuration changes must be reversible; no shared-data rollback is involved.
  - Secrets or production-only configuration involved: no secrets may be committed; production mail credentials remain out-of-repository configuration.
derived:
  - A Platform-only recovery implementation can proceed safely if every UI/contract statement remains explicit that it resets only the Platform Identity credential and revokes only Platform web sessions.
  - Wrapping the broker reset call in a Platform DB transaction provides atomic password-update/session-generation/token-deletion semantics on the default Platform connection because Laravel deletes the token before the broker reset call returns.
  - The repository's default log mail transport is incompatible with the no-reset-token-logging invariant and must be changed to a non-logging safe development default before enabling framework reset notifications.
unknown:
  - Exact deployed production authentication topology and login-server image digest remain unknown.
  - Exact future global revocation behavior for Canary one-time tokens, account_sessions and active game sessions remains unknown/blocked.
  - Future mapping between Platform Identity recovery and any migrated/shared game-account credential remains undefined and outside this task.
conflicts:
  - Native Canary and upstream external login-server retain incompatible credential verification capabilities documented by AUTH_GAME_LOGIN_CONTRACT.md.
first_failure:
  marker: none
  evidence: T3.3 implementation validation has not run yet
rejected_hypotheses:
  - Platform password reset may be described as global game-account credential reset: rejected because alternate Canary/login-server password/session paths remain independent and unrevoked.
  - Default MAIL_MAILER=log is safe for password reset notifications: rejected because framework ResetPassword embeds the token in the URL and LogTransport logs the rendered message body.
  - Custom reset-token cryptography is required: rejected because maintained Laravel PasswordBroker and DatabaseTokenRepository already provide random token generation, hashed storage, expiry/throttle checks and broker-side deletion.
changed_paths:
  - docs/agents/tasks/archive/OTERYN-20260719-web-login-sessions.md
  - docs/agents/tasks/active/OTERYN-20260719-password-recovery-credentials.md
validation:
  - command: PR #12 merge-gate verification and squash merge
    result: PASS
    evidence: final PR #12 head 1d953c5e420a1d4e6f3e8467e84361ccfe591c1c passed CI 29680143372 and Agent Governance 29680143369; no review/comment/thread blockers; squash merge produced main commit 74a72d4acc2f0228a147e3ce71a1542f43e97906
  - command: framework password broker/token/mail transport source inspection
    result: PASS
    evidence: Laravel 13 PasswordBroker, DatabaseTokenRepository, ResetPassword notification and LogTransport source inspected before implementation
  - command: composer validate --strict; composer install --no-interaction --prefer-dist --no-progress; composer format:check; composer analyse; composer test
    result: NOT_RUN
    evidence: T3.3 implementation has not started
  - command: git status; git remote -v; git worktree list in a local checkout
    result: BLOCKED
    evidence: no local checkout is exposed in this environment; live GitHub state is used and no local working-tree claim is made
blockers:
  - none for Platform-only T3.3 scope
  - global Canary/login-server password/session revocation remains blocked and explicitly outside this task
next_action: Open the T3.3 draft PR, then implement the framework-backed Platform password recovery/change slice strictly within the declared owned paths.
```

## Notes

A successful Platform password reset/change does not change Canary credentials and does not terminate game sessions. This distinction must remain visible in implementation, tests, PR text and handoff until the cross-repository auth migration is separately approved and proven.