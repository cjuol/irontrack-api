#!/usr/bin/env bash
set -euo pipefail

PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
TARGET_DIR="$PROJECT_DIR/development"

if [[ ! -d "$TARGET_DIR" ]]; then
  echo "No se encontró el directorio: $TARGET_DIR"
  exit 1
fi

USER_NAME="${SUDO_USER:-$(id -un)}"
GROUP_NAME="$(id -gn "$USER_NAME")"

echo "Corrigiendo permisos en: $TARGET_DIR"
sudo chown -R "$USER_NAME":"$GROUP_NAME" "$TARGET_DIR"
chmod -R u+rwX "$TARGET_DIR"

if [[ -f "$TARGET_DIR/.env" ]]; then
  chmod go-rwx "$TARGET_DIR/.env"
fi

echo "Permisos corregidos."