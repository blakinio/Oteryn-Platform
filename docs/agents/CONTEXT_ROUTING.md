# Oteryn Platform Agent Context Routing

## Goal

Load the smallest authoritative context required for the current task. Do not preload broad repository documentation when targeted search can identify the relevant records.

## Core startup context

Every agent reads only:

1. root `AGENTS.md`;
2. `docs/agents/REPOSITORY_MAP.md`;
3. the active task record, when one exists;
4. the live PR for that task, when one exists;
5. the nearest nested `AGENTS.md`, when present.

Then classify the task and load only the matching context.

## Routing table

| Route | Trigger | Load / search |
|---|---|---|
| `agent-governance` | `AGENTS.md`, `docs/agents/**`, agent tooling, ownership or handoff | Read only the relevant governance/handoff records and overlapping active tasks. |
| `web-cms` | Blade/views/CMS/news/public pages | Search affected routes, controllers, views and tests. Check escaping, authorization and CSRF boundaries. |
| `auth-identity` | login, password, sessions, MFA, email verification, recovery | Treat as security-critical. Load relevant auth config, middleware, policies, models, migrations and security tests. |
| `accounts-characters` | account/player creation or management | Load affected Canary schema contract and business invariants before writes. |
| `canary-integration` | shared DB, login-server, Canary schema/protocol contract | Read matching documents in `docs/contracts/**`; verify the live Canary schema/source before making compatibility claims. |
| `database` | migration, transaction, locking, schema, query behavior | Load matching migrations/models and concurrency tests. Require rollback thinking for destructive changes. |
| `admin-rbac` | admin panel, roles, policies, privileged actions | Load authorization policies, middleware and audit requirements. Deny by default. |
| `api` | REST/API endpoints, external clients | Load routes, request validation, auth middleware, rate limits and API tests. |
| `security` | vulnerability, secret, traversal, XSS, CSRF, SSRF, injection, abuse | Load only affected surfaces plus security policy/tests. Record threat assumptions explicitly. |
| `payments` | payment provider, coins, premium currency, webhook | Future module. Require transaction ledger, idempotency, signature verification and dedicated security review. |
| `ci-repair` | required GitHub check fails | Read the failing workflow/job/step and current task. Investigate root cause before rerun. |

Multiple routes may apply, but each must be justified by task scope or evidence.

## Search before read

Before creating a new abstraction or integration point, search:

1. active task records and open PRs for overlapping paths or intent;
2. repository source/tests for existing implementations;
3. relevant architecture/contracts documentation.

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
