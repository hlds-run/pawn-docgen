#!/bin/sh
set -e

BASE_DIR="/srv/pawn-docgen"

MARKER="$BASE_DIR/.db-initialized"

if [ -f "$MARKER" ]; then
    echo "[init-db] already initialized, skipping"
    exit 0
fi

echo "[init-db] running database init"

php /$BASE_DIR/generate/update.php

touch "$MARKER"

echo "[init-db] done"
