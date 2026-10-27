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
if command -v certutil >/dev/null && [ -f "$HOME/.pki/nssdb/cert9.db" ]; then
    certutil -A -d "sql:$HOME/.pki/nssdb" -n 'Compas-@ID@' -t 'C,,' -i compas-ca.crt
fi
if [ "${COMPAS_NO_OBRIR:-0}" != 1 ] && command -v xdg-open >/dev/null; then xdg-open '@URL@'; fi
