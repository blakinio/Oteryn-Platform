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
  - Canary rehearsal harness 9200c562e7e87dd098c1205c1df83c9d9ce95c1b
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
- Actions access: no PAT or production secret is introduced; the workflow relies only on the repo-scoped Platform token for its own private repository and read-only public repository checkout for Canary/OTClient.
- Runtime secrets: generated ephemerally inside the job and excluded from retained evidence.
- Rollback: native issuer/routing activation is rehearsal-only and torn down with the ephemeral environment.

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-24T11:55:00+02:00
head: 3ac0524b0a5e535ff7f2530ce8b9bdc1af5d2b77
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
  - Oteryn Platform is private while Canary and OTClient are public; Platform PR 126 is the correct Actions execution boundary without a cross-repository PAT.
  - Gateway and OTClient artifacts from source build run 30047343772 are reused only after source-SHA and binary checksum verification.
  - Exact Canary b15b7d544f4795e3a2a65b88de35391b9fd0a20d with the complete private-issuer cache policy was built successfully in run 30080248772; artifact 8591665710 has digest sha256:6b0e13966047c571de2c7a3f948f0f9b54b1619800c4b591cd70adf5a7a860f1.
  - Rehearsal run 30083102212 passed exact revision verification, deterministic data services, credential overlap, TLS, OAuth PKCE, real authorization-code expiry, ticket expiry/replay/protocol checks, service credential checks, wrong account/world checks, Platform and Canary outage fail-closed recovery, account override rejection, malformed Canary response rejection, cache headers and normal happy path without 5xx.
  - The same run proved the source-IP isolation fix: no later OAuth probe was rejected by the production login rate limiter.
  - Gateway preserved and logged the supplied correlation ID native-auth-rehearsal-gateway-correlation.
  - Platform returned request ID 11b6c6af-5724-402b-98b3-3f44316112a0 and the identical ID appears in the final retained Platform log.
  - The correlation checker read docker logs immediately after the response and raced the logging flush, recording platform_id_logged false despite the retained final log proving the ID.
  - Commit 3ac0524b0a5e535ff7f2530ce8b9bdc1af5d2b77 adds bounded state-based polling for the exact Platform and Gateway IDs; it passed git diff --check and py_compile, and its one-shot workflow removed itself from the resulting tree.
  - Platform restart readiness deletes stale bootstrap evidence and requires live internal HTTP 200 before returning.
  - Platform b5dd6a7be5c704d5706241240e06f8bb8c4b5efe passed Composer validation, audit, Pint, PHPStan and the full PHPUnit suite in run 30079059960.
  - acceptance_extensions.py adds physical invalid-session rejection, unauthorized-character burn, restart invalidation/recovery, malformed Gateway-to-OTClient failure, complete cache-header checks, correlation IDs and JWT-like sensitive scanning.
derived:
  - Correlation is present in both products; only observation readiness in the rehearsal required correction.
unknown:
  - physical OTClient negative and happy flows, logout database state, Game Session replay, credential rotation, rollback and final smoke results after the corrected correlation checkpoint
conflicts: []
first_failure:
  marker: correlation-log-flush-race
  evidence: run 30083102212 request-correlation.json reported Platform response ID 11b6c6af-5724-402b-98b3-3f44316112a0 but platform_id_logged false; the retained platform.log contains that exact ID
rejected_hypotheses:
  - add a Platform product propagation fix: rejected because the response ID and retained log ID are identical
  - accept a missing correlation ID: rejected because both Platform and Gateway IDs remain mandatory
  - add an arbitrary long sleep: rejected in favor of bounded polling for the exact expected ID
  - change product source or product SHA: rejected because the defect belongs to evidence observation timing
changed_paths:
  - .github/workflows/native-auth-canary-cache-build.yml
  - .github/workflows/native-auth-ephemeral-cutover-rehearsal.yml
  - docs/agents/tasks/active/OTERYN-20260723-native-auth-ephemeral-cutover-rehearsal.md
  - tests/e2e/native_auth_ephemeral_cutover/acceptance_extensions.py
  - tests/e2e/native_auth_ephemeral_cutover/exact_runner.py
  - tests/e2e/native_auth_ephemeral_cutover/platform_runner.py
validation:
  - command: Native Auth Ephemeral Cutover Rehearsal run 30083102212
    result: FAIL
    evidence: every matrix assertion through cache headers and correlation response IDs passed; first failure was the pre-flush Platform log observation
  - command: Platform Agent Governance run 30083102216
    result: PASS
    evidence: active task checkpoint validation passed on the source-IP isolation head
  - command: Platform CI run 30079059960
    result: PASS
    evidence: Composer validation/audit, Pint, PHPStan and complete PHPUnit suite passed for Platform b5dd6a7
  - command: exact Canary build run 30080248772
    result: PASS
    evidence: artifact 8591665710 built from b15b7d544f4795e3a2a65b88de35391b9fd0a20d and was uploaded with retained digest
blockers:
  - none
next_action: execute the exact-revision rehearsal with bounded correlation log readiness and repair only the first concrete downstream failure.
```
