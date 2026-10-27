#!/usr/bin/env bash
# SPDX-License-Identifier: AGPL-3.0-or-later
set -euo pipefail
cd -- "$(dirname -- "$(readlink -f -- "$0")")"
empremta="$(openssl x509 -in compas-ca.crt -outform DER | sha256sum | cut -d' ' -f1 | tr '[:lower:]' '[:upper:]')"
[ "$empremta" = '@EMPREMTA@' ] || { echo 'Certificat incorrecte / Certificado incorrecto'; exit 1; }
printf '\nCompàs: @URL@\nSHA-256: %s\n' "$empremta"
echo 'Compara l’empremta amb la del servidor / Compara la huella con la del servidor.'
read -r -p 'Confiar en el certificat de l’associació / Confiar en el certificado de la asociación? [si/NO] ' resposta
[ "$resposta" = si ] || exit 1
if command -v update-ca-certificates >/dev/null; then
    sudo install -m 644 compas-ca.crt '/usr/local/share/ca-certificates/compas-@ID@.crt'
    sudo update-ca-certificates
elif command -v update-ca-trust >/dev/null; then
    sudo install -m 644 compas-ca.crt '/etc/pki/ca-trust/source/anchors/compas-@ID@.crt'
    sudo update-ca-trust
else
    echo 'Importa compas-ca.crt en el navegador com a autoritat de confiança / como autoridad de confianza.'
fi
if command -v certutil >/dev/null; then
    # Chromium M146 usa el directori nou, però conserva l'antic si ja existia.
    nss="$HOME/.local/share/pki/nssdb"
    [ ! -f "$HOME/.pki/nssdb/cert9.db" ] || nss="$HOME/.pki/nssdb"
    if [ ! -f "$nss/cert9.db" ]; then
        mkdir -p "$nss"
        certutil -N -d "sql:$nss" --empty-password
    fi
    certutil -A -d "sql:$nss" -n 'Compas-@ID@' -t 'C,,' -i compas-ca.crt
else
    echo 'Si el navegador encara avisa, importa compas-ca.crt en la seua configuració de certificats.'
    echo 'Si el navegador todavía avisa, importa compas-ca.crt en su configuración de certificados.'
fi
if [ "${COMPAS_NO_OBRIR:-0}" != 1 ] && command -v xdg-open >/dev/null; then xdg-open '@URL@'; fi
