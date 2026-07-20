# Oteryn Platform Production Readiness Checklist

## Status

Phase 7 operational checklist — repository baseline created 2026-07-20.

This checklist is an evidence gate. A checked repository control is not proof that the equivalent production infrastructure is deployed.

Do not paste secrets, credentials, private connection strings, private IP inventories or copied production `.env` files into this document or pull requests.

## Gate states

- `REPO-PROVEN` — deterministically verifiable from the repository and required CI.
- `ENV-EVIDENCE-REQUIRED` — must be proven using sanitized evidence from the actual deployed environment.
- `CROSS-REPO-BLOCKED` — depends on a separately authorized external repository programme.

## 1. Exact release identity

Before any production-ready claim:

- [ ] Record the exact Oteryn Platform commit SHA being evaluated. `ENV-EVIDENCE-REQUIRED`
- [ ] Record exact deployed Canary/login-server versions relevant to authentication/game-login behavior. `ENV-EVIDENCE-REQUIRED`
- [ ] Prove the evaluated commit passed required CI, including Composer advisory audit, Pint, PHPStan and the full test suite. `REPO-PROVEN`
- [ ] Confirm there is no newer unreviewed production-only code/config divergence outside the declared deployment mechanism. `ENV-EVIDENCE-REQUIRED`

## 2. Provider-independent application configuration

Run against the effective production configuration without printing secrets:

```text
php artisan production:verify-configuration
```

Required result: exit code `0`.

The verifier proves only these application invariants:

- `APP_ENV=production`;
- debug disabled;
- application encryption key configured;
- HTTPS, non-localhost/loopback application URL;
- Secure and HttpOnly session cookies;
- delivery-capable default mail transport;
- valid non-test sender address.

- [ ] `production:verify-configuration` passes against the effective production runtime. `ENV-EVIDENCE-REQUIRED`

Do not infer database, Redis, queue, logging-provider or Cloudflare correctness from this command.

## 3. Dependency and application security gates

- [x] Required CI runs `composer audit --no-interaction`. `REPO-PROVEN`
- [x] Current repository uses deny-by-default administrator authorization with exact permissions. `REPO-PROVEN`
- [x] Administrator web routes require Platform authentication and confirmed MFA. `REPO-PROVEN`
- [x] CSP/browser security headers have regression coverage. `REPO-PROVEN`
- [x] Application request correlation is server-generated and does not trust inbound request IDs. `REPO-PROVEN`
- [ ] Re-run required CI for the exact production candidate SHA. `ENV-EVIDENCE-REQUIRED`
- [ ] Review unresolved critical/high dependency or application findings and resolve or explicitly risk-accept them. `ENV-EVIDENCE-REQUIRED`

## 4. Edge, TLS and origin exposure

Required evidence is defined in `docs/operations/PRODUCTION_TOPOLOGY_EVIDENCE.md`.

- [ ] Prove production DNS/proxy mode and TLS termination. `ENV-EVIDENCE-REQUIRED`
- [ ] Prove effective WAF/rate-limit/Access policy where used. `ENV-EVIDENCE-REQUIRED`
- [ ] Prove whether direct-origin access is blocked or explicitly risk-accepted. `ENV-EVIDENCE-REQUIRED`
- [ ] Review ingress firewall/security-group/reverse-proxy rules. `ENV-EVIDENCE-REQUIRED`
- [ ] Decide HSTS only after the real TLS and hostname/subdomain policy is proven; record the decision and rationale. `ENV-EVIDENCE-REQUIRED`

The application intentionally does not hard-code HSTS before this evidence exists.

## 5. Platform database

- [ ] Record the production database engine/topology without credentials. `ENV-EVIDENCE-REQUIRED`
- [ ] Prove the database is not publicly exposed except for explicitly approved network paths. `ENV-EVIDENCE-REQUIRED`
- [ ] Prove credential injection/rotation ownership. `ENV-EVIDENCE-REQUIRED`
- [ ] Prove backup scope, schedule, retention, encryption/access policy and restore ownership. `ENV-EVIDENCE-REQUIRED`
- [ ] Complete and record an operational restore test with date, scope, result, recovery time and data-loss measurement. `ENV-EVIDENCE-REQUIRED`

A production-ready claim is blocked until the restore test exists.

## 6. Canary SQL privilege boundaries

The repository provides these verifiers:

```text
php artisan canary:verify-db-privileges
php artisan canary:verify-provisioning-db-privileges
php artisan canary:verify-character-create-db-privileges
```

- [ ] Run the generic Canary read-only verifier against the effective production credential. `ENV-EVIDENCE-REQUIRED`
- [ ] Run the provisioning verifier against the effective production provisioning credential before enabling provisioning writes. `ENV-EVIDENCE-REQUIRED`
- [ ] Run the character-create verifier against the effective production character-create credential before enabling character creation writes. `ENV-EVIDENCE-REQUIRED`
- [ ] Prove network paths and credential classes are separate as designed. `ENV-EVIDENCE-REQUIRED`

Failure of any effective-grant verifier is a fail-closed deployment blocker for that capability.

## 7. Canary runtime Redis

- [ ] Prove the production runtime Redis endpoint and network boundary without secrets. `ENV-EVIDENCE-REQUIRED`
- [ ] Prove the dedicated read-only ACL/user is provisioned. `ENV-EVIDENCE-REQUIRED`
- [ ] Prove monitoring/alerting for dependency failure and freshness behavior. `ENV-EVIDENCE-REQUIRED`

## 8. Sessions, cache and queue

Repository defaults are not production evidence.

- [ ] Record the effective production session backend and justify it against the number of web instances. `ENV-EVIDENCE-REQUIRED`
- [ ] Record the effective cache backend and whether shared cache is operationally required. `ENV-EVIDENCE-REQUIRED`
- [ ] Record the effective queue mode. Current repository behavior is synchronous; if asynchronous queues are introduced, prove worker supervision, retries and failed-job handling. `ENV-EVIDENCE-REQUIRED`

Do not add Redis or asynchronous infrastructure merely to satisfy this checklist; introduce only what the proven topology/use case requires.

## 9. Mail

- [ ] Prove real production mail transport/provider without exposing credentials. `ENV-EVIDENCE-REQUIRED`
- [ ] Prove sender-domain readiness as applicable to the chosen provider. `ENV-EVIDENCE-REQUIRED`
- [ ] Verify password recovery delivery end to end. `ENV-EVIDENCE-REQUIRED`
- [ ] Record bounce/delivery monitoring ownership. `ENV-EVIDENCE-REQUIRED`

## 10. Logging, monitoring and alerting

Repository-proven application primitives:

- server-generated `X-Request-ID`;
- bounded `http.request.completed` event context;
- optional JSON-to-stderr channel;
- Identity security-event audit;
- administrator audit.

Environment evidence still required:

- [ ] Prove the selected centralized log sink or record that no centralized sink exists. `ENV-EVIDENCE-REQUIRED`
- [ ] Prove effective structured-log format and request-ID preservation. `ENV-EVIDENCE-REQUIRED`
- [ ] Prove retention and access-control policy. `ENV-EVIDENCE-REQUIRED`
- [ ] Prove metrics/alerting/on-call destination for critical application/dependency failures. `ENV-EVIDENCE-REQUIRED`
- [ ] Confirm logs do not expose credentials/tokens in representative production-like events. `ENV-EVIDENCE-REQUIRED`

## 11. Deployment and rollback

- [ ] Document the actual release/deployment mechanism. `ENV-EVIDENCE-REQUIRED`
- [ ] Document when/how migrations are executed. `ENV-EVIDENCE-REQUIRED`
- [ ] Document rollback boundaries for application code and Platform-owned schema changes. `ENV-EVIDENCE-REQUIRED`
- [ ] Prove rollback or forward-recovery procedure in a safe non-production or controlled environment. `ENV-EVIDENCE-REQUIRED`
- [ ] Identify who can execute emergency rollback and how access is controlled. `ENV-EVIDENCE-REQUIRED`

No provider-specific rollback command is committed because the provider/deployment model is currently unproven.

## 12. Identity/admin critical flows

For the exact production candidate/environment:

- [ ] Web login/logout. `ENV-EVIDENCE-REQUIRED`
- [ ] Password reset delivery and completion. `ENV-EVIDENCE-REQUIRED`
- [ ] Password change and Platform session revocation. `ENV-EVIDENCE-REQUIRED`
- [ ] MFA enrollment/challenge/recovery. `ENV-EVIDENCE-REQUIRED`
- [ ] Administrator MFA plus allowed/denied RBAC paths. `ENV-EVIDENCE-REQUIRED`
- [ ] First-admin bootstrap procedure has either been completed securely or has an approved installation plan. `ENV-EVIDENCE-REQUIRED`

## 13. Account/character shared writes

If enabled in the target environment:

- [ ] Greenfield account provisioning succeeds under the verified operation-specific principal. `ENV-EVIDENCE-REQUIRED`
- [ ] Provisioning retry/recovery behavior is operationally understood. `ENV-EVIDENCE-REQUIRED`
- [ ] Character creation succeeds under the verified operation-specific principal. `ENV-EVIDENCE-REQUIRED`
- [ ] Character quota/name-conflict behavior is verified on the deployed schema/version. `ENV-EVIDENCE-REQUIRED`

Do not enable uncontracted character/account mutations.

## 14. Authoritative game login

- [ ] Platform-originated users can enter the authoritative game-login path using Platform credential authority. `CROSS-REPO-BLOCKED`
- [ ] Exact-account binding, assertion expiry/audience, replay resistance and revocation semantics are proven end to end. `CROSS-REPO-BLOCKED`
- [ ] Direct bypass paths are reviewed. `CROSS-REPO-BLOCKED`

Current state: this bridge is not implemented. This is a blocker for claiming end-to-end Platform-owned game authentication readiness.

## Production-ready decision

A production-ready claim requires:

1. all applicable repository gates passing on the exact candidate SHA;
2. all applicable `ENV-EVIDENCE-REQUIRED` items completed or explicitly risk-accepted by the owner;
3. operational backup restore successfully tested;
4. critical/high findings resolved or explicitly accepted;
5. the authoritative game-login requirement resolved if Platform-originated game login is part of the launch scope.

Until then, Phase 7 remains incomplete regardless of repository hardening progress.
