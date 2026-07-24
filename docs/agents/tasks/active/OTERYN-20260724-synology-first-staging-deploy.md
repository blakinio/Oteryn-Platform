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
  - OTERYN_STAGING_APP_KEY exists but is not a valid Laravel base64 application key
cross_repository_tasks:
  - Canary image is consumed read-only from ghcr.io/blakinio/canary
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-24T08:17:42Z
head: 2a479d61b8758d5c6da74925ab97b5aa8377b3c2
branch: chore/OTERYN-20260724-synology-actions-evidence
pr: 132
status: blocked
context_routes:
  - agent-governance
  - testing
  - security
  - ci-repair
owned_paths:
  - .github/workflows/build-synology-staging-images.yml
  - .github/workflows/one-shot-synology-staging-deploy.yml
  - docs/agents/tasks/active/OTERYN-20260724-synology-first-staging-deploy.md
  - docs/agents/tasks/archive/OTERYN-20260724-synology-first-staging-deploy.md
proven:
  - main remained at marked merge e08548866e6edc70f69eaba40249303b69236625 during the first deployment attempt
  - Build Synology Staging Images run 30075876955 completed successfully
  - build jobs 89426364842 and 89426364862 published Platform and Gateway images for tag sha-e08548866e6edc70f69eaba40249303b69236625
  - One-shot Synology Staging Deploy run 30075876983 resolved both exact SHA-tagged images and Canary digest ghcr.io/blakinio/canary@sha256:784e5dbdcc64e311c48c51cd94aa206e2efa1e5eefb2f4ef40170d5aac55031f
  - one-shot job 89426365047 successfully dispatched Deploy Synology Staging run 30075926039 and failed only while monitoring that failed deployment
  - deploy job 89426521777 ran on runner oteryn-synology-staging with label oteryn-staging
  - deploy job 89426521777 first failed at Validate deployment configuration because OTERYN_STAGING_APP_KEY was not a Laravel base64 application key
  - the failed deploy did not write the ephemeral staging environment or start deployment and both environment-file removal and GHCR logout cleanup succeeded
  - inspected logs masked injected secret values and the sanitized evidence extraction did not reproduce secret values
derived:
  - the first failure is GitHub Environment configuration rather than image publication, runner connectivity or Docker tool availability
  - rerunning unchanged configuration would reproduce the deterministic validation failure
unknown:
  - whether the full stack can deploy after OTERYN_STAGING_APP_KEY is corrected
  - Platform health result
  - Gateway health readiness and version results
  - Canary game-port probe result
  - runtime host binding evidence after successful deployment
conflicts: []
first_failure:
  marker: Deploy Synology Staging run 30075926039 job 89426521777 step Validate deployment configuration
  evidence: OTERYN_STAGING_APP_KEY must be a Laravel base64 application key
rejected_hypotheses:
  - push-triggered Platform or Gateway images were unavailable: both exact SHA tags resolved before dispatch
  - the Synology runner was offline: deploy job executed on oteryn-synology-staging
  - required runner Docker tooling was unavailable: Validate runner tools and GHCR login passed
changed_paths:
  - .github/workflows/inspect-synology-actions-evidence.yml
  - docs/agents/tasks/active/OTERYN-20260724-synology-first-staging-deploy.md
validation:
  - command: Build Synology Staging Images run 30075876955
    result: PASS
    evidence: jobs 89426364762 89426364819 89426364842 and 89426364862 succeeded
  - command: One-shot Synology Staging Deploy run 30075876983
    result: FAIL
    evidence: job 89426365047 dispatched run 30075926039 then propagated its failure; GHCR logout passed
  - command: Deploy Synology Staging run 30075926039
    result: FAIL
    evidence: job 89426521777 first failed configuration validation; cleanup passed
  - command: temporary read-only Actions evidence run 30078252858
    result: PASS
    evidence: sanitized exact run job digest runner and failure metadata collected without environment-secret access
blockers:
  - replace synology-staging environment secret OTERYN_STAGING_APP_KEY with a valid Laravel base64 application key without disclosing it
next_action: In GitHub Settings > Environments > synology-staging > Environment secrets, replace OTERYN_STAGING_APP_KEY with a value in Laravel base64 format and confirm only that the secret was updated.
```

## Notes

The temporary dispatcher must not activate production, expose DSM or Docker remotely, change legacy authentication, or print environment secrets.

The failed deployment stopped before writing `deploy/synology/.env` or starting any service. No staging-health or port-exposure claim is made.
