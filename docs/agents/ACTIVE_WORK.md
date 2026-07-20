# Oteryn Platform Active Work

Convenience index only. The individual active task record, live PR and Git state are authoritative.

## Active tasks

- `OTERYN-20260720-phase7-production-config-guardrails` — PR #49 — `task/OTERYN-20260720-phase7-production-config-guardrails`

## Current project phase

**Phase 6 — CMS, Admin, RBAC and Audit: COMPLETE**

**Phase 7 — Production hardening and operations: IN PROGRESS**

## Completed Phase 7 discovery baseline

PR #48 merged as `676a77590e3ec93bcad0247b3065d203ac209c40` and established `docs/operations/PRODUCTION_TOPOLOGY_EVIDENCE.md`.

The repository proves supported configuration surfaces and a logical target architecture, but actual Cloudflare/origin/database/Redis/session/cache/queue/mail/logging/backup/deployment state remains `UNKNOWN` until non-secret deployment evidence proves it.

## Current Phase 7 slice

PR #49 adds provider-independent production configuration guardrails without choosing a hosting provider.

The verifier checks only invariant production safety requirements:

- `APP_ENV=production`;
- debug disabled;
- application encryption key configured;
- HTTPS, non-localhost/loopback `APP_URL`;
- Secure and HttpOnly session cookies;
- delivery-capable default mail transport;
- valid non-test sender address.

The verifier intentionally does **not** require MySQL, Redis sessions/cache, asynchronous queues, a particular logging sink or Cloudflare policy because the topology evidence does not prove those are universal requirements.

Command:

`php artisan production:verify-configuration`

It returns non-zero on any violation and does not print secret values.

## Phase 7 dependency order

1. provider-independent runtime production-safety guardrails — **IN PROGRESS**;
2. actual edge/origin/database exposure review when deployment evidence exists;
3. backup/restore contract and operational test;
4. logging/monitoring/correlation;
5. dependency/security scanning and security headers/CSP;
6. queue/cache/mail setup only where deployment/use-case evidence proves need;
7. critical E2E matrix against exact deployed versions.

## Production enablement note

Repository Phase 7 progress is not proof of production deployment.

Cloudflare/WAF/Access, private origin/database paths, backups, centralized monitoring and actual production endpoints remain `UNKNOWN` until external non-secret evidence proves them.

## Remaining cross-repository dependency

The authoritative Platform game-login bridge remains separate from current Phase 7 work and requires explicit authorization before external repository writes.

## Recommended next work

Finish PR #49 with exact-head CI. After merge, continue with repository-owned dependency/security scanning and security-header/CSP work if actual deployment evidence is still unavailable; edge/origin/database exposure review remains evidence-blocked.

## Recently completed

- `OTERYN-20260720-phase7-production-topology-discovery` — PR #48 / `676a77590e3ec93bcad0247b3065d203ac209c40`.
- `OTERYN-20260720-phase6-closure` — PR #46 / `f25abd8799718ac99acce050ac55018d04fff2de`.

## Coordination rule

Before starting substantial work, search `docs/agents/tasks/active/**` and open PRs for overlapping paths or intent. Do not claim paths already owned by another active task without explicit coordination.
