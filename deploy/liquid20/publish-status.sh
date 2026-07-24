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

if docker container inspect "$CONTAINER_NAME" >/dev/null 2>&1; then
    state="$(docker container inspect --format '{{.State.Status}}' "$CONTAINER_NAME")"
    exit_code="$(docker container inspect --format '{{.State.ExitCode}}' "$CONTAINER_NAME")"
    started_at="$(docker container inspect --format '{{.State.StartedAt}}' "$CONTAINER_NAME")"
    finished_at="$(docker container inspect --format '{{.State.FinishedAt}}' "$CONTAINER_NAME")"
    runtime_image="$(docker container inspect --format '{{.Config.Image}}' "$CONTAINER_NAME")"
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

updated_at="$(date -u +%Y-%m-%dT%H:%M:%SZ)"
workflow_url="${GITHUB_SERVER_URL:-https://github.com}/${GITHUB_REPOSITORY}/actions/runs/${GITHUB_RUN_ID:-unknown}"

body="$(cat <<EOF
This issue is updated automatically by the trusted Synology runner. It contains no secrets and no raw liquidation event data.

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
