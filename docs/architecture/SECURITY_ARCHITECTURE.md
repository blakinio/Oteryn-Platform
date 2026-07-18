# Oteryn Platform Security Architecture

## Purpose

This document defines mandatory security invariants for Oteryn Platform. It is a baseline for implementation and review, not proof that controls already exist.

## Security principles

1. **Defense in depth.** Cloudflare, reverse proxy, Laravel controls, database permissions and network isolation complement each other.
2. **Deny by default.** Missing or ambiguous authorization must fail closed.
3. **One authoritative identity policy.** Do not implement incompatible authentication policy independently in multiple components.
4. **Least privilege.** Users, administrators, services and database credentials receive only required capabilities.
5. **Explicit trust boundaries.** Browser input, API input and data from external/shared systems are untrusted until validated.
6. **No security by obscurity.** Hidden URLs or origin IP secrecy are not authorization controls.
7. **Security changes are testable.** Fixed vulnerabilities should gain regression tests where practical.

## Threat surfaces

Security-critical surfaces include:

- login/logout;
- password hashing and migration;
- session creation/revocation;
- MFA and recovery;
- password reset and email verification;
- administrator/RBAC operations;
- account and character mutations;
- file/media uploads;
- APIs and webhooks;
- shared database integration;
- future payments/coins/shop;
- infrastructure/origin exposure.

## Authentication

### Passwords

- Use Laravel/PHP framework-supported modern password hashing.
- Preferred target is Argon2id when operational compatibility is proven.
- Never store plaintext or reversibly encrypted passwords.
- Never log passwords.
- Legacy/hash migration must be designed against the actual login-server/Canary contract before implementation.

### Sessions

- Regenerate session identifiers on authentication/privilege transitions as appropriate.
- Revoke or rotate sessions after password reset/change, MFA reset or other security-sensitive state changes according to explicit policy.
- Session cookies must use appropriate Secure, HttpOnly and SameSite behavior for production deployment.
- Do not expose session IDs in URLs or logs.

### MFA

Target requirement:

- mandatory MFA for administrator accounts before production readiness;
- optional or product-policy-driven MFA for normal users;
- secure enrollment confirmation;
- MFA secret protection at rest using framework/application encryption facilities where appropriate;
- recovery/reset procedures treated as privileged security actions;
- audit events for enrollment/reset/disable operations.

### Recovery and verification

- reset/verification tokens must be cryptographically strong, time-limited and single-purpose;
- avoid account enumeration through materially different public responses;
- rate-limit recovery attempts;
- successful reset should apply the defined session revocation policy.

## Authorization and RBAC

Authorization is enforced server-side using policies/gates/application rules.

Minimum direction:

- separate content administration from account/security administration;
- avoid a single unrestricted admin flag as the only authorization model;
- privileged account actions require dedicated permissions;
- no web feature may provide arbitrary PHP/code execution or unrestricted plugin upload;
- administrative actions are audited.

Potential roles/permissions are not final until an RBAC ADR/task defines them.

## Browser security

- CSRF protection remains enabled for browser state-changing requests.
- Escape untrusted output by default.
- Rich HTML content requires explicit sanitization.
- Use secure response headers appropriate to the deployed frontend, including a deliberate Content Security Policy when implementation is ready.
- Never rely only on hidden form fields for authorization.

## Input and database security

- Validate input using framework request validation/domain rules.
- Use ORM/query builder/parameterized SQL.
- Never concatenate untrusted SQL.
- Enforce database constraints for durable invariants where appropriate.
- Use transactions for multi-step sensitive mutations.
- Use row locks/atomic updates where race conditions could violate balances, uniqueness or lifecycle rules.

## Admin protection

Target production posture:

```text
Administrator
    |
    v
Cloudflare Access (preferred additional gate)
    |
    v
Oteryn Platform login + mandatory MFA
    |
    v
Server-side RBAC policy
    |
    v
Audited privileged action
```

Cloudflare Access is additional protection. The application must still enforce authentication, MFA and authorization.

## Edge and origin protection

Recommended production direction:

- proxy public web/API traffic through Cloudflare;
- WAF/rate limiting/Turnstile where useful;
- restrict origin firewall to approved ingress paths when practical;
- database is not publicly exposed;
- internal services use private/explicit network rules;
- Canary game TCP protection requires a separate decision because standard HTTP proxying does not automatically protect arbitrary game ports.

## Rate limiting and abuse prevention

Apply application-level limits at minimum to:

- login;
- registration;
- password reset;
- email verification resend;
- MFA verification/recovery;
- public search endpoints if abused;
- expensive API endpoints.

Cloudflare limits may supplement, not replace, application limits.

## Audit and logging

Audit security-relevant events such as:

- admin privilege changes;
- sensitive account state changes;
- MFA enrollment/reset/disable;
- password reset completion;
- session revocation actions;
- future payment/ledger administrative actions.

Never record:

- raw passwords;
- session tokens;
- reset tokens;
- MFA secrets;
- payment credentials;
- unnecessary personal data.

## Secrets

- `.env` is never committed.
- `.env.example` contains placeholders only.
- production secrets are injected by deployment/secret-management tooling.
- credentials should be rotated when exposure is suspected.
- different components should use different least-privilege database/service credentials where practical.

## Shared Canary/login-server security

No security guarantee may rely on a rule enforced only by Oteryn Platform if another exposed path can bypass it.

Before production, prove end-to-end behavior for:

- credential validation;
- account disabled/banned state;
- email verification policy if required for game login;
- MFA policy if intended to gate game login;
- session/token creation;
- session/token replay behavior;
- password-change/reset revocation;
- direct Canary/login-server bypass paths.

Any unresolved bypass is a blocker for claiming the policy is enforced globally.

## File uploads

No upload functionality should be added casually.

When introduced it requires:

- explicit allowed types;
- server-side MIME/content validation;
- size limits;
- generated storage names;
- storage outside executable application paths;
- authorization;
- image processing safety where applicable;
- malware scanning decision based on threat/use case.

## Future payments

Payments are out of initial scope. Before introduction require a dedicated threat model covering:

- webhook authenticity;
- idempotency;
- replay protection;
- ledger integrity;
- concurrent purchase handling;
- refunds/chargebacks;
- reconciliation;
- administrator abuse controls;
- auditability.

The platform must not store raw card data unless a future explicitly approved architecture and compliance scope requires it; prefer hosted/provider-controlled payment flows.

## Production readiness security gate

A production release must not be called security-ready until at least:

- auth/session contract with game login is proven;
- administrator MFA is enforced;
- critical routes have authorization tests;
- password reset/revocation behavior is tested;
- origin/database exposure is reviewed;
- secrets handling is verified;
- security headers and TLS deployment are reviewed;
- critical dependencies are vulnerability-scanned;
- backups/restore procedure exists;
- security-sensitive audit events are available;
- known critical/high security findings are resolved or explicitly accepted by the owner.
