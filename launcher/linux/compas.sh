#!/usr/bin/env bash
# Compàs: Gestió lliure per a societats musicals — llançador per a Linux
# SPDX-License-Identifier: AGPL-3.0-or-later
#
# Arranca el servidor de PHP només a 127.0.0.1, obri el navegador i, en tancar
# (Ctrl+C o tancant la finestra del terminal), fa una còpia de seguretat si hi ha hagut canvis.
#
# Variables opcionals: COMPAS_DADES (carpeta de dades), COMPAS_PHP (ruta de php), COMPAS_PORT,
# COMPAS_NAVEGADOR=0 (no obrir el navegador).

set -u

ARREL="$(cd "$(dirname "$(readlink -f "$0")")" && pwd)"
DADES="${COMPAS_DADES:-${XDG_DATA_HOME:-$HOME/.local/share}/compas/dades}"
PHP="${COMPAS_PHP:-php}"
PORT=8740
NAVEGADOR=1

# Missatges en valencià o castellà segons l'idioma del sistema
case "${LC_ALL:-${LC_MESSAGES:-${LANG:-}}}" in
    es*) ES=1 ;;
    *) ES=0 ;;
esac
t() { if [ "$ES" = 1 ]; then printf '%s\n' "$2"; else printf '%s\n' "$1"; fi; }

error() {
    printf '\n%s\n\n' "$1" >&2
    # Si s'ha obert des del menú, que l'error no desaparega en un instant
    if [ -t 0 ]; then
        read -r -p "$(t 'Prem Retorn per a tancar.' 'Pulsa Intro para cerrar.')" _ || true
    fi
    exit 1
}

# Configuració (mateix compas.ini que a Windows). Si l'usuari en té un a ~/.config/compas, mana el seu
# (amb el paquet .deb el programa és a /usr/share/compas i no s'hi pot escriure).
CONFIG_USUARI="${XDG_CONFIG_HOME:-$HOME/.config}/compas/compas.ini"
for INI in "$ARREL/compas.ini" "$CONFIG_USUARI"; do
    [ -f "$INI" ] || continue
    while IFS='=' read -r clau valor; do
        clau="$(printf '%s' "$clau" | tr -d '[:space:]' | tr '[:upper:]' '[:lower:]')"
        valor="$(printf '%s' "${valor:-}" | tr -d '[:space:]\r')"
        case "$clau" in
            port) [[ "$valor" =~ ^[0-9]+$ ]] && [ "$valor" -gt 1023 ] && [ "$valor" -lt 65536 ] && PORT="$valor" ;;
            navegador) case "$valor" in 0|no|false) NAVEGADOR=0 ;; esac ;;
        esac
    done < <(grep -v '^[[:space:]]*[;#]' "$INI")
done
PORT="${COMPAS_PORT:-$PORT}"
NAVEGADOR="${COMPAS_NAVEGADOR:-$NAVEGADOR}"
URL="http://localhost:$PORT/"

# PHP i extensions
command -v "$PHP" >/dev/null 2>&1 || error "$(t \
    "No s'ha trobat PHP. Instal·la'l (Debian, Ubuntu, Linux Mint): sudo apt install php-cli php-sqlite3 php-mbstring php-xml" \
    "No se ha encontrado PHP. Instálalo (Debian, Ubuntu, Linux Mint): sudo apt install php-cli php-sqlite3 php-mbstring php-xml")"
"$PHP" -r 'exit(PHP_VERSION_ID >= 80100 ? 0 : 1);' || error "$(t \
    "Compàs necessita PHP 8.1 o posterior. Ara tens el $("$PHP" -r 'echo PHP_VERSION;')." \
    "Compàs necesita PHP 8.1 o posterior. Ahora tienes el $("$PHP" -r 'echo PHP_VERSION;').")"
FALTEN="$("$PHP" -r 'foreach (["pdo_sqlite" => "php-sqlite3", "mbstring" => "php-mbstring"] as $e => $p) { if (!extension_loaded($e)) echo "$p "; }')"
[ -z "$FALTEN" ] || error "$(t \
    "A PHP li falten extensions. Instal·la: sudo apt install $FALTEN" \
    "A PHP le faltan extensiones. Instala: sudo apt install $FALTEN")"
# Recomanades: sense elles Compàs funciona, però no es pot importar des d'Excel o xifrar les còpies
"$PHP" -r 'exit(extension_loaded("SimpleXML") ? 0 : 1);' || t \
    "Avís: sense php-xml no es poden importar fitxers d'Excel (sudo apt install php-xml)." \
    "Aviso: sin php-xml no se pueden importar archivos de Excel (sudo apt install php-xml)."
"$PHP" -r 'exit(extension_loaded("sodium") ? 0 : 1);' || t \
    "Avís: aquest PHP no porta sodium i no es poden xifrar les còpies externes." \
    "Aviso: este PHP no incluye sodium y no se pueden cifrar las copias externas."

mkdir -p "$DADES" || error "$(t "No es pot crear la carpeta de dades: $DADES" "No se puede crear la carpeta de datos: $DADES")"

# Fa una petició HTTP local amb el mateix PHP (així no cal curl ni wget)
peticio() { "$PHP" -r '$c = stream_context_create(["http" => ["timeout" => (float)$argv[2], "ignore_errors" => true]]); $r = @file_get_contents($argv[1], false, $c); if ($r === false) exit(1); echo $r;' "$1" "${2:-2}"; }

obri_navegador() {
    [ "$NAVEGADOR" = 1 ] || return 0
    if command -v xdg-open >/dev/null 2>&1; then
        xdg-open "$URL" >/dev/null 2>&1 &
    fi
}

# Ja està obert? Només cal obrir el navegador.
if RESPOSTA="$(peticio "$URL" 1)"; then
    if printf '%s' "$RESPOSTA" | grep -q 'Compàs'; then
        t "Compàs ja està obert: $URL" "Compàs ya está abierto: $URL"
        obri_navegador
        exit 0
    fi
    error "$(t \
        "El port $PORT l'utilitza un altre programa. Posa un altre port (per exemple, port = 8750) a $CONFIG_USUARI." \
        "El puerto $PORT lo usa otro programa. Pon otro puerto (por ejemplo, port = 8750) en $CONFIG_USUARI.")"
fi

TOKEN="$(od -An -N24 -tx1 /dev/urandom | tr -d ' \n')"
COMPAS_DADES="$DADES" COMPAS_TOKEN="$TOKEN" "$PHP" \
    -d display_errors=0 -d log_errors=1 -d error_log="$DADES/php-errors.log" \
    -d memory_limit=256M -d upload_max_filesize=64M -d post_max_size=64M -d max_execution_time=120 \
    -S "127.0.0.1:$PORT" -t "$ARREL/app" "$ARREL/app/index.php" >>"$DADES/servidor.log" 2>&1 &
SERVIDOR=$!

tanca() {
    trap - EXIT INT TERM HUP
    if kill -0 "$SERVIDOR" 2>/dev/null; then
        t "Fent la còpia de seguretat i tancant Compàs…" "Haciendo la copia de seguridad y cerrando Compàs…"
        peticio "http://127.0.0.1:$PORT/?r=copia_tancament&token=$TOKEN" 60 >/dev/null || true
        kill "$SERVIDOR" 2>/dev/null
        wait "$SERVIDOR" 2>/dev/null
    fi
    exit 0
}
trap tanca EXIT INT TERM HUP

# Espera que el servidor responga
for _ in $(seq 1 50); do
    peticio "$URL" 1 >/dev/null && break
    kill -0 "$SERVIDOR" 2>/dev/null || error "$(t \
        "Compàs no ha pogut arrancar. Mira $DADES/servidor.log" \
        "Compàs no ha podido arrancar. Mira $DADES/servidor.log")"
    sleep 0.2
done

printf '\n  Compàs · %s\n\n' "$URL"
t "  Les dades es guarden a: $DADES" "  Los datos se guardan en: $DADES"
t "  Deixa esta finestra oberta mentre utilitzes Compàs. Per a tancar-lo, prem Ctrl+C o tanca la finestra." \
  "  Deja esta ventana abierta mientras usas Compàs. Para cerrarlo, pulsa Ctrl+C o cierra la ventana."
echo
obri_navegador
wait "$SERVIDOR"
