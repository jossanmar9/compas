#!/usr/bin/env bash
# Desinstal·la Compàs. Les dades i les còpies de seguretat NO s'esborren.
# Copyright (C) 2026 José Vicente Sanjuan
# SPDX-License-Identifier: AGPL-3.0-or-later
set -eu
BASE="${XDG_DATA_HOME:-$HOME/.local/share}"
case "${LC_ALL:-${LC_MESSAGES:-${LANG:-}}}" in es*) ES=1 ;; *) ES=0 ;; esac
t() { if [ "$ES" = 1 ]; then printf '%s\n' "$2"; else printf '%s\n' "$1"; fi; }
rm -rf "$BASE/compas/programa"
rm -f "$HOME/.local/bin/compas" "$BASE/applications/compas.desktop" "$BASE/icons/hicolor/256x256/apps/compas.png"
t "Compàs s'ha desinstal·lat. Les dades es conserven a $BASE/compas/dades (esborra-la tu si ja no les vols)." \
  "Compàs se ha desinstalado. Los datos se conservan en $BASE/compas/dades (bórrala tú si ya no los quieres)."
