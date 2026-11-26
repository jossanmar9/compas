# Compàs com l'app d'Android (COMPAS_ANDROID=1): galeta secreta de l'app, baixades, còpies a Documents i recuperació
# SPDX-License-Identifier: AGPL-3.0-or-later

# Sense la galeta de l'app, el servidor no respon (una altra app del mòbil no hi pot entrar)
try:
    urllib.request.urlopen(B + '?r=inici', timeout=5)
    ok(False, 'sense la galeta de l app: 403')
except urllib.error.HTTPError as e:
    ok(e.code == 403, 'sense la galeta de l app: 403')

def opener_app():
    jar = http.cookiejar.CookieJar()
    jar.set_cookie(http.cookiejar.Cookie(0, 'compas_app', 'secret-de-proves-android', None, False, '127.0.0.1', False, False,
                                         '/', True, False, None, False, None, None, {}))
    return urllib.request.build_opener(urllib.request.HTTPCookieProcessor(jar))

# Les pàgines es veuen com sempre
h = get('r=inici')
ok('<html' in h and not err(h), 'pàgina d inici normal')

# Un CSV (GET): es guarda i es redirigix a r=baixada, que el torna amb el nom del fitxer
class SenseRedir(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, *a, **k):
        return None
galetes = [h for h in op.handlers if isinstance(h, urllib.request.HTTPCookieProcessor)][0]
sense = urllib.request.build_opener(galetes, SenseRedir)
try:
    sense.open(B + '?r=llista&e=persones&csv=1')
    ok(False, 'CSV: redirecció a la baixada')
except urllib.error.HTTPError as e:
    loc = e.headers.get('Location', '')
    ok(e.code == 303 and re.match(r'^\?r=baixada&f=[0-9a-f]{32}$', loc), 'CSV: redirecció a la baixada')
cos, cap = get('r=llista&e=persones&csv=1', raw=True)
ok('attachment' in cap.get('Content-Disposition', '') and '.csv' in cap.get('Content-Disposition', '') and len(cos) > 100, 'CSV: el fitxer arriba amb el seu nom')
ok(cap.get('Content-Length') == str(len(cos)), 'CSV: mida correcta')

# La còpia de seguretat baixada amb un formulari (POST)
r = op.open(urllib.request.Request(B + '?r=copia', data=urllib.parse.urlencode({'_csrf': C, 'accio': 'baixa'}).encode()))
cos = r.read()
ok(cos[:16] == b'SQLite format 3\0' and 'attachment' in r.headers.get('Content-Disposition', '') and '.db' in r.headers.get('Content-Disposition', ''),
   'còpia baixada des d un formulari')

# Una baixada que no existeix, o d'una altra sessió
h = G('r=baixada&f=' + '0' * 32)
ok(h.startswith('HTTP404'), 'baixada inexistent: 404')
f = sorted(Path(D, 'baixades').glob('*.json'))[-1].stem
try:
    d = opener_app().open(B + '?r=baixada&f=' + f).read()
    ok(b'SQLite' not in d and b';' not in d[:200], 'la baixada d una altra sessió no es dona')
except urllib.error.HTTPError as e:
    ok(True, 'la baixada d una altra sessió no es dona')

# Còpies externes: a Documents/Compàs, sense haver de configurar res
P('r=copia', {'accio': 'fes'})
ext = Path(D, 'Documents', 'Compàs')
ok(n("select valor from config where clau='copia_externa'") == str(ext), 'carpeta externa: Documents/Compàs')
ok(any(ext.glob('compas-*.db')), 'la còpia es guarda també a Documents/Compàs')
h = G('r=copia')
ok(('Documents/Compàs' in h) and not err(h), 'la pàgina de còpies parla del mòbil')
ok(('Baixa una còpia al mòbil' in h or 'Descargar una copia al móvil' in h) and 'còpia a l&#039;ordinador' not in h, 'textos del mòbil en lloc de l ordinador')

# Recuperar l'accés sense codi: des de la mateixa app
nou = opener_app()
def obri_nou(q, dades=None):
    return nou.open(B + '?' + q, urllib.parse.urlencode(dades).encode() if dades else None).read().decode('utf-8', 'replace')
h = obri_nou('r=recupera')
tok = re.search(r'name="_csrf" value="([^"]+)"', h).group(1)
ok('fitxer_android' in h, 'recuperació: botó per a fer-ho des del mòbil')
h = obri_nou('r=recupera', {'_csrf': tok, 'accio': 'fitxer_android'})
ok(Path(D, 'recupera-acces').exists() and 'name="codi"' not in h, 'recuperació: l app crea el fitxer i ja no cal el codi')
Path(D, 'recupera-acces').unlink()
