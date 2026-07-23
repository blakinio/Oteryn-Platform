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

- [x] `deploy/synology/` contains a safe Compose template, environment template, operational README, deploy script, rollback script and health-check script for staging use.
- [x] GitHub-hosted Actions build and validate Platform, Game Gateway and dedicated deployment-runner container images; image publishing is limited to trusted non-PR execution.
- [x] A deployment workflow targets only the custom-label Synology runner and performs registry login, pull, controlled Compose update and health verification without compiling source on the NAS.
- [x] Deployment remains fail-closed until required staging variables/secrets and a compatible prebuilt Canary runtime image are explicitly configured.
- [x] No plaintext credential, registration token, private key, production endpoint or database dump is committed or intentionally logged.
- [x] Repository CI/governance and deployment-package image validation pass on the validated implementation head.

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
  - compatible prebuilt Canary image supplied by deployment configuration
blockers: []
activation_gates:
  - self-hosted runner registration on Synology requires a one-time repository runner registration token and Container Manager action outside repository contents
  - full native-auth stack activation requires a compatible prebuilt Canary image reference containing the required Game Session issuer
  - synology-staging GitHub Environment secrets and variables must be configured outside Git before first deployment
cross_repository_tasks:
  - CAN-20260723-native-auth-ephemeral-cutover-rehearsal (read-only compatibility/evidence reference)
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-23T22:15:00Z
head: a1ad36067f8bd26fd49ebf29234e19fbd9efb5d0
branch: feat/OTERYN-20260723-synology-staging-deployment
pr: 127
status: ready
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
  - Synology package shell syntax and both Compose manifests pass Build Synology Staging Images run 30048939459.
  - Platform image builds and validates successfully in run 30048939459.
  - Game Gateway image builds and validates successfully in run 30048939459.
  - dedicated deployment-runner image builds and validates Docker CLI plus Compose successfully in run 30048939459.
  - CI run 30048939537 passes on implementation head a1ad36067f8bd26fd49ebf29234e19fbd9efb5d0.
  - Agent Governance run 30048939570 passes on the same implementation head.
  - Platform DB Outage Validation run 30048939573 passes on the same implementation head.
  - Phase 7 Production-Like Validation run 30048939555 passes on the same implementation head.
  - Game Auth Ticket Concurrency run 30048939410 passes on the same implementation head.
  - deployment is manual-main-only and targets the isolated custom runner label oteryn-staging; the runner registers without default labels.
  - deployment environment files are parsed without shell evaluation and are deleted after workflow completion.
  - Platform persistent storage retains generated Passport signing keys across image replacement.
  - internal Gateway dependencies use generated staging TLS certificates and the Gateway standard CA/hostname verification path.
derived:
  - Synology consumes prebuilt images rather than compiling Platform, Gateway or Canary source on the DS920+.
  - repository merge can proceed independently of runner registration because registration is a staging activation gate rather than an implementation merge dependency.
unknown:
  - exact compatible prebuilt Canary image reference for this staging stack
  - self-hosted runner registration state on the user's Synology
  - final synology-staging Environment secret/variable values
conflicts: []
first_failure:
  marker: none
  evidence: none
rejected_hypotheses:
  - build source directly on Synology: rejected because the requested architecture explicitly keeps CPU-intensive builds off the NAS
  - expose Docker Engine TCP for remote control: rejected; the runner uses a local Docker socket and is isolated to a private repository plus custom label
  - source the generated .env as shell code: rejected; deployment scripts use a bounded non-evaluating KEY=VALUE parser
changed_paths:
  - .github/workflows/build-synology-staging-images.yml
  - .github/workflows/deploy-synology-staging.yml
  - deploy/synology/**
  - docs/agents/tasks/active/OTERYN-20260723-synology-staging-deployment.md
validation:
  - command: Build Synology Staging Images / run 30048939459
    result: PASS
    evidence: manifest and shell validation plus Platform, Game Gateway and deploy-runner image builds all succeeded
  - command: CI / run 30048939537
    result: PASS
    evidence: repository CI succeeded on a1ad36067f8bd26fd49ebf29234e19fbd9efb5d0
  - command: Agent Governance / run 30048939570
    result: PASS
    evidence: governance validation succeeded on the same implementation head
  - command: Phase 7 Production-Like Validation / run 30048939555
    result: PASS
    evidence: existing production-like validation remained green
blockers:
  - staging activation still requires one-time Synology runner registration outside Git
  - first full native-auth deployment still requires a compatible prebuilt Canary image reference
  - synology-staging Environment secrets and variables must be configured outside Git before deployment
next_action: merge PR 127 after current checkpoint-head checks pass, verify trusted-main GHCR image publication, then perform the one-time Synology runner registration outside Git.
```

## Notes

The deployment workflow never accepts pull-request code on the self-hosted runner. Pull requests build and validate images only on GitHub-hosted runners; Synology deployment is manual, checks out trusted `main`, and targets only the custom `oteryn-staging` runner label.

The external activation gates do not promote this staging package to production evidence and do not authorize a production deployment.
