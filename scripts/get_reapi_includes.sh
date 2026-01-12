#!/usr/bin/env sh
set -euo pipefail

REAPI_URL="https://github.com/rehlds/ReAPI/releases/download/5.26.0.338/reapi-bin-5.26.0.338.zip"

TMP_DIR="$(mktemp -d)"
ARCHIVE="$TMP_DIR/reapi.zip"

SRC_DIR="./include"
INCLUDE_DIR="$SRC_DIR"

mkdir -p "$INCLUDE_DIR"

cleanup() {
  rm -rf "$TMP_DIR"
}
trap cleanup EXIT

echo "[*] Downloading ReAPI includes..."
curl -fsSL "$REAPI_URL" -o "$ARCHIVE"

echo "[*] Extracting include files..."
unzip -q "$ARCHIVE" \
  "addons/amxmodx/scripting/include/*" \
  -d "$TMP_DIR"

cp -f "$TMP_DIR/addons/amxmodx/scripting/include/"*.inc "$INCLUDE_DIR/"

echo "[+] Done. Files copied to $INCLUDE_DIR"
