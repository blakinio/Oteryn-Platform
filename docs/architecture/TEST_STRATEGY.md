# Oteryn Platform Test Strategy

## Goal

Make security, business invariants and Canary/login-server compatibility verifiable rather than dependent on manual assumptions.

The strategy is risk based: use the smallest deterministic layer that proves an invariant, and reserve browser/system E2E for composed behavior that lower layers cannot prove efficiently.

ADR 0008 and `docs/testing/E2E_COVERAGE_ROADMAP.md` govern continuous E2E hardening beyond the already-delivered functional acceptance baseline.

## Test layers

### Unit tests

Use for pure domain/application logic:

- validation rules not coupled to HTTP;
- permission decisions with deterministic inputs;
- value objects and transformations;
- token/expiry policy helpers;
- future ledger calculations.

### Feature/HTTP tests

Use Laravel feature tests for:

- routes and middleware;
- authentication and logout;
- CSRF-relevant browser flows;
- authorization policies;
- validation/error behavior;
- rate limiting;
- admin access boundaries;
- CMS/public pages.

Feature tests remain the preferred layer for deterministic request/authorization behavior that does not require a real browser engine.

### Database integration tests

Use an isolated test database for:

- migrations;
- transactions;
- row locking/atomic mutations;
- uniqueness constraints;
- account/character integration adapters;
- concurrency and race conditions;
- idempotent retry and ambiguous-commit recovery;
- data-integrity invariants across approved shared-write boundaries.

Never point automated tests at production data.

Browser E2E must not replace real-database integration as the primary proof for locking, transaction races or uniqueness correctness. Browser scenarios may assert the user-visible outcome of a conflict only when that adds unique composed evidence.

### Contract tests

Required for shared Canary/login-server assumptions.

Contract tests should verify only evidence-backed schemas/interfaces and fail visibly when incompatible changes occur.

Examples:

- required shared columns/types exist;
- read queries return expected shapes;
- approved character/account mutations preserve invariants;
- auth/session token behavior matches documented contract.

### End-to-end and production-like browser tests

The repository contains an exact-SHA Playwright production-like acceptance harness under `scripts/acceptance/**` and `.github/workflows/acceptance-validation.yml`.

The primary full acceptance path runs against:

- the exact tested application SHA;
- a real Laravel HTTP runtime;
- isolated MariaDB Platform and Canary acceptance schemas;
- operation-specific Canary database principals;
- a dedicated Redis runtime principal;
- MailHog SMTP;
- Chromium as the primary full-suite browser;
- a primary desktop viewport;
- serial execution with conservative secret-safe artifact handling.

The currently delivered staging-verifiable functional surface is covered by composed browser/system evidence classified `STAGING_PROVEN`. This does not prove the final production environment.

Critical composed flows include, where implemented:

- registration -> login -> MFA;
- password recovery/change -> stale-session invalidation;
- Platform Identity -> Canary account provisioning -> ready binding;
- character creation -> public character visibility;
- public game-data and dependency failure/recovery behavior;
- administrator bootstrap/authentication -> MFA/RBAC -> privileged action;
- CMS publication lifecycle -> public visibility/hiding -> audit visibility;
- representative authorization and abuse-denial paths.

The authoritative Platform-originated game-login bridge remains unimplemented and separately authorized. When implemented, add end-to-end coverage for credential authority, expiry/replay, revocation, disabled/banned state and character usability across the selected login-server/Canary boundary.

## Implemented bounded acceptance profiles

The risk-based acceptance matrix preserves the complete primary Chromium baseline while adding bounded profiles only where they add unique evidence.

Current Playwright execution projects are:

- `chromium-primary` — preserved full primary Chromium baseline at `1440x1000`;
- `portability-chromium`, `portability-firefox`, `portability-webkit` — bounded critical portability spec at `1440x1000`;
- `responsive-desktop` — Chromium at `1440x1000`;
- `responsive-tablet` — Chromium at `820x1180` with touch enabled;
- `responsive-mobile` — Chromium at `390x844` with touch/mobile emulation enabled;
- `resilience-chromium` — bounded desktop Chromium dependency failure/restoration/recovery profile;
- `accessibility-chromium` — bounded desktop Chromium keyboard/focus interaction profile;
- `soak-chromium` — scheduled/manual read-only public-surface soak profile.

The bounded portability subset proves representative composed outcomes for public navigation/game data, Identity login/logout, authenticated Account Overview, MFA-confirmed privileged access, authorization denial and deterministic CMS/public visibility.

The bounded responsive subset proves representative public navigation, Identity entry forms, Account Overview, MFA challenge and privileged administration usability, including horizontal-overflow/accessibility smoke assertions.

The bounded resilience subset proves representative public dependency lifecycles rather than failure-only behavior:

- Canary read path `/online`: known-good -> controlled `SELECT` denial -> HTTP 503 -> grant restoration -> successful public read;
- Redis runtime path `/servers`: known-good live state -> controlled `HMGET` ACL denial -> bounded unavailable UI -> ACL restoration -> live state recovered.

All resilience dependency mutations are acceptance-scoped and restored in cleanup. The profile configures zero retries.

The bounded accessibility subset uses real keyboard interaction rather than only DOM presence checks. It proves representative:

- login and password-recovery form traversal/activation with `Tab` and `Enter`;
- Account Overview create-character navigation;
- character form forward/reverse focus progression;
- MFA challenge completion;
- privileged managed-page table-to-form navigation;
- visible `:focus-visible` behavior on the controls reached by keyboard.

The accessibility profile is Chromium-only, configures zero retries and keeps raw trace, automatic screenshot and video disabled. It does not claim screen-reader compatibility from focus/DOM assertions alone.

First successful accessibility implementation evidence is acceptance run `29853941922` on exact SHA `3bd1e4901a71841bc4593ec7e4efb98866c8c30f`:

- smoke: PASS, 10 seconds wall-clock;
- portability: PASS, 32 seconds wall-clock;
- responsive: PASS, 9 seconds wall-clock;
- resilience: PASS, 3 seconds wall-clock;
- accessibility: PASS, 6 seconds wall-clock;
- accessibility JUnit: 3 tests, 0 failures, 0 skipped.

Durable evidence:

- `docs/testing/E2E_SECURITY_BOUNDARY_EVIDENCE.md`;
- `docs/testing/E2E_PUBLIC_DEPENDENCY_RECOVERY_EVIDENCE.md`;
- `docs/testing/E2E_ACCESSIBILITY_STABILITY_SOAK_EVIDENCE.md`.

The required pull-request `critical` profile executes primary smoke plus bounded portability, responsive, resilience and accessibility coverage. The `full` profile executes the complete primary Chromium functional baseline plus required resilience and accessibility before visual/accessibility collection. Only successful `full` execution may classify the composed functional result as `FUNCTIONAL_ACCEPTANCE_STAGING_PROVEN`.

The complete secret-sensitive suite remains primary-Chromium only. Do not multiply it across the browser/viewport cross-product merely to increase test count.

## Repeated-run stability validation

`.github/workflows/acceptance-validation.yml` is reusable through `workflow_call` while preserving its direct pull-request, push and manual execution semantics.

`.github/workflows/acceptance-stability.yml` provides scheduled/manual repeated-run evidence:

- three fresh isolated `critical` jobs per run;
- `fail-fast: false` so a failed iteration does not suppress later evidence;
- distinct iteration/run suffixes and artifact identities;
- Playwright global retries forced to zero through `ACCEPTANCE_ZERO_RETRIES=1`;
- fresh MariaDB, Redis, MailHog, Laravel runtime, cache and session state per iteration.

A test that fails an iteration is not considered healthy because another iteration passes. Stability failures must be classified as product, harness or infrastructure failures from the first failing project/test/iteration.

This repeat profile remains scheduled/manual rather than blocking every pull request. First multi-iteration runtime evidence remains pending until the workflow first executes.

## Read-only soak validation

`.github/workflows/acceptance-soak.yml` schedules/exposes a bounded read-only public soak through the reusable acceptance workflow.

The default profile:

- runs `soak-chromium` for 300 seconds unless manually configured otherwise;
- uses zero retries;
- repeatedly reads home, online, highscores and servers;
- requires HTTP 200 plus representative expected UI on every navigation;
- performs no login, MFA, password recovery, account, character or privileged mutation.

The soak records non-secret calibration metrics:

- exact tested SHA;
- target/measured duration;
- iteration/request count;
- overall and per-route navigation-time distributions;
- Laravel serve process-tree RSS start/end/max;
- Redis key count before/after.

No latency, memory or Redis-key budget is enforced until repeated measurements establish normal variance and a defensible regression threshold. The first scheduled/manual runtime baseline remains pending.

## E2E layering and expansion rules

Before adding a browser/system test:

1. identify the concrete risk;
2. search existing unit/feature/integration/contract/operations evidence;
3. state what unique browser/system proof is missing;
4. keep the scenario deterministic and exact-SHA;
5. preserve secret-safe artifacts;
6. classify the result by environment rather than by aspiration.

Do not add browser tests merely to increase test count.

Continuous hardening follows ADR 0008:

- full primary-browser production-like acceptance remains the composed functional baseline;
- a bounded critical subset provides Chromium/Firefox/WebKit portability evidence;
- representative desktop/tablet/mobile profiles cover critical responsive journeys;
- bounded Chromium keyboard/focus accessibility interaction is required in critical/full acceptance;
- dependency interruption must be deterministic, fail closed, restore the dependency and prove subsequent recovery;
- concurrency correctness remains primarily real-database integration evidence;
- migration/upgrade/rollback validation must use representative synthetic existing data, never production dumps;
- observability tests should correlate sanitized request IDs/audit/log outcomes where deterministic;
- repeated-run and soak profiles remain scheduled/manual until stable measurements justify any stronger gate.

The detailed priority and profile model is `docs/testing/E2E_COVERAGE_ROADMAP.md`.

## Security regression tests

Every confirmed vulnerability fix should add a focused regression test where practical.

Priority areas:

- IDOR/account ownership;
- privilege escalation;
- CSRF;
- XSS/sanitization;
- SQL injection/query safety;
- session fixation/revocation;
- password reset enumeration/replay;
- MFA bypass;
- rate-limit bypass;
- shared-data race conditions;
- future webhook/payment replay.

Use browser E2E for representative composed abuse boundaries only when it adds proof beyond deterministic feature/security tests.

## Failure and recovery validation

Failure testing must prove more than an error response.

A controlled failure scenario should establish:

1. known pre-state;
2. deterministic failure injection;
3. fail-closed/no-false-success behavior;
4. relevant persisted-state integrity;
5. dependency restoration;
6. successful recovery.

Search existing Phase 7 production-like validation and the dedicated Platform DB outage workflow before creating new failure orchestration. Do not duplicate an existing evidence layer without a new assertion.

The required acceptance `resilience` profile provides the first browser-level implementation of this lifecycle for public Canary read access and Redis runtime ACL access. Additional dependency scenarios are added only when they close a distinct evidence gap.

## Migration, upgrade and rollback validation

For releases with persistent data or schema changes:

- create representative synthetic pre-upgrade data;
- apply the target migration/deployment transition;
- verify schema and data invariants;
- run bounded critical smoke on the upgraded exact SHA;
- exercise controlled rollback only where the migration/deployment contract supports it safely;
- verify expected post-rollback application/data state.

Never use copied production dumps in CI.

Migration/browser smoke complements, but does not replace, migration and database integration tests.

The required Phase 7 production-like release path includes an isolated representative existing-data upgrade/rollback slice. It constructs synthetic Identity and published-news state on the actual PR `BASE_SHA` schema, runs exact-candidate migrations, verifies a non-secret persisted-data fingerprint, executes bounded candidate HTTP smoke, switches the existing release symlink back to `BASE_SHA` against the post-upgrade database, reruns smoke, and then redeploys the candidate and reruns smoke. Any migration, compatibility, data-integrity or smoke failure fails the release-validation job closed.

Durable evidence is recorded in `docs/testing/E2E_MIGRATION_ROLLBACK_EVIDENCE.md`.

This controlled path remains `STAGING_PROVEN`; it does not establish production migration duration, lock behavior, provider rollback mechanics, production RTO/RPO or universal backward compatibility for future destructive changes.

## Observability correlation validation

The Phase 7 running-HTTP path proves that one concrete application-generated response `X-Request-ID` maps to exactly one structured `http.request.completed` JSON event with the same `request_id` and expected method/status pair.

Durable evidence is `docs/testing/E2E_OBSERVABILITY_CORRELATION_EVIDENCE.md`.

This is controlled-runtime correlation evidence only. Production edge propagation, centralized log shipping, retention, alerting and distributed tracing remain environment-specific.

## Test data

- use factories/seeders designed for test environments;
- never copy production dumps into CI;
- fixtures must not contain real emails, tokens, credentials or personal data;
- cross-repository fixtures should include the schema/version evidence they represent;
- representative migration datasets must be synthetic and deterministic;
- resilience tests may mutate only disposable acceptance-scoped dependency principals and must restore them before completion;
- repeated critical iterations must use isolated runtime/dependency state rather than sharing identity-scoped limiter/session/cache state;
- soak journeys must remain read-only unless a separately justified mutation-soak design is approved.

## Secret-safe browser diagnostics

Raw Playwright traces, automatic screenshots and video may capture session cookies, password-reset URLs, TOTP enrollment secrets or recovery codes.

Therefore:

- keep raw trace/screenshot/video disabled by default for secret-bearing flows;
- opt into richer artifacts only for demonstrably non-secret scenarios or artifacts sanitized by construction;
- never persist passwords, reset links, TOTP secrets, recovery codes, session cookies, production credentials or private endpoints;
- prefer bounded assertion messages, exact SHA, browser/profile identity and sanitized correlation identifiers.

## CI direction

The mandatory PHP CI gate runs, in order:

1. `composer validate --strict`;
2. `composer install --no-interaction --prefer-dist --no-progress` from the committed lockfile;
3. `composer audit --no-interaction` and fails when Composer reports a security advisory for the installed dependency set;
4. `composer format:check`;
5. `composer analyse` using PHPStan with Larastan at level 10 across `app`, `bootstrap`, `config`, `database`, `routes` and `tests`;
6. `composer test`.

No PHPStan baseline is currently committed. New static-analysis errors fail CI directly rather than being absorbed into an ignore list or baseline.

Dependabot is configured for bounded weekly Composer and GitHub Actions updates. It complements but does not replace the required Composer advisory gate.

The acceptance and release-validation workflows provide:

- required pull-request `critical`: primary Chromium smoke + bounded Chromium/Firefox/WebKit portability + Chromium desktop/tablet/mobile responsive + bounded Chromium resilience + bounded Chromium keyboard accessibility;
- standalone `smoke`, `portability`, `responsive`, `resilience`, `accessibility` and `soak` profiles;
- full exact-SHA primary Chromium production-like functional acceptance plus required resilience/accessibility on main/manual full execution;
- full-profile visual/accessibility collection only after full functional baseline, resilience and accessibility succeed;
- durable non-secret browser evidence tied to exact tested SHA, profile, browser/project, viewport and measured profile duration;
- profile-specific JUnit evidence with project identity preserved in test names;
- reusable acceptance execution for isolated repeated critical/soak workflows;
- scheduled/manual three-iteration zero-retry stability measurement;
- scheduled/manual read-only public soak calibration;
- required Phase 7 exact-SHA representative existing-data upgrade/rollback/redeploy validation;
- required Phase 7 exact response-to-structured-log request correlation.

Do not broaden the complete secret-sensitive suite to every browser/viewport before repeated-run evidence demonstrates that the signal and CI cost justify it.

## Merge expectations

- security-critical changes require relevant regression tests;
- shared data changes require contract/integration evidence;
- known dependency advisories fail the required Composer audit gate;
- do not merge on tests from an old commit when current head changed;
- do not weaken or delete failing tests to make CI green;
- document exact unavailable environments rather than claiming tests passed;
- E2E evidence must identify the exact tested SHA and applicable browser/profile;
- browser-specific skips require explicit justification and must not silently count as coverage;
- accessibility interaction tests must use real keyboard/focus behavior for the boundary they claim;
- a repeated-run failure must not be hidden by Playwright retries;
- soak metrics remain calibration-only until evidence-backed thresholds exist;
- resilience tests must prove restoration and successful recovery, not only failure;
- resilience dependency mutations must be acceptance-scoped and cleaned up deterministically;
- migration-bearing candidates must pass the representative existing-data Phase 7 path or document a concrete safe rollout/rollback blocker rather than bypassing the check.

## Production readiness E2E matrix

Before a production-ready/go-live claim, directly verify the applicable launch scope in the final production environment:

| Flow | Required |
|---|---|
| Registration | Yes if registration is enabled |
| Web login/logout | Yes |
| Password change | Yes |
| Password reset | Yes |
| Session revocation | Yes |
| Admin MFA | Yes |
| Authorization denial | Yes |
| Canary/game login | Yes only if the authoritative game-login bridge is part of launch scope |
| Ban/disabled state | Yes when an authoritative applicable path exists |
| Auth token/session expiry | Yes for applicable token/session paths |
| Replay attempt | Yes where tokenized flow exists |
| Account provisioning | Yes if enabled in launch scope |
| Character creation/public visibility | Yes if enabled in launch scope |
| Critical CMS/public visibility | Yes if enabled in launch scope |
| Backup restore | Operational production evidence |

The authoritative production execution boundary is issue #91 plus `docs/operations/PRODUCTION_READINESS_CHECKLIST.md` and `docs/testing/PRODUCTION_SMOKE_CHECKLIST.md`.

Results must be tied to exact deployed versions/commit SHAs. Repository or staging evidence never substitutes for direct final-production proof.
