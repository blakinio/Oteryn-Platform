# OTERYN-20260721-cms-audit-regressions

Status: completed and merged through PR #74.

Goal: close issue #72 with focused repository regressions for news unpublish, managed-page edit/unpublish, and bounded administrator audit metadata.

Delivered evidence:
- existing published news can be unpublished and becomes hidden from public list/detail;
- existing managed pages can be edited and unpublished, with changed content persisted and public visibility removed;
- representative privileged role-management and CMS audit records exclude Identity credential material, password-reset material, MFA secret/recovery material, their stored protected forms, and application-secret material;
- no runtime application code changed; the existing implementation passed the new regressions.

Validated implementation SHA: `06d87d36d60db58a9377528960de19314a2c003f`.

Validation:
- CI #836: PASS;
- Agent Governance #756: PASS;
- Phase 7 Production-Like Validation #81: PASS;
- Platform DB Outage Validation #11: PASS.

PR #74 final current-head SHA `0c4a0c8033a749c1d3f0799ca8a85f3d7caaf4a1` passed CI #837, Agent Governance #757, Phase 7 Production-Like Validation #82, and Platform DB Outage Validation #12 before squash merge.

Issue #72 is closed. Aggregate Functional Acceptance remains pending only on the independently owned PR #67 / issues #68-#70 live-acceptance evidence path.
