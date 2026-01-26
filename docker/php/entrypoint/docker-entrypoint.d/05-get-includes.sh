#!/bin/sh
set -e

BASE_DIR="/srv/pawn-docgen"
cd $BASE_DIR

echo "[get-includes] download includes"

$BASE_DIR/scripts/get_amxmodx_includes.sh
$BASE_DIR/scripts/get_reapi_includes.sh
$BASE_DIR/scripts/get_easyhttp_includes.sh

echo "[get-includes] done"
