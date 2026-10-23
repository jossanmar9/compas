#!/usr/bin/env bash
# Construeix el paquet .deb de Compàs a partir de la carpeta del programa ja preparada
# (la mateixa del .tar.gz: app, src, docs amb els PDF, compas.sh, compas.ini…).
# Ús: eines/construeix-deb.sh <carpeta Compas> <versió> <carpeta d'eixida>
# Copyright (C) 2026 José Vicente Sanjuan
# SPDX-License-Identifier: AGPL-3.0-or-later
set -euo pipefail

ORIGEN="$(readlink -f "$1")"
VERSIO="$2"
EIXIDA="$(readlink -f "$3")"
ARREL="$(cd "$(dirname "$(readlink -f "$0")")/.." && pwd)"
TMP="$(mktemp -d)"
trap 'rm -rf "$TMP"' EXIT
P="$TMP/compas"

# Programa a /usr/share/compas (sense els scripts d'instal·lació manual, que ací no calen)
mkdir -p "$P/usr/share/compas" "$P/usr/bin" "$P/usr/share/applications" "$P/usr/share/icons/hicolor/256x256/apps" "$P/usr/share/doc/compas" "$P/DEBIAN"
cp -a "$ORIGEN/." "$P/usr/share/compas/"
rm -f "$P/usr/share/compas/installa.sh" "$P/usr/share/compas/desinstalla.sh"
rm -rf "$P/usr/share/compas/data"
chmod 755 "$P/usr/share/compas/compas.sh"
ln -s ../share/compas/compas.sh "$P/usr/bin/compas"

cp "$ORIGEN/app/assets/icona.png" "$P/usr/share/icons/hicolor/256x256/apps/compas.png"
cat > "$P/usr/share/applications/compas.desktop" <<DESKTOP
[Desktop Entry]
Type=Application
Name=Compàs
GenericName=Gestió d'associacions
GenericName[es]=Gestión de asociaciones
Comment=Gestió lliure per a associacions
Comment[es]=Gestión libre para asociaciones
Exec=compas
Icon=compas
Terminal=false
Categories=Office;Education;
DESKTOP

cp "$ARREL/launcher/linux/deb/copyright" "$P/usr/share/doc/compas/copyright"
gzip -9n -c "$ORIGEN/CHANGELOG.md" > "$P/usr/share/doc/compas/changelog.gz"

# Permisos estàndard: directoris 755, fitxers 644, el llançador executable
find "$P/usr" -type d -exec chmod 755 {} +
find "$P/usr" -type f -exec chmod 644 {} +
chmod 755 "$P/usr/share/compas/compas.sh"

MIDA="$(du -sk "$P/usr" | cut -f1)"
sed -e "s/@VERSIO@/$VERSIO/" -e "s/@MIDA@/$MIDA/" "$ARREL/launcher/linux/deb/control" > "$P/DEBIAN/control"

mkdir -p "$EIXIDA"
dpkg-deb --root-owner-group -Zxz --build "$P" "$EIXIDA/compas_${VERSIO}_all.deb"
