# OTERYN-20260719 Identity Core and Registration

## Goal

Implement the first bounded Phase 3 slice: a Platform-owned Identity core and secure web registration flow without writing Canary-owned/shared tables, migrating existing Canary credentials, issuing game-login credentials, or claiming global game-login policy enforcement.

## Acceptance criteria

- [x] Add a Platform-owned Identity persistence model with canonical normalized email and database-enforced unique identity.
- [x] Store new Platform Identity passwords only with Laravel framework hashing configured for Argon2id; do not read or write `accounts.password` in Canary.
- [x] Enforce a server-side password policy using Laravel validation primitives.
- [x] Add browser registration GET/POST routes with CSRF protection preserved and application-level rate limiting.
- [x] Record a minimal append-oriented security audit event for successful identity registration without password, password hash, reset token, session token, MFA secret, or raw game credential data.
- [x] Add negative/security regression coverage for malformed email, weak password, confirmation mismatch, canonical duplicate email, rate limiting, and password non-plaintext storage.
- [x] Explicitly keep email verification unenforced in this task because the current product/game-login contract does not prove it is a required global gate.
- [x] Do not create or mutate Canary `accounts`, `account_sessions`, game-login tokens, characters, guilds, bans, coins, or any other shared data.
- [x] Do not implement login/logout, remember-me, password reset/change, MFA, game-login token issuance, admin/RBAC, or global session revocation in this PR.
- [x] Run the repository-required Composer validation, lockfile install, Pint check, PHPStan/Larastan level 10, and full tests; inspect current-head CI before readiness.

## Phase 3 decomposition

Phase 3 is intentionally split into sequential small PRs. Each future task must independently re-check live prerequisites and claim exact paths before implementation.

### T3.1 — `OTERYN-20260719-identity-core-registration`

Current task. Platform-owned Identity persistence, registration, canonical email, password policy/hashing, registration rate limiting, and registration security audit only.

Current owned paths are declared below.

### T3.2 — `OTERYN-20260719-web-login-sessions`

Proposed bounded ownership, to be narrowed against the repository state at task start:

- `app/Identity/Sessions/**`
- selected login/logout/session actions under `app/Identity/Actions/**`
- `app/Http/Controllers/Identity/SessionController.php`
- `app/Http/Requests/Identity/LoginIdentityRequest.php`
- selected `config/auth.php` / `config/session.php` changes only when required
- identity login/logout route additions only
- `tests/Feature/Identity/Sessions/**`
- `tests/Unit/Identity/Sessions/**`
- its own task record

Scope: web login/logout, session regeneration/fixation protection, secure cookie policy, web-session revocation primitives, account/security-state enforcement and audit. No game-login authorization bridge.

### T3.3 — `OTERYN-20260719-password-recovery-credentials`

Proposed bounded ownership:

- `app/Identity/Recovery/**`
- selected password change/reset actions under `app/Identity/Actions/**`
- recovery/change HTTP request/controller paths only
- recovery-token migration/configuration only if framework defaults are insufficient
- identity recovery notifications only
- `tests/Feature/Identity/Recovery/**`
- `tests/Unit/Identity/Recovery/**`
- its own task record

Scope: password change/reset, expiry, single-use reset semantics and web-session revocation according to an explicit policy. Any claim about revoking Canary/login-server credentials remains blocked until the relevant cross-repository session classes are integrated and proven.

### T3.4 — `OTERYN-20260719-mfa-foundation`

Proposed bounded ownership:

- `app/Identity/Mfa/**`
- selected MFA enrollment/verification/recovery HTTP paths only
- MFA-specific platform migrations/configuration
- minimal admin-MFA enforcement hook only; no full Phase 6 RBAC/admin panel
- `tests/Feature/Identity/Mfa/**`
- `tests/Unit/Identity/Mfa/**`
- its own task record

Scope: administrator MFA foundation, enrollment, verification, recovery codes, reset/revocation and critical-operation re-authentication. Global game-login MFA enforcement must not be claimed while alternate login paths remain outside authoritative Identity.

### T3.5 — `OTERYN-20260719-game-login-authorization-bridge`

`BLOCKED` pending the rollout gates in `AUTH_GAME_LOGIN_CONTRACT.md`.

Potential Platform-side ownership after unblock:

- `app/Identity/GameAuthorization/**`
- `app/Integration/GameLogin/**`
- game-authorization-specific Platform migrations only
- `tests/Feature/Identity/GameAuthorization/**`
- `tests/Integration/GameLogin/**`
- directly related `docs/contracts/AUTH_GAME_LOGIN_CONTRACT.md` updates
- its own task record

Any Canary/login-server implementation requires separately authorized repository tasks. This Platform task must not silently modify another repository.

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
  - docs/agents/ACTIVE_WORK.md
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
updated_at: 2026-07-19T07:48:00Z
head: f9b116d67a239b403df3e59ccddb8e4f7cba1f25
branch: task/OTERYN-20260719-identity-core-registration
pr: 11
status: ready
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
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260719-identity-core-registration.md
proven:
  - Main remained at db39667c6afc407b05a824186db83e4b5923c608 throughout implementation; no newer main merge was returned by live GitHub commit search before this checkpoint.
  - No active task was claimed in docs/agents/ACTIVE_WORK.md and no open pull request was returned by live GitHub search before this task branch was created.
  - The mandatory PHPStan/Larastan gate is merged on main via PR #6, configured at level 10 with no baseline, and the final PR #6 head passed CI and Agent Governance.
  - The Canary database privilege boundary is merged via PR #7 and current main contains a fail-closed grant verifier requiring direct SELECT only on the exact implemented Canary read-table allowlist.
  - AUTH_GAME_LOGIN_CONTRACT.md remains PARTIALLY PROVEN with credential migration blocked and documents multiple alternate password/session game-login paths.
  - Canary auth-source behavior has not changed between the auth contract pinned Canary SHA 096f6445b29f69a62f03d391a2c02c4dcee74feb and observed Canary SHA f89b8aeff90c2b360acee43fe9cad3c75cf13c6a; intervening changed paths are documentation/E2E only.
  - Current upstream opentibiabr/login-server main remained at the contract-pinned SHA 2612930de4d97123a397f8f2cd0d5f784094af40 during prerequisite verification.
  - CANARY_DATA_CONTRACT.md approves zero direct shared-data writes and specifically leaves account creation NOT APPROVED.
  - The pre-task Laravel skeleton had no App Models User model, no users migration and no base App Http Controllers Controller class; the implementation follows the repository's standalone-controller pattern.
  - Platform Identity registration now stores canonical lowercased/trimmed email with a database unique constraint and hashes the Platform-only password through Laravel Hash using the configured Argon2id driver.
  - Registration is wrapped in one Platform database transaction that creates the identity and its minimal identity.registered security event together.
  - Registration POST is protected by the web middleware stack/CSRF form token and a named application rate limiter of five attempts per minute per source IP.
  - Registration regression tests cover canonical duplicate identity, malformed email, weak password, confirmation mismatch, rate limiting, non-plaintext password storage, Argon2id verification and registration audit persistence.
  - CI run 29678590603 on head f9b116d67a239b403df3e59ccddb8e4f7cba1f25 passed Composer validation, lockfile install, Pint, PHPStan/Larastan and full tests.
  - Agent Governance run 29678590631 on head f9b116d67a239b403df3e59ccddb8e4f7cba1f25 passed.
  - Trust boundary affected: Internet/browser to Oteryn Platform only; Canary/login-server credential validation is not changed by this task.
  - Authentication invariant affected: new Platform credentials are non-reversible, uniquely keyed by canonical email, rate-limited at registration, and never written to Canary credential fields.
  - Canary/login-server schema or session compatibility changes: none in this task.
  - Rollback requirement: the two Platform-owned migrations are reversible; no shared-data rollback is involved.
  - Secrets or production-only configuration involved: none; no real credentials were added to Git.
derived:
  - A Platform-owned Identity registration slice is safe to deliver only while explicitly decoupled from Canary account creation, credential migration, game-login authorization, MFA-as-global-game-policy and global revocation claims.
  - T3.5 game-login authorization bridge remains blocked until direct-path hardening, exact deployed topology, atomic shared token consumption, revocation semantics, rollout order and cross-repository implementation are approved and proven.
  - Email verification is not a required registration gate for this task because the current contract does not prove a product requirement or a globally enforced game-login verification policy.
unknown:
  - Exact deployed production authentication topology and login-server image digest remain unknown.
  - Future mapping/linking semantics between a Platform Identity and existing or newly created Canary accounts remain unknown and are outside this task.
  - Whether future product policy requires verified email for web-only access remains undecided; global game-login enforcement is not proven.
conflicts:
  - Native Canary and upstream external login-server still have incompatible credential verification capabilities: Canary custom Argon2 plus SHA-1 fallback versus upstream login-server SHA-1 only.
first_failure:
  marker: composer format:check on PR head 535caa0c6fbcfd770c369de5d098784bcf8e202e
  evidence: CI run 29678452998 job 88170104743 failed at Pint; fixed by formatting the promoted constructor. The next head then exposed PHPStan mixed-type findings, which were fixed by declaring Identity attribute types and using typed attributes; CI run 29678590603 is green.
rejected_hypotheses:
  - Registration may create a Canary accounts row directly: rejected because CANARY_DATA_CONTRACT.md has zero approved shared writes and account creation is NOT APPROVED.
  - Platform-only MFA or email verification can currently be claimed as a global game-login gate: rejected by the alternate-path inventory in AUTH_GAME_LOGIN_CONTRACT.md.
  - Laravel Argon2id hashes may be written into Canary accounts.password: rejected because Laravel/Canary stored-format compatibility is explicitly UNKNOWN/BLOCKED.
  - A conventional App Http Controllers Controller base class could be reused: rejected because the current repository has no such class and existing controllers are standalone.
changed_paths:
  - app/Audit/SecurityEventRecorder.php
  - app/Http/Controllers/Identity/RegistrationController.php
  - app/Http/Requests/Identity/RegisterIdentityRequest.php
  - app/Identity/Actions/RegisterIdentity.php
  - app/Identity/Models/Identity.php
  - app/Identity/Support/CanonicalEmail.php
  - app/Providers/AppServiceProvider.php
  - config/hashing.php
  - database/migrations/2026_07_19_073600_create_identities_table.php
  - database/migrations/2026_07_19_073601_create_identity_security_events_table.php
  - resources/views/identity/register.blade.php
  - routes/web.php
  - tests/Feature/Identity/RegistrationTest.php
  - tests/Unit/Identity/CanonicalEmailTest.php
  - docs/agents/tasks/active/OTERYN-20260719-identity-core-registration.md
validation:
  - command: repository/source/contract/live-PR prerequisite inspection
    result: PASS
    evidence: main docs/contracts, PR #6, PR #7, current Canary comparison, current login-server SHA and no pre-task active ownership overlap
  - command: git status; git remote -v; git worktree list in a local checkout
    result: BLOCKED
    evidence: no pre-existing local checkout is exposed to this environment; a temporary clone attempt could not resolve github.com, so live GitHub branch/PR state was used instead and no claim is made about an unavailable local working tree
  - command: composer validate --strict; composer install --no-interaction --prefer-dist --no-progress; composer format:check; composer analyse; composer test
    result: PASS
    evidence: GitHub Actions CI run 29678590603 on head f9b116d67a239b403df3e59ccddb8e4f7cba1f25; all required steps passed
  - command: Agent Governance
    result: PASS
    evidence: GitHub Actions run 29678590631 on head f9b116d67a239b403df3e59ccddb8e4f7cba1f25
blockers:
  - none for this bounded Platform-owned registration task
  - T3.5 and any global game-login MFA, credential migration or cross-session revocation claim remain blocked by AUTH_GAME_LOGIN_CONTRACT.md rollout gates
next_action: Review PR #11 and squash-merge it only if required checks remain green on the final current head; then start T3.2 as a new task/branch only after re-verifying prerequisites and owned-path overlap.
```

## Notes

This task is a bounded Phase 3 implementation task, not a credential migration or game-login integration task. A successful Platform registration does not imply that a corresponding Canary game account exists or that the new Platform password can authenticate to Canary.
