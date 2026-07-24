# OTERYN-20260723-native-auth-ephemeral-cutover-rehearsal

## Goal

Own the private-repository GitHub Actions execution boundary for the cross-repository ephemeral production-like native-auth cutover rehearsal joining exact Platform, Gateway, Canary and OTClient components.

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
  - OTClient 9189d1063e968a0c2ffab11c5069db192e753397
  - Canary rehearsal harness f46ae126557d4d26043c77fe17968b72fd5bc688
blocks: []
cross_repository_tasks:
  - OTC-20260724-shell-safe-open-url
  - CAN-20260723-native-auth-ephemeral-cutover-rehearsal
  - OTERYN-20260724-trusted-reverse-proxy-scheme
  - OTERYN-20260724-oauth-token-cache-headers
  - CAN-20260724-game-session-cache-headers
  - OTERYN-20260723-native-auth-production-cutover
  - CAN-20260723-oteryn-native-auth-production-cutover
```

## Acceptance criteria

- [x] Exact source revisions and binary artifact digests are verified before execution.
- [x] OAuth PKCE, ticket, Gateway, Game Session, TLS, failure injection, cache, correlation and physical negative scenarios are encoded.
- [x] The shell-safe OTClient Linux release is pinned by exact source SHA, workflow run, artifact ID, archive digest and executable digest.
- [x] Physical malformed Gateway response reaches the fake Gateway boundary and fails closed in the real OTClient.
- [x] Physical happy-path world entry, logout, replay rejection, credential rotation, rollback and final smoke complete.
- [x] Full sanitized retained evidence produces `PRODUCTION_LIKE_PROVEN`.
- [x] Classification never exceeds `PRODUCTION_LIKE_PROVEN`; production go-live remains pending.

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-24T15:22:00+02:00
head: bdf1af37f1883159177b2d008ba5553cd4052099
branch: test/OTERYN-20260723-native-auth-ephemeral-cutover-rehearsal
pr: 126
status: ready
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
  - OTClient source 9189d1063e968a0c2ffab11c5069db192e753397 launches Unix URLs through argv and produced Linux artifact 8595332324 with archive digest sha256:396e0e1fed38c14f43c88cba4e578997ecbd56c2f211ee8b398c712a10c44850.
  - Platform workflow verifies the exact OTClient artifact metadata and executable digest sha256:9c95ca6e3c26b387f61fcaeb99596d877c1db1bd85a8df1dac310f4a9af03c22.
  - Canary harness f46ae126557d4d26043c77fe17968b72fd5bc688 selects the exact authorized Knight 1 widget before CharacterList.doLogin.
  - Platform rehearsal run 30095854266 completed successfully and its required gate passed.
  - Retained artifact 8597730728 has digest sha256:e7e908e9129658654054a96adf641757edc2c904fc2b01a5b9fc97e393d18009 and classification PRODUCTION_LIKE_PROVEN.
  - Retained evidence records one Knight 1 world entry, nonzero lastlogin and lastlogout, zero players_online, safe logout and replay rejection.
derived:
  - The Unix shell-safe URL fix is physically proven through complete OAuth PKCE and world entry.
  - The cross-repository production-like native-auth acceptance gate is green without making a production deployment claim.
unknown: []
conflicts: []
first_failure:
  marker: none
  evidence: All required stages passed in run 30095854266.
rejected_hypotheses:
  - Platform authorization validation removed OAuth fields: the fixed client delivered the full query and completed OAuth.
  - Canary failed to persist logout: the earlier check queried Knight 1 while the implicitly focused client had logged in as Druid 1; exact character selection produced nonzero Knight 1 timestamps.
  - Game Session burn retry was a server defect: the first rejected ProtocolGame needed synchronous client cancellation before the second attempt.
changed_paths:
  - .github/workflows/native-auth-ephemeral-cutover-rehearsal.yml
  - docs/agents/tasks/active/OTERYN-20260723-native-auth-ephemeral-cutover-rehearsal.md
  - .github/workflows/native-auth-canary-cache-build.yml
  - tests/e2e/native_auth_ephemeral_cutover/acceptance_extensions.py
  - tests/e2e/native_auth_ephemeral_cutover/exact_runner.py
  - tests/e2e/native_auth_ephemeral_cutover/platform_runner.py
validation:
  - command: Platform rehearsal run 30095854266
    result: PASS
    evidence: Full rehearsal and required gate succeeded on head bdf1af37f1883159177b2d008ba5553cd4052099.
  - command: Retained evidence artifact 8597730728
    result: PASS
    evidence: PRODUCTION_LIKE_PROVEN; digest sha256:e7e908e9129658654054a96adf641757edc2c904fc2b01a5b9fc97e393d18009.
blockers: []
next_action: Inspect checks triggered by this checkpoint-only commit and, if green, mark PR 126 ready and squash-merge it.
```
