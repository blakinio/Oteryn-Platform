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

- [x] Platform port `8000`, Gateway port `8080` and Canary legacy login port `7171` remain bound only to `127.0.0.1`.
- [x] Canary game port `7172` can be deliberately bound to the exact private Synology LAN address `192.168.1.2`; wildcard bind addresses are rejected.
- [x] Canary advertises the same exact LAN address used for the game-port bind.
- [x] Deployment creates or updates exactly one enabled online staging World Registry route with Platform world ID `1`, host `192.168.1.2` and port `7172` only after runtime health checks pass.
- [x] The Canary Game Session issuer world ID matches the Platform World Registry ID.
- [x] Deployment validation proves that the LAN override changes only the Canary game-port bind.
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
  - deploy/synology/scripts/rollback.sh
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
updated_at: 2026-07-24T12:08:00Z
head: d0569423f504e02567bbcc48f0a665608fa9bc7b
branch: feat/OTERYN-20260724-synology-lan-game-access
pr: 138
status: validating
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
  - deploy/synology/scripts/rollback.sh
  - deploy/synology/README.md
  - .github/workflows/build-synology-staging-images.yml
  - .github/workflows/deploy-synology-staging.yml
  - .github/workflows/one-shot-synology-lan-game-deploy.yml
  - docs/agents/tasks/active/OTERYN-20260724-synology-lan-game-access.md
  - docs/agents/tasks/archive/OTERYN-20260724-synology-lan-game-access.md
proven:
  - Synology staging deployment run 30075926039 job 89465155605 completed successfully before this task
  - the audited runtime initially bound Platform 8000, Gateway 8080 and Canary 7171/7172 to 127.0.0.1
  - Compose now has separate Platform, Gateway, Canary legacy-login and Canary game bind controls
  - deployment validation requires Platform, Gateway and legacy login to remain exact 127.0.0.1 and permits game TCP only on loopback or private IPv4
  - CANARY_SERVER_IP and the Platform World Registry host must exactly match the Canary game bind; their ports must also match
  - the Canary Game Session issuer world ID now comes from the same positive GAME_WORLD_ID used by Platform
  - game-auth:world:ensure validates and idempotently creates or updates the exact route; slug collisions fail closed
  - deploy and rollback update the World Registry route only after exact service and binding health checks pass
  - health-check.sh verifies all four exact published bindings and probes a configured private-LAN game endpoint from the deployment runner
  - Build Synology Staging Images run 30091408082 passed shell syntax, default loopback Compose validation, isolated private-LAN game override validation and all three image builds
  - CI run 30091810902 passed formatting, level-10 static analysis and the complete test suite after focused command corrections
  - the temporary focused PHPStan workflow identified only a mixed identifier cast and PendingCommand test-union assertions; both were corrected and the diagnostic workflow was removed
  - guarded one-shot workflow pins Platform/Gateway to the eventual merge SHA and Canary to ghcr.io/blakinio/canary@sha256:784e5dbdcc64e311c48c51cd94aa206e2efa1e5eefb2f4ef40170d5aac55031f
  - no secret value is written to the repository or diagnostic evidence

derived:
  - only Canary game TCP 7172 should be exposed to the LAN; legacy login 7171 is unnecessary for the Platform-native path
  - Platform and Gateway remain loopback-only and continue to be reached through controlled DSM reverse proxies
  - the World Registry route is not advertised until the exact game endpoint and service stack are healthy
  - a desktop OTClient still requires separately configured trusted browser/OAuth/Gateway endpoints after server-side LAN game routing succeeds
unknown:
  - whether DSM firewall currently permits TCP 7172 from the deployment-runner container and workstation LAN
  - whether a trusted local HTTPS hostname/certificate is already available for OTClient browser/OAuth endpoints
  - exact installed OTClient artifact and deployment configuration on the workstation
conflicts: []
first_failure:
  marker: CI run 30091205398 job 89474672247 static analysis
  evidence: focused diagnostic run 30091618876 artifact phpstan-world-command reported one mixed identifier cast and four PendingCommand union assertions; later CI run 30091810902 passed after exact fixes
rejected_hypotheses:
  - exposing Canary legacy login 7171 is sufficient: Platform-created Canary accounts have non-user sink password hashes and require native authentication
  - a shared host bind is acceptable: it would unnecessarily expose Platform, Gateway and legacy login together with game TCP
  - the World Registry can be configured before health: doing so could advertise an endpoint that failed to bind or start
changed_paths:
  - app/Console/Commands/EnsureGameWorld.php
  - tests/Feature/GameAuth/EnsureGameWorldCommandTest.php
  - deploy/synology/.env.example
  - deploy/synology/compose.yml
  - deploy/synology/scripts/deploy.sh
  - deploy/synology/scripts/health-check.sh
  - deploy/synology/scripts/rollback.sh
  - deploy/synology/README.md
  - .github/workflows/build-synology-staging-images.yml
  - .github/workflows/deploy-synology-staging.yml
  - .github/workflows/one-shot-synology-lan-game-deploy.yml
  - docs/agents/tasks/active/OTERYN-20260724-synology-lan-game-access.md
validation:
  - command: CI run 30091810902
    result: PASS
    evidence: formatting, level-10 PHPStan and full Laravel tests succeeded
  - command: Build Synology Staging Images run 30091408082
    result: PASS
    evidence: deployment package validation and platform, game-gateway and deploy-runner image builds succeeded
  - command: PR 138 final exact-head validation
    result: PENDING
    evidence: required after this checkpoint commit
blockers: []
next_action: Complete exact-head checks for PR 138, merge with the guarded marker, then inspect the one-shot and live Synology deployment before any cleanup.
```

## Notes

This task authorizes LAN-only game TCP exposure on the exact NAS address `192.168.1.2`. It does not authorize router port forwarding, Internet exposure, wildcard host binds, public DSM exposure, legacy password activation, secret disclosure or writes to Canary/OTClient repositories.
