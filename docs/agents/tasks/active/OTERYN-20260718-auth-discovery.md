# OTERYN-20260718 Authentication and identity discovery

## Goal

Prove the current end-to-end authentication path from account login through game-world entry against the actual current `blakinio/canary` source, identify all password/session/token verification paths and bypasses, then update `docs/contracts/AUTH_GAME_LOGIN_CONTRACT.md` with current `PROVEN` behavior and a separate recommended target contract. Do not implement credential migration or authentication code.

## Acceptance criteria

- [x] Pin discovery evidence to exact current Oteryn Platform and Canary commit SHAs; pin current upstream login-server source separately from the unpinned Docker `latest` image.
- [x] Prove which components can receive initial game-client authentication requests in the repository-supported topology.
- [x] Prove account descriptor rules and current password/hash verification formats.
- [x] Prove current password/session/token paths into character list and game-world authentication.
- [x] Prove session/token issuance, storage, TTL, single-use/replay and revocation semantics where source evidence exists.
- [x] Prove account-ban enforcement and direct/legacy alternate login paths.
- [ ] Prove current password change/reset, email verification and MFA behavior or retain them as `UNKNOWN`/absent when not implemented.
- [x] Document outage/failure behavior for auth/session dependencies where source evidence exists.
- [ ] Separate current-state `PROVEN` facts from recommended target architecture/policy in the final contract.
- [x] Classify material conclusions as `PROVEN`, `DERIVED`, `UNKNOWN` or `CONFLICT`.
- [ ] Update `docs/contracts/AUTH_GAME_LOGIN_CONTRACT.md`.
- [x] Do not implement credential migration, auth endpoints, MFA or Canary writes.
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
  - credential migration remains blocked until a tested password-format migration strategy exists
  - production topology remains unproven because Docker uses an unpinned login-server:latest image and no deployment evidence was provided
cross_repository_tasks:
  - blakinio/canary read-only authentication/session source discovery
  - opentibiabr/login-server read-only compatibility discovery
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-18T23:20:00+02:00
head: df4159d182d1533c60f2721b65aef3013ede22f8
branch: task/OTERYN-20260718-auth-discovery
pr: 3
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
  - Canary remained read-only throughout discovery.
  - Canary auth evidence is pinned to 096f6445b29f69a62f03d391a2c02c4dcee74feb.
  - Current upstream opentibiabr/login-server source evidence is pinned to 2612930de4d97123a397f8f2cd0d5f784094af40, but Canary Docker quickstart uses the unpinned image tag opentibiabr/login-server:latest so exact deployed image/source equivalence is not proven.
  - Canary AccountRepositoryDB uses account name for old protocol descriptors and email for modern protocol descriptors; session lookup accepts SHA-256 or legacy SHA-1 hashes of the presented session key.
  - Canary Account::authenticatePassword first tries its custom Argon2id verifier and then falls back to SHA-1; failed password authentication currently logs the stored credential hash value, which is a security-sensitive logging defect.
  - Canary custom Argon2 verifier extracts Base64 salt/hash components and recomputes raw Argon2id using configured memory/time/parallelism values; standard Laravel PHC Argon2id string compatibility is not proven.
  - Canary config defaults are authType=password, passwordType=sha1, resetSessionsOnStartup=false and allowOldProtocol=true.
  - Native Canary ProtocolLogin authenticates the character-list connection directly with account password and is registered by Game::start on loginProtocolPort.
  - Canary Docker quickstart simultaneously exposes the native Canary login port and runs opentibiabr/login-server as an HTTP/gRPC client login webservice connected directly to the same MySQL database.
  - The quickstart removes MyAAC login.php and documents the external login-server as the intended client login webservice, but this does not disable the native Canary ProtocolLogin port.
  - Current opentibiabr/login-server source authenticates only by SHA-1 of the submitted password against accounts.password, matching either accounts.email or accounts.name.
  - Current opentibiabr/login-server source creates a cryptographically random 32-byte hex session key, stores only its SHA-256 hash in account_sessions and sets expiry to 24 hours.
  - External login-server session persistence fails closed when account_sessions cannot be written; a missing account_sessions table returns a specific session-storage-unavailable error.
  - External login-server character-list query loads all players for account_id and does not filter players.deletion; Canary world authentication later enforces character ownership and deletion state.
  - Canary LoginSessionManager issues a separate 256-bit in-memory token with default TTL 60 seconds, maximum 4096 active entries, account/character-set/protocol binding and unconditional single-use consumption.
  - LoginSessionManager tokens are process-local in-memory state, are not IP-bound, are destroyed by expiry/consumption/eviction/process restart, and have no proven explicit account-wide revocation API.
  - Modern non-old SessionKey clients with authType=session first attempt LoginSessionManager single-use token consumption; failure falls through to DB-backed account_sessions authentication rather than failing immediately.
  - DB-backed account_sessions authentication checks expiry but is replayable until expiry or external deletion because Canary only reads the row and validates expires; no one-time consumption is performed.
  - Modern clients with authType=password receive/echo the ad-hoc accountDescriptor + newline + password session-key value and Canary splits it and revalidates the password on the game connection.
  - Old-protocol AccountPassword layouts have no opaque session-key field and send account descriptor/password directly on both login and game connections.
  - Regardless of password, DB session or pre-authenticated one-time token, Canary game-world authentication always verifies character ownership and deletion state.
  - IP bans are checked on both native character-list ProtocolLogin and ProtocolGame connections; account bans are enforced later during ProtocolGame::login before world placement, not during external or native character-list authentication.
  - Cluster session acquisition is a separate online/concurrency gate after account/character authentication and ban checks; it is not an authentication credential.
  - The current docs/systems/login-session-manager.md says the manager is not wired, but pinned ProtocolLogin and ProtocolGame source do wire issueToken/consumeToken; the documentation is stale relative to source.
  - config.lua.dist documents loginProtocolEnabled as a login-gateway process confirmation, while pinned Game::start registers ProtocolLogin without consulting that flag at the registration point.
  - Current upstream login-server code search found account_sessions creation but no deletion/revocation operation, and no password reset/change, MFA/TOTP or email-verification implementation in that repository.
derived:
  - There is no single current authoritative authentication path across all supported clients/configurations: native Canary password login, external HTTP login-server DB sessions, modern Canary one-time login tokens, DB session fallback and legacy direct-password paths coexist.
  - A future Platform-only MFA/email-verification rule can be bypassed unless every exposed native/external login path is either disabled, network-restricted, or updated to enforce the same authoritative policy.
  - The native Canary login port exposed by Docker is an alternate credential-validation path alongside the documented external login-server and therefore must be treated as a bypass risk in target architecture.
  - Standard Laravel Argon2id hashes must not be written into accounts.password until deterministic compatibility tests or an explicit migration/translation strategy proves Canary can verify them.
  - Password changes do not inherently revoke already issued 60-second LoginSessionManager tokens or 24-hour DB account_sessions unless the relevant token/session stores are explicitly cleared; current source does not prove such coupling.
  - Account-ban policy is globally enforced for game-world entry by Canary, but not necessarily for successful character-list/session issuance.
unknown:
  - Exact production/deployed login topology and exact digest of the opentibiabr/login-server:latest image used by any real environment.
  - Whether any production firewall/proxy prevents direct access to Canary native loginProtocolPort.
  - Exact password-format generation currently used by the existing website/account-creation path; current verifier accepts SHA-1 and custom raw Argon2id but writer compatibility is not proven.
  - Standard Laravel Argon2id PHC compatibility with Canary's custom parser.
  - Actual use/effect of resetSessionsOnStartup beyond its config loading; no current source effect has yet been proven.
  - Password change/reset behavior and whether it deletes account_sessions in the currently deployed website/admin component.
  - Email verification and MFA policy in the currently deployed environment; no enforcement exists in the inspected Canary/native login or upstream login-server paths.
  - Whether loginProtocolEnabled is enforced elsewhere in runtime/network topology despite unconditional ProtocolLogin registration in Game::start.
conflicts:
  - docs/systems/login-session-manager.md states LoginSessionManager is not wired, while current ProtocolLogin/ProtocolGame source wires issuance and consumption.
  - The intended Docker quickstart login path is opentibiabr/login-server, yet the same compose file exposes Canary loginProtocolPort and Game::start registers native ProtocolLogin, leaving an alternate authentication path unless externally restricted.
first_failure:
  marker: CREDENTIAL_HASH_LOGGING
  evidence: pinned Canary Account::authenticatePassword logs the stored accounts.password value on failed authentication.
rejected_hypotheses:
  - Canary ProtocolLogin is the sole supported login-server path: rejected by current Canary README/docker-compose using opentibiabr/login-server for client HTTP login while native ProtocolLogin also exists.
  - External login-server supports Canary custom Argon2 password verification: rejected by pinned login-server Account.Authenticate implementation, which computes SHA-1 only.
  - Secure LoginSessionManager token fully replaces DB sessions in authType=session mode: rejected because ProtocolGame explicitly falls through to account_sessions authentication when one-time token redemption does not succeed.
  - Account ban prevents session issuance/character-list login: rejected because account-ban enforcement occurs later in ProtocolGame::login.
  - Character-list providers consistently hide deleted characters: rejected because native Canary account player loading excludes deletion != 0 while current external login-server query has no deletion filter.
  - Password change automatically revokes every current game-login credential: not proven; one-time tokens are independent in-memory entries and DB sessions persist until expiry/deletion.
changed_paths:
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260718-auth-discovery.md
validation:
  - command: startup repository/task/PR verification
    result: PASS
    evidence: Platform main f968681732ec3e0688ff29426108b49dce79af16; ACTIVE_WORK had no active task; no matching open PR
  - command: pinned Canary auth/session source inspection
    result: PASS
    evidence: blakinio/canary SHA 096f6445b29f69a62f03d391a2c02c4dcee74feb; ProtocolLogin, ProtocolGame, Account, AccountRepositoryDB, IOLoginData, LoginSessionManager, config and runtime wiring inspected read-only
  - command: pinned upstream login-server source inspection
    result: PASS
    evidence: opentibiabr/login-server SHA 2612930de4d97123a397f8f2cd0d5f784094af40; Account authentication/session creation, login flow and player list inspected read-only
blockers:
  - credential migration remains blocked until password-write compatibility and deployed path inventory are proven
  - production security-ready claim blocked by credential-hash logging defect and unresolved alternate login-path enforcement
next_action: Complete targeted evidence for session reset/revocation, password change/reset, email verification/MFA and loginProtocolEnabled behavior, then write the current-state and recommended-target sections of AUTH_GAME_LOGIN_CONTRACT.md.
```

## Notes

Do not infer deployed topology from component names. Source proves code paths, not necessarily which network endpoint is deployed in production unless deployment evidence also exists.