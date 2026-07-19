# OTERYN-20260719 Phase 5 Identity-to-Canary account binding contract

## Goal

Resolve the authorization blocker discovered by the first Phase 5 character-creation contract. Define how one authenticated Oteryn Platform Identity can be durably and unambiguously associated with exactly one Canary `accounts.id` without guessing ownership from non-unique email, trusting browser-supplied account IDs, or silently migrating the still-separate game-login credential model. Approve only an evidence-backed binding/claim boundary or record the exact blocker. Do not implement shared Canary writes in this discovery task.

## Acceptance criteria

- [x] Archive the merged `OTERYN-20260719-phase5-character-creation-contract` task under `docs/agents/tasks/archive/` with exact historical content and remove the active copy.
- [x] Verify current Oteryn Platform `main`, open PR state and current Canary `main` before claiming scope.
- [x] Inspect current Platform Identity persistence, registration/login/session/MFA/recovery boundaries and confirm there is no existing account-link model.
- [x] Inspect current Canary account identity keys and current Canary/external-login-server authentication paths relevant to proving ownership of one `accounts.id`.
- [x] Compare viable binding ceremonies: durable Platform-owned mapping established by authoritative account-control proof, new-account creation with immediate binding, existing-account claim, and privileged/manual recovery.
- [x] Reject binding rules that rely only on client-supplied account IDs, non-unique email equality, direct Platform password-hash verification, or reuse of normal login as a side-effect-free claim API.
- [x] Define the binding lifecycle/cardinality/concurrency/audit requirements supported by current evidence and retain unresolved product cardinality/unlink/rebind policy as explicit blockers rather than guessing.
- [x] Add `docs/contracts/IDENTITY_CANARY_ACCOUNT_BINDING_CONTRACT.md` with the exact blocker and safe target claim boundary.
- [x] Update `ACTIVE_WORK.md` with the current task; do not claim character/account mutation authorization.
- [x] Do not modify Canary/login-server repositories and do not implement shared Canary account/player writes.
- [ ] Run final exact-head CI and Agent Governance before readiness/merge.

## Ownership

```yaml
owned_paths:
  - docs/contracts/IDENTITY_CANARY_ACCOUNT_BINDING_CONTRACT.md
  - docs/contracts/CHARACTER_CREATION_CONTRACT.md
  - docs/contracts/AUTH_GAME_LOGIN_CONTRACT.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260719-phase5-identity-canary-account-binding.md
  - docs/agents/tasks/active/OTERYN-20260719-phase5-character-creation-contract.md
  - docs/agents/tasks/archive/OTERYN-20260719-phase5-character-creation-contract.md
modules:
  - Identity
  - accounts-characters
  - Integration
  - architecture
  - agent-governance
  - security
dependencies:
  - OTERYN-20260719-phase5-character-creation-contract
  - docs/contracts/CHARACTER_CREATION_CONTRACT.md
  - docs/contracts/AUTH_GAME_LOGIN_CONTRACT.md
  - docs/contracts/CANARY_DATA_CONTRACT.md
blockers:
  - none for completing this discovery task; binding implementation is blocked by missing safe authoritative account-control claim capability and unresolved product ownership lifecycle/cardinality
cross_repository_tasks:
  - blakinio/canary is read-only evidence source; no writes are authorized
  - opentibiabr/login-server was inspected read-only at current main; no writes are authorized
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-19T23:42:00+02:00
head: 6fd486dc6465904a8afc3cbbb49d03f4abaaa3a0
branch: task/OTERYN-20260719-phase5-identity-canary-account-binding
pr: 27
status: validating
context_routes:
  - agent-governance
  - architecture
  - identity-auth
  - accounts-characters
  - canary-integration
  - security
  - database
  - testing
owned_paths:
  - docs/contracts/IDENTITY_CANARY_ACCOUNT_BINDING_CONTRACT.md
  - docs/contracts/CHARACTER_CREATION_CONTRACT.md
  - docs/contracts/AUTH_GAME_LOGIN_CONTRACT.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260719-phase5-identity-canary-account-binding.md
  - docs/agents/tasks/active/OTERYN-20260719-phase5-character-creation-contract.md
  - docs/agents/tasks/archive/OTERYN-20260719-phase5-character-creation-contract.md
proven:
  - PR #26 was squash-merged to main as ab78d6ac3bc674deb0868195563b61a753d95f98 after exact-head CI and Agent Governance passed and the final merge gate remained clean.
  - Live open-PR search after PR #26 merge returned no open pull requests before this successor task was claimed.
  - The predecessor active task blob c66f27a5a04e272c35e20b58509acccce7cec933 was copied unchanged to archive and the active copy removed.
  - Current Platform Identity persistence and registration contain no Canary account key/binding, and Platform credentials remain separate from game credentials.
  - Current Canary schema at 2b6ae86539640dfc52323e9d5abbde31d6610c5f has unique accounts.name, non-unique indexed accounts.email and credential-sensitive accounts.password.
  - The current database-enforced Platform canary read credential can SELECT only players, guilds, guild_membership, guild_ranks, channels and cluster_sessions; it cannot read accounts or account_sessions.
  - Current Canary native password verification accepts custom Argon2 and SHA-1 fallback, while current external login-server main 2612930de4d97123a397f8f2cd0d5f784094af40 verifies SHA-1 only.
  - External login-server normal login authenticates with (email OR unique name) plus SHA-1 password match, then loads players, creates a 24-hour account_sessions game session and returns game-login session material; it is not a side-effect-free ownership-claim endpoint.
  - No purpose-built current Canary/login-server external account-control claim API was proven that returns a short-lived single-use assertion bound to one accounts.id without creating a reusable game session.
  - IDENTITY_CANARY_ACCOUNT_BINDING_CONTRACT records that a durable Platform-owned mapping is conceptually valid only after trustworthy account-control proof, but approves no binding implementation yet.
derived:
  - Matching existing accounts by email cannot establish authorization because email is non-unique.
  - Direct Platform verification of accounts.password would create another credential authority and require access to sensitive credential hashes, violating the current least-privilege boundary.
  - Reusing normal external login as a claim flow is unsafe as the general binding contract because it has game-session side effects and SHA-1-only compatibility.
  - A future safe existing-account claim should be owned by the authoritative game-authentication side and return a short-lived single-use accounts.id-bound claim assertion with no reusable game session side effect.
  - An alternative future path is atomic binding when Platform itself is approved to create the Canary account, but account creation/credential authority is not approved today.
unknown:
  - product cardinality: one Platform Identity to one Canary account versus multiple accounts
  - unlink, transfer, rebind and recovery policy
  - privileged break-glass ownership procedure after Phase 6 Admin/RBAC exists
  - which authentication component will become authoritative for a future purpose-built account-control claim capability
conflicts:
  - native Canary and current external login-server support different password verification compatibility, so a claim path based only on the external SHA-1 login cannot prove control for every currently supported account credential state
first_failure:
  marker: SAFE_EXISTING_ACCOUNT_CLAIM_CAPABILITY_MISSING
  evidence: no inspected current path proves one exact accounts.id to Platform without either ambiguous email inference, direct credential-hash access, game-login/session side effects, or incomplete password-format compatibility.
rejected_hypotheses:
  - Bind by email equality alone: rejected because Canary accounts.email is non-unique.
  - Trust browser-supplied accounts.id: rejected because client input cannot prove ownership.
  - Read accounts.password and verify inside Platform: rejected because it exposes shared credential hashes and duplicates incompatible authentication logic.
  - Reuse external login-server normal login as the binding API: rejected because success creates a reusable 24-hour account_sessions entry and supports SHA-1 verification only.
  - Emulate native Canary ProtocolLogin from Platform: rejected because it is a game-client login protocol, not a purpose-built server-to-server claim contract.
changed_paths:
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260719-phase5-identity-canary-account-binding.md
  - docs/agents/tasks/active/OTERYN-20260719-phase5-character-creation-contract.md
  - docs/agents/tasks/archive/OTERYN-20260719-phase5-character-creation-contract.md
  - docs/contracts/IDENTITY_CANARY_ACCOUNT_BINDING_CONTRACT.md
validation:
  - command: successor task claim preflight
    result: PASS
    evidence: main at ab78d6ac3bc674deb0868195563b61a753d95f98 and no open PR before branch creation
  - command: predecessor archive exact-content check
    result: PASS
    evidence: archive was created from merged predecessor blob c66f27a5a04e272c35e20b58509acccce7cec933 before active copy deletion
  - command: Platform Identity and DB privilege boundary inspection
    result: PASS
    evidence: Identity model/migrations/registration and canary-readonly provisioning prove no binding and no accounts/account_sessions access
  - command: current Canary account/auth inspection
    result: PASS
    evidence: schema and native Account authentication at Canary 2b6ae86539640dfc52323e9d5abbde31d6610c5f inspected read-only
  - command: current external login-server account/login inspection
    result: PASS
    evidence: account.go and grpc/login.go at 2612930de4d97123a397f8f2cd0d5f784094af40 inspected read-only; normal login creates account_sessions after SHA-1 credential verification
  - command: exact-head GitHub Actions CI and Agent Governance
    result: NOT_RUN
    evidence: final discovery documentation head not yet validated
blockers:
  - none for discovery merge; binding implementation and all user-scoped Canary mutations remain blocked pending a safe account-control claim capability or approved atomic account-creation path plus explicit ownership lifecycle policy
next_action: Update ACTIVE_WORK with the exact binding blocker and cross-repository/authentication dependency, then validate final diff and exact-head CI plus Agent Governance before readiness/merge.
```

## Notes

This task resolves authorization ownership only. Current evidence does not permit a self-service existing-account binding implementation. It must not be used to bypass the separate credential/game-login authority blockers or to grant character/account mutation capability prematurely.
