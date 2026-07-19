# OTERYN-20260719 Password Recovery and Credential Changes

## Goal

Implement the third bounded Phase 3 slice for Platform-owned Identity credentials: forgot/reset password and authenticated password change with framework-backed reset tokens, atomic single-use reset completion, Platform web-session revocation and security audit, without modifying Canary/login-server credentials or claiming global game-login revocation.

## Acceptance criteria

- [x] Configure Laravel's maintained password broker for the Platform `Identity` provider and Platform-owned reset-token storage.
- [x] Make `Identity` reset-capable through Laravel framework contracts/traits; do not implement custom token cryptography.
- [x] Add forgot-password request flow with canonical email normalization, generic public response and application rate limits.
- [x] Ensure the default development mail configuration does not log reset links/tokens; keep production mail transport externally configurable without committing secrets.
- [x] Add reset-password flow using framework token validation with expiry, hashed token storage and single-use semantics.
- [x] Wrap successful reset password mutation, Platform web-session generation revocation, audit and reset-token deletion in one Platform database transaction so reset completion is atomic on the Platform connection.
- [x] Add authenticated password change requiring the current password and the authoritative Platform password policy.
- [x] Revoke all Platform web sessions after successful password change/reset; invalidate the current browser session after password change.
- [x] Centralize the Platform password policy so registration, password change and password reset use one rule definition.
- [x] Add security audit events for password change and completed password reset without passwords, hashes or reset tokens.
- [x] Add negative/security regression coverage for generic forgot-password response, rate limiting, invalid/expired token behavior, replayed token, single-use successful reset, old-password rejection, current-session invalidation, stale-session rejection after reset, weak password, confirmation mismatch and wrong-current-password denial.
- [x] Do not implement credential migration, Canary `accounts.password` changes, `account_sessions` deletion, Canary LoginSessionManager token revocation, active-game disconnection, MFA, Admin/RBAC or game-login authorization.
- [x] Run Composer validation, lockfile install, Pint, PHPStan/Larastan level 10 and the full test suite; inspect current-head CI before readiness.

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
  - bootstrap/app.php
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
updated_at: 2026-07-19T09:05:00Z
head: 43c00caf3e9718646b72e1faac24978f7979a1ca
branch: task/OTERYN-20260719-password-recovery-credentials
pr: 13
status: ready
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
  - bootstrap/app.php
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
  - PR #13 is the dedicated T3.3 draft PR on task/OTERYN-20260719-password-recovery-credentials; no overlapping open PR was present when the task was claimed.
  - Platform Identity passwords are separate Platform-owned credentials; this task has no write path to Canary accounts.password or other shared credential/session tables.
  - AUTH_GAME_LOGIN_CONTRACT.md states that password change/reset is not proven to delete account_sessions, invalidate pending Canary LoginSessionManager tokens or disconnect active game sessions; changing a password alone is insufficient to revoke already-issued game-login credentials.
  - Laravel 13 PasswordBroker uses a timeboxed user lookup, delegates reset-token storage to a token repository and deletes the reset token after a successful reset callback.
  - Laravel 13 DatabaseTokenRepository creates a random HMAC-SHA256 reset token, stores only a hash through the configured application hasher, applies expiry/throttle checks and replaces an existing token when issuing a new one.
  - PasswordResetCompleter wraps the broker reset call in a Platform DB transaction; on the default Platform connection the password mutation, web-session generation revocation, audit and broker token deletion complete inside that outer transaction.
  - IdentityCredentialUpdater hashes new passwords through Laravel Hash, revokes Platform web sessions through web_session_generation and records password-change/reset audit events inside Platform DB transactions.
  - Registration, password change and password reset use one IdentityPasswordPolicy rule.
  - Authenticated password change requires Laravel current_password:web validation, rejects reuse of the current plaintext input, revokes Platform web sessions and explicitly invalidates the current browser session.
  - Forgot-password responses are generic for existing and unknown identities; recovery requests are rate-limited per hashed canonical identity plus source IP and by a separate source-wide IP limit.
  - Reset completion is rate-limited, reset tokens expire through the framework broker configuration, successful reset deletes the token, and replay is rejected by regression tests.
  - Reset-token rows store the framework-hashed token rather than the raw token; regression coverage verifies the raw notification token differs from the persisted value and validates through Hash check.
  - The repository default mailer was changed from log to array because Laravel ResetPassword embeds the reset token in its URL and Laravel LogTransport writes the rendered message to logs; PasswordResetLinkSender additionally fails closed when the selected transport is log.
  - bootstrap/app.php now explicitly redirects unauthenticated web users to /login and authenticated guest-route users to /, making auth/guest middleware behavior deterministic for recovery routes.
  - Security audit events added by this task contain only identity reference and event type; no password, password hash or reset token payload is stored.
  - Security regression tests cover generic recovery responses, per-identity/source rate limits, log-mailer refusal, expired tokens, successful single-use reset, replay rejection, reset-token preservation on pre-validation failure, existing-session revocation after reset, wrong-current-password denial, weak/mismatched/reused password denial, password-change rate limiting and current-session logout after change.
  - Code-validation head 43c00caf3e9718646b72e1faac24978f7979a1ca passed CI run 29680745110 including Composer validation, lockfile install, Pint, PHPStan/Larastan level 10 and the full test suite.
  - Trust boundary affected: browser/email recovery channel to Oteryn Platform Identity and Platform web-session validity only.
  - Authentication invariant affected: reset tokens are time-limited, hashed at rest and single-use; successful password change/reset revokes Platform web sessions.
  - Canary/login-server schema or session compatibility changes: none in this task.
  - Rollback requirement: Platform-owned reset-token migration is reversible; no shared-data rollback is involved.
  - Secrets or production-only configuration involved: no secrets are committed; production mail credentials remain out-of-repository configuration.
derived:
  - Platform-only recovery is safe within its bounded web-auth scope because it never mutates shared game credentials and explicitly refuses to claim global game-session revocation.
  - The outer Platform transaction around PasswordBroker reset provides atomic password-update/session-generation/audit/token-deletion semantics when the broker uses the default Platform database connection.
  - A non-logging local mail default plus explicit refusal of the framework log transport removes the repository's previously direct reset-token-to-application-log path.
unknown:
  - Exact deployed production authentication topology and login-server image digest remain unknown.
  - Exact future global revocation behavior for Canary one-time tokens, account_sessions and active game sessions remains unknown/blocked.
  - Future mapping between Platform Identity recovery and any migrated/shared game-account credential remains undefined and outside this task.
conflicts:
  - Native Canary and upstream external login-server retain incompatible credential verification capabilities documented by AUTH_GAME_LOGIN_CONTRACT.md.
first_failure:
  marker: none
  evidence: the first complete T3.3 code-validation head 43c00caf3e9718646b72e1faac24978f7979a1ca passed every CI step; earlier adjustments were made before a failed complete gate was observed.
rejected_hypotheses:
  - Platform password reset may be described as global game-account credential reset: rejected because alternate Canary/login-server password/session paths remain independent and unrevoked.
  - Default MAIL_MAILER=log is safe for password reset notifications: rejected because framework ResetPassword embeds the token in the URL and LogTransport logs the rendered message body.
  - Custom reset-token cryptography is required: rejected because maintained Laravel PasswordBroker and DatabaseTokenRepository already provide random token generation, hashed storage, expiry/throttle checks and broker-side deletion.
  - Password confirmation must be forwarded to PasswordBroker: rejected because confirmation is a request-validation concern; the broker reset credential payload only needs canonical email, reset token and the already-validated new password.
changed_paths:
  - .env.example
  - app/Audit/SecurityEventRecorder.php
  - app/Http/Controllers/Identity/PasswordChangeController.php
  - app/Http/Controllers/Identity/PasswordRecoveryController.php
  - app/Http/Controllers/Identity/PasswordResetController.php
  - app/Http/Requests/Identity/ChangePasswordRequest.php
  - app/Http/Requests/Identity/ForgotPasswordRequest.php
  - app/Http/Requests/Identity/RegisterIdentityRequest.php
  - app/Http/Requests/Identity/ResetPasswordRequest.php
  - app/Identity/Credentials/IdentityCredentialUpdater.php
  - app/Identity/Credentials/PasswordResetCompleter.php
  - app/Identity/Credentials/PasswordResetLinkSender.php
  - app/Identity/Models/Identity.php
  - app/Identity/Support/IdentityPasswordPolicy.php
  - app/Providers/AppServiceProvider.php
  - bootstrap/app.php
  - config/auth.php
  - config/mail.php
  - database/migrations/2026_07_19_084700_create_password_reset_tokens_table.php
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260719-password-recovery-credentials.md
  - docs/agents/tasks/archive/OTERYN-20260719-web-login-sessions.md
  - resources/views/identity/change-password.blade.php
  - resources/views/identity/forgot-password.blade.php
  - resources/views/identity/reset-password.blade.php
  - routes/web.php
  - tests/Feature/Identity/Recovery/PasswordChangeTest.php
  - tests/Feature/Identity/Recovery/PasswordRecoveryTest.php
validation:
  - command: PR #12 merge-gate verification and squash merge
    result: PASS
    evidence: final PR #12 head 1d953c5e420a1d4e6f3e8467e84361ccfe591c1c passed CI 29680143372 and Agent Governance 29680143369; no review/comment/thread blockers; squash merge produced main commit 74a72d4acc2f0228a147e3ce71a1542f43e97906
  - command: framework password broker/token/mail transport source inspection
    result: PASS
    evidence: Laravel 13 PasswordBroker, DatabaseTokenRepository, PasswordBrokerManager, ResetPassword notification and LogTransport source inspected before implementation
  - command: composer validate --strict; composer install --no-interaction --prefer-dist --no-progress; composer format:check; composer analyse; composer test
    result: PASS
    evidence: GitHub Actions CI run 29680745110 on code-validation head 43c00caf3e9718646b72e1faac24978f7979a1ca passed every required step
  - command: git status; git remote -v; git worktree list in a local checkout
    result: BLOCKED
    evidence: no local checkout is exposed in this environment; live GitHub state is used and no local working-tree claim is made
blockers:
  - none for Platform-only T3.3 scope
  - global Canary/login-server password/session revocation remains blocked and explicitly outside this task
next_action: Revalidate CI and Agent Governance on the checkpoint/documentation head, then inspect PR #13 reviews and squash-merge only if the merge gate remains fully satisfied.
```

## Notes

A successful Platform password reset/change does not change Canary credentials and does not terminate game sessions. This distinction must remain visible in implementation, tests, PR text and handoff until the cross-repository auth migration is separately approved and proven.
