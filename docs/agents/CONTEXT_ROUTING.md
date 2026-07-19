# Oteryn Platform Agent Context Routing

## Goal

Load the smallest authoritative context required for the current task. Do not preload broad repository documentation when targeted search can identify the relevant records.

## Governance contract boundary

- `docs/agents/GOVERNANCE_CONTRACT.json` is the machine-readable version marker for the shared checkpoint/handoff contract.
- `docs/agents/CONTEXT_HANDOFF.md` is the human-readable checkpoint/handoff contract used by Oteryn Platform.
- This routing document is repository-specific. It is not required to match Canary routing content or Canary's machine-readable router.
- A change to shared checkpoint/handoff structure must follow the upgrade process in `GOVERNANCE_CONTRACT.json`; repository-specific routing changes do not by themselves require a shared contract version bump.

## Core startup context

Every agent starts with:

1. root `AGENTS.md`;
2. `docs/agents/REPOSITORY_MAP.md`;
3. `docs/agents/PROJECT_STATE.md`;
4. the active task record, when one exists;
5. the live PR for that task, when one exists;
6. the nearest nested `AGENTS.md`, when present.

Then classify the task and load only the matching context.

## Routing table

| Route | Trigger | Load / search |
|---|---|---|
| `agent-governance` | `AGENTS.md`, `docs/agents/**`, `tools/agents/**`, ownership or handoff | Read `GOVERNANCE_CONTRACT.json`, relevant governance/handoff records, `TASK_TEMPLATE.md`, `ACTIVE_WORK.md` and overlapping active tasks. |
| `architecture` | new module, durable boundary, major dependency, product architecture | Read `SYSTEM_ARCHITECTURE.md`, search `MODULE_CATALOG.md`, relevant ADRs and contracts. Create/update ADR when decision outlives one task. |
| `web-cms` | Blade/views/CMS/news/public pages | Read relevant module catalog section; search affected routes, controllers, views and tests. Check escaping, sanitization, authorization and CSRF boundaries. |
| `auth-identity` | login, password, sessions, MFA, verification, recovery | Read `SECURITY_ARCHITECTURE.md` and `AUTH_GAME_LOGIN_CONTRACT.md`, then relevant auth config/code/tests. Treat unresolved game-login compatibility as a blocker for global-security claims. |
| `accounts-characters` | account/player creation or management | Read `DATA_OWNERSHIP.md` and `CANARY_DATA_CONTRACT.md`; load affected models/services/tests only after required contract fields are proven. |
| `public-game-data` | highscores, characters, guilds, online/status | Read relevant `CANARY_DATA_CONTRACT.md` sections and query/read-model code. Prefer read-only boundaries. |
| `canary-integration` | shared DB, login-server, Canary schema/protocol contract | Read matching documents in `docs/contracts/**`; verify live Canary/login-server evidence before compatibility claims. |
| `database` | migration, transaction, locking, schema, query behavior | Read `DATA_OWNERSHIP.md`, matching migrations/models and concurrency tests. Require rollback thinking for destructive changes. |
| `admin-rbac` | admin panel, roles, policies, privileged actions | Read `SECURITY_ARCHITECTURE.md`, Admin module catalog section, authorization policies and audit requirements. Deny by default. |
| `api` | REST/API endpoints, external clients | Load routes, request validation, auth middleware, rate limits and API tests. Reuse module services rather than duplicating business logic. |
| `security` | vulnerability, secret, traversal, XSS, CSRF, SSRF, injection, abuse | Read `SECURITY_ARCHITECTURE.md` and affected surface/tests. Record threat assumptions explicitly and add regression tests where practical. |
| `testing` | test infrastructure, CI validation, E2E | Read `TEST_STRATEGY.md` and affected contracts/modules. Tie compatibility evidence to exact versions/SHAs where practical. |
| `payments` | payment provider, coins, premium currency, webhook, shop | Read ADR 0003 and security/data ownership sections. Payments are deferred unless the user explicitly starts that phase. Require a new payment ADR/threat model. |
| `ci-repair` | required GitHub check fails | Read the failing workflow/job/step and current task. Investigate root cause before rerun. |

Multiple routes may apply, but each must be justified by task scope or evidence.

## Authoritative architecture documents

Use targeted sections, not automatic full-document loading:

- `docs/architecture/SYSTEM_ARCHITECTURE.md` — system boundaries and target topology;
- `docs/architecture/MODULE_CATALOG.md` — responsibility and dependency ownership;
- `docs/architecture/SECURITY_ARCHITECTURE.md` — mandatory security invariants;
- `docs/architecture/DATA_OWNERSHIP.md` — persistent data ownership and shared write rules;
- `docs/architecture/TEST_STRATEGY.md` — validation layers and production E2E expectations;
- `docs/architecture/ROADMAP.md` — phase ordering and gates;
- `docs/architecture/adr/**` — durable decisions;
- `docs/contracts/**` — cross-component compatibility contracts.

## Search before read

Before creating a new abstraction or integration point, search:

1. active task records and open PRs for overlapping paths or intent;
2. `MODULE_CATALOG.md` for an existing owner;
3. repository source/tests for existing implementations;
4. relevant ADRs and contracts.

Do not recursively follow documentation links without evidence that they are relevant.

## Context expansion rule

Expand context only when:

- a search result points to another authoritative source;
- a required fact remains `UNKNOWN` after targeted search;
- authoritative sources conflict;
- validation or a safety gate explicitly requires more context.

## Working-set discipline

Keep the active working set limited to:

- current task goal and acceptance criteria;
- branch/head/PR state;
- affected paths and ownership;
- `PROVEN`, `DERIVED`, `UNKNOWN`, `CONFLICT` facts;
- relevant source excerpts;
- latest validation evidence;
- exactly one next concrete action.

Chat history is not authoritative project memory.
