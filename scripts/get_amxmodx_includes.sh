#!/usr/bin/env bash
set -euo pipefail

AMXX_URL="https://www.amxmodx.org/amxxdrop/1.10/amxmodx-latest-base-linux.tar.gz"

TMP_DIR="$(mktemp -d)"
ARCHIVE="$TMP_DIR/amxmodx.tar.gz"

SRC_DIR="./include"
INCLUDE_DIR="$SRC_DIR"

mkdir -p "$INCLUDE_DIR"

cleanup() {
  rm -rf "$TMP_DIR"
}
trap cleanup EXIT

echo "[*] Downloading AMX Mod X includes..."
curl -fsSL "$AMXX_URL" -o "$ARCHIVE"

echo "[*] Extracting include files..."
tar -xzf "$ARCHIVE" \
  --strip-components=4 \
  -C "$INCLUDE_DIR" \
  addons/amxmodx/scripting/include

echo "[+] Done. Files copied to $INCLUDE_DIR"
