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
updated_at: 2026-07-24T11:35:00+02:00
head: 0685c447d780a8d36724bab21bb56cb00af69178
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
  - MariaDB final-server readiness, MYSQL_PWD compatibility and deterministic schema imports are proven; Redis read-only ACL write rejection is proven.
  - Credential overlap and native issuer activation pass for current/previous Platform and Canary credentials.
  - TLS validation passes for trusted CA/hostnames, wrong CA, hostname mismatch, non-loopback HTTP dependency rejection, private issuer isolation, no verification bypass and no retained private keys.
  - Rehearsal run 30081998956 passed the complete OAuth PKCE matrix and the real authorization-code expiry check using isolated Platform fake time.
  - The same run passed expired Game Login Ticket rejection, first use, replay rejection, wrong protocol, invalid Platform and Canary service credentials, wrong account and wrong world routing.
  - Run 30081998956 failed only when the harness restarted Platform after an injected outage and immediately probed health while the new server was still starting.
  - The restart bug was caused by a stale platform-bootstrap.json marker surviving between container stages; it was not a Platform product failure.
  - Commit 0685c447d780a8d36724bab21bb56cb00af69178 removes the stale marker before every Platform start and requires a real internal HTTP 200 before returning.
  - Canary harness 9200c562e7e87dd098c1205c1df83c9d9ce95c1b accepts the documented Game Login Ticket HTTP 200 contract and has green required CI.
  - Platform b5dd6a7be5c704d5706241240e06f8bb8c4b5efe combines explicit trusted-proxy handling with complete OAuth token cache headers and passed Composer validation, audit, Pint, PHPStan and full PHPUnit in run 30079059960.
  - acceptance_extensions.py adds physical invalid-session rejection, unauthorized-character burn, restart invalidation/recovery, account-override rejection, malformed Canary-to-Gateway and Gateway-to-OTClient failures, complete cache-header checks, correlation IDs and JWT-like sensitive scanning.
derived:
  - Platform outage recovery will now be measured only after a fresh bootstrap and live HTTP readiness, eliminating the false 502 race.
unknown:
  - downstream Canary outage/recovery, physical OTClient, logout, replay, rotation, malformed-response, correlation, cache, rollback and final smoke results after the corrected Platform restart checkpoint
conflicts: []
first_failure:
  marker: stale-platform-restart-readiness
  evidence: run 30081998956 retained OAuth expiry PASS and failure-injection booleans through platform_redemption_unavailable_fail_closed, then failed because start_platform reused the previous bootstrap marker before the replacement server listened
rejected_hypotheses:
  - classify the health race as a Platform availability defect: rejected because retained logs show the replacement process running migrations after the immediate failed probe
  - add an arbitrary fixed sleep: rejected because readiness must be state-based and deterministic
  - probe through the public proxy before it exists: rejected; the adapter uses the isolated platform_service network and backend alias
  - change product source or product SHA: rejected because the defect belongs to rehearsal orchestration
  - disable TLS verification: rejected because TLS verification already passes and remains mandatory
changed_paths:
  - .github/workflows/native-auth-canary-cache-build.yml
  - .github/workflows/native-auth-ephemeral-cutover-rehearsal.yml
  - docs/agents/tasks/active/OTERYN-20260723-native-auth-ephemeral-cutover-rehearsal.md
  - tests/e2e/native_auth_ephemeral_cutover/acceptance_extensions.py
  - tests/e2e/native_auth_ephemeral_cutover/exact_runner.py
  - tests/e2e/native_auth_ephemeral_cutover/platform_runner.py
validation:
  - command: Native Auth Ephemeral Cutover Rehearsal run 30081998956
    result: FAIL
    evidence: OAuth PKCE, real code expiry, tickets and the first failure-injection assertions passed; first failure was the stale-marker Platform restart readiness race
  - command: Platform Agent Governance run 30081998951
    result: PASS
    evidence: active task checkpoint validation passed on the fake-time fix head
  - command: Platform CI run 30079059960
    result: PASS
    evidence: Composer validation/audit, Pint, PHPStan and complete PHPUnit suite passed for Platform b5dd6a7
  - command: Canary source CI run 30080195391 and 30080469762
    result: PASS
    evidence: complete private issuer cache policy passed required Canary CI
  - command: exact Canary build run 30080248772
    result: PASS
    evidence: artifact 8591665710 built from b15b7d544f4795e3a2a65b88de35391b9fd0a20d and was uploaded with retained digest
blockers:
  - none
next_action: execute the exact-revision rehearsal with fresh Platform restart readiness and repair only the first concrete downstream failure.
```
