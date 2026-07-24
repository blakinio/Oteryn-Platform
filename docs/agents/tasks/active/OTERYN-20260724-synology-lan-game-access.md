---
task_id: OTERYN-20260724-synology-lan-game-access
required_reads:
  - AGENTS.md
  - docs/agents/REPOSITORY_MAP.md
  - docs/agents/CONTEXT_ROUTING.md
  - docs/agents/BUILD_TEST_MATRIX.md
  - docs/agents/CONTEXT_HANDOFF.md
  - docs/architecture/SECURITY_ARCHITECTURE.md
  - docs/contracts/WORLD_REGISTRY_CONTRACT.md
  - docs/contracts/GAME_SESSION_CANARY_CONTRACT.md
  - deploy/synology/README.md
  - .github/workflows/deploy-synology-staging.yml
search_first:
  - active Synology deployment tasks and workflow ownership
  - existing World Registry configuration commands
  - native-auth LAN routing and binding controls
optional_reads: []
---

# OTERYN-20260724-synology-lan-game-access

## Goal

Enable Oteryn native game entry from the trusted home LAN through Synology `192.168.1.2:7172`, while keeping Platform, Game Gateway and Canary legacy login bound to Synology loopback and preserving deny-by-default deployment behavior.

## Acceptance criteria

- [ ] Platform port `8000`, Gateway port `8080` and Canary legacy login port `7171` remain bound only to `127.0.0.1`.
- [ ] Canary game port `7172` can be deliberately bound to the exact private Synology LAN address `192.168.1.2`; wildcard bind addresses are rejected.
- [ ] Canary advertises the same exact LAN address used for the game-port bind.
- [ ] Deployment creates or updates exactly one enabled online staging World Registry route with Platform world ID `1`, host `192.168.1.2` and port `7172` only after runtime health checks pass.
- [ ] The Canary Game Session issuer world ID matches the Platform World Registry ID.
- [ ] Deployment validation proves that the LAN override changes only the Canary game-port bind.
- [ ] A guarded temporary one-shot workflow deploys the exact reviewed `main` revision and approved immutable Canary image.
- [ ] Live Synology evidence proves the intended bindings, World Registry route and service health without exposing secrets.
- [ ] The temporary one-shot trigger is removed and this task is archived after successful deployment.

## Ownership

```yaml
owned_paths:
  - app/Console/Commands/EnsureGameWorld.php
  - tests/Feature/GameAuth/EnsureGameWorldCommandTest.php
  - deploy/synology/.env.example
  - deploy/synology/compose.yml
  - deploy/synology/scripts/deploy.sh
  - deploy/synology/scripts/health-check.sh
  - deploy/synology/README.md
  - .github/workflows/build-synology-staging-images.yml
  - .github/workflows/deploy-synology-staging.yml
  - .github/workflows/one-shot-synology-lan-game-deploy.yml
  - docs/agents/tasks/active/OTERYN-20260724-synology-lan-game-access.md
  - docs/agents/tasks/archive/OTERYN-20260724-synology-lan-game-access.md
modules:
  - Synology staging deployment
  - World Registry
  - Game Gateway / Canary native-auth routing
dependencies:
  - first successful Synology staging deployment archived by PR 137
  - approved Canary Game Session image digest
  - online self-hosted runner labeled oteryn-staging
blockers: []
cross_repository_tasks:
  - blakinio/canary is read-only; current compatible image is consumed by immutable digest
  - blakinio/otclient is read-only; desktop endpoint configuration remains a later bounded step
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-24T11:40:00Z
head: cdc1d07d7c7d4cca0f1133e2beb30890359eadd1
branch: feat/OTERYN-20260724-synology-lan-game-access
pr: none
status: implementing
context_routes:
  - agent-governance
  - canary-integration
  - auth-identity
  - security
  - testing
owned_paths:
  - app/Console/Commands/EnsureGameWorld.php
  - tests/Feature/GameAuth/EnsureGameWorldCommandTest.php
  - deploy/synology/.env.example
  - deploy/synology/compose.yml
  - deploy/synology/scripts/deploy.sh
  - deploy/synology/scripts/health-check.sh
  - deploy/synology/README.md
  - .github/workflows/build-synology-staging-images.yml
  - .github/workflows/deploy-synology-staging.yml
  - .github/workflows/one-shot-synology-lan-game-deploy.yml
  - docs/agents/tasks/active/OTERYN-20260724-synology-lan-game-access.md
  - docs/agents/tasks/archive/OTERYN-20260724-synology-lan-game-access.md
proven:
  - Synology staging deployment run 30075926039 job 89465155605 completed successfully before this task
  - the audited runtime initially bound Platform 8000, Gateway 8080 and Canary 7171/7172 to 127.0.0.1
  - current Compose uses one shared OTERYN_BIND_ADDRESS for Platform, Gateway and both Canary ports
  - current deployment writes CANARY_SERVER_IP=127.0.0.1 and does not provision a game_worlds route
  - DatabaseWorldRegistry returns only online and login-enabled records with valid game_host and game_port
  - Platform account provisioning stores a random sink password hash, so legacy password login is not a valid path for Platform-created accounts
  - native authentication protocol v1 supports one Platform world mapped to one exact Canary process
  - PR 137 removed the completed first-deploy one-shot workflow and archived its task

derived:
  - only Canary game TCP 7172 should be exposed to the LAN; legacy login 7171 is unnecessary for the Platform-native path
  - Platform and Gateway should remain loopback-only and continue to be reached through controlled DSM reverse proxies
  - the World Registry route must be updated only after the exact game endpoint is healthy
  - binding and advertised address must be validated as the same private IPv4 address to avoid unusable client routing
unknown:
  - whether DSM firewall currently permits TCP 7172 from the workstation LAN
  - whether a trusted local HTTPS hostname/certificate is already available for OTClient browser/OAuth endpoints
  - exact installed OTClient artifact and deployment configuration on the workstation
conflicts: []
first_failure:
  marker: none
  evidence: implementation not yet run
rejected_hypotheses:
  - exposing Canary legacy login 7171 is sufficient: Platform-created Canary accounts have non-user sink password hashes and require native authentication
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260724-synology-lan-game-access.md
validation:
  - command: not-run
    result: NOT_RUN
    evidence: task record created before implementation
blockers: []
next_action: Implement separate loopback and private-LAN bind controls plus deterministic World Registry provisioning.
```

## Notes

This task authorizes LAN-only game TCP exposure on the exact NAS address `192.168.1.2`. It does not authorize router port forwarding, Internet exposure, wildcard host binds, public DSM exposure, legacy password activation, secret disclosure or writes to Canary/OTClient repositories.
