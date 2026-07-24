# OTERYN-20260723-native-auth-ephemeral-cutover-rehearsal

## Goal

Own the GitHub Actions execution boundary for the cross-repository ephemeral production-like native-auth cutover rehearsal. The runner lives in `blakinio/Oteryn-Platform` because Platform is private and repository-scoped Actions credentials cannot check it out from Canary. The validation harness remains owned by Canary task `CAN-20260723-native-auth-ephemeral-cutover-rehearsal` on a public exact revision.

Maximum evidence classification is `PRODUCTION_LIKE_PROVEN`. This task performs no production deployment, uses no production secrets or user data, does not remove legacy auth, and does not close the manual Production Go-Live Gate.

## Ownership

```yaml
owned_paths:
  - docs/agents/tasks/active/OTERYN-20260723-native-auth-ephemeral-cutover-rehearsal.md
  - .github/workflows/native-auth-ephemeral-cutover-rehearsal.yml
  - tests/e2e/native_auth_ephemeral_cutover/platform_runner.py
modules:
  - native-auth production-like validation runner
dependencies:
  - Oteryn Platform 53158217a6c6017230301cf4daa783b04fcc13d5
  - Canary 981c82f5ebb6bc22c867312c2b274a71f6aeeb3e
  - OTClient bb87346f6c516a19d19497d82bb01fb389334ff5
  - Canary rehearsal harness f1e1664f9ad0097ede7a0a9b023251561ff24cf2
blocks:
  - PRODUCTION_LIKE_PROVEN native-auth rehearsal evidence
cross_repository_tasks:
  - CAN-20260723-native-auth-ephemeral-cutover-rehearsal
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
updated_at: 2026-07-24T07:25:00+02:00
head: cbf70d6d7487c4043582d443274578f3adb0bf54
branch: test/OTERYN-20260723-native-auth-ephemeral-cutover-rehearsal
pr: 126
status: blocked_external
context_routes:
  - auth-identity
  - canary-integration
  - security
  - testing
owned_paths:
  - docs/agents/tasks/active/OTERYN-20260723-native-auth-ephemeral-cutover-rehearsal.md
  - .github/workflows/native-auth-ephemeral-cutover-rehearsal.yml
  - tests/e2e/native_auth_ephemeral_cutover/platform_runner.py
proven:
  - Oteryn Platform is private while Canary and OTClient are public; Platform PR 126 is the correct Actions execution boundary without a cross-repository PAT.
  - Workflow pins Platform/Gateway 53158217a6c6017230301cf4daa783b04fcc13d5, Canary 981c82f5ebb6bc22c867312c2b274a71f6aeeb3e, OTClient bb87346f6c516a19d19497d82bb01fb389334ff5 and Canary harness f1e1664f9ad0097ede7a0a9b023251561ff24cf2.
  - Exact Gateway, Canary and OTClient build artifacts from run 30047343772 are reused only after source-SHA and binary checksum verification.
  - MariaDB final-server readiness, MYSQL_PWD client compatibility and in-container --skip-ssl schema import are deterministic; database_schema_import is PASS.
  - Redis read-only ACL rejects writes; output-aware validation records redis_readonly_acl_write_rejected true.
  - Canary world_id 1 is explicitly mapped to the private issuer; credential overlap accepts current and previous Platform and Canary credentials and stage4_native_issuer_activated is true.
  - TLS validation is PASS for valid CA/hostnames, wrong CA, hostname mismatch, non-loopback HTTP dependency rejection, private issuer network isolation, no verification bypass and no retained private keys.
  - Laravel APP_KEY is now generated from exactly 32 random bytes and encoded as standard base64.
  - Sensitive-log scans for every retained failed-run artifact through run 30068710857 are PASS.
  - Head cbf70d6d7487c4043582d443274578f3adb0bf54 retains redacted OAuth subprocess stdout/stderr in oauth-probe-diagnostics.log on failure.
derived:
  - Product components have passed exact builds and the rehearsal has advanced through provisioning, credential overlap and TLS; the next unresolved runtime boundary is OAuth/PKCE probe execution.
  - Runs 30069031309 and its two retries are not validation evidence because every job failed before the first step and produced no logs or artifact.
unknown:
  - exact OAuth probe exception on diagnostic head cbf70d6d7487c4043582d443274578f3adb0bf54
  - final OAuth, Game Login Ticket, Gateway, Game Session, physical OTClient world-entry, logout, replay, rotation-retirement, failure-injection and rollback results
conflicts: []
first_failure:
  marker: github-actions-job-start-gate
  evidence: run 30069031309 and two retries created jobs with conclusion failure, steps null, no logs and no evidence artifact; five unrelated workflows on the same commit failed identically before execution
rejected_hypotheses:
  - native-auth source or runner Python caused run 30069031309: rejected because no workflow step, checkout or interpreter started
  - retrying the same run immediately resolves the gate: rejected by two successful rerun API requests followed by identical zero-step failures
  - classify prior partial runs as PRODUCTION_LIKE_PROVEN: rejected because successful_world_entries remains zero and downstream matrices are incomplete
changed_paths:
  - .github/workflows/native-auth-ephemeral-cutover-rehearsal.yml
  - docs/agents/tasks/active/OTERYN-20260723-native-auth-ephemeral-cutover-rehearsal.md
  - tests/e2e/native_auth_ephemeral_cutover/platform_runner.py
validation:
  - command: Native Auth Ephemeral Cutover Rehearsal run 30068164918
    result: FAIL
    evidence: provisioning, Redis ACL, credential overlap and private issuer activation passed; harness TLS polarity bug was the first failure
  - command: Native Auth Ephemeral Cutover Rehearsal run 30068422345
    result: FAIL
    evidence: TLS matrix passed; invalid Laravel APP_KEY prevented OAuth matrix
  - command: Native Auth Ephemeral Cutover Rehearsal run 30068710857
    result: FAIL
    evidence: valid APP_KEY removed Platform encryption failure; OAuth probe still returned nonzero without retained subprocess diagnostics
  - command: Native Auth Ephemeral Cutover Rehearsal run 30069031309 plus two failed-job retries
    result: BLOCKED
    evidence: jobs failed before step allocation with steps null, no downloadable logs and no artifact
blockers:
  - GitHub Actions currently rejects all Platform workflow jobs before allocating a runner or executing step 1; the diagnostic head cannot be exercised until hosted-runner execution resumes or the repository/account Actions gate is cleared.
next_action: rerun Native Auth Ephemeral Cutover Rehearsal on head cbf70d6d7487c4043582d443274578f3adb0bf54 after GitHub-hosted jobs can start, then inspect the retained redacted oauth-probe-diagnostics.log and repair only the concrete OAuth failure.
```
