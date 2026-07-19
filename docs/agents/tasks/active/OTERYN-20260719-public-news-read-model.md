# OTERYN-20260719 Public news read model

## Goal

Deliver the next bounded Phase 4 public website slice as a Platform-owned, read-only news display: persist news posts in the Platform database, expose published-only public list/detail routes through a dedicated CMS read/query boundary, and render plain text safely without introducing authoring, Admin/RBAC, uploads, Canary access or rich-HTML sanitization requirements.

## Acceptance criteria

- [x] Archive the merged `OTERYN-20260719-public-site-shell-and-search` task record under `docs/agents/tasks/archive/` with the exact historical blob unchanged.
- [ ] Add an authoritative Laravel migration for Platform-owned `news_posts` with unique slug, title, plain-text body and nullable publication timestamp.
- [ ] Add a narrow CMS public-news query service over the Platform database; do not access Canary or add a shared-data contract.
- [ ] Public list/detail reads expose only posts with `published_at IS NOT NULL` and `published_at <= read_time`; drafts and future-scheduled posts remain non-public.
- [ ] List ordering is deterministic by `published_at DESC`, then `id DESC`, with bounded pagination.
- [ ] Add `GET /news` and `GET /news/{slug}` public routes and a News link in the shared public navigation.
- [ ] Render title/body through escaped Blade output only; this task stores/renders plain text and must not introduce raw rich HTML or file/media uploads.
- [ ] Add focused feature/database coverage for published visibility, draft/scheduled exclusion, detail 404 semantics, deterministic ordering/pagination and XSS escaping.
- [ ] Update Phase 4/CMS project documentation without claiming CMS authoring, Admin/RBAC or live multichannel runtime availability are complete.
- [ ] Run repository CI and Agent Governance on the delivery-validation head; require a fresh exact-head pass after the final ready checkpoint before merge.

## Ownership

```yaml
owned_paths:
  - app/Cms/Models/NewsPost.php
  - app/Cms/PublicNewsQuery.php
  - app/Http/Controllers/Cms/PublicNewsController.php
  - database/migrations/2026_07_19_175500_create_news_posts_table.php
  - routes/web.php
  - resources/views/game/layout.blade.php
  - resources/views/news/index.blade.php
  - resources/views/news/show.blade.php
  - tests/Feature/Cms/PublicNewsTest.php
  - docs/architecture/ROADMAP.md
  - docs/architecture/MODULE_CATALOG.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260719-public-news-read-model.md
  - docs/agents/tasks/active/OTERYN-20260719-public-site-shell-and-search.md
  - docs/agents/tasks/archive/OTERYN-20260719-public-site-shell-and-search.md
modules:
  - CMS
  - public web
  - database
  - testing
  - agent-governance
dependencies:
  - OTERYN-20260719-public-site-shell-and-search
blockers:
  - none
cross_repository_tasks:
  - none
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-19T17:55:00+02:00
head: 49f641c8cb2f3548f80cb7e70ae041d4ff496314
branch: task/OTERYN-20260719-public-news-read-model
pr: none
status: investigating
context_routes:
  - agent-governance
  - architecture
  - web-cms
  - database
  - security
  - testing
owned_paths:
  - app/Cms/Models/NewsPost.php
  - app/Cms/PublicNewsQuery.php
  - app/Http/Controllers/Cms/PublicNewsController.php
  - database/migrations/2026_07_19_175500_create_news_posts_table.php
  - routes/web.php
  - resources/views/game/layout.blade.php
  - resources/views/news/index.blade.php
  - resources/views/news/show.blade.php
  - tests/Feature/Cms/PublicNewsTest.php
  - docs/architecture/ROADMAP.md
  - docs/architecture/MODULE_CATALOG.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260719-public-news-read-model.md
  - docs/agents/tasks/active/OTERYN-20260719-public-site-shell-and-search.md
  - docs/agents/tasks/archive/OTERYN-20260719-public-site-shell-and-search.md
proven:
  - Main was verified at fc50b92208de67a4630d994a8ad3923f2e1fa07e, the squash merge of PR #19, before starting this task.
  - Live GitHub PR search returned no open pull requests after PR #19 merged.
  - The merged public-site task archive uses blob b8fa780c198d579699fbf18828c95691fc57746a, exactly matching the former active task blob.
  - DATA_OWNERSHIP explicitly classifies CMS/news content as Platform-owned, with Laravel migrations authoritative for Platform-owned schema.
  - SYSTEM_ARCHITECTURE assigns news and managed public content to the CMS module and Platform-owned application storage.
  - SECURITY_ARCHITECTURE requires escaped untrusted output by default and explicit sanitization for rich HTML; this task avoids a rich-HTML boundary by storing/rendering plain text only.
  - TEST_STRATEGY requires Laravel feature tests for CMS/public pages and isolated database tests for migrations.
  - Phase 4 remains in progress; public layout/navigation, character search/profile, highscores, guild pages, configured server metadata and cluster-wide online list already exist, while managed news display is not yet implemented.
derived:
  - A published-only Platform-owned news read model is the smallest remaining Phase 4 slice that does not depend on unresolved Canary runtime transport or Phase 6 Admin/RBAC.
  - Using a nullable published_at timestamp as the sole public visibility gate supports both drafts and future scheduling without introducing an authoring workflow in this task.
unknown: []
conflicts: []
first_failure:
  marker: none
  evidence: none
rejected_hypotheses:
  - Implement CMS authoring together with public news display: rejected because authoring requires Phase 6 Admin/RBAC and privileged audit boundaries.
  - Store/render arbitrary rich HTML now: rejected because it would require a deliberate maintained sanitization solution; plain text is sufficient for this bounded public-read slice.
  - Source news from Canary or MyAAC tables: rejected because architecture/data ownership assigns CMS/news content to Platform-owned storage.
changed_paths:
  - docs/agents/tasks/archive/OTERYN-20260719-public-site-shell-and-search.md
  - docs/agents/tasks/active/OTERYN-20260719-public-site-shell-and-search.md
  - docs/agents/tasks/active/OTERYN-20260719-public-news-read-model.md
validation:
  - command: startup source-of-truth verification
    result: PASS
    evidence: merged PR #19/current main, zero open PRs, Phase 4 roadmap/state and CMS/data/security/testing architecture were inspected before implementation
  - command: implementation validation
    result: NOT_RUN
    evidence: implementation has not started yet
blockers:
  - none
next_action: Update ACTIVE_WORK and open the draft PR, then implement the Platform-owned published-only news read model with focused tests.
```

## Notes

This task introduces Platform-owned CMS persistence only for the public read boundary. It does not authorize news authoring UI/API, administrator permissions, RBAC, privileged audit operations, rich HTML, media uploads, Canary access, caching or production deployment work.
