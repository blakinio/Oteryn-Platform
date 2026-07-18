# Oteryn Platform Repository Map

Navigation map for autonomous agents. This is not an exhaustive inventory. Confirm current paths before editing.

| Area | Typical paths | Responsibility / cautions |
|---|---|---|
| Application core | `app/**` | Laravel application logic. Keep domain responsibilities explicit and avoid unrelated refactors. |
| HTTP / API | `routes/**`, `app/Http/**` | Public routes, middleware, controllers, request validation and API boundaries. Treat auth-sensitive endpoints as security-critical. |
| Authentication / identity | `app/**/Auth/**`, `app/Models/**`, auth configuration | Passwords, sessions, MFA, verification, recovery, authorization. Never invent security state or bypass framework protections. |
| Database | `database/migrations/**`, `database/seeders/**`, `database/factories/**` | Schema and data changes. Destructive production migrations require an explicit rollback plan and manual gate. |
| Canary integration | `app/**`, `config/**`, `docs/contracts/**` | Contracts with Canary/login-server/database schema. Document cross-repository assumptions and version-sensitive fields. |
| Public web / CMS | `resources/views/**`, `resources/js/**`, `resources/css/**` | Blade/frontend/CMS. Escape untrusted output and preserve CSRF protections. |
| Admin | admin-specific controllers/routes/policies/views | Privileged operations. Require explicit authorization policies and auditability. |
| Tests | `tests/**` | Unit, feature, integration and security regression tests. |
| Configuration | `config/**`, `.env.example` | Commit examples only. Never commit real credentials, tokens or production secrets. |
| CI | `.github/workflows/**` | Required checks and deployment validation. Do not weaken checks to obtain green CI. |
| Docs | `docs/**` | Architecture, contracts, ADRs, security decisions and operational handoffs. |
| Agent memory | `AGENTS.md`, `docs/agents/**` | Coordination, routing, task records and durable continuation state. |

## Discovery commands

```sh
find . -name AGENTS.md -print
find docs/agents/tasks/active -maxdepth 1 -type f -print
rg -n "auth|session|mfa|password|account|player|guild|canary|login-server" app routes config database tests docs
rg -n "Route::|middleware|Gate::|Policy|Hash::|DB::transaction" app routes tests
```

Use targeted discovery. Do not preload the whole repository when a narrow search can identify the relevant module or contract.
