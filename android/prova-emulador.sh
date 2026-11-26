#!/usr/bin/env bash
# Prova de l'app en un emulador d'Android (la fa .github/workflows/android.yml):
# instal·la l'APK, l'obri i comprova que PHP arranca i que Compàs respon. Deixa captures a android/proves/.
# SPDX-License-Identifier: AGPL-3.0-or-later
set -uo pipefail

APK="${1:?Falta el fitxer APK}"
PKG=io.github.jossanmar9.compas
OUT=android/proves
mkdir -p "$OUT"
api=$(adb shell getprop ro.build.version.sdk | tr -d '\r')

adb install -r "$APK" || exit 1
lib=$(adb shell pm path "$PKG" | head -1 | tr -d '\r' | sed 's#^package:##; s#/base.apk$##')
echo "== PHP de l'app ($lib)"
adb shell run-as "$PKG" "$lib/lib/x86_64/libphp.so" -v
adb shell run-as "$PKG" "$lib/lib/x86_64/libphp.so" -m > "$OUT/moduls-$api.txt"
cat "$OUT/moduls-$api.txt"
falten=0
for m in pdo_sqlite sqlite3 mbstring sodium SimpleXML dom zlib iconv fileinfo session ctype "Zend OPcache"; do
    grep -qx "$m" "$OUT/moduls-$api.txt" || { echo "FALTA l'extensió $m"; falten=1; }
done

adb shell am start -W -n "$PKG/.Principal"
ok=0
for i in $(seq 1 60); do
    if adb shell run-as "$PKG" cat files/dades/servidor.log 2>/dev/null | grep -q '\[200\]: GET /'; then ok=1; break; fi
    sleep 2
done
sleep 6
adb exec-out screencap -p > "$OUT/inici-$api.png"
echo "== servidor.log"
adb shell run-as "$PKG" cat files/dades/servidor.log
echo "== php-errors.log"
adb shell run-as "$PKG" cat files/dades/php-errors.log 2>/dev/null || true
echo "== logcat"
adb logcat -d -s chromium:E AndroidRuntime:E ActivityManager:W | tail -40

# Una còpia de seguretat en passar a segon pla (només si hi ha canvis: ara no n'hi ha, però no ha de fallar)
adb shell input keyevent KEYCODE_HOME
sleep 3
adb shell am start -W -n "$PKG/.Principal"
sleep 4
adb exec-out screencap -p > "$OUT/torna-$api.png"

if [ "$ok" != 1 ]; then echo "Compàs no ha respost"; exit 1; fi
if adb shell run-as "$PKG" cat files/dades/servidor.log | grep -qE 'Fatal error|Warning:|Uncaught'; then echo "Errors de PHP"; exit 1; fi
exit $falten
