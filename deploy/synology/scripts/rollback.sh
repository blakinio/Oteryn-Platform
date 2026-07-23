#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
DEPLOY_DIR="$(cd -- "$SCRIPT_DIR/.." && pwd)"
ENV_FILE="${OTERYN_ENV_FILE:-$DEPLOY_DIR/.env}"
COMPOSE_FILE="$DEPLOY_DIR/compose.yml"

if [[ ! -f "$ENV_FILE" ]]; then
    echo "Missing staging environment file: $ENV_FILE" >&2
    exit 1
fi

set -a
# shellcheck disable=SC1090
source "$ENV_FILE"
set +a

state_dir="${OTERYN_STATE_DIR:-$HOME/.local/state/oteryn-staging}"
state_file="$state_dir/last-good.env"
if [[ ! -f "$state_file" ]]; then
    echo "No previous runtime image snapshot exists at $state_file" >&2
    exit 1
fi

# shellcheck disable=SC1090
source "$state_file"

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

OTERYN_ENV_FILE="$ENV_FILE" "$SCRIPT_DIR/health-check.sh"

echo "Runtime image rollback completed. Database migrations were not reversed automatically."
