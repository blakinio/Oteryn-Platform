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
updated_at: 2026-07-24T11:45:00+02:00
head: b4f83a0a2ffcf2d7254cdaa05cff71c3fe2b0ea9
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
  - Rehearsal run 30082489849 passed the complete OAuth PKCE matrix, real authorization-code expiry, ticket issue/expiry/replay/protocol checks, service credential checks, wrong account/world checks and Platform outage fail-closed recovery.
  - The first remaining failure in run 30082489849 was HTTP 429 on a later controlled login because all independent OAuth probe containers appeared to Platform as the same reverse-proxy source IP.
  - Platform production rate limiters are correctly active: identity-login allows five attempts per minute per canonical email and source IP, while identity-login-source allows twenty attempts per minute per source IP.
  - Commit b4f83a0a2ffcf2d7254cdaa05cff71c3fe2b0ea9 keeps those limiters enabled, forwards X-Forwarded-For through the trusted TLS proxy and assigns each independent probe a unique deterministic client-subnet IP.
  - Platform restart readiness now deletes the stale bootstrap marker and requires live internal HTTP 200 before returning.
  - Canary harness 9200c562e7e87dd098c1205c1df83c9d9ce95c1b accepts the documented Game Login Ticket HTTP 200 contract and has green required CI.
  - Platform b5dd6a7be5c704d5706241240e06f8bb8c4b5efe combines explicit trusted-proxy handling with complete OAuth token cache headers and passed Composer validation, audit, Pint, PHPStan and full PHPUnit in run 30079059960.
  - acceptance_extensions.py adds physical invalid-session rejection, unauthorized-character burn, restart invalidation/recovery, account-override rejection, malformed Canary-to-Gateway and Gateway-to-OTClient failures, complete cache-header checks, correlation IDs and JWT-like sensitive scanning.
derived:
  - Independent OAuth client processes will now exercise production throttling under distinct, correctly forwarded source identities instead of sharing the proxy identity.
unknown:
  - downstream Canary outage/recovery, physical OTClient, logout, replay, rotation, malformed-response, correlation, cache, rollback and final smoke results after source-IP isolation
conflicts: []
first_failure:
  marker: collapsed-oauth-probe-source-identity
  evidence: run 30082489849 retained all prior PASS evidence and oauth-probe-diagnostics.log recorded login submit status 429 on the sixth same-email login behind the proxy
rejected_hypotheses:
  - disable or raise Platform rate limits: rejected because the production control is working as designed and remains part of the security boundary
  - clear the file cache between probes: rejected because that would erase production limiter state rather than model independent clients
  - wait a fixed minute: rejected because it would make the matrix slower and timing-dependent
  - change product source or product SHA: rejected because the defect belongs to proxy/client topology in the rehearsal
  - disable TLS verification: rejected because TLS verification already passes and remains mandatory
changed_paths:
  - .github/workflows/native-auth-canary-cache-build.yml
  - .github/workflows/native-auth-ephemeral-cutover-rehearsal.yml
  - docs/agents/tasks/active/OTERYN-20260723-native-auth-ephemeral-cutover-rehearsal.md
  - tests/e2e/native_auth_ephemeral_cutover/acceptance_extensions.py
  - tests/e2e/native_auth_ephemeral_cutover/exact_runner.py
  - tests/e2e/native_auth_ephemeral_cutover/platform_runner.py
validation:
  - command: Native Auth Ephemeral Cutover Rehearsal run 30082489849
    result: FAIL
    evidence: all previous checkpoints plus Platform outage recovery passed; first failure was production login throttling caused by collapsed probe source identity
  - command: Platform Agent Governance run 30082489828
    result: PASS
    evidence: active task checkpoint validation passed on the restart-readiness head
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
next_action: execute the exact-revision rehearsal with distinct forwarded OAuth probe source IPs and repair only the first concrete downstream failure.
```
