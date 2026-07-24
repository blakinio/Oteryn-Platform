# OTERYN-20260723-native-auth-ephemeral-cutover-rehearsal

## Goal

Own the GitHub Actions execution boundary for the cross-repository ephemeral production-like native-auth cutover rehearsal. The runner lives in `blakinio/Oteryn-Platform` because Platform is private and repository-scoped Actions credentials cannot check it out from Canary. The validation harness remains owned by Canary task `CAN-20260723-native-auth-ephemeral-cutover-rehearsal` on a public exact revision.

Maximum evidence classification is `PRODUCTION_LIKE_PROVEN`. This task performs no production deployment, uses no production secrets or user data, does not remove legacy auth, and does not close the manual Production Go-Live Gate.

## Ownership

```yaml
owned_paths:
  - docs/agents/tasks/active/OTERYN-20260723-native-auth-ephemeral-cutover-rehearsal.md
  - .github/workflows/native-auth-ephemeral-cutover-rehearsal.yml
  - tests/e2e/native_auth_ephemeral_cutover/exact_runner.py
  - tests/e2e/native_auth_ephemeral_cutover/platform_runner.py
modules:
  - native-auth production-like validation runner
dependencies:
  - Oteryn Platform 9b80d3f4399c2d4a638a4d040f34cf60792acefc
  - Game Gateway 53158217a6c6017230301cf4daa783b04fcc13d5
  - Canary 981c82f5ebb6bc22c867312c2b274a71f6aeeb3e
  - OTClient bb87346f6c516a19d19497d82bb01fb389334ff5
  - Canary rehearsal harness f1e1664f9ad0097ede7a0a9b023251561ff24cf2
blocks:
  - PRODUCTION_LIKE_PROVEN native-auth rehearsal evidence
cross_repository_tasks:
  - CAN-20260723-native-auth-ephemeral-cutover-rehearsal
  - OTERYN-20260724-trusted-reverse-proxy-scheme
  - CAN-20260724-game-session-cache-headers
  - OTERYN-20260723-native-auth-production-cutover
  - CAN-20260723-oteryn-native-auth-production-cutover
```

## Acceptance criteria

- [x] Runner executes in the private Platform repository so exact Platform/Gateway source is directly available without cross-private-repository credentials.
- [x] Exact public Canary and OTClient revisions are checked out and their previously built exact artifacts are checksum-verified before rehearsal reuse.
- [x] Exact Canary harness revision is recorded and used without silent fallback.
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
updated_at: 2026-07-24T10:12:00+02:00
head: dee6b19554d1e58355cc6395fa4849505cbdff9d
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
  - tests/e2e/native_auth_ephemeral_cutover/exact_runner.py
  - tests/e2e/native_auth_ephemeral_cutover/platform_runner.py
proven:
  - Oteryn Platform is private while Canary and OTClient are public; Platform PR 126 is the correct Actions execution boundary without a cross-repository PAT.
  - Exact Gateway, Canary and OTClient build artifacts from run 30047343772 are reused only after source-SHA and binary checksum verification.
  - MariaDB final-server readiness, MYSQL_PWD client compatibility and in-container --skip-ssl schema import are deterministic; database_schema_import is PASS.
  - Redis read-only ACL rejects writes; output-aware validation records redis_readonly_acl_write_rejected true.
  - Canary world_id 1 is explicitly mapped to the private issuer; credential overlap accepts current and previous Platform and Canary credentials and stage4_native_issuer_activated is true.
  - TLS validation is PASS for valid CA/hostnames, wrong CA, hostname mismatch, non-loopback HTTP dependency rejection, private issuer network isolation, no verification bypass and no retained private keys.
  - Rehearsal run 30069293159 attempt 4 retained OAuth diagnostics and proved Platform generated an internal HTTP login form action behind the valid HTTPS proxy boundary.
  - Platform PR 131 exact head 9b80d3f4399c2d4a638a4d040f34cf60792acefc fixes explicit trusted proxy handling and passed standard CI run 30077363907 before its final documentation-only checkpoint.
  - exact_runner.py preserves the checked-out Canary harness unchanged while setting exact component metadata, applying rehearsal-only TRUSTED_PROXIES=10.201.3.0/24 to Platform and recording current build artifact IDs/digests.
derived:
  - The next run exercises the real OAuth PKCE boundary with the exact Platform reverse-proxy fix instead of rewriting browser/probe URLs.
unknown:
  - final OAuth, Game Login Ticket, Gateway, Game Session, physical OTClient world-entry, logout, replay, rotation-retirement, failure-injection and rollback results
conflicts: []
first_failure:
  marker: platform-forwarded-https-boundary
  evidence: artifact 8589703457 oauth-probe-diagnostics.log showed the login form POST attempted the internal HTTP origin and returned connection refused
rejected_hypotheses:
  - disable TLS verification: rejected because TLS validation already passes with hostname and CA verification
  - rewrite the form action inside the OAuth probe: rejected because the defect belonged to Platform production URL generation
  - mutate the exact Canary harness checkout: rejected because exact_runner can adapt metadata and ephemeral deployment configuration without changing harness bytes
changed_paths:
  - .github/workflows/native-auth-ephemeral-cutover-rehearsal.yml
  - docs/agents/tasks/active/OTERYN-20260723-native-auth-ephemeral-cutover-rehearsal.md
  - tests/e2e/native_auth_ephemeral_cutover/exact_runner.py
  - tests/e2e/native_auth_ephemeral_cutover/platform_runner.py
validation:
  - command: Native Auth Ephemeral Cutover Rehearsal run 30069293159 attempt 4
    result: FAIL
    evidence: provisioning, credential overlap and TLS passed; exact first product failure was forwarded HTTPS handling in Platform
  - command: Platform trusted proxy CI run 30077363907
    result: PASS
    evidence: Composer validation/audit, Pint, PHPStan and complete PHPUnit suite passed
blockers:
  - none
next_action: execute the Platform-fix-pinned rehearsal and repair only the first concrete downstream native-auth failure.
```
