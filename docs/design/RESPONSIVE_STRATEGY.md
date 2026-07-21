# Oteryn Platform Responsive Strategy

## Status

Target responsive composition for Public Portal, Account Center and Admin Console.

The current stylesheet has no explicit responsive breakpoint system and current acceptance evidence includes document-level overflow on multiple mobile/tablet surfaces. This document defines the implementation contract required to eliminate those failures.

## Principles

1. Mobile is a recomposed interface, not a scaled-down desktop portal.
2. Tablet is an intentional layout state, not an accidental interpolation.
3. Components may scroll locally when appropriate; the document must not scroll horizontally.
4. Tables choose a deliberate responsive pattern per dataset.
5. Long user/game/admin content must wrap, truncate with access to full value, or scroll inside a bounded component.
6. Navigation remains keyboard and focus accessible at every breakpoint.
7. Artwork and utility rails disappear before main content becomes too narrow.

## Breakpoint model

Implementation may tune exact values after browser evidence, but use the following semantic ranges as the starting contract:

```text
Mobile compact     < 640px
Mobile wide        640px–767px
Tablet             768px–1023px
Desktop            1024px–1279px
Wide desktop       >= 1280px
```

CSS should prefer content-driven behavior and `clamp()`/grid/flex constraints over breakpoint proliferation.

No screen should depend on exact device names.

## Global containment rules

Every shell must enforce:

- `min-width: 0` on grid/flex children that contain text/tables;
- media/images constrained to container width;
- long strings allowed to wrap or locally scroll;
- form controls with `max-width: 100%` and no viewport-breaking `min-width`;
- tables inside explicit responsive containers when they remain tabular;
- code/URI/JSON blocks using bounded overflow;
- no fixed desktop width on primary page regions.

The acceptance criterion is `document.documentElement.scrollWidth <= viewport width` for required viewports unless an explicitly documented browser quirk is proven.

## Public Portal composition

### Wide desktop — >= 1280px

Preferred composition for suitable pages:

```text
World header / brand
Primary navigation

Context nav     Main content                         Utility rail
(optional)      dominant column                      world/search

Footer
```

Use a 12-column layout conceptually:

- context navigation: 2–2.5 columns when useful;
- main: 6.5–8 columns;
- utility: 2.5–3 columns when useful.

This is not mandatory on every surface.

Table-heavy pages such as Highscores may use:

```text
Context nav (optional)     Wide main table/content
```

with the utility rail moved below or omitted.

### Desktop — 1024px–1279px

Use two columns at most:

- main + one contextual/utility column;
- or full-width main.

Priority order for removal:

1. decorative art width;
2. right utility rail;
3. left context navigation becomes compact/dropdown/drawer;
4. main content remains readable.

Never squeeze all three desktop columns into this range.

### Tablet — 768px–1023px

Intentional tablet composition:

```text
Compact header + primary navigation trigger
Main content
Optional inline context navigation
World status / Character Search utility cards
```

Rules:

- one dominant content column;
- optional secondary column only for short, non-table utility content when real measured width permits;
- contextual navigation becomes horizontal chips/tabs only when the set is short, otherwise collapsible/drawer;
- wide tables use reduced columns or local scroll;
- no persistent right utility rail;
- Account and Admin sidebars collapse.

### Mobile — < 768px

Target shell:

```text
[ OTERYN ]                         [ Account ] [ Menu ]

Page heading / primary content

World status card when relevant
Character Search when relevant
Contextual content

Footer
```

Rules:

- single content column;
- primary navigation in an accessible drawer;
- Account CTA remains visible or is the first clearly separated drawer group;
- no desktop sidebar rendered beside content;
- utility cards move into the content flow;
- primary actions may become full-width;
- multi-action toolbars wrap vertically or to two rows.

## Public navigation behavior

### Desktop

- brand/home visible;
- News, Community and Game visible;
- Account state visible;
- dropdown/popover menus, if used, are keyboard operable and do not require hover.

### Tablet/mobile

Use a menu/drawer with:

```text
Public
- Home
- News

Community
- Character Search
- Online
- Highscores

Game
- Servers

Account
- Login / Register for guests
- real authenticated destinations only for signed-in users
```

Future-ready destinations are omitted until delivered.

Drawer requirements:

- logical focus order;
- focus moves into the opened drawer;
- Escape closes where JavaScript enhancement exists;
- focus returns to the trigger;
- background interaction is prevented while modal drawer is open;
- no JavaScript-only path is required to reach essential navigation when a server-rendered fallback can be provided.

## Account Center composition

### Desktop

Target:

```text
Portal header/nav

Account navigation     Account content
                       optional status summary rail only when useful
```

Focused operations may narrow the content region while keeping account orientation.

### Tablet

- account navigation becomes top section navigation or collapsible menu;
- content remains one main column;
- security summaries stack below overview.

### Mobile

- compact portal header;
- account section selector near page heading;
- focused forms use full available width;
- security/destructive actions remain visually separated;
- recovery codes and MFA provisioning values are bounded inside the viewport.

## Admin Console composition

### Wide desktop

```text
Admin sidebar     Admin workspace
                  page header / actions
                  filters / table / form
```

The workspace may use the widest layout token.

### Desktop/tablet

- sidebar may narrow to icon+label compact form or collapse to drawer;
- page actions wrap beneath the title;
- tables use explicit responsive strategy;
- forms use a readable max width while list/table pages use full workspace width.

### Mobile

- admin navigation becomes drawer;
- page title and primary action stack;
- filters stack;
- actions become clearly grouped buttons/menus;
- dense tables use responsive rows or local scroll;
- raw audit metadata becomes a summary/detail pattern, not a full-width JSON column.

Admin mobile is a supported operational surface, not a promise that every desktop column remains simultaneously visible.

## Table strategy

There is no single rule for all tables. Choose one of the following documented patterns.

### Pattern A — full table + contained horizontal scroll

Use when column relationships matter and all columns are operationally important.

Requirements:

- wrapper has `overflow-x: auto`;
- wrapper is the only horizontally scrolling region;
- page/document width remains fixed;
- first column may be sticky only after accessibility/usability validation;
- visible cue indicates more content when necessary.

Good candidates:

- Admin audit on tablet if a summary/detail redesign is not yet implemented;
- complex RBAC table on tablet.

### Pattern B — reduced columns

Hide secondary columns at defined breakpoints while preserving access to full details through a row detail action.

Good candidates:

- Highscores;
- Online;
- Admin news/pages lists.

Columns may only be removed if critical information remains accessible.

### Pattern C — responsive row cards

Transform rows into labeled key/value blocks on narrow screens.

Good candidates:

- Character rows;
- Guild members;
- simple Online rows;
- compact Admin content lists.

Labels must remain visible; do not rely on column position after transformation.

### Pattern D — summary + detail disclosure

Show compact summary rows and open/expand detail metadata.

Good candidates:

- Audit log metadata;
- dense role assignments/actions.

The detail control must be keyboard accessible and screen-reader understandable.

## Per-surface table recommendation

| Surface | Desktop | Tablet | Mobile |
|---|---|---|---|
| Highscores | Full table | Reduced columns or contained scroll | Responsive rows or compact contained scroll |
| Online | Full/compact table or rows | Reduced columns | Responsive rows |
| Guild members | Full table | Reduced columns | Responsive member cards/rows |
| Admin news | Full table | Reduced columns | Responsive rows/cards |
| Admin managed pages | Full table | Reduced columns | Responsive rows/cards |
| Role management | Full dense table | Contained scroll or summary/detail | Summary/detail with grouped actions |
| Audit log | Full table with bounded metadata | Reduced summary + detail | Summary/detail; metadata in bounded disclosure |

Implementation must select and document the exact pattern in the slice PR rather than allowing default overflow.

## Long-content resilience

### Character, guild and identity names

Use:

- `overflow-wrap: anywhere` only in containers where breaking is preferable to overflow;
- natural wrapping for normal names;
- truncation only when a visible full-value affordance exists.

### Email addresses

Allow wrapping or `overflow-wrap: anywhere` in narrow admin/account rows.

### Server/channel names and runtime messages

- wrap inside status cards;
- no fixed-width status columns;
- preserve full authoritative text when practical.

### MFA provisioning URI/manual secret

- never render raw URI as an unbounded inline string;
- use a dedicated secure-information block;
- allow local horizontal scroll for machine-oriented URI if it must be shown;
- provide human-oriented manual secret/copy hierarchy where securely implemented;
- QR presentation must scale within the container.

### Audit metadata/JSON

- do not place unbounded raw JSON in a normal table cell;
- show concise metadata summary;
- use disclosure/detail panel;
- code block uses `white-space: pre-wrap` or local scroll according to content type.

## Form strategy

At all breakpoints:

- controls default to `width: 100%` within their field container;
- container controls the maximum readable form width;
- select/input/textarea share box sizing;
- action rows wrap;
- validation messages wrap;
- no control has a global `min-width` that can exceed the viewport.

Desktop forms may use two-column field groups only when fields are semantically related and remain readable.

Sensitive Account forms should generally remain one-column.

## Error pages

403/404/419/429/500/503 use responsive product-owned templates.

Mobile:

- concise code/title;
- message;
- stacked actions;
- no oversized decorative illustration pushing recovery below the fold.

Desktop:

- optional restrained artwork/brand mark;
- centered or shell-contained panel;
- real recovery actions.

## Empty and dependency states

Empty-state cards adapt to one column on mobile.

Dependency failure must not cause layout replacement with a framework page when a safe application-owned state can be rendered.

For request-level 503 responses, use the branded error template.

For partial data dependencies such as runtime status unknown, retain the normal shell and show an inline dependency-state component.

## Touch and pointer behavior

- Interactive targets should remain comfortably operable on touch screens.
- Hover cannot be the only way to expose essential actions.
- Table row actions should not require precise tiny icons on mobile.
- Dropdowns/disclosures must support click/tap and keyboard.

## Acceptance viewports

Minimum required screenshot evidence:

- desktop: `1440x1000`;
- tablet: `768x1024` for all materially distinct/table-heavy surfaces;
- mobile: `390x844`.

Add a wide-desktop/ultrawide smoke viewport for shell composition, recommended around `1920x1080`, to verify useful space utilization without over-stretching prose.

Breakpoint acceptance is based on behavior, not only screenshots at exact widths. Implementation should also probe widths around breakpoint transitions.

## Responsive Definition of Done

A slice is not responsive-ready until:

- no document-level horizontal overflow exists at required viewports;
- navigation is reachable and keyboard operable;
- tables use an intentional local strategy;
- long fixture values remain contained;
- primary actions remain visible/reachable;
- form controls stay within their container;
- text remains readable without browser zoom hacks;
- no essential content is hidden solely to make the layout fit;
- tablet behavior has been explicitly reviewed rather than inferred from desktop/mobile.
