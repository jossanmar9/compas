#!/usr/bin/env python3
"""PHP + servidor HTTPS reals, confiança privada només de la prova, sense tocar el sistema."""
# SPDX-License-Identifier: AGPL-3.0-or-later
import http.cookiejar
import os
from pathlib import Path
import re
import socket
import sqlite3
import ssl
import subprocess
import sys
import tempfile
import time
import urllib.error
import urllib.parse
import urllib.request
import zipfile

ROOT = Path(__file__).resolve().parent.parent
HELPER = Path(sys.argv[1] if len(sys.argv)>1 else ROOT/'dist/compas-xarxa.exe').resolve()
NAME = 'compas-proves.local'
SECRET = 'compas-https-prova-privada-abcdefghijklmnopqrstuvwxyz'
def port():
    with socket.socket() as s:
        s.bind(('127.0.0.1', 0)); return s.getsockname()[1]
def ok(value, label):
    if not value: raise AssertionError(label)
    print('PASS:', label)
original_resolve = socket.getaddrinfo
def resolve(host, *args, **kwargs):
    return original_resolve('127.0.0.1' if host == NAME else host, *args, **kwargs)
socket.getaddrinfo = resolve  # Només la prova: SNI i verificació de nom TLS continuen actius.

with tempfile.TemporaryDirectory(prefix='compas-https-') as tmp:
    backend, public = port(), port()
    folder=Path(tmp)
    log=open(folder/'servidor.log','w')
    php=subprocess.Popen(['php','-S',f'127.0.0.1:{backend}','-t',str(ROOT/'app'),str(ROOT/'app/index.php')],env=dict(os.environ,COMPAS_DADES=tmp,COMPAS_LAN='1',COMPAS_PROXY_TOKEN=SECRET,COMPAS_NOM_LAN=NAME),stdout=log,stderr=log)
    proxy=subprocess.Popen([str(HELPER),'-dades',str(folder/'https'),'-nom',NAME,'-port',str(public),'-backend',f'127.0.0.1:{backend}','-sense-mdns'],env=dict(os.environ,COMPAS_PROXY_TOKEN=SECRET),stdout=log,stderr=log)
    try:
        for _ in range(100):
            try:
                if (folder/'https/compas-ca.crt').exists():
                    with socket.create_connection(('127.0.0.1',public),timeout=.2): break
            except OSError: pass
            ok(proxy.poll() is None and php.poll() is None, 'servidors en marxa') if proxy.poll() is not None or php.poll() is not None else None
            time.sleep(.1)
        context=ssl.create_default_context(cafile=str(folder/'https/compas-ca.crt'))
        base=f'https://{NAME}:{public}/'
        def opener():
            jar=http.cookiejar.CookieJar()
            return urllib.request.build_opener(urllib.request.ProxyHandler({}),urllib.request.HTTPCookieProcessor(jar),urllib.request.HTTPSHandler(context=context)),jar
        op,jar=opener()
        def read(op,path='',data=None):
            return op.open(base+path, urllib.parse.urlencode(data).encode() if data is not None else None, timeout=15).read().decode()
        def csrf(body): return re.search(r'name="_csrf" value="([^"]+)"',body).group(1)
        # Un client LAN autenticat pel proxy continua sent remot per a la configuració inicial.
        direct=urllib.request.build_opener(urllib.request.ProxyHandler({}))
        req=urllib.request.Request(f'http://127.0.0.1:{backend}/',headers={'Host':NAME,'X-Compas-Proxy-Token':SECRET,'X-Compas-Client':'192.168.250.100'})
        try: direct.open(req);raise AssertionError('configuració inicial remota permesa')
        except urllib.error.HTTPError as e: ok(e.code==403,'el proxy no convertix un client remot en servidor local')
        h=read(op); token=csrf(h)
        h=read(op,'',dict(_csrf=token,societat_nom='HTTPS Prova',idioma='ca',usuari='admin',contrasenya='compas-proves-2026!',contrasenya2='compas-proves-2026!',usuari2='secre',contrasenya_b='compas-proves-2027!',contrasenya_b2='compas-proves-2027!',accepta_avis='1',exemple='1'))
        h=read(op,'?r=inici');token=csrf(h)
        ok('r=eixir' in h,'configuració i entrada per HTTPS')
        ok(all(c.secure and c.has_nonstandard_attr('HttpOnly') for c in jar),'galeta Secure i HttpOnly')
        read(op,'?r=usuaris&id=nou',dict(_csrf=token,usuari='consulta',rol='consultor',actiu='1',idioma='ca',contrasenya='compas-proves-2028!',contrasenya2='compas-proves-2028!'))
        other,otherjar=opener(); h=read(other);t=csrf(h)
        h=read(other,'?r=entrada',dict(_csrf=t,usuari='consulta',contrasenya='compas-proves-2028!'))
        ok('r=eixir' in h,'segon compte simultani per HTTPS')
        try: read(other,'?r=economia');raise AssertionError('consultor amb economia')
        except urllib.error.HTTPError as e: ok(e.code==403,'consultor limitat darrere del proxy')
        direct=urllib.request.build_opener(urllib.request.ProxyHandler({}))
        for headers in [{},{'X-Compas-Client':'127.0.0.1','X-Compas-Local':'1','X-Compas-Proxy-Token':'fals'}]:
            try: direct.open(urllib.request.Request(f'http://127.0.0.1:{backend}/',headers=headers));raise AssertionError('PHP directe accessible')
            except urllib.error.HTTPError as e: ok(e.code==403,'PHP intern rebutja petició directa o falsa')
        req=urllib.request.Request(base+'?r=copia_tancament',headers={'X-Compas-Token':'inventat','X-Compas-Proxy-Token':SECRET,'X-Compas-Client':'127.0.0.1','X-Compas-Local':'1'})
        try: op.open(req);raise AssertionError('aturada des de client')
        except urllib.error.HTTPError as e: ok(e.code==403,'secret de tancament no es pot injectar pel proxy')
        req=urllib.request.Request(base,headers={'Host':'atacant.local'})
        try: op.open(req);raise AssertionError('Host fals acceptat')
        except urllib.error.HTTPError as e: ok(e.code==421,'Host extern rebutjat')
        with zipfile.ZipFile(folder/'https/Client-Compas.zip') as z:
            ok(not any(n.endswith('.key') or n.endswith('.db') for n in z.namelist()),'paquet client sense claus ni dades')
            ok('compas-ca.crt' in z.namelist() and base in z.read('connexio.txt').decode(),'paquet amb URL i certificat públic correctes')
        db=sqlite3.connect(folder/'compas.db');ok(db.execute('pragma foreign_key_check').fetchone() is None,'integritat de la base de dades');db.close()
    finally:
        proxy.terminate();php.terminate();proxy.wait(timeout=10);php.wait(timeout=10);log.close()
        text=(folder/'servidor.log').read_text(errors='replace')
        if any(x in text for x in ['Fatal error','Uncaught','PHP Warning:']): raise AssertionError(text)
print('HTTPS: totes les proves correctes.')
