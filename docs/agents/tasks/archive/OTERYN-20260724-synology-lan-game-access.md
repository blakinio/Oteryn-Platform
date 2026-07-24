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
- [x] A guarded temporary one-shot workflow deploys the exact reviewed `main` revision and approved immutable Canary image.
- [x] Live Synology evidence proves the intended bindings, World Registry route and service health without exposing secrets.
- [x] The temporary one-shot trigger is removed and this task is archived after successful deployment.

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
  - blakinio/canary remained read-only; the compatible image was consumed by immutable digest
  - blakinio/otclient remained read-only; desktop endpoint configuration is outside this server deployment task
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-24T12:31:00Z
head: f54994edc8619c858462ecdbd39bfeff7d4bbfb8
branch: chore/OTERYN-20260724-synology-lan-game-cleanup
pr: 141
status: ready
context_routes:
  - agent-governance
  - canary-integration
  - auth-identity
  - security
  - testing
owned_paths:
  - .github/workflows/build-synology-staging-images.yml
  - .github/workflows/one-shot-synology-lan-game-deploy.yml
  - docs/agents/tasks/active/OTERYN-20260724-synology-lan-game-access.md
  - docs/agents/tasks/archive/OTERYN-20260724-synology-lan-game-access.md
proven:
  - PR 138 merged as 050b8b1e0d10265f25d77f62b6c5775871771dc1 after all exact-head checks passed
  - Build Synology Staging Images produced Platform and Gateway images tagged sha-050b8b1e0d10265f25d77f62b6c5775871771dc1
  - guarded one-shot run 30092577407 job 89478968529 completed successfully, resolved the exact merge-SHA images and dispatched the LAN-only deployment
  - Deploy Synology Staging run 30092611233 job 89479075078 completed successfully on runner oteryn-synology-staging
  - deployment validation, ephemeral environment creation, prebuilt image deployment, health checks, environment-file removal and GHCR logout all passed
  - Platform image is ghcr.io/blakinio/oteryn-platform:sha-050b8b1e0d10265f25d77f62b6c5775871771dc1
  - Gateway image is ghcr.io/blakinio/oteryn-game-gateway:sha-050b8b1e0d10265f25d77f62b6c5775871771dc1
  - Canary image is ghcr.io/blakinio/canary@sha256:784e5dbdcc64e311c48c51cd94aa206e2efa1e5eefb2f4ef40170d5aac55031f
  - read-only audit run 30092947173 jobs 89480131709 and 89480149200 completed successfully
  - live audit found exactly one running mariadb, redis, canary, platform, internal-proxy and gateway container, each with restart_count 0
  - live audit proved Platform 127.0.0.1:8000, Gateway 127.0.0.1:8080, Canary legacy login 127.0.0.1:7171 and Canary game 192.168.1.2:7172
  - live audit proved 192.168.1.2:7172 reachable from the dedicated deployment runner
  - live audit proved Canary advertised IP 192.168.1.2 and Game Session issuer world ID 1
  - live audit proved the enabled online Platform World Registry route id 1, slug oteryn-staging, host 192.168.1.2 and port 7172
  - live audit proved the ephemeral deployment environment file absent
  - the temporary read-only audit workflow and completed one-shot deployment workflow are removed on PR 141
  - build workflow path filters no longer reference the removed one-shot workflow
  - no secret value was recorded in repository evidence, workflow output, PR text or task checkpoint

derived:
  - the server-side LAN game endpoint is ready without exposing Platform, Gateway or legacy password login directly to the LAN
  - the native client should receive world route 192.168.1.2:7172 from the authoritative Platform World Registry after a successful Gateway login
unknown:
  - direct TCP reachability from the user's workstation has not yet been measured independently of the deployment runner
  - a trusted local HTTPS hostname and certificate for the browser OAuth and Gateway endpoints have not yet been configured or proven
  - the exact compatible OTClient artifact and workstation deployment configuration remain outside this repository task
conflicts: []
first_failure:
  marker: CI run 30091205398 job 89474672247 static analysis
  evidence: focused diagnostic run 30091618876 identified one mixed identifier cast and four PendingCommand union assertions; later CI run 30091810902 passed after exact fixes
rejected_hypotheses:
  - exposing Canary legacy login 7171 is sufficient: Platform-created Canary accounts use non-user sink password hashes and require native authentication
  - a shared host bind is acceptable: it would unnecessarily expose Platform, Gateway and legacy login together with game TCP
  - the World Registry can be configured before health: doing so could advertise an endpoint that failed to bind or start
  - the LAN deployment failed because of DSM firewall policy: both the deploy health check and independent runner audit reached 192.168.1.2:7172 successfully
changed_paths:
  - .github/workflows/build-synology-staging-images.yml
  - .github/workflows/one-shot-synology-lan-game-deploy.yml
  - docs/agents/tasks/active/OTERYN-20260724-synology-lan-game-access.md
  - docs/agents/tasks/archive/OTERYN-20260724-synology-lan-game-access.md
validation:
  - command: PR 138 exact-head validation
    result: PASS
    evidence: Agent Governance 30092228332, CI 30092228271, Build Synology Staging Images 30092228269, Game Auth Ticket Concurrency 30092228275, Platform DB Outage 30092228289, Phase 7 30092228303 and Acceptance E2E 30092228283 all succeeded
  - command: One-shot Synology LAN Game Deploy run 30092577407 job 89478968529
    result: PASS
    evidence: exact images resolved, guarded dispatch completed and deployment run monitoring succeeded
  - command: Deploy Synology Staging run 30092611233 job 89479075078
    result: PASS
    evidence: all deployment and credential-cleanup steps succeeded on the dedicated Synology runner
  - command: Inspect Synology LAN Game Deploy run 30092947173 jobs 89480131709 and 89480149200
    result: PASS
    evidence: exact run resolution and live runtime, binding, route, reachability, image and cleanup assertions succeeded
  - command: PR 141 final cleanup exact-head validation
    result: NOT_RUN
    evidence: required after final cleanup and archive commits
blockers: []
next_action: Merge PR 141 after all required checks pass on its final cleanup head.
```

## Notes

This task delivered LAN-only game TCP exposure on exact NAS address `192.168.1.2` without router forwarding, Internet exposure, wildcard binds, public DSM exposure, legacy-password activation, secret disclosure or writes to Canary/OTClient repositories. Actual desktop login still requires a compatible OTClient plus trusted local HTTPS browser/OAuth/Gateway endpoint configuration.
