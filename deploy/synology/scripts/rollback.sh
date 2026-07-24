#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
DEPLOY_DIR="$(cd -- "$SCRIPT_DIR/.." && pwd)"
ENV_FILE="${OTERYN_ENV_FILE:-$DEPLOY_DIR/.env}"
COMPOSE_FILE="$DEPLOY_DIR/compose.yml"

# shellcheck source=deploy/synology/scripts/lib.sh
source "$SCRIPT_DIR/lib.sh"
load_oteryn_env_file "$ENV_FILE"

state_dir="${OTERYN_STATE_DIR:-/var/lib/oteryn-staging-state}"
state_file="$state_dir/last-good.env"
if [[ ! -f "$state_file" ]]; then
    echo "No previous runtime image snapshot exists at $state_file" >&2
    exit 1
fi

load_oteryn_env_file "$state_file"

for name in PLATFORM_IMAGE GATEWAY_IMAGE CANARY_IMAGE; do
    if [[ -z "${!name:-}" ]]; then
        echo "Rollback snapshot is incomplete: $name" >&2
        exit 1
    fi
done

export PLATFORM_IMAGE GATEWAY_IMAGE CANARY_IMAGE
compose=(docker compose --env-file "$ENV_FILE" -f "$COMPOSE_FILE")

"${compose[@]}" pull platform gateway canary
"${compose[@]}" up -d canary platform internal-proxy gateway

OTERYN_ENV_FILE="$ENV_FILE" bash "$SCRIPT_DIR/health-check.sh"

echo "Runtime image rollback completed. Database migrations were not reversed automatically."
