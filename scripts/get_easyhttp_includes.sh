#!/usr/bin/env sh
set -euo pipefail

EASY_HTTP_URL="https://github.com/Next21Team/AmxxEasyHttp/releases/download/1.4.0/easy_http.inc"

SRC_DIR="./include"
INCLUDE_DIR="$SRC_DIR"

mkdir -p "$INCLUDE_DIR"

echo "[*] Downloading easy_http.inc..."
curl -fsSL "$EASY_HTTP_URL" -o "$INCLUDE_DIR/easy_http.inc"

echo "[+] Done. File saved to $INCLUDE_DIR/easy_http.inc"
