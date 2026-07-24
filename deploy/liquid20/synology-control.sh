#!/usr/bin/env bash
set -Eeuo pipefail

ACTION="${1:-status}"
CONTAINER_NAME="${LIQUID20_CONTAINER_NAME:-liquid20-collector}"
IMAGE="${LIQUID20_IMAGE:-}"
COLLECTOR_COMMIT="${LIQUID20_COLLECTOR_COMMIT:-}"
DATA_ROOT="${LIQUID20_DATA_ROOT:-/volume1/docker/freqtrade-liquidations/data}"
HOST_ID="${LIQUID20_HOST_ID:-synology-pl-01}"
ARTIFACT_DIR="${LIQUID20_ARTIFACT_DIR:-}"
ALPINE_IMAGE="${LIQUID20_HELPER_IMAGE:-alpine:3.20}"

append_summary() {
    if [[ -n "${GITHUB_STEP_SUMMARY:-}" ]]; then
        printf '%s\n' "$*" >> "$GITHUB_STEP_SUMMARY"
    fi
}

set_output() {
    local name="$1"
    local value="$2"
    if [[ -n "${GITHUB_OUTPUT:-}" ]]; then
        printf '%s=%s\n' "$name" "$value" >> "$GITHUB_OUTPUT"
    fi
}

require_simple_name() {
    local label="$1"
    local value="$2"
    if [[ ! "$value" =~ ^[A-Za-z0-9][A-Za-z0-9_.-]{0,127}$ ]]; then
        echo "$label is not a bounded simple name: $value" >&2
        exit 64
    fi
}

require_absolute_path() {
    local label="$1"
    local value="$2"
    if [[ ! "$value" =~ ^/[A-Za-z0-9._/-]+$ ]]; then
        echo "$label must be an absolute simple path: $value" >&2
        exit 64
    fi
}

container_exists() {
    docker container inspect "$CONTAINER_NAME" >/dev/null 2>&1
}

container_running() {
    [[ "$(docker container inspect --format '{{.State.Running}}' "$CONTAINER_NAME" 2>/dev/null || true)" == "true" ]]
}

latest_run_id() {
    docker run --rm \
        -v "$DATA_ROOT:/data:ro" \
        "$ALPINE_IMAGE" \
        sh -ec 'latest="$(ls -1dt /data/runs/* 2>/dev/null | head -n 1 || true)"; if [ -n "$latest" ]; then basename "$latest"; fi'
}

run_marker_exists() {
    local run_id="$1"
    docker run --rm \
        -v "$DATA_ROOT:/data:ro" \
        "$ALPINE_IMAGE" \
        test -f "/data/github-uploaded/$run_id"
}

show_status() {
    local state="missing"
    local exit_code="none"
    local started_at="none"
    local finished_at="none"
    local image="none"
    local run_id=""

    if container_exists; then
        state="$(docker container inspect --format '{{.State.Status}}' "$CONTAINER_NAME")"
        exit_code="$(docker container inspect --format '{{.State.ExitCode}}' "$CONTAINER_NAME")"
        started_at="$(docker container inspect --format '{{.State.StartedAt}}' "$CONTAINER_NAME")"
        finished_at="$(docker container inspect --format '{{.State.FinishedAt}}' "$CONTAINER_NAME")"
        image="$(docker container inspect --format '{{.Config.Image}}' "$CONTAINER_NAME")"
    fi

    run_id="$(latest_run_id || true)"

    echo "container=$CONTAINER_NAME state=$state exit_code=$exit_code image=$image"
    echo "started_at=$started_at finished_at=$finished_at latest_run=${run_id:-none}"

    append_summary "## Liquid20 Synology status"
    append_summary ""
    append_summary "- Container: \`$CONTAINER_NAME\`"
    append_summary "- State: \`$state\`"
    append_summary "- Exit code: \`$exit_code\`"
    append_summary "- Image: \`$image\`"
    append_summary "- Started: \`$started_at\`"
    append_summary "- Finished: \`$finished_at\`"
    append_summary "- Latest run: \`${run_id:-none}\`"

    set_output container_state "$state"
    set_output latest_run_id "$run_id"

    if container_exists; then
        echo "--- bounded container log tail ---"
        docker logs --tail 120 "$CONTAINER_NAME" 2>&1 || true
        echo "--- end log tail ---"
    fi

    if [[ -n "$run_id" ]]; then
        echo "--- latest run files ---"
        docker run --rm \
            -v "$DATA_ROOT:/data:ro" \
            "$ALPINE_IMAGE" \
            sh -ec "ls -lah '/data/runs/$run_id'"
        echo "--- end latest run files ---"
    fi
}

ensure_data_root() {
    docker run --rm \
        -v "$DATA_ROOT:/data:rw" \
        "$ALPINE_IMAGE" \
        sh -ec 'mkdir -p /data/runs /data/github-uploaded && chmod 0750 /data /data/runs /data/github-uploaded'
}

deploy_acceptance() {
    if [[ -z "$IMAGE" ]]; then
        echo "LIQUID20_IMAGE is required for deployment" >&2
        exit 64
    fi
    if [[ ! "$COLLECTOR_COMMIT" =~ ^[0-9a-f]{40}$ ]]; then
        echo "LIQUID20_COLLECTOR_COMMIT must be an exact lower-case 40-character SHA" >&2
        exit 64
    fi

    if container_running; then
        echo "A Liquid20 container is already running; preserving it without restart or replacement."
        show_status
        return 0
    fi

    ensure_data_root

    if container_exists; then
        docker rm "$CONTAINER_NAME"
    fi

    docker pull "$IMAGE"

    docker run --detach \
        --name "$CONTAINER_NAME" \
        --restart no \
        --user 0:0 \
        --label com.blakinio.liquid20.managed-by=github-actions \
        --label "com.blakinio.liquid20.collector-commit=$COLLECTOR_COMMIT" \
        --env "COLLECTOR_COMMIT=$COLLECTOR_COMMIT" \
        --env "LIQUIDATION_STAGING_HOST_ID=$HOST_ID" \
        --env LIQUID20_MODE=acceptance \
        --env TZ=UTC \
        --volume "$DATA_ROOT:/data:rw" \
        --tmpfs /tmp:size=64m,mode=1777 \
        --cap-drop ALL \
        --security-opt no-new-privileges:true \
        "$IMAGE"

    sleep 5
    if ! container_running; then
        echo "Liquid20 container did not remain running after deployment." >&2
        docker logs --tail 200 "$CONTAINER_NAME" 2>&1 || true
        exit 1
    fi

    show_status
}

collect_latest() {
    local run_id=""
    local helper_id=""

    if [[ -z "$ARTIFACT_DIR" ]]; then
        echo "LIQUID20_ARTIFACT_DIR is required for collection" >&2
        exit 64
    fi

    run_id="$(latest_run_id)"
    if [[ -z "$run_id" ]]; then
        echo "No Liquid20 run directory exists under $DATA_ROOT/runs" >&2
        exit 66
    fi
    require_simple_name run_id "$run_id"

    if run_marker_exists "$run_id" && [[ "${LIQUID20_FORCE_COLLECT:-false}" != "true" ]]; then
        echo "Run $run_id was already uploaded; skipping duplicate artifact creation."
        set_output artifact_ready false
        set_output run_id "$run_id"
        return 0
    fi

    rm -rf "$ARTIFACT_DIR"
    mkdir -p "$ARTIFACT_DIR/run"

    helper_id="$(docker create -v "$DATA_ROOT:/source:ro" "$ALPINE_IMAGE")"
    if ! docker cp "$helper_id:/source/runs/$run_id/." "$ARTIFACT_DIR/run"; then
        docker rm -f "$helper_id" >/dev/null 2>&1 || true
        exit 1
    fi
    docker rm "$helper_id" >/dev/null

    if [[ -f "$ARTIFACT_DIR/run/artifact-sha256.txt" ]]; then
        (
            cd "$ARTIFACT_DIR/run"
            sha256sum --check artifact-sha256.txt
        )
    fi

    if container_exists; then
        docker logs "$CONTAINER_NAME" > "$ARTIFACT_DIR/container.log" 2>&1 || true
        docker container inspect \
            --format '{"name":"{{.Name}}","image":"{{.Config.Image}}","status":"{{.State.Status}}","exit_code":{{.State.ExitCode}},"started_at":"{{.State.StartedAt}}","finished_at":"{{.State.FinishedAt}}"}' \
            "$CONTAINER_NAME" > "$ARTIFACT_DIR/container-state.json"
    fi

    printf '%s\n' "$run_id" > "$ARTIFACT_DIR/run-id.txt"
    printf '%s\n' "$COLLECTOR_COMMIT" > "$ARTIFACT_DIR/collector-commit.txt"

    set_output artifact_ready true
    set_output artifact_path "$ARTIFACT_DIR"
    set_output run_id "$run_id"
    echo "Prepared Liquid20 artifact for run $run_id at $ARTIFACT_DIR"
}

mark_uploaded() {
    local run_id="${2:-}"
    require_simple_name run_id "$run_id"
    docker run --rm \
        -v "$DATA_ROOT:/data:rw" \
        "$ALPINE_IMAGE" \
        sh -ec "mkdir -p /data/github-uploaded && touch '/data/github-uploaded/$run_id'"
    echo "Marked run $run_id as uploaded outside the immutable run directory."
}

monitor() {
    local state="missing"
    show_status
    state="$(docker container inspect --format '{{.State.Status}}' "$CONTAINER_NAME" 2>/dev/null || true)"
    if [[ -n "$state" && "$state" != "running" ]]; then
        collect_latest
    else
        set_output artifact_ready false
    fi
}

require_simple_name LIQUID20_CONTAINER_NAME "$CONTAINER_NAME"
require_absolute_path LIQUID20_DATA_ROOT "$DATA_ROOT"
docker version >/dev/null

case "$ACTION" in
    bootstrap | deploy)
        deploy_acceptance
        set_output artifact_ready false
        ;;
    status)
        show_status
        set_output artifact_ready false
        ;;
    monitor)
        monitor
        ;;
    collect)
        LIQUID20_FORCE_COLLECT=true collect_latest
        ;;
    mark-uploaded)
        mark_uploaded "$@"
        ;;
    *)
        echo "Unsupported action: $ACTION" >&2
        exit 64
        ;;
esac
