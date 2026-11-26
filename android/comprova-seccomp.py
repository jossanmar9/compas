#!/usr/bin/env python3
"""
Comprova que el PHP d'Android només fa crides al sistema que Android deixa fer a les apps.

Android (des de la 8) talla amb seccomp les crides al sistema que bionic no fa servir: un programa que en
fa una mor amb SIGSYS (codi 159). El PHP de l'app està compilat amb musl, no amb bionic, així que es
comprova amb les crides que ha fet de veres (strace, mentre passen les proves de Compàs en un
ordinador arm64) contra la llista d'Android 10 per a arm64, que és la versió més antiga que admet l'app.

La llista es fa com la fa Android (bionic/libc/tools/genseccomp.py): les crides de SYSCALLS.TXT i de les
llistes permeses, menys les de les llistes prohibides.

Ús: comprova-seccomp.py <registres de strace>...
SPDX-License-Identifier: AGPL-3.0-or-later
"""
import base64
import re
import sys
import urllib.error
import urllib.request

ETIQUETA = 'android10-release'
ARQ = 'arm64'
BASE = f'https://android.googlesource.com/platform/bionic/+/refs/tags/{ETIQUETA}/libc/'
PERMESES = ['SYSCALLS.TXT', 'SECCOMP_WHITELIST_COMMON.TXT', 'SECCOMP_WHITELIST_APP.TXT']
PROHIBIDES = ['SECCOMP_BLACKLIST_COMMON.TXT', 'SECCOMP_BLACKLIST_APP.TXT']
LINIA = re.compile(r'^\s*[^(]*?\s\*?([\w|]+)(?::(\w+))?(?::\d+)?\s*\(.*\)\s+([\w,]+)\s*$')


def llista(nom: str) -> set[str]:
    for intent in range(4):
        try:
            text = base64.b64decode(urllib.request.urlopen(BASE + nom + '?format=TEXT', timeout=30).read()).decode()
            break
        except urllib.error.HTTPError as e:
            if e.code == 404 and nom != 'SYSCALLS.TXT':
                print(f'(no hi ha {nom} a {ETIQUETA})')
                return set()
            if intent == 3:
                raise
        except Exception:
            if intent == 3:
                raise
    crides = set()
    for l in text.splitlines():
        l = l.split('#', 1)[0].strip()
        m = LINIA.match(l)
        if not m:
            continue
        funcio, crida, arqs = m.groups()
        arqs = arqs.split(',')
        if 'all' in arqs or 'lp64' in arqs or ARQ in arqs:
            crides.add(crida or funcio.split('|')[0])
    return crides


def fetes(fitxers: list[str]) -> set[str]:
    """Noms de les crides d'un registre de strace (una crida per línia, «pid nom(…»)"""
    crides = set()
    for f in fitxers:
        for l in open(f, errors='replace'):
            m = re.match(r'^(?:\d+\s+)?([a-z_][a-z_0-9]*)\(', l)
            if m:
                crides.add(m.group(1))
    return crides


def main() -> int:
    permeses = set().union(*(llista(n) for n in PERMESES)) - set().union(*(llista(n) for n in PROHIBIDES))
    fet = fetes(sys.argv[1:])
    if not fet:
        print('No hi ha cap crida al sistema als fitxers de strace')
        return 1
    # Noms que strace i bionic escriuen diferent
    alies = {'fstat': 'fstat64', 'newfstatat': 'newfstatat', 'pread64': 'pread64', 'pwrite64': 'pwrite64'}
    fora = sorted(c for c in fet if c not in permeses and alies.get(c) not in permeses)
    print(f'{len(fet)} crides diferents; {len(permeses)} permeses a les apps a {ETIQUETA} ({ARQ}).')
    print('Fetes:', ', '.join(sorted(fet)))
    if fora:
        print('NO PERMESES (Android tancaria PHP):', ', '.join(fora))
        return 1
    print('Totes són permeses.')
    return 0


if __name__ == '__main__':
    sys.exit(main())
