#!/bin/sh
set -e

BASE_DIR="/srv/pawn-docgen"
CACHE_DIR="$BASE_DIR/cache/og_images"

MARKER="$BASE_DIR/.og-cache-generated"

if [ -f "$MARKER" ]; then
    echo "[og-cache] cache already generated, skipping"
    exit 0
fi

echo "[og-cache] generating OG image cache"

mkdir -p "$CACHE_DIR"

# Запуск PHP-скрипта для генерации кеша
php "$BASE_DIR/generate/generate_og_cache.php"

touch "$MARKER"

echo "[og-cache] done"