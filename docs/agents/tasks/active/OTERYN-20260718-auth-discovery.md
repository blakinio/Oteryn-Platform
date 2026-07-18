# OTERYN-20260718 Authentication and identity discovery

## Goal

Prove the current end-to-end authentication path from account login through game-world entry against the actual current `blakinio/canary` source, identify all password/session/token verification paths and bypasses, then update `docs/contracts/AUTH_GAME_LOGIN_CONTRACT.md` with current `PROVEN` behavior and a separate recommended target contract. Do not implement credential migration or authentication code.

## Acceptance criteria

- [ ] Pin discovery evidence to exact current Oteryn Platform and Canary commit SHAs.
- [ ] Prove which component receives the initial game-client authentication request.
- [ ] Prove account descriptor rules and password/hash verification formats.
- [ ] Prove every current password/session/token path into character list and game-world authentication.
- [ ] Prove session/token issuance, storage, TTL, single-use/replay and revocation semantics where source evidence exists.
- [ ] Prove account-ban enforcement and direct/legacy alternate login paths.
- [ ] Prove current password change/reset, email verification and MFA behavior or retain them as `UNKNOWN`/absent when not implemented.
- [ ] Document outage/failure behavior for auth/session dependencies where source evidence exists.
- [ ] Separate current-state `PROVEN` facts from recommended target architecture/policy.
- [ ] Classify material conclusions as `PROVEN`, `DERIVED`, `UNKNOWN` or `CONFLICT`.
- [ ] Update `docs/contracts/AUTH_GAME_LOGIN_CONTRACT.md`.
- [ ] Do not implement credential migration, auth endpoints, MFA or Canary writes.
- [ ] Complete checkpoint, validation and handover with exactly one concrete `next_action`.

## Ownership

```yaml
owned_paths:
  - docs/contracts/AUTH_GAME_LOGIN_CONTRACT.md
  - docs/agents/tasks/active/OTERYN-20260718-auth-discovery.md
  - docs/agents/tasks/archive/OTERYN-20260718-auth-discovery.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/PROJECT_STATE.md
modules:
  - Identity
  - Integration
dependencies:
  - docs/architecture/SECURITY_ARCHITECTURE.md
  - docs/architecture/SYSTEM_ARCHITECTURE.md
  - docs/architecture/DATA_OWNERSHIP.md
  - docs/contracts/CANARY_DATA_CONTRACT.md
blockers:
  - none
cross_repository_tasks:
  - blakinio/canary read-only authentication/session source discovery
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-18T23:00:00+02:00
head: f968681732ec3e0688ff29426108b49dce79af16
branch: task/OTERYN-20260718-auth-discovery
pr: none
status: investigating
context_routes:
  - agent-governance
  - auth-identity
  - canary-integration
  - security
owned_paths:
  - docs/contracts/AUTH_GAME_LOGIN_CONTRACT.md
  - docs/agents/tasks/active/OTERYN-20260718-auth-discovery.md
  - docs/agents/tasks/archive/OTERYN-20260718-auth-discovery.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/PROJECT_STATE.md
proven:
  - Oteryn Platform main is at f968681732ec3e0688ff29426108b49dce79af16 after the Canary data-contract discovery merge.
  - No active task and no overlapping open auth-discovery PR existed at startup.
  - Oteryn Platform currently has no implemented real authentication/login flow.
  - Canary must remain read-only for this task.
  - Current Canary main HEAD observed at task startup is 096f6445b29f69a62f03d391a2c02c4dcee74feb.
  - The existing AUTH_GAME_LOGIN_CONTRACT.md is explicitly DISCOVERY REQUIRED and does not prove current responsibilities.
derived:
  - The safe next step is bounded read-only inspection of current Canary login/account/session source before any credential or session design is implemented in Platform.
unknown:
  - Whether Canary ProtocolLogin is the actual sole login-server path or another deployed component exists.
  - Exact accepted password hashes and descriptor rules.
  - Secure-token issuance, TTL, replay/single-use and revocation behavior.
  - Legacy/direct password or session paths that can bypass future Platform policy.
  - Password reset/change, email verification and MFA enforcement behavior.
conflicts: []
first_failure:
  marker: none
  evidence: none
rejected_hypotheses: []
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260718-auth-discovery.md
validation:
  - command: startup repository/task/PR verification
    result: PASS
    evidence: Platform main f968681732ec3e0688ff29426108b49dce79af16; ACTIVE_WORK had no active task; no matching open PR
blockers:
  - none
next_action: Inspect current Canary SHA 096f6445b29f69a62f03d391a2c02c4dcee74feb for ProtocolLogin, ProtocolGame, Account authentication, LoginSessionManager, account_sessions writers, AUTH_TYPE configuration and password/session revocation paths.
```

## Notes

Do not infer deployed topology from component names. Source proves code paths, not necessarily which network endpoint is deployed in production unless deployment evidence also exists.