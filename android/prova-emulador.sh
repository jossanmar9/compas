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

adb root >/dev/null 2>&1 || true
sleep 3
adb wait-for-device
adb install -r "$APK" || exit 1
lib=$(adb shell pm path "$PKG" | head -1 | tr -d '\r' | sed 's#^package:##; s#/base.apk$##')
php=""
for a in arm64 x86_64; do
    adb shell run-as "$PKG" ls "$lib/lib/$a/libphp.so" >/dev/null 2>&1 && { php="$lib/lib/$a/libphp.so"; break; }
done
echo "== PHP de l'app ($php)"
adb shell run-as "$PKG" "$php" -v
adb shell run-as "$PKG" "$php" -m > "$OUT/moduls-$api.txt"
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
echo "== logcat de Compàs"
adb logcat -d -s Compas:V AndroidRuntime:E System.err:W chromium:E | tail -60
echo "== seccomp (crides al sistema que Android no deixa fer)"
adb shell dmesg 2>/dev/null | grep -iE "type=1326|seccomp|syscall=" | tail -20
adb logcat -d -b all | grep -iE "type=1326|seccomp" | tail -20
echo "== fitxers"
adb shell run-as "$PKG" ls -la files files/dades files/compas 2>&1 | head -40
echo "== processos"
adb shell ps -A | grep -i -E "libphp|compas"
echo "== pantalla (text)"
adb shell uiautomator dump /sdcard/ui.xml >/dev/null 2>&1 && adb shell cat /sdcard/ui.xml | grep -o 'text="[^"]*"' | grep -v 'text=""' | head -40

# Una còpia de seguretat en passar a segon pla (només si hi ha canvis: ara no n'hi ha, però no ha de fallar)
adb shell input keyevent KEYCODE_HOME
sleep 3
adb shell am start -W -n "$PKG/.Principal"
sleep 4
adb exec-out screencap -p > "$OUT/torna-$api.png"

if [ "$ok" != 1 ]; then echo "Compàs no ha respost"; exit 1; fi
if adb shell run-as "$PKG" cat files/dades/servidor.log | grep -qE 'Fatal error|Warning:|Uncaught'; then echo "Errors de PHP"; exit 1; fi
exit $falten
