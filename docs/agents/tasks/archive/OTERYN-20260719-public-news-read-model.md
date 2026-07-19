# OTERYN-20260719 Public news read model

## Goal

Deliver the next bounded Phase 4 public website slice as a Platform-owned, read-only news display: persist news posts in the Platform database, expose published-only public list/detail routes through a dedicated CMS read/query boundary, and render plain text safely without introducing authoring, Admin/RBAC, uploads, Canary access or rich-HTML sanitization requirements.

## Acceptance criteria

- [x] Archive the merged `OTERYN-20260719-public-site-shell-and-search` task record under `docs/agents/tasks/archive/` with the exact historical blob unchanged.
- [x] Add an authoritative Laravel migration for Platform-owned `news_posts` with unique slug, title, plain-text body and nullable publication timestamp.
- [x] Add a narrow CMS public-news query service over the Platform database; do not access Canary or add a shared-data contract.
- [x] Public list/detail reads expose only posts with `published_at IS NOT NULL` and `published_at <= read_time`; drafts and future-scheduled posts remain non-public.
- [x] List ordering is deterministic by `published_at DESC`, then `id DESC`, with bounded pagination.
- [x] Add `GET /news` and `GET /news/{slug}` public routes and a News link in the shared public navigation.
- [x] Render title/body through escaped Blade output only; this task stores/renders plain text and does not introduce raw rich HTML or file/media uploads.
- [x] Add focused feature/database coverage for published visibility, draft/scheduled exclusion, detail 404 semantics, deterministic ordering/pagination and XSS escaping.
- [x] Update Phase 4/CMS project documentation without claiming CMS authoring, Admin/RBAC or live multichannel runtime availability are complete.
- [x] Run repository CI and Agent Governance on the delivery-validation head; require a fresh exact-head pass after this ready checkpoint before merge.

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
updated_at: 2026-07-19T18:15:00+02:00
head: 02913cab0121fc7165ef0a02efd945c0e64c3093
branch: task/OTERYN-20260719-public-news-read-model
pr: 20
status: ready
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
  - Draft PR #20 targets main from the dedicated task/OTERYN-20260719-public-news-read-model branch.
  - DATA_OWNERSHIP explicitly classifies CMS/news content as Platform-owned, with Laravel migrations authoritative for Platform-owned schema.
  - The implementation adds Platform-owned news_posts persistence with unique slug, title, plain-text body, nullable indexed published_at and timestamps.
  - PublicNewsQuery reads only the Platform-owned NewsPost model and applies published_at IS NOT NULL plus published_at <= read_time for both list and detail reads.
  - Public news list ordering is published_at DESC then id DESC and is bounded to 10 rows per page by the public query boundary.
  - GET /news and GET /news/{slug} are implemented; the shared public navigation exposes News alongside Home, Online, Highscores and Servers.
  - Draft and future-scheduled posts are excluded from list results and direct detail lookup returns 404 for them.
  - News title/body use escaped Blade interpolation only; body is treated as plain text with whitespace preservation and no raw-HTML directive or upload path is introduced.
  - Focused RefreshDatabase feature tests cover required schema, database slug uniqueness, publication-state visibility, deterministic order/pagination, detail 404 semantics and XSS escaping.
  - ROADMAP, MODULE_CATALOG and PROJECT_STATE describe the public CMS read boundary while leaving authoring/management, Admin/RBAC, rich HTML, media uploads and live multichannel runtime availability incomplete.
  - PR #20 contains exactly 15 task-owned paths; the archived public-site task is represented as an exact-content rename with zero additions and zero deletions.
  - Delivery-validation head 02913cab0121fc7165ef0a02efd945c0e64c3093 passed CI run 29694191399 (#317), including Composer validation/install, Pint, PHPStan/Larastan level 10 and the full test suite.
  - Delivery-validation head 02913cab0121fc7165ef0a02efd945c0e64c3093 passed Agent Governance run 29694191397 (#238).
derived:
  - A published-only Platform-owned news read model is the smallest remaining Phase 4 slice that does not depend on unresolved Canary runtime transport or Phase 6 Admin/RBAC.
  - Using nullable published_at as the sole public visibility gate supports both drafts and future scheduling without introducing an authoring workflow in this task.
  - Because this task reads only Platform-owned persistence, no Canary privilege allowlist or cross-repository data contract expansion is required.
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
  - app/Cms/Models/NewsPost.php
  - app/Cms/PublicNewsQuery.php
  - app/Http/Controllers/Cms/PublicNewsController.php
  - database/migrations/2026_07_19_175500_create_news_posts_table.php
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/tasks/active/OTERYN-20260719-public-news-read-model.md
  - docs/agents/tasks/archive/OTERYN-20260719-public-site-shell-and-search.md
  - docs/architecture/MODULE_CATALOG.md
  - docs/architecture/ROADMAP.md
  - resources/views/game/layout.blade.php
  - resources/views/news/index.blade.php
  - resources/views/news/show.blade.php
  - routes/web.php
  - tests/Feature/Cms/PublicNewsTest.php
validation:
  - command: startup source-of-truth verification
    result: PASS
    evidence: merged PR #19/current main, zero open PRs, Phase 4 roadmap/state and CMS/data/security/testing architecture were inspected before implementation
  - command: local Composer/Pint/PHPStan/tests
    result: NOT_RUN
    evidence: current execution environment cannot resolve github.com and has no usable local Oteryn-Platform checkout; exact-head GitHub Actions is the executable validation source
  - command: Agent Governance run 29693989967 (#233)
    result: PASS
    evidence: intermediate implementation/test head 75c9b0dd288a607b134602223a21d43b7f766c5e passed active checkpoint validation
  - command: GitHub Actions CI run 29694191399 (#317)
    result: PASS
    evidence: exact delivery-validation head 02913cab0121fc7165ef0a02efd945c0e64c3093 passed Composer validation/install, Pint, PHPStan/Larastan level 10 and the full test suite
  - command: Agent Governance run 29694191397 (#238)
    result: PASS
    evidence: exact delivery-validation head 02913cab0121fc7165ef0a02efd945c0e64c3093 passed active checkpoint validation
blockers:
  - none
next_action: Revalidate CI and Agent Governance on the final ready-checkpoint head, then perform the full PR #20 merge gate and squash-merge only if main divergence, final diff and review state remain clean.
```

## Notes

This task introduces Platform-owned CMS persistence only for the public read boundary. It does not authorize news authoring UI/API, administrator permissions, RBAC, privileged audit operations, rich HTML, media uploads, Canary access, caching or production deployment work.
