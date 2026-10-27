# Assistent des d'Inici: servidor, rols, CSRF i ordres autenticades sense executar GUI.
# SPDX-License-Identifier: AGPL-3.0-or-later
import hashlib
import hmac
from pathlib import Path

secret = 'control-proves-xarxa-abcdefghijklmnopqrstuvwxyz'
fitxer = Path(D) / 'control-xarxa.txt'
ok('r=xarxa_local' in G('r=inici'), 'administrador veu el boto a Inici')
G('r=xarxa_local')
ok(not fitxer.exists(), 'GET no envia ordres al llançador')
P('r=xarxa_local', {})
ordre, signatura = fitxer.read_text().strip().splitlines()
ok(hmac.compare_digest(signatura, hmac.new(secret.encode(), ordre.encode(), hashlib.sha256).hexdigest()), 'ordre autenticada amb HMAC')

script = Path.cwd() / 'launcher/linux/control-xarxa.php'
def consumeix():
    return subprocess.run(['php', str(script), D], env=dict(os.environ, COMPAS_TOKEN=secret), capture_output=True).returncode

ok(consumeix() == 0 and not fitxer.exists(), 'el consumidor valida i consumix una ordre')
fitxer.write_text(ordre + '\n' + '0' * 64 + '\n')
ok(consumeix() == 1 and not fitxer.exists(), 'elimina i rebutja una ordre manipulada')
caducada = 'xarxa|1000000000|' + 'a' * 32
fitxer.write_text(caducada + '\n' + hmac.new(secret.encode(), caducada.encode(), hashlib.sha256).hexdigest() + '\n')
ok(consumeix() == 1, 'rebutja ordre caducada encara que estiga signada')

P('r=usuaris&id=nou', dict(usuari='consulta', rol='consultor', actiu='1', idioma=L, contrasenya='compas-consulta-2026!', contrasenya2='compas-consulta-2026!'))
def sessio(nom, pw):
    o=urllib.request.build_opener(urllib.request.ProxyHandler({}), urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))
    h=o.open(B).read().decode();token=re.search(r'name="_csrf" value="([^"]+)"',h).group(1)
    o.open(B+'?r=entrada',urllib.parse.urlencode(dict(_csrf=token,usuari=nom,contrasenya=pw)).encode()).read()
    return o,token
gestor,t=sessio('secre','contrasenya-llarga2')
h=gestor.open(B+'?r=inici').read().decode()
ok('r=xarxa_local' in h, 'usuari normal veu el boto')
gestor.open(B+'?r=xarxa_local',urllib.parse.urlencode(dict(_csrf=t)).encode()).read()
ok(fitxer.exists() and consumeix() == 0, 'usuari normal pot obrir l’assistent')
try:
    gestor.open(B+'?r=xarxa_local',b'')
    ok(False,'CSRF rebutjat')
except urllib.error.HTTPError as e:
    ok(e.code==400 and not fitxer.exists(),'sense CSRF no envia ordres')
consultor,t=sessio('consulta','compas-consulta-2026!')
ok('r=xarxa_local' not in consultor.open(B+'?r=inici').read().decode(),'consultor no veu el boto')
for dades in [None,urllib.parse.urlencode(dict(_csrf=t)).encode()]:
    try:
        consultor.open(B+'?r=xarxa_local',dades)
        ok(False,'consultor rebutjat')
    except urllib.error.HTTPError as e:
        ok(e.code==403 and not fitxer.exists(),'consultor bloquejat també per petició directa')
