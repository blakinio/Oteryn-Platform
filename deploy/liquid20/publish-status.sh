#!/usr/bin/env bash
set -Eeuo pipefail

CONTAINER_NAME="${LIQUID20_CONTAINER_NAME:-liquid20-collector}"
DATA_ROOT="${LIQUID20_DATA_ROOT:-/volume1/docker/freqtrade-liquidations/data}"
IMAGE="${LIQUID20_IMAGE:-unknown}"
ISSUE_NUMBER="${LIQUID20_STATUS_ISSUE:-148}"
OPERATION="${LIQUID20_OPERATION:-unknown}"
CONTROL_OUTCOME="${LIQUID20_CONTROL_OUTCOME:-unknown}"
ALPINE_IMAGE="${LIQUID20_HELPER_IMAGE:-alpine:3.20}"

if [[ ! "$ISSUE_NUMBER" =~ ^[1-9][0-9]*$ ]]; then
    echo "LIQUID20_STATUS_ISSUE must be a positive issue number" >&2
    exit 64
fi
if [[ -z "${GITHUB_TOKEN:-}" || -z "${GITHUB_REPOSITORY:-}" || -z "${GITHUB_API_URL:-}" ]]; then
    echo "GitHub issue publication environment is incomplete" >&2
    exit 64
fi

state="missing"
exit_code="none"
started_at="none"
finished_at="none"
runtime_image="none"
latest_run="none"
diagnostic_html="No failure diagnostic is available."
acceptance_summary="- Status: \`not available\`"

if docker container inspect "$CONTAINER_NAME" >/dev/null 2>&1; then
    state="$(docker container inspect --format '{{.State.Status}}' "$CONTAINER_NAME")"
    exit_code="$(docker container inspect --format '{{.State.ExitCode}}' "$CONTAINER_NAME")"
    started_at="$(docker container inspect --format '{{.State.StartedAt}}' "$CONTAINER_NAME")"
    finished_at="$(docker container inspect --format '{{.State.FinishedAt}}' "$CONTAINER_NAME")"
    runtime_image="$(docker container inspect --format '{{.Config.Image}}' "$CONTAINER_NAME")"

    if [[ "$state" != "running" ]]; then
        raw_diagnostic="$(docker logs --tail 40 "$CONTAINER_NAME" 2>&1 || true)"
        if [[ -n "$raw_diagnostic" ]]; then
            diagnostic_html="$(
                printf '%s' "$raw_diagnostic" | python3 -c '
import html
import re
import sys

pattern = re.compile(r"(?i)(authorization|api[_ -]?key|api[_ -]?secret|password|token)(\s*[:=]\s*)(\S+)")
lines = []
for line in sys.stdin.read().splitlines()[-40:]:
    line = pattern.sub(r"\1\2[redacted]", line)
    lines.append(line[:500])
print(html.escape("\n".join(lines))[:6000])
'
            )"
        fi
    fi
fi

latest_run_value="$(
    docker run --rm \
        -v "$DATA_ROOT:/data:ro" \
        "$ALPINE_IMAGE" \
        sh -ec 'latest="$(ls -1dt /data/runs/* 2>/dev/null | head -n 1 || true)"; if [ -n "$latest" ]; then basename "$latest"; fi' \
        2>/dev/null || true
)"
if [[ -n "$latest_run_value" ]]; then
    latest_run="$latest_run_value"
fi

if [[ "$latest_run" != "none" ]]; then
    report_json="$(
        docker run --rm \
            -v "$DATA_ROOT:/data:ro" \
            "$ALPINE_IMAGE" \
            sh -ec "report='/data/runs/$latest_run/multi-source-acceptance-report.json'; if [ -f \"\$report\" ]; then cat \"\$report\"; fi" \
            2>/dev/null || true
    )"
    if [[ -n "$report_json" ]]; then
        acceptance_summary="$(
            printf '%s' "$report_json" | python3 -c '
import json
import math
import re
import sys

try:
    report = json.load(sys.stdin)
except (json.JSONDecodeError, TypeError):
    print("- Status: `invalid report JSON`")
    raise SystemExit(0)

passed = report.get("passed")
status = "passed" if passed is True else "failed" if passed is False else "invalid"
lines = [f"- Status: `{status}`"]
failed = report.get("failed_gates", [])
if isinstance(failed, list):
    safe_failed = [
        name
        for name in failed
        if isinstance(name, str) and re.fullmatch(r"[A-Za-z0-9_.-]{1,120}", name)
    ]
    lines.append(f"- Failed gates: `{len(safe_failed)}`")
    if safe_failed:
        lines.append("- Gate names: " + ", ".join(f"`{name}`" for name in safe_failed[:20]))

coverage = report.get("coverage", {})
if isinstance(coverage, dict):
    union = coverage.get("union_observed_symbols", [])
    intersection = coverage.get("intersection_observed_symbols", [])
    if isinstance(union, list):
        lines.append(f"- Union observed symbols: `{len(union)}`")
    if isinstance(intersection, list):
        lines.append(f"- Intersection observed symbols: `{len(intersection)}`")

sources = report.get("sources", {})
if isinstance(sources, dict):
    for source_id in ("bybit-linear", "binance-usdm"):
        source = sources.get(source_id, {})
        metrics = source.get("metrics", {}) if isinstance(source, dict) else {}
        if not isinstance(metrics, dict):
            continue
        events = metrics.get("events_written")
        symbols = metrics.get("observed_symbol_count")
        availability = metrics.get("availability_ratio")
        disconnects = metrics.get("disconnects_per_hour")
        fields = []
        if isinstance(events, int) and not isinstance(events, bool):
            fields.append(f"events={events}")
        if isinstance(symbols, int) and not isinstance(symbols, bool):
            fields.append(f"symbols={symbols}")
        if (
            isinstance(availability, (int, float))
            and not isinstance(availability, bool)
            and math.isfinite(float(availability))
        ):
            fields.append(f"availability={float(availability):.6f}")
        if (
            isinstance(disconnects, (int, float))
            and not isinstance(disconnects, bool)
            and math.isfinite(float(disconnects))
        ):
            fields.append(f"disconnects/h={float(disconnects):.3f}")
        if fields:
            lines.append(f"- `{source_id}`: " + ", ".join(fields))

print("\n".join(lines))
'
        )"
    elif [[ "$state" == "running" ]]; then
        acceptance_summary="- Status: \`in progress\`
- Final report: \`not written yet\`"
    else
        acceptance_summary="- Status: \`not available\`
- Final report: \`missing\`"
    fi
fi

updated_at="$(date -u +%Y-%m-%dT%H:%M:%SZ)"
workflow_url="${GITHUB_SERVER_URL:-https://github.com}/${GITHUB_REPOSITORY}/actions/runs/${GITHUB_RUN_ID:-unknown}"

body="$(cat <<EOF
This issue is updated automatically by the trusted Synology runner. It contains no credentials and no raw liquidation event data.

## Current state

- Container: \`$CONTAINER_NAME\`
- State: \`$state\`
- Exit code: \`$exit_code\`
- Runtime image: \`$runtime_image\`
- Expected immutable image: \`$IMAGE\`
- Latest run ID: \`$latest_run\`
- Container started: \`$started_at\`
- Container finished: \`$finished_at\`
- Last operation: \`$OPERATION\`
- Control step outcome: \`$CONTROL_OUTCOME\`
- Updated at: \`$updated_at\`
- Workflow run: $workflow_url

## Acceptance summary

$acceptance_summary

## Bounded failure diagnostic

<details><summary>Last container output (maximum 40 lines / 6000 characters)</summary>

<pre>$diagnostic_html</pre>
</details>

The diagnostic is shown only when the container is not running. Recognized credential-shaped values are redacted before publication.

Hourly monitoring is metadata-only and does not upload artifacts. Full immutable evidence remains on the Synology data volume until an explicit \`collect\` operation is requested.

The 24-hour acceptance result is authoritative only after the immutable run directory contains \`multi-source-acceptance-report.json\` and the report states \`"passed": true\`.
EOF
)"

payload="$(printf '%s' "$body" | python3 -c 'import json, sys; print(json.dumps({"body": sys.stdin.read()}))')"

curl --fail --silent --show-error \
    --request PATCH \
    --header "Authorization: Bearer $GITHUB_TOKEN" \
    --header "Accept: application/vnd.github+json" \
    --header "X-GitHub-Api-Version: 2022-11-28" \
    --header "Content-Type: application/json" \
    --data "$payload" \
    "$GITHUB_API_URL/repos/$GITHUB_REPOSITORY/issues/$ISSUE_NUMBER" \
    >/dev/null

echo "Updated Liquid20 status issue #$ISSUE_NUMBER: state=$state latest_run=$latest_run"
