# OTERYN-20260718 Platform architecture bootstrap

## Goal

Establish the durable product, architecture, security, module, integration and roadmap documentation required for future agents to build Oteryn Platform without relying on chat history.

## Acceptance criteria

- A new agent can identify the product goal and non-goals.
- A new agent can identify current architecture boundaries and module ownership.
- Security-critical invariants are explicit.
- Canary/login-server integration assumptions are separated into PROVEN versus UNKNOWN facts.
- A phased implementation roadmap exists.
- Durable ADRs record the initial technology and architecture direction.
- Repository/context routing points agents to the new authoritative documents.

## Owned paths

- `README.md`
- `CHANGELOG.md`
- `docs/architecture/**`
- `docs/contracts/**`
- `docs/agents/PROJECT_STATE.md`
- `docs/agents/ACTIVE_WORK.md`
- `docs/agents/REPOSITORY_MAP.md`
- `docs/agents/CONTEXT_ROUTING.md`
- `docs/agents/tasks/active/OTERYN-20260718-platform-architecture-bootstrap.md`

## Modules touched

- agent governance
- architecture
- security
- identity/auth planning
- Canary integration planning

## Dependencies

- `blakinio/canary` is read-only evidence for future contract discovery.
- Exact login-server repository/interface must be verified before auth integration implementation.

## Known unknowns

- Exact production Canary database schema and which tables/columns Oteryn Platform will be allowed to write.
- Final password/session compatibility requirements between Oteryn Platform, login-server and Canary.
- Final deployment topology, hosting provider, cache/queue backend and mail provider.
- Whether initial release requires one world or multi-world support.

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-18T19:00:00Z
head: UNKNOWN
branch: main
pr: none
status: implementing
context_routes:
  - agent-governance
  - architecture
  - security
  - canary-integration
owned_paths:
  - README.md
  - CHANGELOG.md
  - docs/architecture/**
  - docs/contracts/**
  - docs/agents/PROJECT_STATE.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/REPOSITORY_MAP.md
  - docs/agents/CONTEXT_ROUTING.md
  - docs/agents/tasks/active/OTERYN-20260718-platform-architecture-bootstrap.md
proven:
  - Repository is blakinio/Oteryn-Platform and currently contains agent-governance bootstrap documentation.
  - Oteryn Platform is intended to replace MyAAC rather than extend it as the long-term web/application platform.
  - Canary remains a separate game-server repository.
  - Payments are explicitly deferred and must remain a later modular capability.
derived:
  - Initial architecture should optimize for a modular monolith rather than premature microservices.
  - Integration with Canary and login-server must be treated as explicit versioned contracts.
unknown:
  - Exact Canary schema write contract.
  - Exact login-server authentication/session contract.
  - Production deployment topology.
conflicts: []
first_failure:
  marker: none
  evidence: none
rejected_hypotheses: []
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260718-platform-architecture-bootstrap.md
validation:
  - command: documentation structure review
    result: NOT_RUN
    evidence: architecture documents still being created
blockers: []
next_action: Create authoritative product, architecture, security, contracts, ADR and roadmap documents, then update repository routing.
```
