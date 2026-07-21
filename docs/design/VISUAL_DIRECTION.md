# Oteryn Platform Visual Direction

## Status

Durable visual direction for the Public Game Portal, Account Center and Admin Console.

This document defines the target design language. It does not claim that the current UI implements it, and it does not change the current Visual / UX Acceptance result.

## North star

**Modern dark-fantasy MMORPG portal.**

Oteryn should immediately read as a portal to a persistent MMORPG world, not as:

- a generic SaaS dashboard;
- a Laravel starter application;
- a developer/admin tool exposed to players;
- a copied Tibia/MyAAC theme;
- a neon cyberpunk interface.

The visual system should combine atmospheric world identity with modern readability, responsive behavior and accessible interaction patterns.

## Core visual qualities

### Atmospheric, not cluttered

Use a dark atmospheric outer canvas with restrained fantasy cues:

- subtle stone, parchment, smoke, landscape or world motifs;
- low-frequency background texture rather than noisy repeating patterns;
- artwork concentrated in hero/header moments rather than behind every paragraph;
- clear separation between decoration and functional UI.

### Premium game portal, not retro imitation

The visual language may evoke classic MMORPG portals through hierarchy and composition, but should use:

- contemporary spacing;
- readable type sizes;
- responsive widths;
- disciplined component reuse;
- fewer decorative borders;
- stronger content hierarchy;
- predictable interaction states.

### Warm dark palette

The palette is anchored in charcoal/blackened neutral surfaces, warm ivory text and restrained bronze/gold accents.

Avoid default SaaS blue as the primary brand signal. Blue may exist as an informational semantic color only.

### Functional content remains dominant

Fantasy framing must never reduce the usability of:

- forms;
- tables;
- account/security actions;
- error states;
- admin data management;
- keyboard focus;
- small-screen layouts.

## Three visual systems

### 1. Public Game Portal

Character:

- strongest fantasy/world identity;
- atmospheric header or hero region;
- warm accent details;
- readable elevated content panels;
- optional contextual side/utility surfaces on wide screens;
- clear world-status and character-search affordances.

The portal should feel like entering the Oteryn world before the user reads any copy.

### 2. Account Center

Character:

- visibly part of the same Oteryn ecosystem;
- reduced artwork density compared with Home;
- strong orientation and security hierarchy;
- quieter backgrounds around forms;
- focused operation cards/panels;
- no generic standalone browser-default pages.

Authentication and security flows should feel trustworthy and deliberate rather than decorative. Fantasy styling is restrained around sensitive forms.

### 3. Admin Console

Character:

- professional and neutral;
- denser information layout;
- shared typography, spacing, semantic colors and component tokens;
- minimal fantasy artwork in the working area;
- clear sidebar/drawer structure;
- strong table/form/action ergonomics.

The Admin Console may retain Oteryn brand marks and bronze accent details, but content management must take priority over atmosphere.

## Composition direction

### Outer page canvas

Use a dark global background. On public pages, optional subtle atmospheric imagery can occupy the outer canvas or header region.

The content layer sits above it on readable surfaces with sufficient contrast.

### Content width

Avoid the current narrow `70rem`-style universal constraint as the only layout model.

Target behavior:

- readable prose stays comfortably bounded;
- wide data pages can use substantially more horizontal space;
- 1440p and ultrawide screens gain useful breathing room without stretching text lines excessively;
- tables and admin views may use wider containers than News prose.

### Borders and elevation

Use one coherent hierarchy:

1. base surface;
2. elevated card/panel;
3. interactive/selected state.

Avoid many unrelated border styles. Decorative fantasy borders should be rare and reserved for brand-level moments, not every form field.

## Header and hero direction

### Public world header

The public header may include:

- Oteryn wordmark/logo area;
- atmospheric fantasy artwork or texture;
- concise world identity statement;
- state-aware account CTA.

The navigation itself remains structurally separate and highly readable.

The header must also work with no custom artwork. A gradient-free or restrained textured fallback using design tokens is required so frontend implementation is not blocked by art production.

### Homepage hero

The hero should prioritize:

1. Oteryn identity;
2. one primary action;
3. one secondary action;
4. concise world context.

Do not fill the hero with unverified rates, events, client version or gameplay claims.

## Fantasy asset strategy

### UI assets

The frontend implementation may create and maintain:

- CSS layout and tokens;
- buttons;
- cards;
- panels;
- borders;
- simple icons;
- simple original SVG ornaments;
- navigation treatments;
- status indicators;
- dividers;
- form controls.

These are part of the application UI system.

### Art and branding assets

Separate asset pipeline:

- Oteryn logo/wordmark artwork;
- hero illustration;
- world/environment illustration;
- character/monster artwork;
- decorative fantasy scene backgrounds.

No asset from Tibia, RubinOT, Medivia, TibiaScape, MyAAC themes or another game/service may be copied into Oteryn.

Reference products are interaction/IA inspiration only.

### Placeholder-safe requirement

All core layouts must remain production-usable without final artwork.

Fallback composition should use:

- tokenized background colors;
- subtle CSS gradients only where restrained and accessible;
- optional original geometric/ornamental SVG;
- safe aspect-ratio containers for future art;
- no empty giant hero gaps awaiting an image.

## Typography direction

### Brand/display

A distinctive display face may later be introduced for the Oteryn wordmark or major hero title, but the production UI must not depend on an unlicensed/custom font.

Default implementation should use a robust system-oriented serif/sans pairing or a single high-quality system sans stack.

Recommended functional direction:

- brand/display: optional restrained fantasy serif or original wordmark asset;
- interface/body: modern sans/system stack;
- tables/admin: same interface family for density and clarity.

### Hierarchy

- Hero/display: expressive but sparse.
- Page title: strong, compact hierarchy.
- Section title: clear separation without oversized marketing typography.
- Body: readable at normal browser zoom.
- Meta/table text: smaller but never microscopic.

## Interaction tone

### Primary actions

Warm bronze/gold accent with high-contrast text.

Examples:

- Create Account;
- Sign In;
- Create Character;
- Save/Publish.

### Secondary actions

Quieter surface/border treatment.

Examples:

- Cancel;
- Back;
- View details;
- Return to portal.

### Destructive actions

Semantic danger styling distinct from primary accent.

Examples:

- Disable MFA;
- Remove role.

Never rely on color alone; label and placement must communicate consequence.

## Status language

World/account/system status should use a shared pattern:

- icon or shape;
- status label;
- optional short explanation;
- semantic color as reinforcement only.

Examples:

- Online;
- Unknown;
- Pending;
- Ready;
- Conflict;
- Unavailable.

Do not use a green/red dot without text.

## Public portal visual motifs

Preferred motifs:

- aged bronze;
- dark iron;
- warm parchment/ivory text accents;
- stone or dark wood used sparingly;
- subtle heraldic lines;
- atmospheric fog/landscape silhouettes.

Avoid:

- heavy leather/wood borders around every card;
- excessive bevels;
- fake medieval unreadable fonts;
- bright neon glows;
- high-opacity textures behind body text;
- multiple competing gold tones;
- constant animation.

## Account Center visual motifs

The Account Center should reduce visual noise and emphasize state:

- account summary cards;
- security status rows;
- clear next actions;
- focused forms in bounded panels;
- restrained decorative header.

Sensitive content such as MFA secrets/recovery codes should use dedicated secure-information containers, clear warning language and practical copy/save affordances.

## Admin visual motifs

The Admin Console should use:

- neutral dark surfaces;
- compact navigation;
- strong selected states;
- semantic badges;
- deliberate table density;
- sticky/local action regions where useful;
- collapsible navigation on smaller screens.

Do not use hero artwork inside list/edit/audit work areas.

## Motion

Motion is optional and secondary.

Rules:

- no required information depends on animation;
- transitions are short and functional;
- respect `prefers-reduced-motion`;
- avoid parallax or continuous decorative movement in core portal navigation/forms;
- loading indicators must not be the only state communication.

## Accessibility visual rules

- Visible `:focus-visible` treatment is a first-class component state.
- Primary text and interactive text must meet applicable contrast requirements.
- Secondary text cannot become low-contrast decoration.
- Status is never communicated only by hue.
- Text must remain readable over artwork through overlays/surface separation.
- Browser zoom and text enlargement must not cause document-level horizontal overflow.

## Visual acceptance principle

The target direction is successful only when the implemented UI remains coherent across:

- desktop;
- intentional tablet composition;
- mobile;
- empty states;
- validation states;
- authorization denial;
- dependency failures;
- long names/content;
- keyboard navigation;
- reduced motion.

A visually impressive homepage does not compensate for broken Account, Admin, error or responsive states.
