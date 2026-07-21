# Functional Acceptance Matrix

## Scope and evidence rules

This matrix inventories the functional surface delivered on `main` at baseline SHA `221a13f6d7fba28ba765d67594a5cce4bf9523c4` and maps it to the strongest directly available evidence.

Repository checkpoint evidence uses `PROVEN`, `DERIVED`, `UNKNOWN` and `CONFLICT`. Environment evidence uses `STAGING_PROVEN`, `PRODUCTION_PROVEN` and `UNKNOWN`.

For the coverage columns below:

- `PROVEN` means a directly inspected test or executable validation covers the stated behavior;
- `DERIVED` means the behavior follows from inspected implementation plus adjacent tests, but no focused test was found for that exact assertion;
- `UNKNOWN` means no sufficient direct evidence was found;
- `—` means that test layer is not applicable to the row.

`Live staging E2E` is intentionally stricter than the existing Phase 7 composed `STAGING_PROVEN` classification. The Phase 7 workflow runs the full regression suite with testing-only SQLite, array mail, array sessions and array cache, then separately live-smokes the deployed release for health, headers, cookies, request correlation, logging and representative error behavior. Therefore a feature can have strong feature/integration evidence and participate in the Phase 7 `STAGING_PROVEN` composition while still being `UNKNOWN` for a real HTTP end-to-end user/admin flow through production-like dependencies.

## Current acceptance result

**Functional inventory: PROVEN for the currently delivered `main` surface.**

**Full functional acceptance: UNKNOWN / gaps found.**

The blocking acceptance gap is not the core business-contract test suite. It is the absence of live production-like HTTP E2E execution for the critical Identity/MFA, provisioning/binding, character, administrator/RBAC/CMS/audit and cross-surface flows. Additional focused gaps are listed after the matrix.

Final production state remains `UNKNOWN`; no row in this document is `PRODUCTION_PROVEN`.

## Identity and authentication

| Feature | Source/module | Expected behavior | Unit | Feature/HTTP | Integration | Live staging E2E | Negative/failure | Authorization | Evidence | Production smoke |
|---|---|---|---|---|---|---|---|---|---|---|
| Registration | `Identity`, registration routes/actions | Canonical email, Argon2id password, CSRF-bearing form, Platform Identity creation and provisioning intent | `PROVEN` | `PROVEN` | `PROVEN` through provisioning adapter slice | `UNKNOWN` | `PROVEN` | `—` guest-only | `CanonicalEmailTest`; `RegistrationTest`; Phase 7 full regression suite | yes |
| Registration client-controlled Canary identifiers | `Identity` + `Accounts` | Ignore/reject client authority over `account_id` and provisioning identity | `—` | `PROVEN` | `PROVEN` | `UNKNOWN` | `PROVEN` | `PROVEN` server-owned binding | `RegistrationTest::test_registration_normalizes_email_hashes_password_provisions_binding_and_records_security_events` | no |
| Login | `Identity` session controller/session manager | Canonical-email login, regenerated authenticated session, no remember-me shortcut | `PROVEN` | `PROVEN` | `—` | `UNKNOWN` | `PROVEN` | `PROVEN` guest-only entry | `WebSessionTest`; `IdentityWebSessionManagerTest` | yes |
| Invalid login / enumeration resistance | `Identity` | Known and unknown identities receive the same public invalid-credential error; disabled identity denied | `—` | `PROVEN` | `—` | `UNKNOWN` | `PROVEN` | `PROVEN` | `WebSessionTest` | no |
| Logout | `Identity` | Invalidate current session, rotate CSRF state and record security event | `PROVEN` | `PROVEN` | `—` | `UNKNOWN` | `PROVEN` | `PROVEN` authenticated only | `WebSessionTest::test_logout_invalidates_current_session_and_records_audit_event` | yes |
| Password recovery request | `Identity` recovery | Generic response, hashed reset token, delivery-capable mail requirement, rate limits | `—` | `PROVEN` | SMTP protocol path `STAGING_PROVEN` separately | `UNKNOWN` | `PROVEN` | `—` guest flow | `PasswordRecoveryTest`; Phase 7 SMTP validation | yes |
| Password reset | `Identity` credentials | Expiring single-use token, password mutation, web-session revocation, replay rejection | `—` | `PROVEN` | `—` | `UNKNOWN` | `PROVEN` | `—` token-authorized flow | `PasswordRecoveryTest` | yes |
| Authenticated password change | `Identity` credentials | Require current password, reject weak/reused password, revoke all Platform web sessions and log out current browser | `—` | `PROVEN` | `—` | `UNKNOWN` | `PROVEN` | `PROVEN` auth required | `PasswordChangeTest` | yes |
| Web-session generation invalidation | `Identity` sessions | Stale or missing generation fails closed on next request | `PROVEN` | `PROVEN` | `—` | `UNKNOWN` | `PROVEN` | `PROVEN` | `WebSessionTest` | yes |
| Unauthenticated access denial | web auth middleware | Protected account/admin routes redirect or deny unauthenticated requests | `—` | `PROVEN` for password change, character creation and admin | `—` | `UNKNOWN` | `PROVEN` | `PROVEN` | `PasswordChangeTest`; `CharacterCreationTest`; `AdminAuthorizationTest` | yes |
| Authenticated access behavior | web auth + current-session middleware | Current authenticated session reaches allowed account surfaces; stale/disabled sessions fail closed | `—` | `PROVEN` | `—` | `UNKNOWN` | `PROVEN` | `PROVEN` | `WebSessionTest`; `CharacterCreationTest` | yes |

## MFA

| Feature | Source/module | Expected behavior | Unit | Feature/HTTP | Integration | Live staging E2E | Negative/failure | Authorization | Evidence | Production smoke |
|---|---|---|---|---|---|---|---|---|---|---|
| MFA enrollment | `Identity/Mfa` | Authenticated identity starts enrollment without becoming confirmed | `PROVEN` | `PROVEN` | `—` | `UNKNOWN` | `PROVEN` | `PROVEN` auth required | `MfaStateFoundationTest`; `MfaWebFlowTest` | yes |
| MFA confirmation | `Identity/Mfa` | Current password + fresh TOTP confirms MFA, stores replay state, generates hashed recovery codes | `PROVEN` | `PROVEN` | `—` | `UNKNOWN` | `PROVEN` | `PROVEN` | `MfaWebFlowTest` | yes |
| MFA login challenge | `Identity/Mfa` | Confirmed-MFA identity remains guest until a valid second factor is consumed | `PROVEN` | `PROVEN` | `—` | `UNKNOWN` | `PROVEN` | `PROVEN` pending-login state | `MfaWebFlowTest` | yes |
| Invalid TOTP | `Identity/Mfa` | Invalid code rejected without authentication | `PROVEN` | `PROVEN` | `—` | `UNKNOWN` | `PROVEN` | `PROVEN` | `MfaWebFlowTest` challenge/rate-limit coverage | no |
| Replayed TOTP | `Identity/Mfa` | Previously consumed timestep rejected across login attempts | `PROVEN` | `PROVEN` | `—` | `UNKNOWN` | `PROVEN` | `PROVEN` | `MfaWebFlowTest::test_same_totp_timestep_cannot_be_replayed_across_login_attempts` | no |
| Recovery-code login | `Identity/Mfa` | Valid recovery code completes challenge | `PROVEN` | `PROVEN` | `—` | `UNKNOWN` | `PROVEN` | `PROVEN` | `MfaWebFlowTest` | yes |
| Recovery-code single use | `Identity/Mfa` | Consumed recovery code is removed and replay is rejected | `PROVEN` | `PROVEN` | `—` | `UNKNOWN` | `PROVEN` | `PROVEN` | `MfaWebFlowTest::test_recovery_code_is_consumed_once_and_cannot_be_reused` | no |
| MFA disable | `Identity/Mfa` | Require current password and valid factor; clear MFA state, revoke Platform web sessions and log out | `PROVEN` | `PROVEN` | `—` | `UNKNOWN` | `PROVEN` | `PROVEN` auth required | `MfaWebFlowTest` | yes |
| MFA security-action session revocation | `Identity/Mfa`, sessions | Enrollment confirmation/disable revokes generations according to policy | `PROVEN` | `PROVEN` | `—` | `UNKNOWN` | `PROVEN` | `PROVEN` | `MfaWebFlowTest` | yes |
| `mfa.confirmed` privileged gate | `EnsureConfirmedMfa` | Privileged routes deny identities without confirmed MFA even when role grants permission | `—` | `PROVEN` | `—` | `UNKNOWN` | `PROVEN` | `PROVEN` | `AdminAuthorizationTest`; `AdminCmsManagementTest`; `AdminAuditTest` | yes |

## Account provisioning and binding

| Feature | Source/module | Expected behavior | Unit | Feature/HTTP | Integration | Live staging E2E | Negative/failure | Authorization | Evidence | Production smoke |
|---|---|---|---|---|---|---|---|---|---|---|
| Platform-originated Canary account provisioning | `Accounts`, `CanaryIntegration` | Registration creates server-owned provisioning intent and dedicated adapter creates Canary account | `PROVEN` privilege verifier | `PROVEN` | `PROVEN` real MariaDB | `UNKNOWN` | `PROVEN` | `PROVEN` no client-selected account | `RegistrationTest`; `CanaryProvisioningMariaDbIntegrationTest`; Phase 7 grant verifier | yes |
| Identity -> Canary binding completion | `IdentityCanaryAccount` | Successful provisioning finalizes immutable ready binding to exact Canary account | `—` | `PROVEN` | `PROVEN` | `UNKNOWN` | `PROVEN` | `PROVEN` server-owned | `RegistrationTest`; `ProvisionCanaryAccountTest` | yes |
| 1:1 uniqueness | binding migration/action | One Platform Identity has one binding and one Canary account cannot bind to two identities | `—` | `PROVEN` | `PROVEN` DB constraint path | `UNKNOWN` | `PROVEN` | `PROVEN` | `ProvisionCanaryAccountTest::test_database_constraint_prevents_two_identities_from_binding_same_canary_account` | no |
| Provisioning retry/idempotency | provisioning saga | Ready binding does not re-provision; retry reuses durable pending intent | `—` | `PROVEN` | `PROVEN` | `UNKNOWN` | `PROVEN` | `PROVEN` | `ProvisionCanaryAccountTest`; MariaDB forward-recovery test | no |
| Duplicate provisioning attempt | provisioning adapter | Same durable provisioning marker recovers existing account rather than duplicating it | `—` | `PROVEN` | `PROVEN` | `UNKNOWN` | `PROVEN` | `PROVEN` | `CanaryProvisioningMariaDbIntegrationTest` | no |
| Partial-failure recovery | provisioning saga | Canary commit with missing Platform finalization is forward-recovered without duplicate | `—` | `PROVEN` | `PROVEN` real MariaDB | `UNKNOWN` | `PROVEN` | `PROVEN` | `CanaryProvisioningMariaDbIntegrationTest::test_platform_saga_finalizes_a_previously_committed_canary_account_without_creating_a_duplicate` | no |
| Dependency unavailable | provisioning saga | Registration survives with pending retryable intent; retry command can process pending records | `—` | `PROVEN` | `DERIVED` | `UNKNOWN` | `PROVEN` | `PROVEN` | `RegistrationTest`; `ProvisionCanaryAccountTest`; `canary:provision-pending-accounts` command | no |
| Provisioning conflict | provisioning saga | Marker/binding conflict fails closed and records conflict state/audit | `—` | `PROVEN` | `PROVEN` | `UNKNOWN` | `PROVEN` | `PROVEN` | `ProvisionCanaryAccountTest`; MariaDB integration | no |
| Dedicated least-privilege provisioning DB boundary | `canary_provisioning` | Only approved accounts INSERT/SELECT columns; prohibited reads and cross-surface writes denied | `PROVEN` | `—` | `PROVEN` real MariaDB | `STAGING_PROVEN` boundary validation | `PROVEN` | `PROVEN` DB enforced | privilege verifier; MariaDB integration; Phase 7 workflow | yes |

## Character management

Character deletion and rename are not delivered and are intentionally excluded from this matrix as functional flows.

| Feature | Source/module | Expected behavior | Unit | Feature/HTTP | Integration | Live staging E2E | Negative/failure | Authorization | Evidence | Production smoke |
|---|---|---|---|---|---|---|---|---|---|---|
| Character creation | `Characters`, `CanaryIntegration` | Authenticated identity with ready binding creates approved starter row through dedicated principal | `PROVEN` policy/verifier | `PROVEN` | `PROVEN` real MariaDB | `UNKNOWN` | `PROVEN` | `PROVEN` | `CharacterCreationTest`; `CanaryCharacterCreateMariaDbIntegrationTest` | yes |
| Canonical name normalization | `CharacterNamePolicy` | Normalize whitespace/casing to canonical product name before gateway call | `PROVEN` | `PROVEN` | `PROVEN` | `UNKNOWN` | `PROVEN` | `PROVEN` | `CharacterNamePolicyTest`; `CharacterCreationTest` | no |
| Reserved-name rejection | `CharacterNamePolicy` | Reserved Oteryn names fail before Canary gateway invocation | `PROVEN` | `PROVEN` | `—` | `UNKNOWN` | `PROVEN` | `PROVEN` | `CharacterCreationTest` | no |
| Duplicate/global-name handling | character creator | Existing same-account character is idempotently recovered; another account racing same name gets conflict | `—` | `PROVEN` | `PROVEN` real MariaDB concurrency | `UNKNOWN` | `PROVEN` | `PROVEN` | `CharacterCreationTest`; MariaDB race test | no |
| Character quota | character creator | Maximum ten active characters; deleted row excluded from quota but retains global name reservation | `—` | `DERIVED` bounded error path | `PROVEN` real MariaDB | `UNKNOWN` | `PROVEN` | `PROVEN` | `CanaryCharacterCreateMariaDbIntegrationTest` | no |
| Account-binding authorization | character controller/action | Use only authenticated identity's ready binding; pending binding fails closed; client cannot supply foreign `account_id` | `—` | `PROVEN` | `PROVEN` | `UNKNOWN` | `PROVEN` | `PROVEN` | `CharacterCreationTest` | yes |
| Same-account concurrency | character creator | Concurrent last-slot creates produce exactly one new character | `—` | `—` | `PROVEN` real MariaDB + `pcntl` | `UNKNOWN` | `PROVEN` | `PROVEN` | `CanaryCharacterCreateMariaDbIntegrationTest` | no |
| Cross-account same-name concurrency | character creator | Concurrent identical global name commits exactly once | `—` | `—` | `PROVEN` real MariaDB + `pcntl` | `UNKNOWN` | `PROVEN` | `PROVEN` | `CanaryCharacterCreateMariaDbIntegrationTest` | no |
| Idempotent retry/ambiguous-commit recovery | character creator | Previously committed same-account character is recovered without UPDATE privilege | `—` | `PROVEN` | `PROVEN` | `UNKNOWN` | `PROVEN` | `PROVEN` | `CharacterCreationTest`; MariaDB forward-recovery test | no |
| Dedicated `canary_character_create` privileges | character DB boundary | Approved account/player SELECT plus exact player INSERT only | `PROVEN` | `—` | `PROVEN` real MariaDB | `STAGING_PROVEN` boundary validation | `PROVEN` | `PROVEN` DB enforced | privilege verifier; MariaDB integration; Phase 7 workflow | yes |
| Uncontracted character/shared writes denied | DB privilege boundary | No account update, player update/delete or unrelated table insert | `PROVEN` | `—` | `PROVEN` | `STAGING_PROVEN` boundary validation | `PROVEN` | `PROVEN` DB enforced | MariaDB integration; Phase 7 prohibited cross-surface write checks | no |

## Public game data and public content

| Feature | Source/module | Expected behavior | Unit | Feature/HTTP | Integration | Live staging E2E | Negative/failure | Authorization | Evidence | Production smoke |
|---|---|---|---|---|---|---|---|---|---|---|
| Home/public shell | public Blade shell | Home renders shared navigation and character search | `—` | `PROVEN` | `—` | `STAGING_PROVEN` for live `/` reachability only | `PROVEN` | public | `PublicSiteShellTest`; Phase 7 live home smoke | yes |
| Character search exact-name redirect | `PublicGameDataController` | Non-empty exact name redirects to canonical character profile route | `—` | `PROVEN` | `—` | `UNKNOWN` | `PROVEN` missing-name validation | public | `PublicSiteShellTest` | no |
| Character page | `PublicGameData` | Show approved active-character fields only; hide account id; deleted character 404 | `—` | `PROVEN` | SQLite read-only test boundary | `UNKNOWN` | `PROVEN` | public | `PublicGameDataTest` | yes |
| Highscores | `PublicGameData` | Active characters only, deterministic ranking and bounded pagination | `—` | `PROVEN` | SQLite read-only test boundary | `UNKNOWN` | `PROVEN` | public | `PublicGameDataTest` | yes |
| Guild page | `PublicGameData` | Approved guild/member fields, deleted members excluded, escaped MOTD, bounded query count | `—` | `PROVEN` | SQLite read-only test boundary | `UNKNOWN` | `PROVEN` | public | `PublicGameDataTest` | no |
| Online list | `PublicGameData` | Fresh `ONLINE` cluster leases only, deleted/offline/expired entries excluded, public allowlist only | `—` | `PROVEN` | SQLite read-only test boundary | `UNKNOWN` | `PROVEN` including Canary query failure -> 503 | public | `PublicGameDataTest` | yes |
| Servers static metadata | `PublicGameData` | Enabled configured channels only; maintenance shown; disabled channels hidden | `—` | `PROVEN` | SQLite read-only test boundary | `UNKNOWN` | `PROVEN` | public | `PublicGameDataTest` | yes |
| Runtime server state | `CanaryRuntimeRedisReader` | Fresh deterministic Redis hash supplies explicit state/player count; full derived only under approved rule | `PROVEN` | `PROVEN` | Real Redis ACL path `STAGING_PROVEN` | `UNKNOWN` for live `/servers` | `PROVEN` | public read-only | `ServerRuntimeAvailabilityTest`; runtime unit tests; Phase 7 Redis validation | yes |
| Missing/expired runtime state | runtime read service | Render `Unknown`, never fabricate offline/count | `PROVEN` | `PROVEN` | `STAGING_PROVEN` real Redis missing path | `UNKNOWN` | `PROVEN` | public read-only | `ServerRuntimeAvailabilityTest`; Phase 7 Redis validation | no |
| Malformed runtime data | runtime reader/service | Fail closed and discard runtime snapshot | `PROVEN` | `PROVEN` adjacent failure semantics | `STAGING_PROVEN` real Redis malformed path | `UNKNOWN` | `PROVEN` | public read-only | runtime tests; Phase 7 Redis validation | no |
| Redis unavailable | runtime service | Keep static channel metadata, mark runtime unavailable; do not fabricate healthy state | `PROVEN` | `PROVEN` | `STAGING_PROVEN` unavailable endpoint | `UNKNOWN` | `PROVEN` | public read-only | `ServerRuntimeAvailabilityTest`; Phase 7 Redis validation | no |
| Public news list/detail | `CMS` public query | Published-only, future/drafts hidden, deterministic ordering, pagination and escaped output | `—` | `PROVEN` | Platform DB feature coverage | `UNKNOWN` | `PROVEN` | public | `PublicNewsTest` | yes |
| Managed public pages | `CMS` public page query | Published-only detail; drafts/future hidden; plain text escaped | `—` | `PROVEN` | Platform DB feature coverage | `UNKNOWN` | `PROVEN` | public | `AdminCmsManagementTest` | yes |
| Generic Canary read-only DB boundary | `canary` connection | SELECT only on approved table allowlist; writes and excessive privileges fail closed | `PROVEN` verifier | `PROVEN` public query tests | `PROVEN` real MariaDB grants | `STAGING_PROVEN` boundary validation | `PROVEN` | DB enforced | read-only privilege verifier; Phase 7 workflow | yes |

## Administrator and RBAC

| Feature | Source/module | Expected behavior | Unit | Feature/HTTP | Integration | Live staging E2E | Negative/failure | Authorization | Evidence | Production smoke |
|---|---|---|---|---|---|---|---|---|---|---|
| First-admin bootstrap requires existing Identity | `admin:bootstrap`, `AdminRoleManager` | Unknown target Identity fails; no implicit Identity creation | `—` | `DERIVED` command implementation | Platform DB transaction | `UNKNOWN` | `DERIVED` | privileged local operator command | `routes/console.php`; `AdminRoleManager` | yes |
| First-admin bootstrap requires confirmed MFA | `AdminRoleManager` | Unconfirmed Identity cannot become first administrator | `—` | `PROVEN` command test | Platform DB transaction | `UNKNOWN` | `PROVEN` | bootstrap invariant | `AdminRoleManagementTest` | yes |
| Bootstrap one-time closure | `AdminRoleManager` | Any existing admin-role assignment closes bootstrap permanently | `—` | `PROVEN` | Platform DB lock/transaction | `UNKNOWN` | `PROVEN` | bootstrap invariant | `AdminRoleManagementTest`; `AdminRoleManager` | yes |
| Deny-by-default admin authorization | `AdminAuthorization`, middleware | No role/permission => deny | `—` | `PROVEN` | `—` | `UNKNOWN` | `PROVEN` | `PROVEN` | `AdminAuthorizationTest` | yes |
| Explicit permission checks | admin middleware/routes | Every privileged web route requires exact named permission in addition to auth + MFA | `—` | `PROVEN` representative permissions | `—` | `UNKNOWN` | `PROVEN` | `PROVEN` | `routes/web.php`; admin feature tests | yes |
| Unknown permission fail-closed | admin middleware | Unknown permission string returns forbidden | `—` | `PROVEN` | `—` | `UNKNOWN` | `PROVEN` | `PROVEN` | `AdminAuthorizationTest::test_unknown_permission_fails_closed` | no |
| No wildcard unrestricted bypass | RBAC model | Access derives from explicit role-permission mapping, not wildcard admin bypass | `DERIVED` | `PROVEN` through explicit allow/deny cases | `—` | `UNKNOWN` | `PROVEN` | `PROVEN` | `AdminAuthorizationTest`; route middleware composition | no |
| Role assignment | `AdminRoleManager` | Authorized security administrator assigns known role and audit is written | `—` | `PROVEN` | Platform DB transaction | `UNKNOWN` | `PROVEN` missing permission | `PROVEN` | `AdminRoleManagementTest` | yes |
| Role removal | `AdminRoleManager` | Authorized removal deletes assignment and audits | `—` | `PROVEN` service/feature composition | Platform DB transaction | `UNKNOWN` | `PROVEN` | `PROVEN` | `AdminRoleManagementTest` | yes |
| Last-platform-admin protection | `AdminRoleManager` | Final `platform_admin` assignment cannot be removed | `—` | `PROVEN` | Platform DB locking | `UNKNOWN` | `PROVEN` | `PROVEN` | `AdminRoleManagementTest` | yes |
| Unauthorized admin access | admin routes | Guest, no-MFA or missing-permission identities cannot access/mutate privileged surfaces | `—` | `PROVEN` | `—` | `UNKNOWN` | `PROVEN` | `PROVEN` | `AdminAuthorizationTest`; CMS/audit tests | yes |

## CMS and managed content

| Feature | Source/module | Expected behavior | Unit | Feature/HTTP | Integration | Live staging E2E | Negative/failure | Authorization | Evidence | Production smoke |
|---|---|---|---|---|---|---|---|---|---|---|
| News create | `SaveNewsPost`, admin news controller | Authorized content editor creates draft; audit event written | `—` | `PROVEN` | Platform DB transaction | `UNKNOWN` | `PROVEN` unauthorized mutation | `PROVEN` | `AdminCmsManagementTest` | yes |
| News edit/publish | admin news update | Authorized editor updates draft to published content; public detail becomes visible; audit written | `—` | `PROVEN` | Platform DB transaction | `UNKNOWN` | `PROVEN` published-only public read | `PROVEN` | `AdminCmsManagementTest` | yes |
| News unpublish | admin news update | Setting `published_at` to null makes content non-public | `—` | `DERIVED` implementation supports nullable `published_at` | Platform DB transaction | `UNKNOWN` | `UNKNOWN` focused regression | `PROVEN` route permission | `SaveNewsPost`; public published-only query; no focused unpublish test found | yes |
| Managed page create/publish | `SaveManagedPage` + admin pages | Authorized editor creates published plain-text page; public page visible; audit written | `—` | `PROVEN` | Platform DB transaction | `UNKNOWN` | `PROVEN` draft/future hiding | `PROVEN` | `AdminCmsManagementTest` | yes |
| Managed page edit/unpublish | admin page update | Existing page can be edited and publication state changed | `—` | `DERIVED` update route/action exists | Platform DB transaction | `UNKNOWN` | `UNKNOWN` focused edit/unpublish regression | `PROVEN` route permission | routes/action implementation; no focused edit/unpublish test found | yes |
| Escaped/plain-text rendering | public news/pages | User-authored title/body rendered escaped; no raw script/image execution surface | `—` | `PROVEN` | `—` | `UNKNOWN` | `PROVEN` XSS regression | public | `PublicNewsTest`; `AdminCmsManagementTest` | no |
| Unauthorized CMS mutation denial | admin CMS routes | Missing CMS permission or missing confirmed MFA denies mutation without persistence | `—` | `PROVEN` | `—` | `UNKNOWN` | `PROVEN` | `PROVEN` | `AdminCmsManagementTest` | yes |

## Audit

| Feature | Source/module | Expected behavior | Unit | Feature/HTTP | Integration | Live staging E2E | Negative/failure | Authorization | Evidence | Production smoke |
|---|---|---|---|---|---|---|---|---|---|---|
| Privileged mutations produce audit events | `AdminAuditRecorder` | Bootstrap, role and CMS mutations append audit records | `—` | `PROVEN` | Platform DB transactions | `UNKNOWN` | `PROVEN` | `PROVEN` | `AdminRoleManagementTest`; `AdminCmsManagementTest` | yes |
| Role-change audit | `AdminRoleManager` | Assignment/removal record actor, action, target and role metadata | `—` | `PROVEN` | `PROVEN` DB persistence | `UNKNOWN` | `PROVEN` | `PROVEN` | `AdminRoleManagementTest`; `AdminRoleManager` | no |
| CMS audit | CMS save actions | Create/update records actor/action/target and bounded publication metadata | `—` | `PROVEN` | `PROVEN` DB persistence | `UNKNOWN` | `PROVEN` | `PROVEN` | `AdminCmsManagementTest`; `SaveNewsPost` | no |
| Bounded audit visibility | admin audit controller | Audit view is paginated/bounded | `—` | `PROVEN` | Platform DB | `UNKNOWN` | `PROVEN` | `PROVEN` | `AdminAuditTest` | yes |
| Permission-protected audit access | admin audit route | Requires auth + confirmed MFA + `audit.view` | `—` | `PROVEN` | `—` | `UNKNOWN` | `PROVEN` | `PROVEN` | `AdminAuditTest`; routes | yes |
| No secrets/passwords/tokens/hashes in admin audit records | audit call sites | Privileged audit metadata limited to role, slug and publication state; no secret fields supplied by inspected call sites | `—` | `DERIVED` | `DERIVED` | `UNKNOWN` | `UNKNOWN` dedicated regression | `PROVEN` write call sites are server-controlled | `AdminRoleManager`; CMS save actions; no dedicated audit-secret regression found | no |

## Security behavior

| Feature | Source/module | Expected behavior | Unit | Feature/HTTP | Integration | Live staging E2E | Negative/failure | Authorization | Evidence | Production smoke |
|---|---|---|---|---|---|---|---|---|---|---|
| CSP and browser security headers | `SecurityHeaders` | Enforced CSP without unsafe inline/eval plus nosniff/frame/referrer/permissions policy | `—` | `PROVEN` | `—` | `STAGING_PROVEN` live response | `PROVEN` | public/auth surfaces | `SecurityHeadersTest`; Phase 7 live smoke | yes |
| Session cookies | session config | Secure + HttpOnly in production-like runtime; SameSite Lax configured | `—` | `PROVEN` defaults | `—` | `STAGING_PROVEN` Secure/HttpOnly live cookie | `PROVEN` config verifier | `—` | `WebSessionTest`; `ProductionConfigurationVerifierTest`; Phase 7 live smoke | yes |
| HTTPS application configuration | production verifier | Production `APP_URL` must use HTTPS and non-loopback host | `—` | `PROVEN` | `—` | `STAGING_PROVEN` config guardrail | `PROVEN` | `—` | `ProductionConfigurationVerifierTest`; Phase 7 workflow | yes |
| Final TLS/reverse-proxy trust/HSTS | deployment boundary | Verify actual certificate, termination, proxy trust and HSTS decision on final production | `—` | `—` | `—` | `UNKNOWN` | `UNKNOWN` | `—` | Explicitly deferred by Phase 7 evidence | yes |
| Debug disabled | production verifier | `APP_DEBUG=true` fails production configuration verification | `—` | `PROVEN` | `—` | `STAGING_PROVEN` | `PROVEN` | `—` | `ProductionConfigurationVerifierTest`; Phase 7 workflow | yes |
| Sensitive error handling | runtime | Representative 404 does not expose stack trace or application key | `—` | `DERIVED` | `—` | `STAGING_PROVEN` bounded representative check | `PROVEN` representative only | `—` | Phase 7 live smoke | yes |
| Request correlation | `RequestCorrelation` | Server-generated UUID request ID; inbound attacker ID not trusted | `—` | `PROVEN` | `—` | `STAGING_PROVEN` live header | `PROVEN` | `—` | `RequestCorrelationTest`; Phase 7 live smoke | yes |
| Structured logging | request middleware/logging config | Bounded request-completion event, JSON-to-stderr option | `—` | `PROVEN` | `—` | `STAGING_PROVEN` live log | `PROVEN` | `—` | `RequestCorrelationTest`; Phase 7 live smoke | yes |
| No sensitive values in request logs | request correlation | Query token/email omitted from bounded completion context | `—` | `PROVEN` | `—` | `STAGING_PROVEN` representative APP_KEY exclusion | `PROVEN` bounded context | `—` | `RequestCorrelationTest`; Phase 7 live smoke | yes |
| Rate limits | Identity/MFA/character routes where configured | Login, registration, recovery, password, MFA and character create throttles enforce abuse bounds | `—` | `PROVEN` for Identity/MFA; route middleware present for character create | `—` | `UNKNOWN` | `PROVEN` for Identity/MFA | `PROVEN` | Identity/MFA feature tests; routes | no |
| CSRF protection | Laravel web middleware + forms | State-changing browser requests require valid CSRF token | `—` | `DERIVED` forms contain `_token`; standard web routes used | `—` | `UNKNOWN` | `UNKNOWN` explicit missing/invalid-token regression | `PROVEN` route group boundary | registration/login/MFA/character forms contain tokens; no focused CSRF rejection test found | yes |
| Authorization bypass attempts | auth/current-session/MFA/RBAC/binding | Guest, stale session, no MFA, missing/unknown permission and foreign client account id fail closed | `—` | `PROVEN` | `PROVEN` for binding/DB boundary | `UNKNOWN` | `PROVEN` | `PROVEN` | Identity, Admin and Character feature tests | yes |

## Infrastructure-dependent failure and recovery paths

| Feature | Source/module | Expected behavior | Unit | Feature/HTTP | Integration | Live staging E2E | Negative/failure | Authorization | Evidence | Production smoke |
|---|---|---|---|---|---|---|---|---|---|---|
| Platform DB unavailable | Platform persistence | Fail safely without sensitive diagnostics or false success | `—` | `UNKNOWN` | `UNKNOWN` | `UNKNOWN` | `UNKNOWN` | `—` | No focused production-like outage exercise found | yes |
| Canary DB unavailable | Canary read/write boundaries | Verifiers/failing reads do not fabricate success; public online dependency failure is explicit | `PROVEN` verifier | `PROVEN` online 503 | `STAGING_PROVEN` unavailable verifier endpoint | `UNKNOWN` full HTTP surface | `PROVEN` | DB principals | Phase 7 workflow; `PublicGameDataTest` | yes |
| Insufficient provisioning DB privileges | provisioning boundary | Effective-grant verifier fails closed | `PROVEN` | `—` | `STAGING_PROVEN` real grant mutation | `—` | `PROVEN` | DB enforced | Phase 7 workflow | yes |
| Excessive generic Canary DB privileges | read boundary | Excess privilege causes verifier failure | `PROVEN` | `—` | `STAGING_PROVEN` real grant mutation | `—` | `PROVEN` | DB enforced | Phase 7 workflow | yes |
| Excessive/denied character-create privileges | character DB boundary | Uncontracted reads/updates/deletes/inserts denied; verifier accepts only exact grant shape | `PROVEN` | `—` | `PROVEN`; cross-surface denial `STAGING_PROVEN` | `—` | `PROVEN` | DB enforced | MariaDB integration; Phase 7 workflow | yes |
| Redis unavailable/malformed/missing | runtime boundary | Fail closed/degrade explicitly without synthetic healthy state | `PROVEN` | `PROVEN` | `STAGING_PROVEN` | `UNKNOWN` live `/servers` | `PROVEN` | Redis ACL | runtime tests; Phase 7 workflow | yes |
| Mail unavailable | mail boundary | Delivery attempt fails rather than reporting fake transport success | `—` | `PROVEN` non-delivery mail rejection | `STAGING_PROVEN` unavailable SMTP endpoint | `UNKNOWN` password-recovery HTTP flow | `PROVEN` | `—` | `PasswordRecoveryTest`; Phase 7 workflow | yes |
| Invalid production configuration | operations verifier | Fail closed for debug, non-production env, missing key, insecure URL/cookies, non-delivery mail | `—` | `PROVEN` | `—` | `STAGING_PROVEN` | `PROVEN` | operator command | `ProductionConfigurationVerifierTest`; Phase 7 workflow | yes |
| Interrupted deployment isolation | release workflow | Incomplete release is never activated | `—` | `—` | `—` | `STAGING_PROVEN` | `PROVEN` | operator/deployment | Phase 7 workflow | yes |
| Rollback and redeploy | release workflow | Switch to previous known-good release, validate, then redeploy candidate | `—` | `—` | `—` | `STAGING_PROVEN` | `PROVEN` | operator/deployment | Phase 7 workflow | yes |
| Backup/restore/recovery | operations | Logical backup, clean restore, integrity checks and restored-environment smoke | `—` | `—` | `STAGING_PROVEN` real MariaDB | `STAGING_PROVEN` controlled recovery | `PROVEN` | operator | Phase 7 workflow; staging restore 105 ms on final PR head, not production RTO/RPO | yes |

## Required cross-surface acceptance scenarios

| Scenario | Current status | Evidence | Acceptance gap |
|---|---|---|---|
| New Identity -> login -> MFA -> Canary provisioning -> binding -> character creation -> public character visibility | `UNKNOWN` for live staging E2E | Each component has strong feature/integration coverage; provisioning and character DB boundaries are real-MariaDB tested and Phase 7 privilege-validated | No single running production-like HTTP scenario executes the entire chain with real staging dependencies and verifies final public visibility |
| Admin bootstrap -> MFA/RBAC -> create/publish news or page -> public visibility -> audit event | `UNKNOWN` for live staging E2E | Admin/RBAC/CMS/audit feature coverage is `PROVEN` | No single running production-like HTTP/operator scenario executes the full chain against the deployed staging release |

## Explicitly not implemented / not accepted as delivered scope

The following are deliberately outside the delivered surface and must not be inferred from adjacent functionality:

- public existing-account claim/import flow;
- character deletion;
- character rename;
- self-service Canary binding unlink/rebind/transfer;
- account deletion;
- authoritative Platform game-login bridge unless separately authorized and implemented.

## Gap register and bounded follow-up work

Priority is security/authentication and data integrity before UI polish.

### FAV-01 — live Identity/MFA/session/CSRF production-like E2E

Prove through the running production-like HTTP application, using production-like Platform MariaDB, session configuration and SMTP, at minimum:

- registration -> login;
- password recovery via captured SMTP reset link -> reset -> old-session rejection;
- MFA enrollment -> confirmation -> logout -> password login -> TOTP challenge;
- invalid/replayed TOTP and used recovery-code rejection;
- MFA disable and generation revocation;
- explicit missing/invalid CSRF rejection for representative state-changing routes;
- auth/stale-session denial.

Do not weaken existing rate limits or bypass middleware to make the scenario pass.

### FAV-02 — live provisioning/binding/character/public production-like E2E

Prove one real cross-surface staging flow through the running application and dedicated production-like DB principals:

`new Identity -> provisioning -> ready immutable binding -> character creation -> public exact-name search/profile visibility`.

Include foreign/client `account_id` abuse, pending-binding denial, duplicate provisioning retry, duplicate character name and quota/concurrency assertions at the appropriate integration layer. Preserve the generic Canary connection as read-only and use only the two approved operation-specific write principals.

### FAV-03 — live admin/RBAC/CMS/audit production-like E2E

Prove through the running production-like application:

`existing MFA-confirmed Identity -> one-time admin bootstrap -> RBAC allow/deny -> role management -> CMS create/edit/publish/unpublish -> public visibility/hiding -> audit visibility`.

Include unknown permission, missing permission, no-MFA, last-platform-admin removal and unauthorized CMS mutation denial.

### FAV-04 — Platform DB outage and bounded error/log validation

Exercise safe Platform DB unavailability against the running production-like release and prove:

- no false-success mutation result;
- no stack trace, password, token, hash, application key or connection secret in the HTTP response or bounded request log;
- deterministic health/readiness/operator observability semantics appropriate to the implemented deployment model.

### FAV-05 — focused CMS state-transition and audit-secret regressions

Add focused repository regressions for currently `DERIVED`/`UNKNOWN` assertions:

- news unpublish hides an already-published post;
- managed page edit and unpublish change public visibility correctly;
- administrator audit records for privileged operations do not contain passwords, reset tokens, TOTP secrets, recovery codes, credential hashes or application secrets.

## Production smoke boundary

The minimal final production-only verification is maintained separately in `docs/testing/PRODUCTION_SMOKE_CHECKLIST.md`. Staging evidence must never be promoted to `PRODUCTION_PROVEN` without direct final-production execution against the exact deployed SHA.
