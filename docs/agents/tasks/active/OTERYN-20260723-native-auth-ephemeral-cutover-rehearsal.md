# OTERYN-20260723-native-auth-ephemeral-cutover-rehearsal

## Goal

Own the GitHub Actions execution boundary for the cross-repository ephemeral production-like native-auth cutover rehearsal. The runner lives in `blakinio/Oteryn-Platform` because Platform is private and repository-scoped Actions credentials cannot check it out from Canary. The validation harness remains owned by Canary task `CAN-20260723-native-auth-ephemeral-cutover-rehearsal` on a public exact revision.

Maximum evidence classification is `PRODUCTION_LIKE_PROVEN`. This task performs no production deployment, uses no production secrets or user data, does not remove legacy auth, and does not close the manual Production Go-Live Gate.

## Ownership

```yaml
owned_paths:
  - docs/agents/tasks/active/OTERYN-20260723-native-auth-ephemeral-cutover-rehearsal.md
  - .github/workflows/native-auth-ephemeral-cutover-rehearsal.yml
  - .github/workflows/native-auth-canary-cache-build.yml
  - tests/e2e/native_auth_ephemeral_cutover/acceptance_extensions.py
  - tests/e2e/native_auth_ephemeral_cutover/exact_runner.py
  - tests/e2e/native_auth_ephemeral_cutover/platform_runner.py
modules:
  - native-auth production-like validation runner
dependencies:
  - Oteryn Platform b5dd6a7be5c704d5706241240e06f8bb8c4b5efe
  - Game Gateway 53158217a6c6017230301cf4daa783b04fcc13d5
  - Canary b15b7d544f4795e3a2a65b88de35391b9fd0a20d
  - OTClient bb87346f6c516a19d19497d82bb01fb389334ff5
  - Canary rehearsal harness 1046687e44aa2f9321cf7d71364dc076aedf08c5
blocks:
  - PRODUCTION_LIKE_PROVEN native-auth rehearsal evidence
cross_repository_tasks:
  - CAN-20260723-native-auth-ephemeral-cutover-rehearsal
  - OTERYN-20260724-trusted-reverse-proxy-scheme
  - OTERYN-20260724-oauth-token-cache-headers
  - CAN-20260724-game-session-cache-headers
  - OTERYN-20260723-native-auth-production-cutover
  - CAN-20260723-oteryn-native-auth-production-cutover
```

## Acceptance criteria

- [x] Runner executes in the private Platform repository so exact Platform/Gateway source is directly available without cross-private-repository credentials.
- [x] Exact public Canary and OTClient revisions are checked out and exact binary artifacts are checksum-verified before reuse.
- [x] Exact Canary harness revision is recorded and used without silent fallback or mutation.
- [x] OAuth PKCE, ticket, Gateway, Game Session, physical OTClient, failure injection, correlation, cache, rotation and rollback assertions are encoded in the final matrix.
- [ ] Full ephemeral production-like rehearsal completes with sanitized retained evidence.
- [x] Result is never classified above `PRODUCTION_LIKE_PROVEN`.
- [x] Manual production gate remains pending.

## Security boundaries

- Trust boundary: OTClient -> Platform public HTTPS -> Gateway public HTTPS -> Platform private HTTPS -> Canary private issuer HTTPS -> Canary game protocol.
- No PAT or production secret is introduced; runtime credentials are generated ephemerally and excluded from retained evidence.
- Native issuer/routing activation and rollback are rehearsal-only.

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-24T12:15:00+02:00
head: 41c2353e37de9192f260460bb4e21dc2c857f7b3
branch: test/OTERYN-20260723-native-auth-ephemeral-cutover-rehearsal
pr: 126
status: validating
context_routes:
  - auth-identity
  - canary-integration
  - security
  - testing
owned_paths:
  - docs/agents/tasks/active/OTERYN-20260723-native-auth-ephemeral-cutover-rehearsal.md
  - .github/workflows/native-auth-ephemeral-cutover-rehearsal.yml
  - .github/workflows/native-auth-canary-cache-build.yml
  - tests/e2e/native_auth_ephemeral_cutover/acceptance_extensions.py
  - tests/e2e/native_auth_ephemeral_cutover/exact_runner.py
  - tests/e2e/native_auth_ephemeral_cutover/platform_runner.py
proven:
  - Platform run 30083664968 passed every matrix gate through cache headers, correlation, physical random-session rejection, unauthorized-character burn and Canary restart invalidation/recovery.
  - Its first failure was before the malformed Gateway boundary: browser_driver received HTTP 400 from /oauth/authorize and the fake Gateway access log remained empty.
  - Canary harness commit fdab1a6b7e4fe8275f12c19812194fbc2ee01c2c aligns malformed-helper readiness with the working happy path by waiting for CharacterList before OterynIdentity.start.
  - Canary branch head 1046687e44aa2f9321cf7d71364dc076aedf08c5 contains that helper fix plus a documentation-only active-task checkpoint; required CI 30084397393 and ownership 30084528867 passed.
  - Workflow commit 41c2353e37de9192f260460bb4e21dc2c857f7b3 pins exact harness 1046687e44aa2f9321cf7d71364dc076aedf08c5 and updates the retained-revision assertion accordingly.
  - The same workflow correction reads failure-injection-summary.json, expects the actual client-events.tsv filename and asserts failure_injection_status PASS; these latent final-gate mismatches were previously hidden because runtime failed before the assertion step.
  - Product revisions and Gateway/Canary/OTClient binary artifacts remain unchanged and checksum-gated.
unknown:
  - physical malformed Gateway request/access result with corrected UI readiness
  - final happy-path world entry, logout, Game Session replay, rotation, rollback and final smoke results
conflicts: []
first_failure:
  marker: malformed-gateway-native-ui-readiness
  evidence: run 30083664968 artifact 8592956146 retained no POST /v1/login in malformed-gateway-access.log and Platform recorded /oauth/authorize status 400
rejected_hypotheses:
  - accept timeout-only Lua evidence: rejected because physical boundary access remains mandatory
  - weaken final artifact assertions: rejected; filenames and exact harness SHA now match the actual evidence contract
  - change product revisions: rejected because the defect belongs to validation helper readiness
changed_paths:
  - .github/workflows/native-auth-canary-cache-build.yml
  - .github/workflows/native-auth-ephemeral-cutover-rehearsal.yml
  - docs/agents/tasks/active/OTERYN-20260723-native-auth-ephemeral-cutover-rehearsal.md
  - tests/e2e/native_auth_ephemeral_cutover/acceptance_extensions.py
  - tests/e2e/native_auth_ephemeral_cutover/exact_runner.py
  - tests/e2e/native_auth_ephemeral_cutover/platform_runner.py
validation:
  - command: Platform Native Auth Ephemeral Cutover Rehearsal run 30083664968
    result: FAIL
    evidence: first failure was malformed Gateway helper readiness after all earlier extended gates passed
  - command: Canary CI run 30084397393
    result: PASS
    evidence: required Canary CI passed on the helper change
  - command: Canary Agent Task Ownership run 30084528867
    result: PASS
    evidence: active task ownership and checkpoint validation passed
blockers:
  - none
next_action: execute the full exact-revision rehearsal on the corrected harness and repair only the first concrete downstream failure.
```
