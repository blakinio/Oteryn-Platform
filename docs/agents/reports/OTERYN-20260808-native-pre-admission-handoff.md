# Native pre-admission handoff architecture review — 2026-08-08

## Result

`ARCHITECTURE_BOUNDARY_RESOLVED — IMPLEMENTATION / CONSUMER / CUTOVER NOT IMPLIED`

## Evidence reviewed

- accepted Platform ADR 0031;
- current focused `OTERYN_V2_INTEGRATION_ARCHITECTURE.md`;
- current `GAME_GATEWAY_IDENTITY_CONTRACT.md`;
- historical/transitional `OTERYN_NATIVE_GAMEPLAY_PROTOCOL_CONTRACT.md`;
- accepted `OTERYN_V2_RUNTIME_STATUS_PROJECTION_CONTRACT.md`;
- read-only accepted Oteryn-v2 ADR-0003 Platform Identity/Game Gateway/admission boundary.

## Finding

Platform and Oteryn-v2 already agreed on authority direction, but Platform lacked a focused target semantic contract for the object crossing from authenticated/routed Gateway orchestration into native game admission. The unresolved gap could permit future implementation to conflate a Platform authorization attempt with the game-owned canonical gameplay session, reuse legacy Canary IDs/semantics, or mishandle replay/ambiguous outcomes.

## Resolution

`docs/contracts/OTERYN_V2_PRE_ADMISSION_HANDOFF_CONTRACT.md` now freezes the Platform-side semantics without inventing unfinished Oteryn-v2 implementation details.

The contract establishes:

- Game Login Ticket, native pre-admission material and canonical game-domain `GameSessionId` are distinct lifecycle objects;
- Platform authorization is one bounded attempt, not proof of final gameplay admission;
- canonical `AccountId`, `CharacterId`, `WorldId`, `ChannelId` and route/revision context replace Canary numeric identities in the native boundary;
- issuance depends on authoritative ticket redeem, safe current character authorization evidence, World Registry policy and fresh applicable current-owner runtime evidence;
- audience/route/revision binding, short lifetime, one-success anti-replay semantics and ambiguous-outcome handling are mandatory;
- Oteryn-v2 revalidates authoritative ownership/lifecycle and owns final admission, character lease, fencing, canonical `GameSessionId`, reconnect and recovery;
- Channel switching requires fresh destination authorization;
- no password/OAuth/Canary fallback is permitted;
- exact transport/encoding/signing, TTL value, replay store, FND-04 state machine, lease/fencing algorithm and canonical `GameSessionId` wire form remain external/deferred.

## Reconciliation

- `OTERYN_V2_INTEGRATION_ARCHITECTURE.md` routes admission semantics to the focused contract, moves the item from P1 deferred backlog to resolved focused boundaries and preserves implementation/cutover nonclaims.
- `GAME_GATEWAY_IDENTITY_CONTRACT.md` explicitly stops its native authority at ticket redemption/orchestration and routes post-redeem native handoff semantics to the focused contract while retaining Canary-compatible response evidence as compatibility state.
- historical Platform native Game Session v2/protocol artifacts remain reconciliation evidence only and receive no new target authority.

## Nonclaims

This review does not prove or authorize:

- native pre-admission producer runtime implementation;
- Oteryn-v2 consumer implementation;
- exact cross-repository envelope/schema/transport compatibility;
- game lease/fencing implementation;
- protocol-oteryn activation;
- staging E2E;
- production activation;
- any write to Oteryn-v2.

## Self-review

```yaml
result: PASS
scope_paths:
  - docs/contracts/OTERYN_V2_PRE_ADMISSION_HANDOFF_CONTRACT.md
  - docs/architecture/OTERYN_V2_INTEGRATION_ARCHITECTURE.md
  - docs/contracts/GAME_GATEWAY_IDENTITY_CONTRACT.md
material_findings: []
authority_conflicts: []
negative_paths_checked:
  - replay/duplicate use
  - ambiguous issuer outcome
  - ambiguous game-admission outcome
  - stale ownership projection
  - stale runtime/route owner generation
  - wrong route/channel/revision
  - account-security change after issuance
  - channel switching
  - reconnect/recovery
  - legacy password/OAuth/Canary fallback
compatibility_checked: true
rollback_checked: true
external_repository_writes: false
runtime_browser_e2e: NOT_APPLICABLE
```

## Remaining external unknowns

- exact accepted Oteryn-v2 FND-04 admission/session state machine;
- exact pre-admission envelope schema/encoding/transport/signing primitive;
- exact short TTL value and security-revocation-after-issuance mechanism;
- exact atomic consume/replay mechanism;
- exact character lease/fencing algorithm;
- canonical `GameSessionId` wire representation;
- exact producer/consumer compatibility revision and native E2E evidence.
