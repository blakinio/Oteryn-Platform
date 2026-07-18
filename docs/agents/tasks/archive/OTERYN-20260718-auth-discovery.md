# OTERYN-20260718 Authentication and identity discovery

## Status

Completed on 2026-07-18. PR #3 is the delivery PR for the evidence-backed web-to-game authentication contract.

## Goal

Prove the current end-to-end authentication path from account login through game-world entry against current Canary and upstream login-server source, identify password/session/token verification paths and bypasses, and document a separate recommended target contract without implementing credential migration or authentication code.

## Completed deliverables

- pinned Oteryn Platform baseline to `f968681732ec3e0688ff29426108b49dce79af16`;
- pinned Canary auth evidence to `096f6445b29f69a62f03d391a2c02c4dcee74feb`;
- pinned current upstream `opentibiabr/login-server` source evidence to `2612930de4d97123a397f8f2cd0d5f784094af40`, while retaining the unpinned Docker `latest` deployment as `UNKNOWN`;
- proved native Canary password login, external HTTP/gRPC login-server DB sessions, Canary one-time tokens, DB session fallback and legacy direct-password paths;
- proved current password descriptor/hash compatibility behavior and blocked speculative Laravel credential migration;
- proved one-time token TTL/single-use/process-local semantics and DB-session 24-hour replay semantics;
- proved account-ban enforcement occurs at Canary world entry rather than session issuance;
- documented bypass risk from parallel native/external/legacy authentication paths;
- documented current password-reset/change/MFA/email-verification revocation gaps as `UNKNOWN`/not enforced in inspected paths;
- documented a recommended target architecture with one authoritative Identity policy and short-lived atomic game-login authorization;
- recorded security finding that failed native password authentication logs the stored credential hash;
- updated `docs/contracts/AUTH_GAME_LOGIN_CONTRACT.md`;
- made no writes to Canary or upstream login-server repositories and implemented no auth/credential migration.

## Acceptance criteria result

- PASS — exact Platform and Canary SHAs pinned.
- PASS — external login-server current source SHA pinned separately from unpinned deployment image.
- PASS — initial authentication entry points and alternate paths mapped.
- PASS — password descriptor/hash behavior documented.
- PASS — password/session/token paths to character list and game world mapped.
- PASS — token/session TTL, single-use/replay and known revocation behavior documented.
- PASS — account ban and direct/legacy bypass paths documented.
- PASS — password change/reset, email verification and MFA gaps retained explicitly rather than guessed.
- PASS — outage/failure behavior documented where source proves it.
- PASS — current state separated from recommended target design.
- PASS — no credential migration/auth implementation performed.
- PASS — current-head CI passed.

## Final context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-18T23:35:00+02:00
head: c44770a41dfdc36d21584ba5efdd6bd2ce6eb1f7
branch: task/OTERYN-20260718-auth-discovery
pr: 3
status: ready
context_routes:
  - agent-governance
  - auth-identity
  - canary-integration
  - security
owned_paths:
  - docs/contracts/AUTH_GAME_LOGIN_CONTRACT.md
  - docs/agents/tasks/archive/OTERYN-20260718-auth-discovery.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/PROJECT_STATE.md
proven:
  - Canary auth evidence is pinned to 096f6445b29f69a62f03d391a2c02c4dcee74feb.
  - Current upstream login-server source evidence is pinned to 2612930de4d97123a397f8f2cd0d5f784094af40; Docker quickstart still uses unpinned opentibiabr/login-server:latest.
  - Native Canary and external login-server authentication paths coexist in repository-supported topology.
  - Native Canary accepts custom Argon2 verification then SHA-1 fallback; current upstream external login-server verifies SHA-1 only.
  - Standard Laravel Argon2id PHC compatibility with Canary custom verification is not proven.
  - Canary LoginSessionManager issues 256-bit in-memory single-use tokens with default 60-second TTL and no proven account-wide revoke operation.
  - External login-server issues 32-byte random DB-backed account_sessions keys with SHA-256 stored identifier and 24-hour expiry; Canary DB-session authentication is replayable until expiry/deletion.
  - Modern authType=session one-time-token failure falls back to DB-backed account_sessions authentication.
  - Old protocols send reusable password credentials directly on login and game connections.
  - Character ownership/deletion checks remain enforced at Canary world authentication for every account-auth path.
  - Account bans are enforced at Canary ProtocolGame world entry, not necessarily before character-list/session issuance.
  - Failed native Canary password authentication logs the stored credential value and must be fixed before production security readiness.
  - docs/contracts/AUTH_GAME_LOGIN_CONTRACT.md now contains current-state evidence, conflicts, bypass analysis, revocation matrix, recommended target contract and rollout gates.
  - PR #3 changed only task/governance and AUTH_GAME_LOGIN_CONTRACT.md paths within owned_paths before final handover updates.
  - GitHub Actions run 29660491141 passed Composer validation, lockfile install, Pint and Laravel tests on the contract head.
derived:
  - Credential migration cannot safely begin until direct/native/external login paths share one authoritative Identity policy or are explicitly disabled/restricted.
  - Platform-only MFA/email verification cannot gate all game login while alternate password/session paths remain externally reachable.
  - The safest independent implementation work that can proceed without resolving auth writes is the read-only PublicGameData model against already proven Canary read fields.
unknown:
  - Exact deployed login-server image digest/version and production network exposure.
  - Exact currently deployed website password reset/change implementation and account_sessions revocation behavior.
  - Whether production networking already prevents direct access to native Canary loginProtocolPort.
  - Standard Laravel Argon2id PHC compatibility with Canary custom parser.
  - Exact runtime effect of resetSessionsOnStartup at the pinned Canary revision.
  - Immediate active-game-session revocation behavior for password changes/bans.
conflicts:
  - Current Canary LoginSessionManager documentation says it is not wired while current source wires issuance/consumption.
  - Docker quickstart documents external login-server as intended client login while also publishing native Canary loginProtocolPort.
  - Current native Canary supports custom Argon2 plus SHA-1 while current upstream external login-server verifies SHA-1 only.
  - Native and external character-list behavior differ for players.deletion filtering.
first_failure:
  marker: CREDENTIAL_HASH_LOGGING
  evidence: pinned Canary Account::authenticatePassword logs the stored accounts.password value on failed authentication.
rejected_hypotheses:
  - Canary ProtocolLogin is the sole login path: rejected by Docker quickstart external login-server evidence.
  - External login-server supports Canary custom Argon2: rejected by pinned SHA-1-only Account.Authenticate implementation.
  - Canary one-time token completely replaces DB sessions: rejected by explicit account_sessions fallback in ProtocolGame.
  - Password change inherently revokes existing game-login credentials: not proven; token/session stores are independent of current password after issuance.
  - Platform-only MFA can be globally enforced without changing other login paths: rejected by proven native/external/legacy alternatives.
changed_paths:
  - docs/contracts/AUTH_GAME_LOGIN_CONTRACT.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/archive/OTERYN-20260718-auth-discovery.md
  - docs/agents/PROJECT_STATE.md
validation:
  - command: pinned Canary auth/session source inspection
    result: PASS
    evidence: Canary SHA 096f6445b29f69a62f03d391a2c02c4dcee74feb
  - command: pinned upstream login-server source inspection
    result: PASS
    evidence: opentibiabr/login-server SHA 2612930de4d97123a397f8f2cd0d5f784094af40
  - command: auth contract consistency/security review
    result: PASS
    evidence: unsupported behavior remains UNKNOWN/CONFLICT; current behavior and target recommendations are separated
  - command: GitHub Actions CI run 29660491141
    result: PASS
    evidence: composer validate, composer install, composer format:check and composer test passed
blockers:
  - credential migration remains blocked by heterogeneous verification paths and unproven Laravel/Canary hash compatibility
  - production auth readiness blocked by stored credential hash logging and unresolved alternate login-path enforcement
  - no blocker to merging this documentation-only discovery task
next_action: Create OTERYN-20260718-game-read-model and implement only the proven read-only highscores, character-profile, guild and channel/server-status surfaces from CANARY_DATA_CONTRACT.md, leaving cluster-wide online character identity UNKNOWN until its source/freshness contract is proven.
```

## Handover

The authoritative detailed current/target authentication findings are in `docs/contracts/AUTH_GAME_LOGIN_CONTRACT.md`. Do not start credential migration from chat history or generic Laravel defaults. Continue from the single `next_action` above.