# OTERYN-20260719 Identity Core and Registration

## Goal

Implement the first bounded Phase 3 slice: a Platform-owned Identity core and secure web registration flow without writing Canary-owned/shared tables, migrating existing Canary credentials, issuing game-login credentials, or claiming global game-login policy enforcement.

## Acceptance criteria

- [ ] Add a Platform-owned Identity persistence model with canonical normalized email and database-enforced unique identity.
- [ ] Store new Platform Identity passwords only with Laravel framework hashing configured for Argon2id; do not read or write `accounts.password` in Canary.
- [ ] Enforce a server-side password policy using Laravel validation primitives.
- [ ] Add browser registration GET/POST routes with CSRF protection preserved and application-level rate limiting.
- [ ] Record a minimal append-oriented security audit event for successful identity registration without password, password hash, reset token, session token, MFA secret, or raw game credential data.
- [ ] Add negative/security regression coverage for malformed email, weak password, confirmation mismatch, canonical duplicate email, rate limiting, and password non-plaintext storage.
- [ ] Explicitly keep email verification unenforced in this task because the current product/game-login contract does not prove it is a required global gate.
- [ ] Do not create or mutate Canary `accounts`, `account_sessions`, game-login tokens, characters, guilds, bans, coins, or any other shared data.
- [ ] Do not implement login/logout, remember-me, password reset/change, MFA, game-login token issuance, admin/RBAC, or global session revocation in this PR.
- [ ] Run the repository-required Composer validation, lockfile install, Pint check, PHPStan/Larastan level 10, and full tests; inspect current-head CI before readiness.

## Phase 3 decomposition

This task is intentionally the first of multiple small PRs:

1. `OTERYN-20260719-identity-core-registration` — Platform-owned Identity core and registration only.
2. `OTERYN-20260719-web-login-sessions` — web login/logout, secure sessions, fixation protection, revocation primitives and account-state enforcement.
3. `OTERYN-20260719-password-recovery-credentials` — password change/reset and revocation semantics, only after the required session classes are explicit.
4. `OTERYN-20260719-mfa-foundation` — administrator MFA foundation and narrowly approved user MFA policy.
5. `OTERYN-20260719-game-login-authorization-bridge` — remains BLOCKED until the cross-repository rollout/token/revocation/direct-path gates in `AUTH_GAME_LOGIN_CONTRACT.md` are satisfied.

Future task records must independently verify prerequisites and claim non-overlapping `owned_paths` before implementation.

## Ownership

```yaml
owned_paths:
  - app/Identity/**
  - app/Audit/SecurityEventRecorder.php
  - app/Http/Controllers/Identity/RegistrationController.php
  - app/Http/Requests/Identity/RegisterIdentityRequest.php
  - app/Providers/AppServiceProvider.php
  - config/hashing.php
  - database/migrations/*create_identities_table.php
  - database/migrations/*create_identity_security_events_table.php
  - resources/views/identity/register.blade.php
  - routes/web.php
  - tests/Feature/Identity/RegistrationTest.php
  - tests/Unit/Identity/CanonicalEmailTest.php
  - docs/agents/tasks/active/OTERYN-20260719-identity-core-registration.md
modules:
  - Identity
  - Audit (registration security event only)
  - HTTP registration boundary
  - testing
  - security
  - database
  - agent-governance
dependencies:
  - docs/contracts/AUTH_GAME_LOGIN_CONTRACT.md
  - docs/contracts/CANARY_DATA_CONTRACT.md
  - OTERYN-20260718-static-analysis-gate
  - OTERYN-20260718-db-privilege-boundary
blockers:
  - none for Platform-owned registration without Canary writes or game-login linkage
cross_repository_tasks:
  - blakinio/canary and opentibiabr/login-server are read-only evidence only; no writes are authorized
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-19T07:33:51Z
head: db39667c6afc407b05a824186db83e4b5923c608
branch: task/OTERYN-20260719-identity-core-registration
pr: none
status: investigating
context_routes:
  - agent-governance
  - architecture
  - auth-identity
  - security
  - database
  - canary-integration
  - testing
owned_paths:
  - app/Identity/**
  - app/Audit/SecurityEventRecorder.php
  - app/Http/Controllers/Identity/RegistrationController.php
  - app/Http/Requests/Identity/RegisterIdentityRequest.php
  - app/Providers/AppServiceProvider.php
  - config/hashing.php
  - database/migrations/*create_identities_table.php
  - database/migrations/*create_identity_security_events_table.php
  - resources/views/identity/register.blade.php
  - routes/web.php
  - tests/Feature/Identity/RegistrationTest.php
  - tests/Unit/Identity/CanonicalEmailTest.php
  - docs/agents/tasks/active/OTERYN-20260719-identity-core-registration.md
proven:
  - No active task is claimed in docs/agents/ACTIVE_WORK.md and no open pull request was returned by live GitHub search before this task branch was created.
  - The mandatory PHPStan/Larastan gate is merged on main via PR #6, configured at level 10 with no baseline, and the final PR head passed CI and Agent Governance.
  - The Canary database privilege boundary is merged via PR #7 and current main contains a fail-closed grant verifier requiring direct SELECT only on the exact implemented Canary read-table allowlist.
  - AUTH_GAME_LOGIN_CONTRACT.md remains PARTIALLY PROVEN with credential migration blocked and documents multiple alternate password/session game-login paths.
  - Canary auth-source behavior has not changed between the auth contract pinned Canary SHA 096f6445b29f69a62f03d391a2c02c4dcee74feb and current observed Canary SHA f89b8aeff90c2b360acee43fe9cad3c75cf13c6a; the intervening changed paths are documentation/E2E only.
  - Current upstream opentibiabr/login-server main remains at the contract-pinned SHA 2612930de4d97123a397f8f2cd0d5f784094af40.
  - CANARY_DATA_CONTRACT.md approves zero direct shared-data writes and specifically leaves account creation NOT APPROVED.
  - The current Laravel skeleton has no App Models User model and no users migration; this task therefore does not reuse an existing Identity model.
  - Trust boundary affected: Internet/browser to Oteryn Platform only; Canary/login-server credential validation is not changed by this task.
  - Authentication invariant affected: new Platform credentials must be non-reversible, uniquely keyed by canonical email, rate-limited at registration, and never written to Canary credential fields.
  - Canary/login-server schema or session compatibility changes: none in this task.
  - Rollback requirement: migrations introduced by this task must be reversible; no shared-data rollback is involved.
  - Secrets or production-only configuration involved: none; no real credentials are added to Git.
derived:
  - A Platform-owned Identity registration slice can proceed safely only if it remains explicitly decoupled from Canary account creation, credential migration, game-login authorization, MFA-as-global-game-policy, and global revocation claims.
  - T3.5 game-login authorization bridge remains blocked until direct-path hardening, exact deployed topology, atomic shared token consumption, revocation semantics, rollout order, and cross-repository implementation are approved and proven.
  - Email verification is not a required registration gate for this task because the current contract does not prove a product requirement or global game-login enforcement path; adding such a global claim now would be unsafe.
unknown:
  - Exact deployed production authentication topology and login-server image digest remain unknown.
  - Future mapping/linking semantics between a Platform Identity and existing or newly created Canary accounts remain unknown and are outside this task.
  - Whether future product policy requires verified email for web-only access remains undecided; global game-login enforcement is not proven.
conflicts:
  - Native Canary and upstream external login-server still have incompatible credential verification capabilities (Canary custom Argon2 plus SHA-1 fallback versus upstream login-server SHA-1 only).
first_failure:
  marker: none
  evidence: prerequisites for this bounded Platform-only slice are satisfied; cross-repository credential/game-login work is excluded and remains blocked
rejected_hypotheses:
  - Registration may create a Canary accounts row directly: rejected because CANARY_DATA_CONTRACT.md has zero approved shared writes and account creation is NOT APPROVED.
  - Platform-only MFA or email verification can currently be claimed as a global game-login gate: rejected by the alternate-path inventory in AUTH_GAME_LOGIN_CONTRACT.md.
  - Laravel default Argon2id hashes may be written into Canary accounts.password: rejected because Laravel/Canary stored-format compatibility is explicitly UNKNOWN/BLOCKED.
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260719-identity-core-registration.md
validation:
  - command: repository/source/contract/PR prerequisite inspection
    result: PASS
    evidence: main docs/contracts, PR #6, PR #7, current Canary compare, current login-server SHA
  - command: composer validate --strict; composer install --no-interaction --prefer-dist --no-progress; composer format:check; composer analyse; composer test
    result: NOT_RUN
    evidence: implementation has not started yet
blockers:
  - none for Platform-owned registration scope
next_action: Open the draft PR, then implement the Platform-owned Identity registration slice strictly within the declared owned paths.
```

## Notes

This task is a bounded Phase 3 implementation task, not a credential migration or game-login integration task. A successful Platform registration does not imply that a corresponding Canary game account exists or that the new Platform password can authenticate to Canary.
