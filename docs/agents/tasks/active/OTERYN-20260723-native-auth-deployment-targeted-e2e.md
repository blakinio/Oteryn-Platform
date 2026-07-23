---
task_id: OTERYN-20260723-native-auth-deployment-targeted-e2e
program_id: ""
coordination_id: OTS-20260721-oteryn-identity-auth
status: validating
agent: "GPT-5.6 Thinking"
branch: test/OTERYN-20260723-native-auth-deployment-targeted-e2e
base_branch: main
created: 2026-07-23T19:30:00+02:00
updated: 2026-07-23T19:34:00+02:00
last_verified_commit: 74db3153b0de3e222a8f4cc1caa7ea6a8c3e393a
risk: high
related_issue: "91"
related_pr: "125"
depends_on:
  - "Oteryn Platform PR #124 merged as 53158217a6c6017230301cf4daa783b04fcc13d5"
  - "Canary PR #807 merged as 981c82f5ebb6bc22c867312c2b274a71f6aeeb3e"
  - "OTClient PR #17 merged as bb87346f6c516a19d19497d82bb01fb389334ff5"
  - "Hardened native-auth E2E run 30021347231"
  - "Production-like boundary validation run 30025787404"
blocks: []
owned_paths:
  exclusive:
    - docs/agents/tasks/active/OTERYN-20260723-native-auth-deployment-targeted-e2e.md
    - .github/workflows/native-auth-deployment-targeted-e2e.yml
  shared: []
modules_touched:
  - Production verification E2E
  - Native auth deployment boundary
reuses:
  - Existing production GitHub Environment boundary
  - Game Gateway /health, /ready and /version endpoints
public_interfaces: []
cross_repo_tasks:
  - CAN-20260723-oteryn-native-auth-production-cutover
---

# Goal

Execute a fail-closed deployment-targeted native-auth preflight against the real configured production environment instead of another local production-like simulation. The validation must never guess endpoints, deployment revisions or credentials and must not promote repository/staging evidence to `PRODUCTION_PROVEN`.

# Acceptance criteria

- [ ] Read target configuration only from the GitHub `production` Environment using non-secret variables and environment secrets.
- [ ] Fail closed with only missing configuration key names when the real target is not configured; never print values.
- [ ] Require declared exact deployed Oteryn Platform/Gateway and Canary SHAs to match the hardened revisions under test before mutation-capable smoke can proceed.
- [ ] Require explicit non-secret mutation-smoke authorization and backup/restore evidence identifier before any production login/world-entry mutation test can proceed.
- [ ] Probe the real Platform HTTPS `/health` route and Game Gateway HTTPS `/health`, `/ready` and `/version` endpoints using normal CA/hostname validation.
- [ ] Verify the Gateway `/version` value identifies the declared exact Gateway revision.
- [ ] Retain only sanitized machine-readable evidence with presence/status metadata, never endpoints, credentials, OAuth tokens, game tickets or Game Session credentials.
- [ ] If prerequisites are absent, record the exact first missing production prerequisite and keep issue #91 open.
- [ ] If preflight passes, continue with the separately bounded physical OTClient native-auth smoke on the configured target.

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-23T19:34:00+02:00
head: 74db3153b0de3e222a8f4cc1caa7ea6a8c3e393a
branch: test/OTERYN-20260723-native-auth-deployment-targeted-e2e
pr: 125
status: validating
context_routes:
  - testing
  - security
  - auth-identity
  - canary-integration
  - agent-governance
owned_paths:
  - docs/agents/tasks/active/OTERYN-20260723-native-auth-deployment-targeted-e2e.md
  - .github/workflows/native-auth-deployment-targeted-e2e.yml
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260723-native-auth-deployment-targeted-e2e.md
  - .github/workflows/native-auth-deployment-targeted-e2e.yml
proven:
  - Platform main is 53158217a6c6017230301cf4daa783b04fcc13d5 at task start.
  - Existing Phase 7 and acceptance workflows use isolated local production-like services and do not target a real deployed environment.
  - Production topology evidence still marks actual host/provider/DNS/TLS/deployment mechanism as UNKNOWN.
  - Hardened native-auth physical E2E and production-like TLS/rotation simulation already pass on exact merged revisions.
  - Draft validation-only PR #125 now carries the production-Environment fail-closed target preflight.
derived:
  - A deployment-targeted run must obtain target metadata from an external environment boundary rather than repository defaults.
unknown:
  - whether the GitHub production Environment currently contains real Platform/Gateway target variables
  - whether controlled production E2E identity credentials are provisioned
  - whether deployed revision metadata is exposed and matches the hardened revisions
  - whether production mutation-smoke authorization and backup/restore evidence are available
conflicts: []
first_failure:
  marker: deployment-target-preflight-running
  evidence: PR #125 GitHub Actions validation is being evaluated against the production Environment
validation: []
blockers: []
next_action: Inspect PR #125 deployment-targeted workflow result and sanitized artifact; continue to physical native-auth smoke only if every fail-closed prerequisite passes.
```
