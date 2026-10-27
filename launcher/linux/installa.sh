#!/usr/bin/env bash
# Instal·la Compàs per a l'usuari actual (sense sudo): programa, accés al menú i ordre «compas».
# Les dades no es toquen mai: es queden a ~/.local/share/compas/dades.
# Copyright (C) 2026 José Vicente Sanjuan
# SPDX-License-Identifier: AGPL-3.0-or-later
set -eu

ORIGEN="$(cd "$(dirname "$(readlink -f "$0")")" && pwd)"
BASE="${XDG_DATA_HOME:-$HOME/.local/share}"
DESTI="$BASE/compas/programa"
case "${LC_ALL:-${LC_MESSAGES:-${LANG:-}}}" in es*) ES=1 ;; *) ES=0 ;; esac
t() { if [ "$ES" = 1 ]; then printf '%s\n' "$2"; else printf '%s\n' "$1"; fi; }

# Copia el programa (si ja hi era, el substitueix: és una actualització)
mkdir -p "$BASE/compas"
rm -rf "$DESTI.nou"
mkdir -p "$DESTI.nou"
cp -a "$ORIGEN/." "$DESTI.nou/"
rm -rf "$DESTI"
mv "$DESTI.nou" "$DESTI"
chmod +x "$DESTI/compas-xarxa" "$DESTI/configura-xarxa.sh" "$DESTI/compas.sh" "$DESTI/installa.sh" "$DESTI/desinstalla.sh"

# Ordre «compas»
mkdir -p "$HOME/.local/bin"
ln -sf "$DESTI/compas.sh" "$HOME/.local/bin/compas"

# Icona i accés al menú d'aplicacions
mkdir -p "$BASE/icons/hicolor/256x256/apps" "$BASE/applications"
cp "$DESTI/app/assets/icona.png" "$BASE/icons/hicolor/256x256/apps/compas.png"
cat > "$BASE/applications/compas.desktop" <<DESKTOP
[Desktop Entry]
Type=Application
Name=Compàs
GenericName=Gestió d'associacions
GenericName[es]=Gestión de asociaciones
Comment=Gestió lliure per a associacions
Comment[es]=Gestión libre para asociaciones
Exec="$DESTI/compas.sh"
Icon=compas
Terminal=false
Categories=Office;Education;
DESKTOP
command -v update-desktop-database >/dev/null 2>&1 && update-desktop-database "$BASE/applications" >/dev/null 2>&1 || true

echo
t "AVÍS: Compàs es distribueix tal com és, sense cap garantia (AGPL-3.0). L'autor no es fa responsable de la pèrdua de dades ni de cap altre dany. Feu còpies de seguretat externes." \
  "AVISO: Compàs se distribuye tal cual, sin ninguna garantía (AGPL-3.0). El autor no se hace responsable de la pérdida de datos ni de ningún otro daño. Haced copias de seguridad externas."
echo
t "Compàs s'ha instal·lat a $DESTI" "Compàs se ha instalado en $DESTI"
t "Obri'l des del menú d'aplicacions (Compàs) o escrivint: compas" "Ábrelo desde el menú de aplicaciones (Compàs) o escribiendo: compas"
command -v php >/dev/null 2>&1 || {
    echo
    t "Falta PHP. Instal·la'l amb: sudo apt install php-cli php-sqlite3 php-mbstring php-xml" \
      "Falta PHP. Instálalo con: sudo apt install php-cli php-sqlite3 php-mbstring php-xml"
}

# Accés separat a l’assistent de xarxa, amb terminal per a les confirmacions.
cat > "$BASE/applications/compas-xarxa.desktop" <<DESKTOP
[Desktop Entry]
Type=Application
Name=Compàs · Xarxa local HTTPS
Name[es]=Compàs · Red local HTTPS
Exec="$DESTI/configura-xarxa.sh"
Icon=compas
Terminal=true
Categories=Office;
DESKTOP
