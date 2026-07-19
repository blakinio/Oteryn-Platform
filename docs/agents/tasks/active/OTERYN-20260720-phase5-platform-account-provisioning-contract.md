# OTERYN-20260720 Phase 5 Platform account provisioning contract

## Goal

Define the smallest evidence-backed operation-level contract for Platform-originated Canary account creation plus immutable `1 Platform Identity <-> 1 Canary accounts.id` ownership binding under the accepted authoritative Platform account model. Approve implementation only if exact account fields, credential/game-login isolation, least-privilege write grants, cross-database failure recovery, idempotency/concurrency and audit invariants are proven. Do not implement Canary account or character writes in this contract task.

## Acceptance criteria

- [x] Verify current Oteryn Platform `main`, open PR state, active task state and predecessor Phase 5 ownership decision before claiming scope.
- [x] Revalidate current Platform Identity persistence, registration transaction, database connection boundaries and absence of existing binding/provisioning infrastructure.
- [x] Revalidate current Canary `accounts` schema, required fields, uniqueness constraints, account-create trigger side effects and current account authentication behavior at current Canary `main`.
- [x] Revalidate current external login-server credential verification and session creation behavior at current upstream `main`.
- [x] Define the exact Platform-owned binding/provisioning state model for immutable 1:1 ownership.
- [x] Define an account credential field strategy that cannot be used as a user reusable password and does not duplicate Canary credential verification.
- [x] Define the exact dedicated least-privilege Canary provisioning connection/grants without broadening the existing read-only `canary` connection.
- [x] Define deterministic cross-database saga/retry/compensation semantics that prevent orphan authorization, duplicate accounts and conflicting bindings.
- [x] Define audit/readiness semantics and negative/race/failure cases required before implementation.
- [x] Add/update durable contracts and active-work handoff with the exact implementation gate.
- [x] Record exact future login-server/possible Canary cross-repository changes without modifying those repositories.
- [x] Do not modify Canary/login-server repositories and do not implement account/character shared writes.
- [x] Run exact-head CI and Agent Governance before readiness/merge.

## Ownership

```yaml
owned_paths:
  - docs/contracts/PLATFORM_CANARY_ACCOUNT_PROVISIONING_CONTRACT.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260720-phase5-platform-account-provisioning-contract.md
modules:
  - Identity
  - Accounts
  - Integration
  - accounts-characters
  - database
  - security
  - agent-governance
dependencies:
  - OTERYN-20260719-phase5-ownership-binding-dependency-gate
  - docs/architecture/adr/0004-authoritative-platform-account-ownership.md
  - docs/contracts/IDENTITY_CANARY_ACCOUNT_BINDING_CONTRACT.md
  - docs/contracts/AUTH_GAME_LOGIN_CONTRACT.md
  - docs/contracts/CANARY_DATA_CONTRACT.md
  - docs/architecture/DATA_OWNERSHIP.md
blockers:
  - none for contract delivery; implementation is the next bounded task
cross_repository_tasks:
  - blakinio/canary is read-only evidence source; no writes are authorized in this task; possible future game-login changes are recorded in the provisioning contract
  - opentibiabr/login-server is read-only evidence source; no writes are authorized in this task; required future Platform-authorized login exchange is recorded in the provisioning contract
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-20T00:56:00+02:00
head: 08e709418cb55320de089973139b0ab17f1eef59
branch: task/OTERYN-20260720-phase5-platform-account-provisioning-contract
pr: 31
status: ready
context_routes:
  - agent-governance
  - architecture
  - auth-identity
  - accounts-characters
  - canary-integration
  - database
  - security
  - testing
owned_paths:
  - docs/contracts/PLATFORM_CANARY_ACCOUNT_PROVISIONING_CONTRACT.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260720-phase5-platform-account-provisioning-contract.md
proven:
  - Oteryn Platform main is 3b22f13ded681abf8c01b8e4fa816fdc616c7c15, the squash merge of PR #30 post-merge authoritative ownership housekeeping.
  - Live open-PR search returned no open Oteryn Platform pull requests before this task branch was created; PR #31 is the current task PR.
  - ACTIVE_WORK had no active task and named Platform-originated Canary account creation plus immutable 1:1 ownership binding as the recommended next bounded task.
  - ADR 0004 and the binding contract establish the greenfield authoritative Platform model and exclude existing-account claim/import from Phase 5.
  - Platform RegisterIdentity currently creates only the Platform Identity and registration security event inside one Platform DB transaction; no Canary binding/provisioning state exists.
  - The existing Platform canary SQL connection remains the read-only oteryn_readonly boundary and no separate Canary provisioning connection exists.
  - Current Canary main is 2c448205d864f6388b8be932ecbb1a9e6dcaffe0; the two commits after the previous account evidence pin change only OAM market documentation/task history.
  - Current Canary accounts requires unique name and non-null password; email and all other inspected account fields have usable defaults, while creation is an explicit account-age field with default zero.
  - Current Canary oncreate_accounts AFTER INSERT trigger atomically creates Enemies, Friends and Trading Partner account_vipgroups rows.
  - Current native Canary authentication accepts Canary custom Argon2 verification then SHA-1 fallback; current external login-server main remains 2612930de4d97123a397f8f2cd0d5f784094af40 and verifies SHA-1 before creating a reusable DB account_sessions game session.
  - PLATFORM_CANARY_ACCOUNT_PROVISIONING_CONTRACT approves a non-user random sink credential whose plaintext is never persisted/exposed, a server-generated 120-bit random immutable provisioning name, a pending-before-write Platform saga record and a separate least-privilege provisioning connection.
  - The approved saga uses forward recovery by persisted provisioning name plus creation epoch after Canary commit/Platform-finalization failure and never auto-deletes the committed Canary account as compensation.
  - The contract records the required future login-server Platform-assertion exchange and explicitly identifies possible separately authorized Canary work if stronger session/revocation/direct assertion/fencing semantics are required.
  - Delivery-validation head 08e709418cb55320de089973139b0ab17f1eef59 passed Agent Governance run 29706085234 (#338) and CI run 29706085260 (#417).
derived:
  - Existing password login paths are not a usable user authentication path for Platform-originated accounts when the random sink plaintext is never persisted or disclosed.
  - No Canary repository change is required for the account-create write itself; the schema and trigger support the bounded insert contract.
  - The future Platform-authorized game-login bridge remains a separate cross-repository dependency and is not a blocker to implementing ownership provisioning while game-account readiness is kept distinct from game-login availability.
unknown:
  - exact final game-session TTL, replay/single-use and revocation design for the future Platform-authorized login exchange
  - whether the final game-login design requires any Canary code change beyond existing DB-backed account_sessions consumption
conflicts:
  - current native Canary and external login-server reusable-password paths remain incompatible with the target single Platform credential authority, but the approved sink credential makes those paths unreachable to normal Platform users until the dedicated login bridge exists
first_failure:
  marker: none
  evidence: the bounded account-provisioning operation has an evidence-backed implementation shape; remaining auth integration is a separately recorded future dependency
rejected_hypotheses:
  - Reuse the Platform Identity password in accounts.password: rejected because it would duplicate/reintroduce shared reusable credential authority and current hash formats are not compatible.
  - Leave accounts.password empty: rejected because the current schema requires a non-null password and an empty known value would create a weak alternate login secret.
  - Use Identity email as the Canary ownership/binding key: rejected because Canary email is non-unique and ownership must come from the immutable Platform binding.
  - Broaden the existing canary read connection for INSERT: rejected because read and provisioning privilege boundaries must remain separate.
  - Delete a Canary account automatically after Platform finalization failure: rejected because forward recovery is deterministic and destructive compensation after committed Canary trigger side effects is unnecessarily risky.
changed_paths:
  - docs/contracts/PLATFORM_CANARY_ACCOUNT_PROVISIONING_CONTRACT.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260720-phase5-platform-account-provisioning-contract.md
validation:
  - command: startup source-of-truth verification
    result: PASS
    evidence: main 3b22f13ded681abf8c01b8e4fa816fdc616c7c15, no open PRs, ACTIVE_WORK no active task, accepted ADR 0004 ownership direction
  - command: Platform registration/write-boundary inspection
    result: PASS
    evidence: RegisterIdentity performs only Platform DB Identity/audit writes and current config has only the read-only canary integration connection
  - command: current Canary account-create evidence revalidation
    result: PASS
    evidence: Canary main 2c448205d864f6388b8be932ecbb1a9e6dcaffe0 schema accounts table and oncreate_accounts trigger inspected; intervening commits are docs-only
  - command: current auth compatibility revalidation
    result: PASS
    evidence: native Canary SHA-1 fallback and external login-server SHA-1 verification/session creation remain unchanged from inspected current source
  - command: operation-contract threat/failure review
    result: PASS
    evidence: contract defines least privilege, pending-before-write saga intent, exact 1:1 constraints, deterministic partial-failure recovery, duplicate/race handling and secret-safe audit rules
  - command: delivery-validation Agent Governance run 29706085234 (#338)
    result: PASS
    evidence: exact delivery-validation head 08e709418cb55320de089973139b0ab17f1eef59 completed successfully
  - command: delivery-validation CI run 29706085260 (#417)
    result: PASS
    evidence: exact delivery-validation head 08e709418cb55320de089973139b0ab17f1eef59 completed successfully, including formatting, static analysis and tests
blockers:
  - none for merging the contract; account provisioning plus binding implementation remains the next task
next_action: Revalidate CI and Agent Governance on the final ready-checkpoint head, inspect final diff/review/base divergence, then squash-merge PR #31 only if the merge gate remains clean.
```

## Notes

This task approves the bounded account-provisioning implementation shape but performs no Canary/shared write. Per product direction, any required future Canary/login-server changes are recorded durably in the provisioning contract before a separately authorized cross-repository task is started.
