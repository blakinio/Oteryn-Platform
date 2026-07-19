# OTERYN-20260719 Phase 3 Identity Closure

## Goal

Close Phase 3 — Identity foundation against the current roadmap after merged PR #16, without expanding into Phase 4 PublicGameData, Phase 5 shared Canary mutations, or Phase 6 Admin/RBAC implementation.

The closure must make the remaining Phase 3 policies explicit and testable: Platform credentials remain isolated from Canary credentials until the documented authoritative-Identity migration preconditions are satisfied; email verification is not required by current Phase 3 product policy; every future privileged/Admin route must combine authentication, explicit authorization and a confirmed-MFA gate without introducing an administrator boolean.

## Acceptance criteria

- [ ] Archive merged `OTERYN-20260719-platform-web-mfa` task record without changing its contents.
- [ ] Add a reusable fail-closed `mfa.confirmed` middleware gate for future privileged routes; it must require an authenticated Oteryn `Identity` with confirmed MFA and must not classify or authorize administrators itself.
- [ ] Add regression tests proving authenticated identities without confirmed MFA are blocked from an MFA-protected privileged route and confirmed-MFA identities pass the MFA gate.
- [ ] Define the Phase 3 administrator authentication policy as `auth` + explicit Phase 6 RBAC/policy authorization + mandatory `mfa.confirmed`; no `is_admin`/privilege boolean is introduced.
- [ ] Record that current Phase 3 email verification is not required by product policy and is therefore intentionally not enabled; any later global email-verification requirement must account for alternate Canary/login-server paths.
- [ ] Record the Phase 3 credential strategy: Platform Identity credentials are Platform-owned and use framework hashing; Phase 3 performs no shared Canary password migration/write, preserving current game-login compatibility by non-interference. Future migration follows the evidence-backed rollout gates in `AUTH_GAME_LOGIN_CONTRACT.md`.
- [ ] Update `AUTH_GAME_LOGIN_CONTRACT.md` current-state notes to acknowledge implemented Platform web Identity/password reset/MFA while preserving the fact that these controls do not globally gate Canary/login-server authentication.
- [ ] Update ROADMAP, MODULE_CATALOG, PROJECT_STATE and ACTIVE_WORK so Phase 3 is marked complete and the next phase/task is derived from the current roadmap without rewriting historical evidence.
- [ ] Run Composer validation/install, Pint, PHPStan/Larastan level 10, full tests and Agent Governance on the exact final head.
- [ ] Do not modify Canary/login-server repositories, shared credentials, game sessions, Admin/RBAC semantics, or Phase 4 feature code in this task.

## Ownership

```yaml
owned_paths:
  - app/Http/Middleware/EnsureConfirmedMfa.php
  - bootstrap/app.php
  - tests/Feature/Identity/Mfa/PrivilegedMfaGateTest.php
  - docs/architecture/ROADMAP.md
  - docs/architecture/MODULE_CATALOG.md
  - docs/contracts/AUTH_GAME_LOGIN_CONTRACT.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260719-phase3-identity-closure.md
  - docs/agents/tasks/archive/OTERYN-20260719-platform-web-mfa.md
modules:
  - Identity
  - security
  - agent-governance
dependencies:
  - OTERYN-20260719-platform-web-mfa
  - docs/architecture/ROADMAP.md
  - docs/architecture/SECURITY_ARCHITECTURE.md
  - docs/contracts/AUTH_GAME_LOGIN_CONTRACT.md
blockers:
  - none for Phase 3 closure
cross_repository_tasks:
  - blakinio/canary and opentibiabr/login-server remain read-only evidence only; no writes are authorized
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-19T15:20:00+02:00
head: b1947b2e918b689bac636942ce244492227158bb
branch: task/OTERYN-20260719-phase3-identity-closure
pr: none
status: investigating
context_routes:
  - agent-governance
  - auth-identity
  - security
  - architecture
owned_paths:
  - app/Http/Middleware/EnsureConfirmedMfa.php
  - bootstrap/app.php
  - tests/Feature/Identity/Mfa/PrivilegedMfaGateTest.php
  - docs/architecture/ROADMAP.md
  - docs/architecture/MODULE_CATALOG.md
  - docs/contracts/AUTH_GAME_LOGIN_CONTRACT.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260719-phase3-identity-closure.md
  - docs/agents/tasks/archive/OTERYN-20260719-platform-web-mfa.md
proven:
  - PR #16 merged complete Platform web MFA to main as b1947b2e918b689bac636942ce244492227158bb after final CI #242 and Agent Governance #163 passed.
  - ROADMAP Phase 3 deliverables include registration, login/logout, credential strategy, sessions, password reset, optional email verification, rate limiting, security audit, administrator MFA foundation and auth/revocation tests.
  - Current Platform web Identity registration/login/session/password recovery/MFA lifecycle exists on main.
  - AUTH_GAME_LOGIN_CONTRACT proves native Canary and external login-server remain alternate reusable-credential paths and documents the required authoritative-Identity migration order.
  - Phase 6 owns role/permission model, privileged actions and mandatory admin MFA integration; Phase 3 must not invent RBAC or an administrator boolean.
derived:
  - Phase 3 can close without mutating Canary credentials by making Platform credential isolation the explicit compatibility-preserving strategy until cross-path migration gates are satisfied.
  - A reusable confirmed-MFA middleware can provide the tested administrator-authentication foundation without deciding who is an administrator; Phase 6 authorization must make that classification.
unknown:
  - Exact deployed production authentication topology and login-server image digest remain unresolved for future global auth migration.
conflicts:
  - Platform web MFA and email policy cannot be represented as global game-login enforcement while alternate Canary/login-server paths remain reachable.
first_failure:
  marker: none
  evidence: implementation validation has not run yet
rejected_hypotheses:
  - Add an is_admin boolean to complete Phase 3: rejected because authorization/RBAC belongs to Phase 6 and a boolean would become an authorization shortcut.
  - Migrate shared Canary passwords in Phase 3: rejected because the auth contract proves unresolved parallel login paths and SHA-1/custom-Argon compatibility constraints.
  - Enable email verification globally without game-login integration: rejected because alternate game-login paths would bypass a Platform-only gate.
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260719-phase3-identity-closure.md
validation:
  - command: implementation validation
    result: NOT_RUN
    evidence: task branch currently contains only the task record
blockers:
  - none for Phase 3 closure
next_action: Open draft PR, archive the merged T3.4c record, implement the confirmed-MFA middleware and regression tests, then update Phase 3 closure documentation and run full validation.
```

## Notes

Phase 3 closure is a policy and foundation gate, not permission to claim global game-login MFA/password revocation. Cross-repository authoritative login migration remains a later separately authorized programme.
