# OTERYN-20260723-native-auth-ephemeral-cutover-rehearsal

## Goal

Own the GitHub Actions execution boundary for the cross-repository ephemeral production-like native-auth cutover rehearsal. The runner lives in `blakinio/Oteryn-Platform` because Platform is private and repository-scoped Actions credentials cannot check it out from Canary. The validation harness remains owned by Canary task `CAN-20260723-native-auth-ephemeral-cutover-rehearsal` on a public exact revision.

Maximum evidence classification is `PRODUCTION_LIKE_PROVEN`. This task performs no production deployment, uses no production secrets or user data, does not remove legacy auth, and does not close the manual Production Go-Live Gate.

## Ownership

```yaml
owned_paths:
  - docs/agents/tasks/active/OTERYN-20260723-native-auth-ephemeral-cutover-rehearsal.md
  - .github/workflows/native-auth-ephemeral-cutover-rehearsal.yml
modules:
  - native-auth production-like validation runner
dependencies:
  - Oteryn Platform 53158217a6c6017230301cf4daa783b04fcc13d5
  - Canary 981c82f5ebb6bc22c867312c2b274a71f6aeeb3e
  - OTClient bb87346f6c516a19d19497d82bb01fb389334ff5
  - Canary rehearsal harness branch test/CAN-20260723-native-auth-ephemeral-cutover-rehearsal
blocks:
  - PRODUCTION_LIKE_PROVEN native-auth rehearsal evidence
cross_repository_tasks:
  - CAN-20260723-native-auth-ephemeral-cutover-rehearsal
  - OTERYN-20260723-native-auth-production-cutover
  - CAN-20260723-oteryn-native-auth-production-cutover
```

## Acceptance criteria

- [ ] Runner executes in the private Platform repository so exact Platform/Gateway source is directly available without cross-private-repository credentials.
- [ ] Exact public Canary and OTClient revisions are checked out and built in the same workflow.
- [ ] Exact Canary harness revision is recorded and used without silent fallback.
- [ ] Full ephemeral production-like rehearsal completes with sanitized retained evidence.
- [ ] Result is never classified above `PRODUCTION_LIKE_PROVEN`.
- [ ] Manual production gate remains pending.

## Security boundaries

- Trust boundary: OTClient -> Platform public HTTPS -> Gateway public HTTPS -> Platform private HTTPS -> Canary private issuer HTTPS -> Canary game protocol.
- Actions access: no PAT or production secret is introduced; the workflow relies only on the repo-scoped Platform token for its own private repository and unauthenticated/read-only public repository checkout for Canary/OTClient.
- Runtime secrets: generated ephemerally inside the job and excluded from retained evidence.
- Rollback: native issuer/routing activation is rehearsal-only and torn down with the ephemeral environment.

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-23T23:40:00+02:00
head: 53158217a6c6017230301cf4daa783b04fcc13d5
branch: test/OTERYN-20260723-native-auth-ephemeral-cutover-rehearsal
pr: null
status: implementing
context_routes:
  - auth-identity
  - canary-integration
  - security
  - testing
owned_paths:
  - docs/agents/tasks/active/OTERYN-20260723-native-auth-ephemeral-cutover-rehearsal.md
  - .github/workflows/native-auth-ephemeral-cutover-rehearsal.yml
proven:
  - Oteryn Platform is private while Canary and OTClient are public.
  - Canary-hosted run 30046697940 job 89339475081 failed at checkout of private Oteryn Platform before Gateway build because the Canary-scoped GITHUB_TOKEN cannot access the private sibling repository.
  - The same exact Platform/Gateway source is the base of this repository and can be built here with the repository-scoped token.
derived:
  - The execution boundary belongs in Oteryn Platform even though the physical E2E harness remains maintained in Canary.
unknown:
  - final exact Canary harness commit after runtime repair
  - final workflow run/job/artifact identifiers
conflicts: []
first_failure:
  marker: cross-private-repository-checkout
  evidence: Canary rehearsal run 30046697940 job 89339475081 returned Repository not found for blakinio/Oteryn-Platform
rejected_hypotheses:
  - native-auth product code caused the checkout failure: disproven because failure occurred in actions/checkout before source verification/build
  - add a cross-repository PAT: rejected because no additional long-lived credential is required when the workflow runs from the private Platform repository
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260723-native-auth-ephemeral-cutover-rehearsal.md
validation: []
blockers:
  - Canary rehearsal harness must reach a stable exact commit before the Platform-hosted runner can be classified PRODUCTION_LIKE_PROVEN
next_action: open a draft validation PR, add the Platform-hosted full rehearsal workflow pinned to exact public Canary/OTClient/harness revisions, and execute it.
```
