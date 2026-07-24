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
  - OTERYN_STAGING_APP_KEY exists but still fails the required Laravel base64 key format after one user-confirmed replacement
cross_repository_tasks:
  - Canary image is consumed read-only from ghcr.io/blakinio/canary
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-24T09:14:00Z
head: 87a2cabd6fda9b281a074546f860138b6f5bbc57
branch: fix/OTERYN-20260724-synology-staging-retry-evidence
pr: 134
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
  - Build Synology Staging Images run 30075876955 completed successfully
  - build jobs 89426364842 and 89426364862 published Platform and Gateway images for tag sha-e08548866e6edc70f69eaba40249303b69236625
  - One-shot Synology Staging Deploy run 30075876983 resolved both exact SHA-tagged images and Canary digest ghcr.io/blakinio/canary@sha256:784e5dbdcc64e311c48c51cd94aa206e2efa1e5eefb2f4ef40170d5aac55031f
  - one-shot job 89426365047 successfully dispatched Deploy Synology Staging run 30075926039 and failed only while monitoring that failed deployment
  - initial deploy job 89426521777 ran on runner oteryn-synology-staging with label oteryn-staging and first failed configuration validation because OTERYN_STAGING_APP_KEY was not a Laravel base64 application key
  - after the user confirmed replacing OTERYN_STAGING_APP_KEY, rerun attempt job 89444393576 reached the same runner and failed at the same configuration step with the same exact sanitized error
  - both failed attempts skipped environment creation and deployment and completed environment-file removal and GHCR logout cleanup
  - inspected logs masked injected secret values and sanitized evidence extraction did not reproduce secret values
derived:
  - image publication runner connectivity and runner Docker tooling are not the current blocker
  - the value currently saved under OTERYN_STAGING_APP_KEY still does not match the workflow requirement ^base64:[A-Za-z0-9+/=]+$
  - rerunning again without replacing the saved secret value would reproduce the deterministic failure
unknown:
  - whether the full stack can deploy after OTERYN_STAGING_APP_KEY is corrected
  - Platform health result
  - Gateway health readiness and version results
  - Canary game-port probe result
  - runtime host binding evidence after successful deployment
conflicts: []
first_failure:
  marker: Deploy Synology Staging run 30075926039 rerun job 89444393576 step Validate deployment configuration
  evidence: OTERYN_STAGING_APP_KEY must be a Laravel base64 application key
rejected_hypotheses:
  - push-triggered Platform or Gateway images were unavailable: both exact SHA tags resolved before dispatch
  - the Synology runner was offline: both deploy attempts executed on oteryn-synology-staging
  - required runner Docker tooling was unavailable: Validate runner tools and GHCR login passed in both attempts
  - the first secret replacement corrected the format: rerun job 89444393576 returned the same format error
changed_paths:
  - .github/workflows/inspect-synology-retry-evidence.yml
  - docs/agents/tasks/active/OTERYN-20260724-synology-first-staging-deploy.md
validation:
  - command: Build Synology Staging Images run 30075876955
    result: PASS
    evidence: jobs 89426364762 89426364819 89426364842 and 89426364862 succeeded
  - command: One-shot Synology Staging Deploy run 30075876983
    result: FAIL
    evidence: job 89426365047 dispatched run 30075926039 then propagated its failure; GHCR logout passed
  - command: Deploy Synology Staging run 30075926039 initial attempt
    result: FAIL
    evidence: job 89426521777 first failed configuration validation; cleanup passed
  - command: Deploy Synology Staging run 30075926039 rerun attempt
    result: FAIL
    evidence: job 89444393576 returned the same APP_KEY format error; cleanup passed
  - command: temporary read-only retry evidence run 30081788579
    result: PASS
    evidence: job 89444805913 extracted only sanitized job metadata and the exact configuration error
blockers:
  - replace synology-staging environment secret OTERYN_STAGING_APP_KEY with an unquoted single-line value matching base64 followed by a colon and valid Base64 characters only
next_action: In GitHub Settings > Environments > synology-staging > Environment secrets, replace OTERYN_STAGING_APP_KEY with the exact single-line output of php artisan key:generate --show, without quotes spaces or a secret-name prefix, then confirm only that it was replaced.
```

## Notes

The temporary dispatcher must not activate production, expose DSM or Docker remotely, change legacy authentication, or print environment secrets.

Both failed deployment attempts stopped before writing `deploy/synology/.env` or starting any service. No staging-health or port-exposure claim is made.
