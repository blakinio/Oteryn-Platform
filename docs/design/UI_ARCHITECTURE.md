# Oteryn Platform UI Architecture

## Status

Target UI architecture for the production-ready Oteryn frontend.

This document defines shells, composition rules, shared presentation boundaries, state architecture and per-surface blueprints. It does not implement the redesign and does not change Visual / UX Acceptance from its current failing state.

## Architecture goals

- Make the public product unmistakably an Oteryn MMORPG world portal.
- Keep Identity and Account flows inside the same product ecosystem.
- Give Account operations clear orientation without weakening security boundaries.
- Keep Admin operationally efficient and visually distinct from the public portal.
- Preserve Blade/server-rendered architecture unless a separate ADR changes that direction.
- Eliminate document-level responsive overflow through explicit component/layout contracts.
- Treat empty/error/dependency states as first-class product surfaces.
- Allow future Community/Game services to be added without rebuilding the entire navigation model.

## Shell architecture

### Public Portal Shell

Purpose:

- brand/world identity;
- public navigation;
- public content;
- optional world status and Character Search utilities;
- guest/authenticated Account entry.

Wide-desktop concept:

```text
┌────────────────────────────────────────────────────────────────────┐
│ OTERYN WORLD HEADER / BRAND / OPTIONAL ATMOSPHERIC ART             │
│                                                     Account action │
└────────────────────────────────────────────────────────────────────┘
┌────────────────────────────────────────────────────────────────────┐
│ News        Community        Game                                  │
└────────────────────────────────────────────────────────────────────┘

┌───────────────┬─────────────────────────────────┬──────────────────┐
│ Context nav   │                                 │ World / Search   │
│ when useful   │          MAIN CONTENT           │ utility rail     │
│               │                                 │ when useful      │
└───────────────┴─────────────────────────────────┴──────────────────┘

┌────────────────────────────────────────────────────────────────────┐
│ Footer                                                             │
└────────────────────────────────────────────────────────────────────┘
```

Rules:

- three columns are optional, never universal;
- main content remains dominant;
- table-heavy pages may use full/wide content and omit the utility rail;
- context navigation appears only when there are real sibling destinations;
- Home is reached via the Oteryn brand;
- future-ready links remain absent until implemented.

### Identity Shell

Purpose:

- registration;
- login;
- password recovery/reset;
- MFA challenge.

Composition:

```text
Compact Oteryn portal header

Focused identity panel
- page title
- short context
- form
- validation/status
- primary action
- real secondary navigation

Minimal portal footer
```

Rules:

- retain Oteryn branding;
- reduce decorative art around security forms;
- provide real cross-navigation such as Login ↔ Register and Login → Password Recovery;
- MFA challenge clearly explains the second-factor step without exposing security internals;
- do not provide shortcuts that bypass authentication/MFA policy.

### Account Center Shell

Target shell; not currently delivered as a general route.

Purpose:

- account orientation;
- status visibility;
- characters entry points;
- security entry points;
- focused account operation context.

Composition:

```text
Public Portal header/navigation

Account section navigation        Account main content
Overview                           status / characters / security
Characters
Security
Settings [only when delivered]
```

Rules:

- Account Center is part of the portal, not a separate SaaS application;
- `Overview`/provisioning status requires a separately bounded dependency if current controllers/read models cannot supply authoritative state;
- focused operations such as Change Password, MFA and Character Creation remain separate screens using this context;
- no fictional character list or provisioning state may be rendered.

### Admin Shell

Purpose:

- privileged content/access/audit operations;
- high-density data management.

Wide-desktop concept:

```text
┌──────────────────┬─────────────────────────────────────────────────┐
│ OTERYN ADMIN     │ Page title                         Page action  │
│ Dashboard        ├─────────────────────────────────────────────────┤
│ Content          │                                                 │
│  News            │                WORKSPACE                        │
│  Managed Pages   │                                                 │
│ Access           │                                                 │
│  Roles           │                                                 │
│ Operations       │                                                 │
│  Audit Log       │                                                 │
│                  │                                                 │
│ Public site      │                                                 │
│ Account / Logout │                                                 │
└──────────────────┴─────────────────────────────────────────────────┘
```

Rules:

- shared design tokens but reduced fantasy decoration;
- only authorized/current navigation items render;
- active state is explicit;
- no wildcard implication from `platform_admin` UI;
- small screens use a drawer rather than squeezing a persistent sidebar;
- Admin actions reuse domain authorization and never rely on hidden UI for security.

## Shared layout primitives

### AppFrame

Global outer canvas and shell boundary.

Variants:

- public;
- identity/account;
- admin;
- error.

### PageContainer

Width variants:

- prose;
- standard;
- wide;
- admin.

### PageHeader

Contains:

- H1;
- optional description;
- optional metadata;
- primary/secondary action group.

### ContextNav

Used only for real sibling destinations.

Desktop: sidebar or local horizontal nav.
Tablet/mobile: collapsible selector/drawer.

### UtilityRail

Optional wide-desktop region for:

- World Status;
- Character Search;
- contextual quick links.

Disappears/reflows before main content becomes narrow.

### DataRegion

Owns tables/lists, empty state, pagination and dependency state.

### FocusedOperation

Narrow, high-attention composition for:

- auth;
- password;
- MFA;
- character creation;
- destructive/security operations.

## State architecture

Every data-bearing surface selects one state at a time:

```text
Loading (only if asynchronous behavior exists)
Ready with data
Ready empty
Validation/action error
Authorization denied
Dependency unavailable
Unexpected error
```

Server-rendered pages must not invent a loading skeleton where there is no asynchronous loading phase.

### Empty state contract

An empty state contains:

- clear statement of what is absent;
- reason/context only when authoritative;
- one real next action when available.

Examples:

- no published news: return to Home or simply explain none are published;
- no online players: state that no players are currently listed, not that the server is offline;
- no highscores result: explain no result for the active filter/query;
- no characters: Account Center target may offer Create Character once the list surface exists;
- empty Admin content list: offer Create only when the user is authorized.

### Dependency state contract

Distinguish:

- authoritative empty data;
- runtime state unknown;
- data dependency unavailable;
- request-level service unavailable.

Do not convert dependency failure into zero players/offline/no results.

### Error surface contract

Product-owned templates:

- 403 — access denied; actions: nearest safe Home/Account/Admin destination.
- 404 — not found; actions: Home plus contextual Character Search/News where appropriate.
- 419 — page/session expired; action: safely reload/restart the operation or Login where applicable.
- 429 — too many requests; action: wait and retry; do not expose internal throttle keys.
- 500 — unexpected problem; safe retry/Home path.
- 503 — service/dependency unavailable; retry/Home path.

Technical details remain hidden. Shell retention depends on safety and context, but Oteryn branding and recovery navigation should remain whenever practical.

## Surface Blueprint — Public Portal

| Surface | Shell | Navigation context | Primary content | Secondary content | Primary CTA | Secondary CTA | Responsive behavior | Important states | Shared components |
|---|---|---|---|---|---|---|---|---|---|
| Home | Public | Global | Hero/world identity, latest news, authoritative world snapshot | Character Search, optional community snapshot | Create Account for guest or real state-aware account action | News / Servers / other real route | Wide hero + optional utilities; one-column mobile | no news; runtime unknown/unavailable | Hero, Button, ServerStatus, Card, CharacterSearch, SectionHeader, EmptyState |
| Character Search interaction | Public | Community utility | Search form resolving current character lookup behavior | Search guidance | Search | Return Home/Community | Compact form; full-width field on mobile | required-field validation; character not found | Input, Button, Alert/ErrorState |
| News list | Public | News | Published news cards/list | Pagination | Open article | Home | Standard/prose width; card stack mobile | no published news | Card, SectionHeader, Pagination, EmptyState |
| News detail | Public | News / breadcrumb | Article title, metadata, escaped body | Related navigation only if real | Back to News | Home | Prose width; artwork must not reduce readability | missing/unpublished -> 404 | Prose, Breadcrumb, ErrorState |
| Online | Public | Community | Current authoritative online listing | Count/summary only if authoritative | View character | Character Search | Full list desktop; reduced rows/cards mobile | no online players; data dependency 503 | DataRegion, Table/RowCard, EmptyState, ErrorState |
| Highscores | Public | Community | Highscores table | Filters only if current behavior supports them; pagination where delivered | View character where linked | Character Search | Full table desktop; reduced/local-scroll or row pattern tablet/mobile | no result; dependency failure | Table, Pagination, EmptyState, ErrorState |
| Servers | Public | Game | Authoritative server/channel runtime status | World information only when authoritative | Refresh/retry only if real | Home | Wide cards desktop; stacked cards tablet/mobile; long text wraps | online/unknown/unavailable; dependency failure | ServerStatus, StatusIndicator, Alert, ErrorState |
| Character detail | Public | Community / Character Search | Public allowlisted character data | Contextual guild link if current data supports it | Search another character | Home | Definition/list content stacks naturally | character not found -> 404 | Panel, DefinitionList pattern, Breadcrumb, ErrorState |
| Guild detail | Public | Community contextual | Guild metadata and member roster | Character links where available | View member character | Character Search/Home | Full table desktop; reduced/table-to-card mobile | guild not found; no members | Table, RowCard, EmptyState, ErrorState |
| Managed public page | Public | Context depends on linked content | Published escaped managed content | Optional page-specific metadata if current | Contextual real link only | Home | Prose/standard width | missing/unpublished -> 404 | Prose, Breadcrumb, ErrorState |

## Surface Blueprint — Identity and Account

| Surface | Shell | Navigation context | Primary content | Secondary content | Primary CTA | Secondary CTA | Responsive behavior | Important states | Shared components |
|---|---|---|---|---|---|---|---|---|---|
| Registration | Identity | Guest Account | Registration form | Privacy/security guidance only if real copy exists | Create Account | Sign In / Home | Focused panel; full-width fields mobile | validation; generic registration completion; provisioning remains non-visible unless dependency surface added | Input, Button, Alert, FocusedOperation |
| Login | Identity | Guest Account | Email/password form | Recovery/Register links | Sign In | Forgot Password / Create Account / Home | Focused panel | invalid credentials; disabled/stale auth behavior remains server-owned | Input, Button, Alert |
| Password recovery request | Identity | Guest Account | Email recovery form | Anti-enumeration-safe explanation | Send Recovery Link | Back to Sign In | Focused panel | generic response; mail unavailable according to current backend behavior | Input, Button, Alert |
| Password reset | Identity | Guest Account | Token-authorized new password form | Expiry guidance | Reset Password | Back to Sign In | Focused panel | invalid/expired token; validation | Input, Button, Alert |
| Password change | Account/Focused | Security | Current/new password form | Session-revocation explanation | Change Password | Return to real Account/Security parent once delivered; otherwise safe portal destination | Narrow one-column form | incorrect current password; validation; successful revocation/logout | Input, Button, Alert |
| MFA challenge | Identity | Pending login | TOTP/recovery-code challenge | concise recovery-code guidance | Verify | Cancel/return to Login where safely supported | Narrow focused panel | invalid code; replay; consumed code; pending-login expiry | Input, Button, Alert |
| MFA settings — not enabled | Account/Focused | Security | MFA status and enrollment entry | explanation | Enable MFA | Return to Security/portal | Stacked mobile | not enabled | StatusIndicator, Button, Panel |
| MFA enrollment/confirmation | Account/Focused | Security | manual secret/QR/provisioning setup and confirmation form | authenticator setup guidance | Confirm MFA | Cancel/return to MFA settings | Secure data block bounded; QR scales; no URI document overflow | invalid password/TOTP; long provisioning URI | SecureInformation, Input, Button, Alert |
| MFA recovery codes | Account/Focused | Security | one-time recovery codes | save/store warning | Continue / acknowledge according to real flow | Return to MFA settings | Codes wrap/stack; bounded container | one-time reveal | SecureInformation, Alert, Button |
| MFA settings — confirmed | Account/Focused | Security | Enabled status and disable operation | security consequences | Context-dependent normal action | Disable MFA as distinct danger action | Destructive section separated on mobile/desktop | invalid confirmation; session revocation on success | StatusIndicator, Button danger, Alert |
| Character creation | Account/Focused | Characters target context | Character creation fields | starter-policy guidance based only on current validated options | Create Character | Return to real Characters/Account parent once delivered | One-column mobile; labels replace raw numeric display values | validation; pending binding; quota; duplicate/conflict; dependency failure | Input, Select, Button, Alert |

### Missing target Account surfaces

These are not current screens and must not be represented as delivered:

| Target | Status | Requirement before implementation claim |
|---|---|---|
| Account Overview | `DEPENDENCY` | bounded route/controller/read model exposing authoritative Platform Identity/MFA/binding status |
| Provisioning status/recovery | `DEPENDENCY` | authoritative pending/ready/conflict read semantics plus approved retry/support behavior; UI must not invent mutation semantics |
| Character list | `DEPENDENCY` | authoritative user-scoped character read path tied to trusted ready binding |
| Session management | `FUTURE-READY` | explicit Identity/session feature task and security policy |
| General Settings | `FUTURE-READY` | real settings/domain ownership |

## Surface Blueprint — Admin Console

| Surface | Shell | Navigation context | Primary content | Secondary content | Primary CTA | Secondary CTA | Responsive behavior | Important states | Shared components |
|---|---|---|---|---|---|---|---|---|---|
| Admin dashboard | Admin | Dashboard | available admin entry points/summary | Public site/account access | Context-specific real action | Public site | Sidebar wide; drawer tablet/mobile | authorization denied | AdminNav, Card/Panel, ErrorState |
| News list | Admin | Content > News | News records table/list | Pagination/status | Create News | Edit item | Full table desktop; reduced/card rows mobile | empty list; flash success/error | Table, Pagination, EmptyState, Badge, Button |
| News create/edit | Admin | Content > News | Plain-text CMS form | publication state | Save/Create | Cancel/back to list | Readable form width; actions stack mobile | validation; authorization; action result | Input, Textarea, Checkbox/Select as real form requires, Alert, Button |
| Managed pages list | Admin | Content > Managed Pages | Managed-page table/list | Pagination/status | Create Page | Edit item | Full table desktop; reduced/card rows mobile | empty list; flash states | Table, Pagination, EmptyState, Badge |
| Managed page create/edit | Admin | Content > Managed Pages | Plain-text managed-page form | publication state | Save/Create | Cancel/back | Readable form width | validation; authorization; action result | Input, Textarea, Alert, Button |
| Role management | Admin | Access > Roles | Identity/role assignments and actions | role/permission context based on current data | Assign Role | Remove Role as danger | Dense table desktop; summary/detail or local scroll tablet; grouped row actions mobile | no identities; last-platform-admin protection; authorization errors | Table, Badge, Select, Button, Alert, Disclosure |
| Audit log | Admin | Operations > Audit | bounded audit records | metadata detail | View details/disclose | Pagination | Full table desktop; summary/detail tablet/mobile | empty audit log; authorization denied | Table, Pagination, Disclosure, Code/Metadata block, EmptyState |

## Delivered cross-cutting state blueprints

### Validation state

Applies to Registration, Login, Password, MFA, Character Creation and Admin forms.

- keep entered non-secret values when framework behavior safely supports it;
- show summary when multiple errors exist;
- show field-adjacent errors where practical;
- focus should move predictably without trapping keyboard users;
- never echo passwords, MFA secrets or recovery codes into validation output.

### Invalid login state

- same public error semantics for known/unknown identity according to existing anti-enumeration policy;
- no account existence hints;
- retain Login shell and recovery/register links.

### MFA invalid/replay state

- retain MFA challenge context;
- safe generic invalid-factor message;
- no timestep/replay internals exposed.

### News empty

- retain Public shell;
- explicit no-published-news message;
- no empty blank card grid.

### Online empty

- state that no players are listed online;
- do not infer server unavailable.

### Highscores empty

- clear no-results message tied to the current query/filter semantics.

### Guild no-members

- retain Guild detail header/metadata;
- replace empty table with explicit roster empty state.

### Servers runtime unknown/unavailable

- retain Public shell;
- use explicit `Unknown`/`Unavailable` status language;
- do not present stale/missing data as Online/Offline without authoritative semantics.

### Public data dependency 503

- use branded 503 error surface;
- no SQL/framework/stack detail;
- offer retry and Home.

### Authorization denied 403

- use appropriate Public/Account/Admin-safe shell where possible;
- explain lack of access without revealing protected resource detail;
- offer safe navigation.

### Not found 404

- branded recovery surface;
- contextual search action when useful, especially Character lookup;
- no framework-default dead end.

### Admin mutation success/error flash

- success and failure use distinct semantic Alert variants;
- message appears in consistent shell region;
- status remains readable without color.

## Security presentation boundaries

UI architecture must preserve these rules:

- auth/session/MFA logic remains server-owned;
- UI never treats a hidden button as authorization;
- Admin navigation is permission-aware but middleware/policies remain authoritative;
- CSRF protection remains on all state-changing browser requests;
- logout remains a state-changing action;
- account/character ownership is never derived from client-provided IDs;
- MFA secrets/recovery codes are not logged or placed in decorative third-party assets;
- provisioning status is only shown from authoritative Platform state.

## Accessibility architecture

Every shell provides:

- `header`, `nav`, `main`, `footer` landmarks where applicable;
- logical H1/H2/H3 hierarchy;
- skip-to-main-content link;
- visible focus;
- keyboard-operable navigation and disclosures;
- correctly associated labels;
- text labels for status;
- reduced-motion support;
- no required hover-only interaction.

Mobile drawers and menus must manage focus correctly when enhanced with JavaScript.

## Implementation sequence

Do not implement the redesign in one large PR.

### Slice 1 — Design foundation

- semantic design tokens;
- base typography/spacing;
- Button, Input, Select, Alert, Badge, Status, Card/Panel, Table wrapper, Pagination primitives;
- Public shell;
- responsive primary navigation;
- active navigation state.

Validation:

- Home/News smoke at desktop/tablet/mobile;
- keyboard/focus smoke;
- no document overflow.

### Slice 2 — Public portal

- Home;
- News list/detail;
- Online;
- Highscores;
- Servers;
- Character Search interaction;
- Character detail;
- Guild detail;
- Managed public pages;
- table/long-content resilience.

Do not add Guilds index, Downloads or Game Info routes as fake delivered features.

### Slice 3 — Identity / Account shell

- Login;
- Registration;
- Password recovery/reset/change;
- MFA challenge/settings/enrollment/recovery presentation;
- coherent Account/Security navigation using only real destinations.

If Account Overview/provisioning status needs new backend/read-model work, create the dependency task rather than hiding it in this slice.

### Slice 4 — Game Account / Characters

- Account Overview only after dependency is available;
- authoritative provisioning status;
- character list only after authoritative user-scoped read path exists;
- character creation presentation and post-create orientation.

### Slice 5 — Admin shell

- Admin navigation/sidebar/drawer;
- Dashboard;
- News CMS;
- Managed Pages;
- Roles;
- Audit;
- responsive table/action patterns.

### Slice 6 — Error / Empty / Responsive polish

- branded 403/404/419/429/500/503;
- empty states;
- dependency failures;
- long-content fixtures;
- remaining mobile/tablet defects.

### Slice 7 — Final Visual Acceptance

- full Playwright screenshot pass;
- desktop `1440x1000`;
- tablet `768x1024` where materially distinct;
- mobile `390x844`;
- recommended wide-desktop/ultrawide shell smoke;
- accessibility smoke;
- Visual / UX Acceptance Matrix reconciliation.

Visual / UX Acceptance may become PASS only after exact-final-SHA evidence removes every remaining `UX_BLOCKER`/`VISUAL_BLOCKER`.

## Architecture Decision

```text
Public Portal:
Modern dark-fantasy MMORPG branded shell.

Account Center:
Part of the same Oteryn portal ecosystem, with focused account/security flows.

Admin:
Separate professional application shell using shared design tokens.

Primary information-architecture inspiration:
Tibia-style MMORPG portal model.

Modern homepage inspiration:
TibiaScape / modern OTS portals.

Scalability inspiration:
Medivia-style separation of Account, Community and Game services.

Implementation principle:
Inspired by interaction patterns only; no copied proprietary branding or assets.
```
