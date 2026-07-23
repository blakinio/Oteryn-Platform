---
task_id: OTERYN-20260723-native-auth-deployment-targeted-e2e
program_id: ""
coordination_id: OTS-20260721-oteryn-identity-auth
status: blocked
agent: "GPT-5.6 Thinking"
branch: test/OTERYN-20260723-native-auth-deployment-targeted-e2e
base_branch: main
created: 2026-07-23T19:30:00+02:00
updated: 2026-07-23T19:38:00+02:00
last_verified_commit: 578570dd88ad39774cf9204c95d67bb241b3fcbb
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

- [x] Read target configuration only from the GitHub `production` Environment using non-secret variables and environment secrets.
- [x] Fail closed with only missing configuration key names when the real target is not configured; never print values.
- [ ] Require declared exact deployed Oteryn Platform/Gateway and Canary SHAs to match the hardened revisions under test before mutation-capable smoke can proceed. Blocked before revision comparison because deployed revision variables are absent.
- [ ] Require explicit non-secret mutation-smoke authorization and backup/restore evidence identifier before any production login/world-entry mutation test can proceed. Both prerequisites are absent.
- [ ] Probe the real Platform HTTPS `/health` route and Game Gateway HTTPS `/health`, `/ready` and `/version` endpoints using normal CA/hostname validation. No target URLs are configured, so no network probe was attempted.
- [ ] Verify the Gateway `/version` value identifies the declared exact Gateway revision. Blocked by absent Gateway target and deployed revision metadata.
- [x] Retain only sanitized machine-readable evidence with presence/status metadata, never endpoints, credentials, OAuth tokens, game tickets or Game Session credentials.
- [x] If prerequisites are absent, record the exact first missing production prerequisite and keep issue #91 open.
- [ ] If preflight passes, continue with the separately bounded physical OTClient native-auth smoke on the configured target. Preflight did not pass.

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-23T19:38:00+02:00
head: 578570dd88ad39774cf9204c95d67bb241b3fcbb
branch: test/OTERYN-20260723-native-auth-deployment-targeted-e2e
pr: 125
status: blocked
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
  - Platform main was 53158217a6c6017230301cf4daa783b04fcc13d5 at task start.
  - Existing Phase 7 and acceptance workflows use isolated local production-like services and do not target a real deployed environment.
  - Hardened native-auth physical E2E run 30021347231 and production-like TLS/rotation run 30025787404 pass on exact merged revisions.
  - Deployment-targeted run 30029891974 / job 89283557378 executed with GitHub Environment production and failed closed before network or mutation activity.
  - Sanitized artifact 8572824626, digest sha256:803d55996c1a769a487e6fdf22a2b12da5e6eae1a2aa6c97514deef59665504c, records the missing production prerequisites without endpoint or credential values.
  - Required production target URLs, deployed Platform/Gateway/Canary SHA declarations, native OAuth client id, E2E character/world/game route, mutation-smoke authorization, backup/restore evidence id and controlled E2E email/password are absent from the production Environment.
  - Optional production E2E TOTP secret is also absent.
derived:
  - The current blocker is external production target configuration/evidence, not missing native-auth implementation or missing E2E capability.
  - A physical deployment-targeted OTClient login cannot be executed safely until the production Environment exposes a real target and controlled test identity metadata.
unknown:
  - actual deployed production Platform/Gateway/Canary revisions
  - actual production Platform and Gateway URLs
  - actual production game world route intended for controlled smoke
  - whether a controlled production E2E identity exists outside GitHub Environment
  - actual production backup/restore evidence and authorized rollback operator state
conflicts: []
first_failure:
  marker: production-environment-target-configuration-absent
  evidence: deployment-targeted run 30029891974 failed closed; sanitized artifact 8572824626 lists fourteen absent required production prerequisites
validation:
  - run: 30029891974
    job: 89283557378
    result: failure_expected_fail_closed
    artifact: 8572824626
    artifact_digest: sha256:803d55996c1a769a487e6fdf22a2b12da5e6eae1a2aa6c97514deef59665504c
blockers:
  - GitHub production Environment has no configured real Platform/Gateway target URLs or declared deployed revision identities.
  - Controlled production E2E identity credentials and native OAuth client id are not configured.
  - Production mutation-smoke authorization and backup/restore evidence identifier are not configured.
next_action: Provision the non-secret production target/revision variables and controlled production E2E secrets in the GitHub production Environment, with explicit mutation-smoke authorization and backup/restore evidence, then rerun the deployment-targeted preflight before physical OTClient smoke.
```
