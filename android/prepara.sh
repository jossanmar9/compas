#!/usr/bin/env bash
# Prepara el projecte d'Android abans de compilar-lo (ho fan els workflows de GitHub Actions):
#  - app/src/main/assets/compas.zip: el programa (app, src), els manuals i el php.ini
#  - app/src/main/jniLibs/<abi>/libphp.so: el PHP estàtic per a cada arquitectura
# Ús: android/prepara.sh <dir amb php-aarch64 i/o php-x86_64>
# SPDX-License-Identifier: AGPL-3.0-or-later
set -euo pipefail

ARREL="$(cd "$(dirname "$0")/.." && pwd)"
PHPS="${1:?Falta la carpeta amb els PHP}"
DESTI="$ARREL/android/app/src/main"
TMP="$(mktemp -d)"
trap 'rm -rf "$TMP"' EXIT

mkdir -p "$TMP/compas/docs" "$DESTI/assets"
cp -r "$ARREL/app" "$ARREL/src" "$TMP/compas/"
cp "$ARREL"/docs/manual-*.md "$ARREL"/docs/guia-*.md "$TMP/compas/docs/"
mkdir -p "$TMP/compas/docs/imatges"
cp -r "$ARREL/docs/imatges/ca" "$ARREL/docs/imatges/es" "$TMP/compas/docs/imatges/"
cp "$ARREL"/README.md "$ARREL"/CHANGELOG.md "$ARREL"/PRIVACITAT.md "$ARREL"/LLICENCIA.md "$ARREL"/LICENSE "$TMP/compas/"
# El mateix php.ini que a Windows, sense «extension=»: el PHP d'Android ja porta les extensions dins
grep -vE '^\s*(zend_)?extension\s*=' "$ARREL/launcher/php.ini" > "$TMP/compas/php.ini"
find "$TMP/compas" -name '__pycache__' -prune -exec rm -rf {} +
rm -f "$DESTI/assets/compas.zip"
(cd "$TMP/compas" && zip -qr -X -9 "$DESTI/assets/compas.zip" .)

rm -rf "$DESTI/jniLibs"
for par in "aarch64:arm64-v8a" "x86_64:x86_64"; do
    arq="${par%%:*}"; abi="${par##*:}"
    if [ -f "$PHPS/php-$arq" ]; then
        mkdir -p "$DESTI/jniLibs/$abi"
        install -m 755 "$PHPS/php-$arq" "$DESTI/jniLibs/$abi/libphp.so"
    fi
done
ls -la "$DESTI/assets" "$DESTI"/jniLibs/*
