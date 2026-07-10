#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
ENV_FILE="${DREAMHOST_ENV_FILE:-$ROOT_DIR/.env.dreamhost}"

usage() {
  cat <<'USAGE'
Usage:
  bash scripts/dreamhost-rsync.sh pull [--apply] [--delete]
  bash scripts/dreamhost-rsync.sh push [--apply] [--delete]

Defaults are conservative:
  - Without --apply, rsync runs as a dry-run.
  - Without --delete, files missing from the source are not deleted from the target.

Configuration:
  Copy deploy/dreamhost.env.example to .env.dreamhost and fill in the values.
USAGE
}

if [[ $# -lt 1 ]]; then
  usage
  exit 2
fi

ACTION="$1"
shift

APPLY=false
DELETE=false

for arg in "$@"; do
  case "$arg" in
    --apply)
      APPLY=true
      ;;
    --delete)
      DELETE=true
      ;;
    -h|--help)
      usage
      exit 0
      ;;
    *)
      echo "Unknown option: $arg" >&2
      usage
      exit 2
      ;;
  esac
done

if [[ "$ACTION" != "pull" && "$ACTION" != "push" ]]; then
  echo "Unknown action: $ACTION" >&2
  usage
  exit 2
fi

if [[ -f "$ENV_FILE" ]]; then
  set -a
  # shellcheck disable=SC1090
  source "$ENV_FILE"
  set +a
else
  echo "Missing $ENV_FILE" >&2
  echo "Copy deploy/dreamhost.env.example to .env.dreamhost and fill it in." >&2
  exit 2
fi

: "${DREAMHOST_SSH_ALIAS:?Set DREAMHOST_SSH_ALIAS in $ENV_FILE}"
: "${DREAMHOST_REMOTE_PATH:?Set DREAMHOST_REMOTE_PATH in $ENV_FILE}"

DREAMHOST_LOCAL_PATH="${DREAMHOST_LOCAL_PATH:-dreamhost-site/}"
DREAMHOST_RSYNC_SSH="${DREAMHOST_RSYNC_SSH:-ssh}"
EXCLUDES_FILE="${DREAMHOST_EXCLUDES_FILE:-$ROOT_DIR/deploy/dreamhost.rsync-excludes}"
DREAMHOST_PASSWORD_FILE="${DREAMHOST_PASSWORD_FILE:-}"

if [[ "$DREAMHOST_LOCAL_PATH" = /* ]]; then
  LOCAL_PATH="$DREAMHOST_LOCAL_PATH"
else
  LOCAL_PATH="$ROOT_DIR/$DREAMHOST_LOCAL_PATH"
fi

LOCAL_PATH="${LOCAL_PATH%/}/"
REMOTE_PATH="${DREAMHOST_REMOTE_PATH%/}/"
REMOTE_TARGET="$DREAMHOST_SSH_ALIAS:$REMOTE_PATH"

if [[ ! -f "$EXCLUDES_FILE" ]]; then
  echo "Missing excludes file: $EXCLUDES_FILE" >&2
  exit 2
fi

RSYNC_ARGS=(
  -azhv
  --itemize-changes
  --human-readable
  --exclude-from "$EXCLUDES_FILE"
  --filter "protect /.htaccess"
  --filter "protect /.well-known/***"
  --filter "protect /cgi-bin/***"
  --filter "protect /config.local.php"
  -e "$DREAMHOST_RSYNC_SSH"
)

if [[ "$APPLY" != true ]]; then
  RSYNC_ARGS+=(--dry-run)
fi

if [[ "$DELETE" == true ]]; then
  RSYNC_ARGS+=(--delete-delay)
fi

case "$ACTION" in
  pull)
    mkdir -p "$LOCAL_PATH"
    SOURCE="$REMOTE_TARGET"
    DESTINATION="$LOCAL_PATH"
    ;;
  push)
    if [[ ! -d "$LOCAL_PATH" ]]; then
      echo "Local path does not exist: $LOCAL_PATH" >&2
      exit 2
    fi
    SOURCE="$LOCAL_PATH"
    DESTINATION="$REMOTE_TARGET"
    ;;
esac

if [[ "$APPLY" == true ]]; then
  echo "Running rsync $ACTION against DreamHost."
else
  echo "Dry-run rsync $ACTION against DreamHost. Add --apply to make changes."
fi

if [[ -n "$DREAMHOST_PASSWORD_FILE" ]]; then
  if [[ "$DREAMHOST_PASSWORD_FILE" != /* ]]; then
    DREAMHOST_PASSWORD_FILE="$ROOT_DIR/$DREAMHOST_PASSWORD_FILE"
  fi

  if [[ ! -f "$DREAMHOST_PASSWORD_FILE" ]]; then
    echo "Password file does not exist: $DREAMHOST_PASSWORD_FILE" >&2
    exit 2
  fi

  if ! command -v expect >/dev/null 2>&1; then
    echo "DREAMHOST_PASSWORD_FILE requires expect, but expect is not installed." >&2
    exit 2
  fi

  export DREAMHOST_PASSWORD
  DREAMHOST_PASSWORD="$(tr -d '\r\n' < "$DREAMHOST_PASSWORD_FILE")"

  # expect only populates $argv from trailing args when given a script file,
  # not with -c, so write the script out rather than passing it inline.
  EXPECT_SCRIPT="$(mktemp)"
  trap 'rm -f "$EXPECT_SCRIPT"' EXIT
  cat > "$EXPECT_SCRIPT" <<'EXPECT_EOF'
set timeout -1
set password $env(DREAMHOST_PASSWORD)
spawn rsync {*}$argv
expect {
  -re "(?i)are you sure you want to continue connecting" {
    send "yes\r"
    exp_continue
  }
  -re "(?i)password:" {
    send "$password\r"
    exp_continue
  }
  eof {
    catch wait result
    exit [lindex $result 3]
  }
}
EXPECT_EOF
  expect "$EXPECT_SCRIPT" "${RSYNC_ARGS[@]}" "$SOURCE" "$DESTINATION"
else
  rsync "${RSYNC_ARGS[@]}" "$SOURCE" "$DESTINATION"
fi
