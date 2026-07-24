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
  - Canary rehearsal harness f1434b2c299a07175a3c870168b7b6222b4677a7
blocks:
  - PRODUCTION_LIKE_PROVEN native-auth rehearsal evidence
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
- [ ] Physical malformed Gateway response reaches the fake Gateway boundary and fails closed in the real OTClient.
- [ ] Physical happy-path world entry, logout, replay rejection, credential rotation, rollback and final smoke complete.
- [ ] Full sanitized retained evidence produces `PRODUCTION_LIKE_PROVEN`.
- [x] Classification never exceeds `PRODUCTION_LIKE_PROVEN`; production go-live remains pending.

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-24T14:10:00+02:00
head: 51f514f3f1a7482f789402805c89ac5a6155adca
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
  - Platform rehearsal run 30085898466 proved the old OTClient binary reached /oauth/authorize with only client_id because Unix shell parsing removed parameters after the first ampersand.
  - OTClient PR 20 replaced Unix shell command construction with argv-based process launch and added exact one-argument URL regression coverage.
  - OTClient run 30087461815 produced Linux release artifact 8595332324 from source 9189d1063e968a0c2ffab11c5069db192e753397.
  - The OTClient artifact archive digest is sha256:396e0e1fed38c14f43c88cba4e578997ecbd56c2f211ee8b398c712a10c44850.
  - The extracted OTClient ELF digest is sha256:9c95ca6e3c26b387f61fcaeb99596d877c1db1bd85a8df1dac310f4a9af03c22.
  - Workflow commit 51f514f3f1a7482f789402805c89ac5a6155adca downloads the exact cross-repository artifact, verifies GitHub artifact metadata and executable digest, and records split Gateway and OTClient build provenance.
derived:
  - The prior malformed-helper OAuth 400 was caused by the fixed Unix OTClient launch boundary, not by Platform authorization validation or the rehearsal capture helper.
  - The repinned rehearsal can now determine the next real physical native-auth boundary.
unknown:
  - Final result of the Platform rehearsal triggered by the fixed OTClient artifact pin.
  - Whether the next physical boundary is malformed-Gateway fail-closed behavior or a later happy-path/session gate.
conflicts: []
first_failure:
  marker: none
  evidence: The fixed-artifact rehearsal has not completed; the prior shell-truncation failure is addressed by exact OTClient source and artifact evidence.
rejected_hypotheses:
  - OTClient PKCE URL construction omitted fields: source and diagnostics proved the complete URL existed before Platform::openUrl.
  - capture-xdg-open.sh truncated the URL: it accepts and records exactly one argument.
  - Platform OAuth validation caused the missing fields: HTTP 400 was correct for the shell-truncated request.
changed_paths:
  - .github/workflows/native-auth-ephemeral-cutover-rehearsal.yml
  - docs/agents/tasks/active/OTERYN-20260723-native-auth-ephemeral-cutover-rehearsal.md
  - .github/workflows/native-auth-canary-cache-build.yml
  - tests/e2e/native_auth_ephemeral_cutover/acceptance_extensions.py
  - tests/e2e/native_auth_ephemeral_cutover/exact_runner.py
  - tests/e2e/native_auth_ephemeral_cutover/platform_runner.py
validation:
  - command: Platform rehearsal run 30085898466
    result: FAIL
    evidence: Authorization request contained only client_id; retained artifact 8593807390.
  - command: OTClient CI run 30087461815 Linux release job
    result: PASS
    evidence: Artifact 8595332324 was produced from exact source 9189d1063e968a0c2ffab11c5069db192e753397.
  - command: Local artifact inspection
    result: PASS
    evidence: Artifact contains one x86-64 Linux ELF named otclient with digest sha256:9c95ca6e3c26b387f61fcaeb99596d877c1db1bd85a8df1dac310f4a9af03c22.
  - command: Workflow YAML parse
    result: PASS
    evidence: Updated workflow parsed successfully before commit 51f514f3f1a7482f789402805c89ac5a6155adca.
blockers: []
next_action: Inspect the final Platform rehearsal run triggered by the fixed OTClient pin and record either its first failed physical boundary or the retained PRODUCTION_LIKE_PROVEN evidence identifiers.
```
