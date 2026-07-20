# Visual / UI / UX Acceptance Matrix

## Result

**Visual / UX Acceptance — FAIL.**

The delivered web product is a functional technical MVP, not a public-launch-ready UI. The browser evidence proves substantial visual consistency on the styled public/admin shell at desktop sizes, readable color contrast, labeled form controls and keyboard-focus visibility on interactive pages. It also proves launch-blocking UX gaps in navigation/discoverability, responsive overflow, identity/account presentation, error-state recovery and small-screen administrator usability.

This result is independent from functional acceptance. Visual defects do not change a functional PASS/FAIL classification. The separately owned Functional Acceptance Matrix in PR #66 is the authority for functional acceptance status.

**Public launch readiness — NO.** Both the functional gate and the visual/UX gate must independently pass before launch readiness can be claimed.

## Evidence boundary

Product baseline under acceptance: `main@221a13f6d7fba28ba765d67594a5cce4bf9523c4`.

Primary completed browser evidence:

- workflow: `Visual UX Acceptance` run `29784023395`, job `88491497799`;
- acceptance SHA: `7f060d810050e31d978f9f5cf50cfd31fb2a173a`;
- result: workflow/job `success`;
- artifact: `visual-ux-acceptance-29784023395` (`8477783749`);
- artifact digest: `sha256:911a69a9fc973accef55def6e79dc1880a446c3e2c460b5495b39154ae0f08e9`;
- screenshots: `71`;
- viewports: desktop `1440x1000`, mobile `390x844`, tablet `768x1024` for materially table-heavy surfaces;
- browser: headless Chromium through Playwright;
- environment: production `APP_ENV`, MariaDB, Redis and SMTP-compatible service dependencies; authenticated browser traffic uses loopback HTTP with `SESSION_SECURE_COOKIE=false` solely because `php artisan serve` does not terminate TLS. Secure-cookie production behavior remains an independent Phase 7 staging gate.

The PR branch from the product baseline through the evidence SHA changes acceptance-only files. No product view, stylesheet or controller is changed by this acceptance task, so the screenshots evaluate the same product UI shipped by the baseline.

Automated evidence summary:

| Check | Result |
|---|---:|
| HTTP status mismatches | 0 |
| Surfaces with document-level horizontal overflow | 16 |
| Surfaces with unlabeled controls | 0 |
| Surfaces with sampled text contrast below 4.5:1 | 0 |
| Interactive surfaces where visible focus was not observed | 0 |
| Non-interactive error surfaces with no focus target | 6 |
| Surfaces exposing detected raw framework/database exception text | 0 |
| Browser-error surfaces | 6, all representative 403/404/503 responses; 403/503 also report CSP rejection of framework inline error-page styles |

### Proven horizontal-overflow surfaces

- `online/mobile`
- `highscores/mobile`
- `servers/mobile`
- `guild-detail/mobile`
- `mfa-enrollment/mobile`
- `admin-news-list/mobile`
- `admin-news-form/mobile`
- `admin-managed-page-form/mobile`
- `admin-role-management/mobile`
- `admin-audit-log/mobile`
- `highscores/tablet`
- `guild-detail/tablet`
- `servers/tablet`
- `admin-role-management/tablet`
- `admin-audit-log/tablet`
- `servers-runtime-dependency-failure/mobile`

Horizontal scrolling is not isolated to an intentional table viewport; the document itself becomes wider than the viewport. That is a launch UX blocker for the affected surfaces.

## Delivered surface inventory

### Public

- Home and embedded character search form.
- News list.
- News detail.
- Online characters.
- Highscores.
- Servers/runtime status.
- Character detail.
- Guild detail.
- Managed public page.

### Identity and account

- Registration.
- Login.
- Password recovery request.
- Password reset.
- Authenticated password change.
- MFA challenge.
- MFA settings / enrollment.
- MFA recovery-code reveal after confirmation.
- Character creation.

### Administrator

- Admin dashboard.
- News/CMS list and create/edit form.
- Managed-page list and create/edit form.
- Role management.
- Audit log.

### Delivered state variants

- Registration validation.
- Login validation / invalid credentials.
- MFA not enabled / enrollment / confirmed settings / challenge / recovery codes.
- Empty news list.
- Highscores/online/guild empty-state source branches.
- Servers runtime dependency unavailable/unknown state.
- Public game-data controlled dependency failure (`503`).
- Authorization denied (`403`).
- Not found (`404`).
- Administrator action success/error flash states.

### Requested surfaces that are not currently delivered as screens

- **General account/dashboard surface: NOT_DELIVERED — UX_BLOCKER GAP.** There is no account home that exposes character management, MFA/password settings, provisioning status or logout as one coherent flow.
- **Canary account provisioning/status surface: NOT_DELIVERED — UX_BLOCKER GAP.** Registration triggers provisioning work, but the UI has no dedicated status, pending/retry, failure or support-recovery surface. Registration redirects to the registration page with a generic completion message.
- **Dedicated character-search screen: NOT_DELIVERED as a separate view.** Character search is an embedded Home form that redirects to Character detail; this is acceptable as an interaction pattern, but evidence is represented by the Home/search and Character-detail states.

## Visual quality taxonomy

No existing repository-specific visual taxonomy was found, so this matrix uses the requested classifications:

- `PRODUCTION_READY`
- `FUNCTIONAL_BUT_NEEDS_POLISH`
- `UX_BLOCKER`
- `VISUAL_BLOCKER`

No delivered surface is classified `PRODUCTION_READY` because the platform-wide navigation/design-system gaps prevent a coherent launch experience even where an individual page renders cleanly.

## Per-surface matrix

Legend: `D` desktop, `T` tablet, `M` mobile. `N/A` means the state is not applicable to that server-rendered surface. Tablet inherits the same no-breakpoint layout unless explicit tablet evidence is listed.

| Surface | D / T / M status | Navigation / hierarchy / typography / spacing | Forms / tables | Empty / loading / dependency failure | Validation / error | Accessibility / keyboard | Responsive / visual defects | UX defects | Classification | Screenshot evidence |
|---|---|---|---|---|---|---|---|---|---|---|
| Home | D good; T derived same shell; M good | Styled dark public shell; clear H1/card hierarchy; public nav consistent but no active state and no auth/account entry | Character search is labeled and usable | Loading N/A | Browser validation applies to required search input | Semantic main/nav/headings; native visible focus observed; sampled contrast passes | M wraps nav/cards without overflow | Users cannot discover Login, Register or Account from primary navigation | `UX_BLOCKER` | `home-desktop.png`, `home-mobile.png` |
| Character search interaction | D/M usable | Lives inside Home, so location is understandable | Labeled search field and primary submit; no suggestions/autocomplete required by current contract | N/A | Required-field/browser behavior; missing character resolves to 404 rather than inline recovery | Keyboard accessible; visible focus observed | No overflow in seeded state | Search failure becomes a dead-end generic 404; no dedicated results/recovery state | `FUNCTIONAL_BUT_NEEDS_POLISH` | Home screenshots plus `character-detail-*` / `not-found-404-*` |
| News list | D good; T derived; M good in first evidence | Public shell consistent; cards establish hierarchy | No forms/tables | Empty state explicitly says no published news; loading N/A | N/A | Semantic headings/links; visible focus; contrast passes | First run no overflow; long title wraps | Pagination visual quality requires stress-state review; no active nav | `FUNCTIONAL_BUT_NEEDS_POLISH` | `news-list-desktop.png`, `news-list-mobile.png`, `news-empty-state-*` |
| News detail | D good; T derived; M good | Public shell; readable title/date/body; plain-text prose | N/A | Loading N/A | Missing slug -> generic 404 | Semantic H1; links/focus/contrast acceptable | Long content wraps in seeded probe | No breadcrumb/back-to-news affordance beyond global nav | `FUNCTIONAL_BUT_NEEDS_POLISH` | `news-detail-desktop.png`, `news-detail-mobile.png` |
| Online | D good; T derived; M overflow | Public shell; card hierarchy | Card list rather than table | Explicit no-online branch in source; controlled DB failure -> 503 | Safe generic 503; no raw SQL/stack text observed | Headings/links/focus/contrast acceptable in normal page | **Document-level M overflow with long character/content** | Small-screen content can become partially off-screen; 503 is a dead-end default error page | `UX_BLOCKER` | `online-desktop.png`, `online-mobile.png`, `online-dependency-failure-503-*` |
| Highscores | D good; **T overflow**; **M overflow** | Public shell; clear H1 | Semantic table with headers | Explicit empty message; loading N/A | Query failure follows controlled dependency behavior | Table semantics present; keyboard links reachable | **Table widens entire document on T/M; no isolated scroll wrapper or responsive transform** | Small-screen table unusable without moving the whole page horizontally | `UX_BLOCKER` | `highscores-desktop.png`, `highscores-tablet.png`, `highscores-mobile.png` |
| Servers | D good; **T overflow**; **M overflow** | Public shell; card hierarchy and status text | N/A | Explicit runtime unknown/unavailable notice is safe | Dependency failure does not expose raw exception | Status is text, not color-only; visible focus; contrast passes | **Long channel/runtime content widens T/M document; failure M also overflows** | Failure messaging is understandable, but layout is not resilient to long server names/messages | `UX_BLOCKER` | `servers-desktop.png`, `servers-tablet.png`, `servers-mobile.png`, `servers-runtime-dependency-failure-*` |
| Character detail | D good; T derived; M good | Public shell; simple definition-list hierarchy | N/A | Missing character -> 404 | Generic 404 on miss | Semantic heading/definition list; accessible links/focus | No overflow in evidence | No contextual back/search-again action; missing result is dead-end | `FUNCTIONAL_BUT_NEEDS_POLISH` | `character-detail-desktop.png`, `character-detail-mobile.png` |
| Guild detail | D good; **T overflow**; **M overflow** | Public shell; guild metadata then members | Semantic member table | Explicit no-members branch; missing guild -> 404 | Generic 404 on miss | Table headers present; contrast/focus acceptable | **Member table/long rank/nickname widens T/M document** | Small-screen member roster is not usable responsively | `UX_BLOCKER` | `guild-detail-desktop.png`, `guild-detail-tablet.png`, `guild-detail-mobile.png` |
| Managed public page | D good; T derived; M good | Public shell; consistent plain-text content hierarchy | N/A | Unpublished/missing -> 404 | Generic 404 | Semantic H1/main; contrast/focus acceptable | Long text wraps | No managed-page discovery/navigation beyond direct links unless linked elsewhere | `FUNCTIONAL_BUT_NEEDS_POLISH` | `managed-public-page-desktop.png`, `managed-public-page-mobile.png` |
| Login | D functional; T derived; M functional | **Standalone browser-default page; no public shell/nav; no visual brand continuity** | Labels correct; submit understandable | Loading N/A | Invalid credentials visible and understandable | Labels present; native focus visible; no contrast issue | No overflow | **No Register, Forgot password, Home, Account or cancel navigation; recovery flow is undiscoverable from Login** | `UX_BLOCKER` | `login-*`, `login-validation-error-*` |
| Registration | D functional; T derived; M functional | Standalone browser-default page; no platform shell | Labels correct; password confirmation clear | No provisioning-status state | Validation visible; success is generic `Registration completed.` | Labels/focus good | No overflow | **No sign-in/home link; generic success does not communicate Canary provisioning pending/failure/retry state** | `UX_BLOCKER` | `registration-*`, `registration-validation-error-*` |
| Password recovery request | D functional; T derived; M functional | Standalone browser-default page | Labeled email form | SMTP delivery is not represented as a loading state | Generic anti-enumeration response is appropriate | Label/focus good | No overflow | **No link from Login and no global navigation, making the feature effectively undiscoverable without direct URL** | `UX_BLOCKER` | `password-recovery-desktop.png`, `password-recovery-mobile.png` |
| Password reset | D functional; T derived; M functional | Standalone browser-default page | Labeled new-password fields | N/A | Invalid/expired state is understandable in functional contract | Labels/focus good | No overflow | No platform shell or direct navigation continuity before/after reset | `UX_BLOCKER` | `password-reset-desktop.png`, `password-reset-mobile.png` |
| Password change | D functional; T derived; M functional | Standalone browser-default authenticated page | Labeled current/new password controls | N/A | Validation rendered as alert text | Labels/focus good | No overflow | **No account shell, settings navigation or discoverable entry point from public/admin navigation** | `UX_BLOCKER` | `password-change-desktop.png`, `password-change-mobile.png` |
| MFA challenge | D functional; T derived; M functional | Standalone browser-default page | Single labeled code/recovery input | Pending-login expiry is a redirect, not a loading state | Invalid code visible | Label/focus good | No overflow | No branded auth shell/cancel route; recovery-code affordance is text-only and flow context is minimal | `UX_BLOCKER` | `mfa-challenge-desktop.png`, `mfa-challenge-mobile.png` |
| MFA settings — not enabled | D functional; T derived; M derived | Standalone browser-default authenticated page | Start-enrollment action clear | N/A | N/A | Button focus visible | No initial overflow | No account/settings navigation; hard to discover from product shell | `UX_BLOCKER` | `mfa-settings-not-enabled-desktop.png` |
| MFA enrollment | D functional; T derived; **M overflow** | Standalone browser-default page; secret/provisioning URI dominate hierarchy | Password/TOTP labels correct | N/A | Confirmation errors visible | Labels/focus good | **Raw `otpauth://` provisioning URI causes M horizontal overflow** | No QR code, no copy affordance, no account shell; raw URI harms mobile setup | `UX_BLOCKER` | `mfa-enrollment-desktop.png`, `mfa-enrollment-mobile.png` |
| MFA recovery codes | D functional; T derived; M not separately captured | Standalone browser-default page | Codes are presented as plain list | N/A | N/A | Text readable; no color-only info | Desktop evidence only; source layout has no responsive system | One-time critical recovery material lacks strong save/copy/print affordances and account-shell continuity | `UX_BLOCKER` | `mfa-recovery-codes-desktop.png` |
| MFA settings — confirmed | D functional; T derived; M functional | Standalone browser-default authenticated page | Disable form labels correct | N/A | Errors visible | Labels/focus good | No overflow | No global/account navigation; destructive disable action has no distinct visual hierarchy | `UX_BLOCKER` | `mfa-settings-confirmed-desktop.png`, `mfa-settings-confirmed-mobile.png` |
| Character creation | D functional; T derived; M functional | **Standalone browser-default page; no account shell** | Labels present; selects usable | N/A | Validation/success states use alerts/status | Labels/focus good | No overflow in screenshot | **No account/dashboard entry path; sex options expose numeric values in UI; no character-list context or post-create account navigation** | `UX_BLOCKER` | `character-creation-desktop.png`, `character-creation-mobile.png` |
| Admin dashboard | D good; T derived; M good | Styled dark admin shell; clear H1; nav consistent inside admin but no active item | N/A | N/A | Authorization handled before view | Semantic nav/main/headings; focus/contrast good | No overflow | No visible logout/account action; returning to Public site loses admin/account orientation | `UX_BLOCKER` | `admin-dashboard-desktop.png`, `admin-dashboard-mobile.png` |
| CMS news list | D good; T derived; **M overflow** | Admin shell; reasonable hierarchy | Semantic table; create link | Empty state is an empty table rather than designed guidance; loading N/A | Flash errors/status available | Table headers; focus/contrast good | **M document overflow; pagination renderer must be visually normalized under stress data** | Small-screen content management is not usable; actions not optimized for touch/narrow widths | `UX_BLOCKER` | `admin-news-list-desktop.png`, `admin-news-list-mobile.png` |
| CMS news form | D good; T derived; **M overflow** | Admin shell; form hierarchy understandable | Labels correct; generic buttons/inputs | N/A | Validation visible | Labels/focus good | **M overflow from fixed/min-width form control behavior** | No primary/secondary action hierarchy; no cancel/back action adjacent to form | `UX_BLOCKER` | `admin-news-form-desktop.png`, `admin-news-form-mobile.png` |
| Managed pages list | D good; T derived; M usable with seeded short rows | Admin shell | Semantic table | Empty table lacks designed empty guidance | Flash status/error available | Table headers/focus/contrast good | Current evidence no M overflow, but no responsive table strategy exists | No active nav; pagination/action visual consistency is weak | `FUNCTIONAL_BUT_NEEDS_POLISH` | `admin-managed-pages-list-desktop.png`, `admin-managed-pages-list-mobile.png` |
| Managed page form | D good; T derived; **M overflow** | Admin shell | Labels correct | N/A | Validation visible | Labels/focus good | **M overflow** | No primary/secondary/cancel hierarchy | `UX_BLOCKER` | `admin-managed-page-form-desktop.png`, `admin-managed-page-form-mobile.png` |
| Role management | D dense but usable; **T overflow**; **M overflow** | Admin shell; hierarchy clear at top level | Semantic table with many assign/remove forms/buttons | Empty identity state not specially designed | Domain errors render as alerts/status | Labels/controls keyboard reachable | **Dense table widens T/M document** | Destructive Remove and Assign actions have weak visual distinction; button density and long emails are poor on small screens | `UX_BLOCKER` | `admin-role-management-desktop.png`, `admin-role-management-tablet.png`, `admin-role-management-mobile.png` |
| Audit log | D readable; **T overflow**; **M overflow** | Admin shell | Semantic table | Empty table has no designed guidance | N/A | Table headers/focus/contrast good | **Wide columns and raw JSON metadata widen T/M document** | Raw JSON metadata is difficult to scan; no compact/detail pattern or small-screen strategy | `UX_BLOCKER` | `admin-audit-log-desktop.png`, `admin-audit-log-tablet.png`, `admin-audit-log-mobile.png` |
| Authorization denied 403 | D/M readable fallback | **Framework/default error page; no product navigation or hierarchy** | N/A | N/A | Safe generic text | No interactive focus target; no product landmarks | CSP blocks framework inline error-page styles in evidence | **Dead end; no Home/Login/Admin recovery action** | `UX_BLOCKER` | `authorization-denied-403-desktop.png`, `authorization-denied-403-mobile.png` |
| Not found 404 | D/M readable fallback | Framework/default error page | N/A | N/A | Safe generic response | No interactive focus target/product landmarks | No app-specific responsive design | **Dead end; no search/home recovery action** | `UX_BLOCKER` | `not-found-404-desktop.png`, `not-found-404-mobile.png` |
| Controlled dependency failure 503 | D/M readable fallback | Framework/default error page | N/A | Represents public-data dependency failure | No raw SQL/stack/secret text observed | No interactive focus target/product landmarks | CSP blocks framework inline error-page styles | **Dead end; no retry/home guidance; product visual identity disappears** | `UX_BLOCKER` | `online-dependency-failure-503-desktop.png`, `online-dependency-failure-503-mobile.png` |
| News empty state | D/M good | Public shell retained; clear card message | N/A | Explicit empty state | N/A | Semantic/readable | No overflow | Could provide next-action/navigation context, but not blocking itself | `FUNCTIONAL_BUT_NEEDS_POLISH` | `news-empty-state-desktop.png`, `news-empty-state-mobile.png` |
| Servers runtime dependency failure | D good; **M overflow** | Public shell retained; explicit notice/status hierarchy | N/A | Safe runtime unknown/unavailable state | No raw exception observed | Status communicated in text, not color only | **M overflow with long seeded channel/message** | Failure semantics are good, but narrow layout is not resilient | `UX_BLOCKER` | `servers-runtime-dependency-failure-desktop.png`, `servers-runtime-dependency-failure-mobile.png` |

## Cross-cutting UX assessment

### Location and navigation

FAIL.

The public shell provides consistent Home / News / Online / Highscores / Servers navigation, and the administrator shell provides consistent admin-section links. The product does not provide one coherent navigation model across public, identity, account and admin contexts. Public navigation has no Login/Register/Account entry; identity/account/MFA/character screens are standalone; administrator navigation has no visible logout/account action or active section indication.

### Primary and secondary actions

FAIL.

Generic browser/default buttons on identity/account screens and one generic button style on the styled shell do not create a consistent primary/secondary/destructive hierarchy. This is especially weak for administrator role removal and MFA disable actions.

### Forms and validation

PARTIAL PASS.

Browser evidence found zero unlabeled form controls, and validation errors are generally visible and understandable. Form presentation is inconsistent between styled public/admin pages and browser-default identity/account pages. Several admin forms overflow mobile widths. Confirmation messages exist for many mutations, but account provisioning state is not surfaced as a dedicated user-facing state.

### Tables

FAIL.

Table semantics are present, but no responsive table pattern exists. Highscores, guild members, role management and audit demonstrably widen tablet/mobile documents. Admin news also overflows mobile. Horizontal scrolling is not contained to a deliberate table region.

### Long names and content

FAIL.

Deterministic long character/channel/rank/nickname/identity content produced document-level overflow on multiple public and admin surfaces. MFA provisioning URI also overflows mobile.

### Empty states

PARTIAL PASS.

Public news has a clear empty state. Public game-data source branches contain explicit empty messages. Administrator list pages generally degrade to empty tables rather than designed guidance/actions. Representative screenshot evidence covers News empty; other branches remain source-inspected rather than individually screenshot-proven.

### Failure states

FAIL.

Servers runtime failure stays inside the product shell and uses safe user-facing language. Representative 403/404/503 pages fall back to framework/default error presentation with no recovery navigation. 403 and 503 browser evidence also records CSP rejection of the framework page's inline styles. No raw SQL/stack/secret text was detected.

### Confirmation after operations

PARTIAL PASS.

Registration, character creation and administrator mutations provide status/error messaging in the functional implementation. However, generic registration completion does not expose whether Canary provisioning is pending, retrying or failed, and there is no account dashboard where the user can verify resulting state.

### Dead ends

FAIL.

Login/recovery/account/MFA flows have weak or absent cross-navigation; 403/404/503 pages are dead ends; character creation is not discoverable from a coherent account surface; public navigation does not expose authentication/account entry points.

## Accessibility assessment

### Semantic headings and landmarks

PARTIAL PASS.

Styled public/admin pages use semantic header/nav/main/headings. Standalone identity/account views generally use main/H1 but omit product navigation. Framework/default error pages have no product landmarks.

### Form labels

PASS for observed delivered controls.

The automated browser audit found zero unlabeled non-hidden input/select/textarea controls across captured states.

### Keyboard navigation and visible focus

PASS for observed interactive surfaces, with a product-design caveat.

Interactive pages exposed keyboard-focusable controls/links and native visible focus was observed. The product does not define an explicit `:focus-visible` design treatment, so focus appearance depends on browser defaults. The six surfaces with no observed focus target were non-interactive 403/404/503 error pages.

### Color contrast

PASS for sampled core styles.

Automated sampling found no representative core text sample below 4.5:1. The dark public/admin palette has strong body/link/muted/card contrast in inspected CSS. This is not a substitute for a complete WCAG contrast audit of every state.

### Link/button distinction

PARTIAL PASS.

Links remain visually link-like in the styled shell. Buttons are distinguishable from links but lack a systematic primary/secondary/destructive hierarchy.

### Table semantics

PASS semantically; FAIL responsively.

Delivered data tables use table/header structures. Their small-screen layout is the blocking issue.

### ARIA and status communication

PARTIAL PASS.

Status/error regions are used where functional actions return feedback. No evidence was found of critical state conveyed only by color. Additional ARIA is not required merely to compensate for native semantic elements; the main deficiencies are navigation, recovery and responsive layout rather than missing ARIA attributes.

## Design-system assessment

**Assessment: technical MVP; no complete production design system is present.**

| Area | Assessment |
|---|---|
| Colors | Coherent dark palette on public/admin surfaces, but hardcoded literals rather than named tokens. Identity/account/error surfaces do not participate in the palette. |
| Typography | One system/Inter-oriented stack in `app.css`; standalone identity/account pages fall back to browser defaults, creating visible inconsistency. |
| Spacing | A small set of repeated literal paddings/gaps; no documented/tokenized scale. |
| Cards | One reusable `.card` pattern, visually adequate for MVP public content. |
| Buttons | One generic button style in styled surfaces; identity/account use browser defaults; no primary/secondary/destructive system. |
| Inputs | Generic input/textarea rules; no shared select styling; width rules cause mobile overflow on some admin forms. |
| Tables | Basic full-width collapse only; no responsive wrapper, sticky header, card transform or contained horizontal-scroll pattern. |
| Alerts | `.notice` plus basic status/error text; no coherent success/warning/error component system across public/auth/admin. |
| Badges/statuses | Minimal bold `.status`; no reusable badge/status vocabulary. |
| Navigation | Separate public and admin shells; no unified authenticated/account navigation, no active-state pattern, identity/account screens have no shell. |
| Pagination | Public has a simple local pattern; administrator pages call framework pagination rendering without a dedicated Oteryn pagination component/style contract. Stress-state visual acceptance must remain part of the follow-up. |
| Responsive breakpoints | None in the current stylesheet. Flex wrapping exists, but there is no breakpoint strategy. |

## Screenshot evidence index

The complete artifact contains 71 full-page PNGs plus a contact sheet and machine-readable JSON. Representative required coverage includes:

- Homepage / search: `home-desktop.png`, `home-mobile.png`.
- News: `news-list-*`, `news-detail-*`, `news-empty-state-*`.
- Online: `online-*`, `online-dependency-failure-503-*`.
- Highscores: `highscores-desktop.png`, `highscores-tablet.png`, `highscores-mobile.png`.
- Servers: `servers-*`, `servers-runtime-dependency-failure-*`.
- Character: `character-detail-*`; search interaction is represented by Home/search plus detail/not-found outcomes.
- Guild: `guild-detail-*`.
- Login/register: `login-*`, `registration-*`, validation-error variants.
- Password recovery/reset/change: `password-recovery-*`, `password-reset-*`, `password-change-*`.
- MFA: `mfa-challenge-*`, `mfa-settings-*`, `mfa-enrollment-*`, `mfa-recovery-codes-desktop.png`.
- Account/character: `character-creation-*`; no account-dashboard/provisioning-status screen exists to capture.
- Admin: `admin-dashboard-*`, `admin-role-management-*`.
- CMS: `admin-news-list-*`, `admin-news-form-*`, `admin-managed-pages-list-*`, `admin-managed-page-form-*`.
- Audit: `admin-audit-log-*`.
- Errors: `authorization-denied-403-*`, `not-found-404-*`, `online-dependency-failure-503-*`.

## Exact remaining visual / UX blockers

1. **Unified navigation and authenticated orientation:** add discoverable Login/Register/Account entry points, a coherent account/settings shell, logout, admin/account return paths and active navigation state.
2. **Identity/account presentation:** bring Login/Register/password/MFA/character-create screens into a consistent Oteryn UI shell with production-grade hierarchy and action styling.
3. **Responsive overflow:** eliminate document-level overflow on all evidenced public/admin/MFA tablet/mobile surfaces and provide an intentional responsive table strategy.
4. **Long-content resilience:** safely wrap/break long names, emails, channel names, messages, audit metadata and provisioning data without widening the viewport.
5. **MFA enrollment UX:** prevent URI overflow and provide a practical setup affordance such as QR/copy/manual-secret hierarchy while preserving secure semantics.
6. **Error recovery:** replace dead-end framework 403/404/503 presentation with CSP-compatible Oteryn error views containing safe recovery actions and no technical leakage.
7. **Account/provisioning state UX:** provide a coherent user-facing account surface and explicit provisioning status/retry/support path; any required controller/read-model work must be a separate bounded account task rather than hidden inside CSS cleanup.
8. **Administrator small-screen UX:** make CMS/RBAC/audit tables and forms usable on tablet/mobile; improve destructive/action hierarchy and audit metadata readability.
9. **Design-system foundation:** establish reusable tokens/components/patterns for typography, spacing, buttons, inputs/selects, alerts, badges/statuses, navigation, pagination and responsive breakpoints.
10. **Empty-state consistency:** provide actionable empty states for administrator lists and verify all important public/admin empty branches with browser evidence.

## Acceptance gate

Visual / UX Acceptance remains **FAIL** until the bounded UI/UX launch-readiness follow-up is implemented and the browser acceptance harness is rerun with:

- no unexplained document-level horizontal overflow at required viewports;
- coherent public/auth/account/admin navigation;
- CSP-compatible product error pages with recovery actions;
- production-grade identity/account/MFA presentation;
- table and long-content resilience;
- preserved form labels, keyboard access, visible focus and safe failure messaging;
- updated screenshot evidence and per-surface reclassification.
