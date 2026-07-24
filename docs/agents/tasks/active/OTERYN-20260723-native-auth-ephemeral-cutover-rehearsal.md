# OTERYN-20260723-native-auth-ephemeral-cutover-rehearsal

## Goal

Own the private-repository GitHub Actions execution boundary for the cross-repository ephemeral production-like native-auth cutover rehearsal joining real Platform, Gateway, Canary and OTClient components.

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
  - Canary rehearsal harness f1434b2c299a07175a3c870168b7b6222b4677a7
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

- [x] Exact source revisions and binary artifact digests are verified before execution.
- [x] OAuth PKCE, ticket, Gateway, Game Session, TLS, failure injection, cache, correlation and physical negative scenarios are encoded and have passed through the current first failure.
- [ ] Physical malformed Gateway response reaches the fake Gateway boundary and fails closed in the real OTClient.
- [ ] Physical happy-path world entry, logout, replay rejection, credential rotation, rollback and final smoke complete.
- [ ] Full sanitized retained evidence produces `PRODUCTION_LIKE_PROVEN`.
- [x] Classification never exceeds `PRODUCTION_LIKE_PROVEN`; production go-live remains pending.

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-24T12:35:00+02:00
head: 36552c2f686ea73f60bf6cdd39f3f7c7a0937879
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
  - Platform run 30084930018 passed every matrix gate through cache headers, correlation, random invalid-session rejection, unauthorized-character burn and Canary restart invalidation/recovery.
  - Its first failure remained before the malformed Gateway boundary: Platform returned HTTP 400 for /oauth/authorize and malformed-gateway-access.log remained empty.
  - Canary harness f1434b2c299a07175a3c870168b7b6222b4677a7 adds sanitized authorization-request diagnostics after the CharacterList readiness correction did not change the failure.
  - Canary required CI 30085602293 and ownership 30085601878 passed on that exact harness head.
  - The diagnostic retains only query-key names, equality/presence booleans, fixed enum values, lengths, redirect structure, HTTP status/path/phase and fixed classifications; no OAuth state, challenge value, credentials, tokens, cookies or raw body are retained.
  - Workflow commit 36552c2f686ea73f60bf6cdd39f3f7c7a0937879 pins the exact diagnostic harness in checkout and retained-evidence assertions.
  - Product revisions and all three product binary artifacts remain unchanged and checksum-gated.
unknown:
  - exact structural reason Platform rejects the malformed-helper authorization request
  - downstream physical happy path, logout, replay, rotation, rollback and final smoke results
conflicts: []
first_failure:
  marker: malformed-gateway-oauth-authorize-400
  evidence: run 30084930018 artifact 8593440828 recorded browser HTTPError before any POST reached the fake Gateway
rejected_hypotheses:
  - accept timeout-only Lua evidence: rejected because physical boundary access remains mandatory
  - retain complete authorization URLs or response bodies: rejected because they may contain sensitive OAuth material
  - change product revisions before identifying the invalid request field: rejected because maintained OAuth flows pass on the same products
changed_paths:
  - .github/workflows/native-auth-canary-cache-build.yml
  - .github/workflows/native-auth-ephemeral-cutover-rehearsal.yml
  - docs/agents/tasks/active/OTERYN-20260723-native-auth-ephemeral-cutover-rehearsal.md
  - tests/e2e/native_auth_ephemeral_cutover/acceptance_extensions.py
  - tests/e2e/native_auth_ephemeral_cutover/exact_runner.py
  - tests/e2e/native_auth_ephemeral_cutover/platform_runner.py
validation:
  - command: Platform Native Auth Ephemeral Cutover Rehearsal run 30084930018
    result: FAIL
    evidence: first failure was malformed-helper OAuth authorization before the fake Gateway boundary
  - command: Canary CI run 30085602293
    result: PASS
    evidence: required CI passed for sanitized browser diagnostics
  - command: Canary Agent Task Ownership run 30085601878
    result: PASS
    evidence: active ownership and checkpoint validation passed
blockers:
  - none
next_action: run the exact diagnostic harness, inspect sanitized OAuth request metadata, and repair only the invalid request contract.
```
