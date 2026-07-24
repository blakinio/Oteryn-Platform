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
- [x] `Deploy Synology Staging` completes successfully on runner label `oteryn-staging` using environment `synology-staging`.
- [x] The temporary trigger is removed and this task is archived after durable non-secret evidence is recorded.

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
blockers: []
cross_repository_tasks:
  - Canary image is consumed read-only from ghcr.io/blakinio/canary
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-24T11:31:00Z
head: 4fb8e2e921f1908b7bb01b667e335e8146d88a09
branch: chore/OTERYN-20260724-synology-first-staging-cleanup
pr: 137
status: ready
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
  - Build Synology Staging Images run 30075876955 published Platform and Gateway tag sha-e08548866e6edc70f69eaba40249303b69236625
  - One-shot Synology Staging Deploy run 30075876983 resolved Canary image ghcr.io/blakinio/canary@sha256:784e5dbdcc64e311c48c51cd94aa206e2efa1e5eefb2f4ef40170d5aac55031f and dispatched Deploy Synology Staging run 30075926039
  - PR 135 merged bounded MariaDB readiness handling as 7b67a0efcc519dec9d60c919c20266e90bddaf60
  - PR 136 merged the positive Canary issuer world ID and Bash health-check invocation as bbfbb883d68267d70a02e2c94ed7ccaaf7ac355b
  - Deploy Synology Staging run 30075926039 job 89465155605 completed successfully on runner oteryn-synology-staging with label oteryn-staging
  - deployment job 89465155605 passed trusted-main checkout, runner tools, GHCR login, configuration validation, ephemeral environment creation, deployment health checks, environment removal, GHCR logout and checkout cleanup
  - deployment log contained both success markers: Platform, Gateway and Canary staging probes passed; Oteryn Synology staging deployment is healthy
  - audit run 30088438385 jobs 89465969217 and 89465969281 completed successfully
  - audit job 89465969217 proved exactly one running container for mariadb, redis, canary, platform, internal-proxy and gateway, expected immutable images, zero restarts, loopback-only published ports and absence of the ephemeral deploy environment file
  - audit job 89465969281 independently recovered the successful deployment job metadata and both sanitized health markers
  - the temporary inspection workflow and one-shot deployment trigger are removed on PR 137
  - build workflow path filters no longer reference the removed one-shot workflow
  - no secret value was recorded in repository evidence, PR text or task checkpoint
unknown: []
conflicts: []
first_failure:
  marker: Deploy Synology Staging run 30075926039 attempt job 89426521777 configuration validation
  evidence: the initial staging APP_KEY value was not a valid Laravel base64 application key; later corrected without recording the secret
rejected_hypotheses:
  - MariaDB remained unavailable: later deployment and audit proved it healthy with zero restarts
  - Canary schema remained incomplete: diagnostics found all required schema tables and the successful deployment passed Canary probing
  - Canary could not remain running: the final audit found it running with zero restarts on the approved immutable digest
  - deployment cleanup left credentials on disk: final audit proved the ephemeral environment file absent and the successful job logged out of GHCR
changed_paths:
  - .github/workflows/build-synology-staging-images.yml
  - .github/workflows/one-shot-synology-staging-deploy.yml
  - docs/agents/tasks/active/OTERYN-20260724-synology-first-staging-deploy.md
  - docs/agents/tasks/archive/OTERYN-20260724-synology-first-staging-deploy.md
validation:
  - command: Deploy Synology Staging run 30075926039 job 89465155605
    result: PASS
    evidence: all deploy and cleanup steps succeeded; Platform, Gateway and Canary staging probes passed
  - command: Inspect Successful Synology Deploy run 30088438385 job 89465969217
    result: PASS
    evidence: required services, immutable images, restart counts, loopback bindings and environment-file cleanup matched policy
  - command: Inspect Successful Synology Deploy run 30088438385 job 89465969281
    result: PASS
    evidence: successful deployment metadata and both health markers were recovered from the Actions log
  - command: PR 137 exact-head validation
    result: PENDING
    evidence: run required after final cleanup and archive commits
blockers: []
next_action: Merge PR 137 after all required checks pass on its final cleanup head.
```

## Notes

The first staging deployment completed successfully without production activation, remote DSM exposure, direct public Docker exposure or secret disclosure. Platform, Gateway and both Canary game ports were audited as loopback-only at completion; later LAN game access is a separate explicitly authorized deployment task.
