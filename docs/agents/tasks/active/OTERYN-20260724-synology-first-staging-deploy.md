---
task_id: OTERYN-20260724-synology-first-staging-deploy
required_reads:
  - AGENTS.md
  - docs/agents/REPOSITORY_MAP.md
  - docs/agents/CONTEXT_ROUTING.md
  - docs/agents/BUILD_TEST_MATRIX.md
  - .github/workflows/deploy-synology-staging.yml
search_first:
  - active Synology deployment tasks and workflow ownership
optional_reads: []
---

# OTERYN-20260724-synology-first-staging-deploy

## Goal

Perform the first guarded Oteryn staging deployment on the already registered Synology self-hosted runner without requiring a manual `workflow_dispatch` click.

## Acceptance criteria

- [x] A temporary one-shot workflow can dispatch only the trusted `main` deployment workflow after an explicitly marked merge.
- [x] Platform and Gateway images are pinned to the exact one-shot merge SHA tag.
- [x] The Canary image is resolved from the approved public package tag to an immutable digest before dispatch.
- [ ] `Deploy Synology Staging` completes successfully on runner label `oteryn-staging` using environment `synology-staging`.
- [ ] The temporary trigger is removed and this task is archived after durable non-secret evidence is recorded.

## Ownership

```yaml
owned_paths:
  - .github/workflows/build-synology-staging-images.yml
  - .github/workflows/one-shot-synology-staging-deploy.yml
  - deploy/synology/compose.yml
  - deploy/synology/scripts/deploy.sh
  - deploy/synology/scripts/rollback.sh
  - docs/agents/tasks/active/OTERYN-20260724-synology-first-staging-deploy.md
  - docs/agents/tasks/archive/OTERYN-20260724-synology-first-staging-deploy.md
modules:
  - Synology staging deployment
  - GitHub Actions deployment orchestration
dependencies:
  - PR 128 merged as 63a50beca857ef48e8aab04f2b4b5264684ae60f
  - online repository-scoped runner labeled oteryn-staging
  - configured synology-staging GitHub Environment variables and secrets
blockers:
  - PR 136 must pass exact-head validation and merge before the corrected deployment can be rerun from trusted main
cross_repository_tasks:
  - Canary image is consumed read-only from ghcr.io/blakinio/canary
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-24T10:50:00Z
head: 1112ade16c9ecf862520c926424efb8e51738dd0
branch: fix/OTERYN-20260724-synology-canary-start
pr: 136
status: validating
context_routes:
  - agent-governance
  - testing
  - security
  - ci-repair
owned_paths:
  - .github/workflows/build-synology-staging-images.yml
  - .github/workflows/one-shot-synology-staging-deploy.yml
  - deploy/synology/compose.yml
  - deploy/synology/scripts/deploy.sh
  - deploy/synology/scripts/rollback.sh
  - docs/agents/tasks/active/OTERYN-20260724-synology-first-staging-deploy.md
  - docs/agents/tasks/archive/OTERYN-20260724-synology-first-staging-deploy.md
proven:
  - Build Synology Staging Images run 30075876955 completed successfully
  - build jobs 89426364842 and 89426364862 published Platform and Gateway images for tag sha-e08548866e6edc70f69eaba40249303b69236625
  - One-shot Synology Staging Deploy run 30075876983 resolved both exact SHA-tagged images and Canary digest ghcr.io/blakinio/canary@sha256:784e5dbdcc64e311c48c51cd94aa206e2efa1e5eefb2f4ef40170d5aac55031f
  - one-shot job 89426365047 successfully dispatched Deploy Synology Staging run 30075926039 and failed only while monitoring that failed deployment
  - deploy jobs 89426521777 and 89444393576 ran on runner oteryn-synology-staging with label oteryn-staging and failed configuration validation because OTERYN_STAGING_APP_KEY was not a Laravel base64 application key
  - after the second user correction, deploy job 89454820564 passed configuration and reached MariaDB startup but timed out before slow first initialization completed
  - PR 135 merged the bounded MariaDB readiness fix to main as 7b67a0efcc519dec9d60c919c20266e90bddaf60 after exact-head validation succeeded
  - deploy job 89459153834 checked out trusted main at 7b67a0efcc519dec9d60c919c20266e90bddaf60 and passed runner tools, GHCR login, configuration validation and ephemeral environment creation
  - job 89459153834 passed MariaDB readiness, created all seven required Canary schema tables, provisioned Platform database access, started Platform, applied migrations and reached the health-check invocation
  - job 89459153834 first terminated at deploy.sh line 249 because health-check.sh was invoked directly without execute permission, returning exit code 126
  - job 89459153834 completed ephemeral environment removal, GHCR logout and checkout cleanup successfully
  - sanitized Canary diagnostic job 89461725367 found Canary connected to MariaDB with all 59 schema tables and all seven required tables present
  - diagnostic job 89461725367 found Canary restart count 5 and the exact runtime error that CANARY_GAME_SESSION_ISSUER_WORLD_ID must be a positive integer while the issuer is enabled
  - the approved Canary image reference remained ghcr.io/blakinio/canary@sha256:784e5dbdcc64e311c48c51cd94aa206e2efa1e5eefb2f4ef40170d5aac55031f
  - temporary Canary diagnostic workflows were removed from PR 136 after sanitized evidence collection
derived:
  - the APP_KEY and MariaDB readiness blockers are resolved
  - the latest deployment progressed through database and Platform initialization; Synology connectivity, volumes and image availability are not the current blocker
  - the single staging world requires an explicit positive Canary Game Session issuer world ID, for which the minimal deterministic value is 1
  - deploy and rollback scripts must invoke health-check.sh through Bash because repository contents do not guarantee its executable bit on the runner
  - no user action, secret rotation or destructive volume reset is required for the current fixes
unknown:
  - whether the full stack completes after PR 136 is merged
  - Platform health endpoint result from the corrected health-check
  - Gateway readiness and version results
  - Canary login and game-port probe results after stable issuer startup
  - runtime host binding evidence after successful deployment
conflicts: []
first_failure:
  marker: Deploy Synology Staging run 30075926039 attempt job 89459153834 step Deploy prebuilt images
  evidence: deploy/synology/scripts/deploy.sh line 249 could not execute health-check.sh due to permission denied and exited 126
rejected_hypotheses:
  - Canary schema remained incomplete: diagnostic query returned 59 tables including all seven deployment-required tables
  - MariaDB was still the blocker: job 89459153834 passed readiness and completed database provisioning
  - Canary could not connect to MariaDB: Canary logs reported database connection established and migrations executed
  - another user environment change is required: the remaining failures are both represented by repository-controlled Compose and shell invocation changes
changed_paths:
  - deploy/synology/compose.yml
  - deploy/synology/scripts/deploy.sh
  - deploy/synology/scripts/rollback.sh
  - docs/agents/tasks/active/OTERYN-20260724-synology-first-staging-deploy.md
validation:
  - command: Deploy Synology Staging run 30075926039 attempt job 89459153834
    result: FAIL
    evidence: deployment reached health-check invocation then failed with permission denied and exit code 126; cleanup passed
  - command: temporary Canary diagnostic runs 30087022745 and 30087100260
    result: PASS
    evidence: self-hosted jobs 89461472287 and 89461725367 proved schema completeness and identified the missing positive WORLD_ID contract
  - command: PR 136 exact-head validation
    result: NOT_RUN
    evidence: pending after removal of temporary diagnostic workflows and addition of Compose plus Bash invocation fixes
blockers:
  - validate and merge PR 136 before rerunning the trusted-main deployment
next_action: Complete exact-head validation for PR 136, merge the Canary world ID and Bash health-check fixes, then rerun the failed deployment job and verify full health and cleanup.
```

## Notes

The temporary dispatcher must not activate production, expose DSM or Docker remotely, change legacy authentication, or print environment secrets.

The latest failed deployment left MariaDB, Redis, Canary, Platform and internal proxy or gateway containers created or running according to the reached step, while the workflow removed `deploy/synology/.env` and logged out of GHCR. No full staging-health or port-exposure claim is made yet.
