---
task_id: OTERYN-20260723-synology-staging-deployment
required_reads:
  - AGENTS.md
  - docs/agents/CONTEXT_HANDOFF.md
  - docs/agents/REPOSITORY_MAP.md
  - docs/agents/CONTEXT_ROUTING.md
  - docs/architecture/TEST_STRATEGY.md
  - docs/operations/PRODUCTION_TOPOLOGY_EVIDENCE.md
search_first:
  - active deployment/container/Synology tasks and open PR ownership
  - existing Docker/Compose/workflow deployment assets
optional_reads:
  - docs/operations/PRODUCTION_READINESS_CHECKLIST.md
---

# OTERYN-20260723-synology-staging-deployment

## Goal

Prepare a repository-owned Synology Container Manager staging deployment package that keeps compilation and image builds on GitHub-hosted runners, publishes ready-to-run Platform and Game Gateway images to GHCR, and deploys only prebuilt images on a labeled private self-hosted runner attached to the private `blakinio/Oteryn-Platform` repository.

This task does not perform a production deployment, does not expose Docker Engine over TCP, does not commit secrets, and does not claim production verification.

## Acceptance criteria

- [ ] `deploy/synology/` contains a safe Compose template, environment template, operational README, deploy script, rollback script and health-check script for staging use.
- [ ] GitHub-hosted Actions build and validate Platform and Game Gateway container images; image publishing is limited to trusted non-PR execution.
- [ ] A deployment workflow targets only a labeled self-hosted Synology runner and performs registry login, pull, controlled Compose update and health verification without compiling source on the NAS.
- [ ] Deployment remains fail-closed until required staging variables/secrets and a Canary runtime image are explicitly configured.
- [ ] No plaintext credential, registration token, private key, production endpoint or database dump is committed or logged.
- [ ] Repository CI/governance checks pass on the task head, or an exact external blocker is recorded.

## Ownership

```yaml
owned_paths:
  - docs/agents/tasks/active/OTERYN-20260723-synology-staging-deployment.md
  - deploy/synology/**
  - .github/workflows/build-synology-staging-images.yml
  - .github/workflows/deploy-synology-staging.yml
modules:
  - Synology staging deployment
  - container image build/publish
  - self-hosted deployment runner boundary
dependencies:
  - private blakinio/Oteryn-Platform repository
  - existing services/game-gateway runtime
  - external prebuilt Canary image supplied by deployment configuration
blockers:
  - self-hosted runner registration on Synology requires one-time repository runner registration performed outside repository contents
  - full stack activation requires a compatible prebuilt Canary image reference
cross_repository_tasks:
  - CAN-20260723-native-auth-ephemeral-cutover-rehearsal (read-only compatibility/evidence reference)
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-23T22:00:00Z
head: UNKNOWN
branch: feat/OTERYN-20260723-synology-staging-deployment
pr: none
status: implementing
context_routes:
  - agent-governance
  - architecture
  - testing
  - security
owned_paths:
  - docs/agents/tasks/active/OTERYN-20260723-synology-staging-deployment.md
  - deploy/synology/**
  - .github/workflows/build-synology-staging-images.yml
  - .github/workflows/deploy-synology-staging.yml
proven:
  - blakinio/Oteryn-Platform is private and accessible through the installed GitHub integration.
  - main head at task start is 53158217a6c6017230301cf4daa783b04fcc13d5.
  - open PR 126 owns only its native-auth rehearsal workflow, task record and rehearsal runner path; no owned-path overlap exists with this task.
  - existing Phase 7 validation uses PHP 8.5, MariaDB 11.8 and Redis 7.4 and proves repository/staging boundaries only.
derived:
  - Synology should consume prebuilt images rather than compile Platform/Gateway/Canary source on the DS920+.
  - a repository-scoped private self-hosted runner can be limited to deployment jobs by a dedicated custom label.
unknown:
  - exact Synology host filesystem path selected by the user for the deployment checkout
  - exact compatible prebuilt Canary image reference for this staging stack
  - self-hosted runner registration state and label
conflicts: []
first_failure:
  marker: none
  evidence: none
rejected_hypotheses:
  - build source directly on Synology: rejected because the requested architecture explicitly keeps CPU-intensive builds off the NAS
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260723-synology-staging-deployment.md
validation:
  - command: not-run
    result: NOT_RUN
    evidence: implementation has just started
blockers:
  - runner registration and Canary image selection are external configuration gates, not repository implementation failures
next_action: create the draft PR, then implement the Synology deployment package and GitHub Actions workflows on the dedicated task branch.
```

## Notes

The deployment workflow must never accept untrusted pull-request code on the self-hosted runner. Pull requests may build/validate images only on GitHub-hosted runners; the Synology deployment job is manual or trusted-main only.
