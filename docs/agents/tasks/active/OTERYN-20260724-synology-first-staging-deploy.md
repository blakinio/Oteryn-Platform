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
  - deploy/synology/scripts/deploy.sh
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
  - deploy script MariaDB readiness limit is shorter than the observed first initialization time on Synology; fix is pending PR 135 validation and merge
cross_repository_tasks:
  - Canary image is consumed read-only from ghcr.io/blakinio/canary
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-24T10:20:00Z
head: 1496f55fcb1fd1fe717c08994233fb3fe0adaf6b
branch: fix/OTERYN-20260724-synology-deploy-runtime-evidence
pr: 135
status: validating
context_routes:
  - agent-governance
  - testing
  - security
  - ci-repair
owned_paths:
  - .github/workflows/build-synology-staging-images.yml
  - .github/workflows/one-shot-synology-staging-deploy.yml
  - deploy/synology/scripts/deploy.sh
  - docs/agents/tasks/active/OTERYN-20260724-synology-first-staging-deploy.md
  - docs/agents/tasks/archive/OTERYN-20260724-synology-first-staging-deploy.md
proven:
  - Build Synology Staging Images run 30075876955 completed successfully
  - build jobs 89426364842 and 89426364862 published Platform and Gateway images for tag sha-e08548866e6edc70f69eaba40249303b69236625
  - One-shot Synology Staging Deploy run 30075876983 resolved both exact SHA-tagged images and Canary digest ghcr.io/blakinio/canary@sha256:784e5dbdcc64e311c48c51cd94aa206e2efa1e5eefb2f4ef40170d5aac55031f
  - one-shot job 89426365047 successfully dispatched Deploy Synology Staging run 30075926039 and failed only while monitoring that failed deployment
  - deploy jobs 89426521777 and 89444393576 ran on runner oteryn-synology-staging with label oteryn-staging and failed configuration validation because OTERYN_STAGING_APP_KEY was not a Laravel base64 application key
  - after the second user correction, deploy job 89454820564 passed runner tools, GHCR login, deployment configuration validation and ephemeral environment creation
  - deploy job 89454820564 failed in Deploy prebuilt images because MariaDB did not become ready within the script's existing 120-second polling window
  - job 89454820564 completed ephemeral environment removal, GHCR logout and checkout cleanup successfully
  - sanitized runner diagnostic job 89456309129 found the same MariaDB container running, healthy, exit code 0, restart count 0 and not OOM-killed after the deployment timeout
  - MariaDB logs showed normal first initialization and eventual ready-for-connections state after the deployment polling window had already expired
  - inspected logs masked injected secret values and sanitized evidence extraction did not reproduce secret values
  - the temporary runtime inspection workflow was removed from PR 135 after evidence collection
derived:
  - the APP_KEY configuration blocker is resolved
  - image publication, runner connectivity, runner Docker tooling and MariaDB image viability are not the current blocker
  - the 120-second MariaDB readiness loop is too short for first initialization on the Synology storage and produced a false deployment failure
  - extending the bounded readiness timeout is sufficient; destructive volume reset or user action is not justified
unknown:
  - whether the full stack completes after the readiness timeout fix is merged
  - Platform health result
  - Gateway health readiness and version results
  - Canary game-port probe result
  - runtime host binding evidence after successful deployment
conflicts: []
first_failure:
  marker: Deploy Synology Staging run 30075926039 latest attempt job 89454820564 step Deploy prebuilt images
  evidence: MariaDB did not become ready; refusing to continue deployment
rejected_hypotheses:
  - OTERYN_STAGING_APP_KEY remains malformed: configuration validation passed in job 89454820564
  - MariaDB crashed or restart-looped: diagnostic state was running and healthy with restart count 0
  - MariaDB was OOM-killed: diagnostic state reported oom_killed=false
  - MariaDB data volume must be deleted: the existing container completed initialization and became healthy without destructive intervention
changed_paths:
  - deploy/synology/scripts/deploy.sh
  - docs/agents/tasks/active/OTERYN-20260724-synology-first-staging-deploy.md
validation:
  - command: Deploy Synology Staging run 30075926039 latest attempt
    result: FAIL
    evidence: job 89454820564 passed configuration and environment creation then timed out waiting for MariaDB; cleanup passed
  - command: temporary read-only runtime evidence run 30085315640
    result: PASS
    evidence: job 89456023848 extracted the exact sanitized MariaDB readiness failure
  - command: temporary self-hosted runner diagnostic run 30085403003
    result: PASS
    evidence: job 89456309129 proved MariaDB eventually became healthy without restart or OOM
blockers:
  - merge the validated readiness timeout fix from PR 135, then rerun deploy job 89454820564 against trusted main
next_action: Complete exact-head validation for PR 135, merge the bounded MariaDB readiness fix, and rerun the failed deployment job.
```

## Notes

The temporary dispatcher must not activate production, expose DSM or Docker remotely, change legacy authentication, or print environment secrets.

The latest failed deployment created the MariaDB, Redis and TLS bootstrap services but stopped before Platform and Gateway startup. The workflow removed `deploy/synology/.env` and logged out of GHCR. No staging-health or port-exposure claim is made yet.
