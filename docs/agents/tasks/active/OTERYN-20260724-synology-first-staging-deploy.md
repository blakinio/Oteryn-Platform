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

- [ ] A temporary one-shot workflow can dispatch only the trusted `main` deployment workflow after an explicitly marked merge.
- [ ] Platform and Gateway images are pinned to the exact one-shot merge SHA tag.
- [ ] The Canary image is resolved from the approved public package tag to an immutable digest before dispatch.
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
  - none
cross_repository_tasks:
  - Canary image is consumed read-only from ghcr.io/blakinio/canary
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-24T07:20:00Z
head: 60b12fb2d1748fb016484eca521a6c61af505d37
branch: chore/OTERYN-20260724-synology-first-staging-deploy
pr: none
status: implementing
context_routes:
  - agent-governance
  - testing
  - security
owned_paths:
  - .github/workflows/build-synology-staging-images.yml
  - .github/workflows/one-shot-synology-staging-deploy.yml
  - docs/agents/tasks/active/OTERYN-20260724-synology-first-staging-deploy.md
  - docs/agents/tasks/archive/OTERYN-20260724-synology-first-staging-deploy.md
proven:
  - PR 128 merged the containerized-runner boundary repair to main
  - the user explicitly authorized a temporary one-shot trigger instead of a manual workflow_dispatch click
  - the repository-scoped Synology runner previously reported Listening for Jobs
  - synology-staging environment variables and required secrets were added by the user
  - the existing deployment workflow is restricted to refs/heads/main, runner label oteryn-staging and environment synology-staging
derived:
  - a trusted push workflow can use actions write permission to invoke the existing workflow_dispatch endpoint without exposing deployment secrets
unknown:
  - exact immutable Canary image digest that will resolve from ghcr.io/blakinio/canary:latest at execution time
  - first live deployment result on Synology
conflicts: []
first_failure:
  marker: none
  evidence: execution has not started
rejected_hypotheses:
  - duplicate the complete deployment workflow in a temporary file: rejected because dispatching the existing guarded workflow avoids configuration drift
  - build Platform or Canary directly on the Synology NAS: rejected because the NAS is staging runtime only
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260724-synology-first-staging-deploy.md
validation:
  - command: not-run
    result: NOT_RUN
    evidence: one-shot workflow implementation is starting
blockers:
  - none
next_action: add the one-shot dispatcher and make the Synology image build workflow publish the exact merge-SHA images before dispatch.
```

## Notes

The temporary dispatcher must not activate production, expose DSM or Docker remotely, change legacy authentication, or print environment secrets.
