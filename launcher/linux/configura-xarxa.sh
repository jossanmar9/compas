#!/usr/bin/env bash
# Assistent local, sense modificar la configuració del router ni obrir Internet.
# SPDX-License-Identifier: AGPL-3.0-or-later
set -euo pipefail
ARREL="$(cd "$(dirname "$(readlink -f "$0")")" && pwd)"
INI="${XDG_CONFIG_HOME:-$HOME/.config}/compas/compas.ini"
DADES="${COMPAS_DADES:-${XDG_DATA_HOME:-$HOME/.local/share}/compas/dades}"
if [ ! -t 0 ]; then
    for term in x-terminal-emulator xterm; do
        if command -v "$term" >/dev/null; then exec "$term" -e "$0"; fi
    done
    echo 'Obri un terminal i executa compas --xarxa / Abre un terminal y ejecuta compas --xarxa.' >&2; exit 1
fi
echo 'Compàs · Xarxa local / Red local · HTTPS'
read -r -p 'Activar servidor compartit / compartido? [si/NO] ' activa
mkdir -p "$(dirname "$INI")"
if [ "$activa" != si ]; then printf '\nlan = 0\n' >> "$INI"; echo 'Mode local. Torna a obrir Compàs / Vuelve a abrir Compàs.'; exit 0; fi
read -r -p 'Nom / Nombre [compas.local]: ' nom
nom="${nom:-compas.local}"
[[ "$nom" =~ ^[a-z0-9]([a-z0-9-]{0,60}[a-z0-9])?\.local$ ]] || { echo 'Nom incorrecte / Nombre incorrecto'; exit 1; }
echo 'Es prepararà HTTPS al port 443 i un certificat local. Cal confirmar-lo en cada equip.'
echo 'Se preparará HTTPS en el puerto 443 y un certificado local. Hay que confirmarlo en cada equipo.'
read -r -p 'Continuar? [si/NO] ' confirma
[ "$confirma" = si ] || exit 1
command -v setcap >/dev/null || { echo 'Instal·la / Instala libcap2-bin'; exit 1; }
if ! getcap "$ARREL/compas-xarxa" | grep -q cap_net_bind_service; then
    sudo setcap cap_net_bind_service=+ep "$ARREL/compas-xarxa"
fi
"$ARREL/compas-xarxa" -dades "$DADES/https" -nom "$nom" -prepara
cat "$DADES/https/empremta.txt"
echo 'Comprova el certificat del teu servidor / Comprueba el certificado de tu servidor.'
COMPAS_NO_OBRIR=1 bash "$DADES/https/clients/connecta.sh"
if command -v ufw >/dev/null && sudo ufw status | grep -q 'Status: active'; then
    read -r -p 'Permetre HTTPS i descobriment mDNS només des de xarxes privades / Permitir en redes privadas? [si/NO] ' tallafoc
    if [ "$tallafoc" = si ]; then
        for xarxa in 10.0.0.0/8 172.16.0.0/12 192.168.0.0/16; do
            sudo ufw allow from "$xarxa" to any port 443 proto tcp comment 'Compas HTTPS'
            sudo ufw allow from "$xarxa" to any port 5353 proto udp comment 'Compas mDNS'
        done
    fi
fi
printf '\nlan = 1\nnom_lan = %s\nport_https = 443\n' "$nom" >> "$INI"
echo "Preparat / Preparado: https://$nom/"
echo "Paquet per als altres equips / Paquete para los demás equipos: $DADES/https/Client-Compas.zip"
echo 'Torna a obrir Compàs / Vuelve a abrir Compàs.'
