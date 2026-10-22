# Proteccions del servidor: només respon a este ordinador, capçaleres de seguretat, redireccions i intents d'entrada
# Copyright (C) 2026 José Vicente Sanjuan
# SPDX-License-Identifier: AGPL-3.0-or-later
# L'executa tests/integracio.py, que hi posa les funcions comunes (G, P, ok, n, db…).

def peticio(q='', host=None, cookies=True, dades=None):
    req = urllib.request.Request(B + ('?' + q if q else ''), data=dades, headers={'Host': host} if host else {})
    obridor = op if cookies else urllib.request.build_opener()
    try:
        r = obridor.open(req)
        return r.status, r.headers, r.read().decode('utf-8', 'replace')
    except urllib.error.HTTPError as e:
        return e.code, e.headers, e.read().decode('utf-8', 'replace')

# Un altre nom de servidor (DNS rebinding) no rep res
codi, _, cos = peticio('r=inici', host='atacant.example:%d' % PORT)
ok(codi == 421 and 'Compàs' not in cos, 'una petició amb un altre nom de servidor es rebutja (421)')
ok(peticio('r=inici', host='localhost:%d' % PORT)[0] == 200, 'localhost sí que funciona')

# Capçaleres
_, cap, _ = peticio('r=inici')
csp = cap.get('Content-Security-Policy', '')
ok("default-src 'self'" in csp and "frame-ancestors 'none'" in csp and "form-action 'self'" in csp, 'política de seguretat de continguts')
ok(cap.get('X-Frame-Options') == 'DENY' and cap.get('X-Content-Type-Options') == 'nosniff', 'X-Frame-Options i nosniff')

# Canvi d'idioma: només torna a adreces de Compàs
class NoSegueix(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, *a, **k):
        return None
ns = urllib.request.build_opener(NoSegueix)
def desti(tornar):
    try:
        ns.open(B + '?r=idioma&l=' + LANG + '&tornar=' + urllib.parse.quote(tornar, safe=''))
    except urllib.error.HTTPError as e:
        return e.headers.get('Location')
for t in ['//atacant.example/', '/\\atacant.example/', 'https://atacant.example/', '/\t/atacant.example']:
    ok(desti(t) == '?', f'el canvi d\'idioma no redirigix a {t!r}')
ok(desti('/?r=socis') == '/?r=socis', 'el canvi d\'idioma torna a la pàgina de Compàs')

# Document pujat: no pot executar res
did = n("select id from documents where fitxer_nom is not null limit 1")
if did:
    _, cap, _ = peticio(f'r=document_fitxer&id={did}')
    ok('sandbox' in cap.get('Content-Security-Policy', ''), 'els documents pujats s\'obrin aïllats')

# Intents d'entrada: es compten per a tot el programa, encara que cada intent vinga sense galeta
def entra(contrasenya):
    o = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))
    h = o.open(B).read().decode()
    c = re.search(r'name="_csrf" value="([^"]+)"', h).group(1)
    return o.open(B + '?r=entrada', urllib.parse.urlencode(dict(_csrf=c, usuari='admin', contrasenya=contrasenya)).encode()).read().decode()
for i in range(5):
    entra('dolenta')
ok('Espera 30' in entra('contrasenya-llarga1') and n("select valor from config where clau='entrada_fallides'") == '5',
   'després de 5 intents fallits en sessions diferents, ni la contrasenya bona entra')
db.execute("update config set valor = '0' where clau = 'entrada_darrer'"); db.commit()
ok('r=eixir' in entra('contrasenya-llarga1'), 'passat el temps d\'espera, es pot entrar')
ok(n("select count(*) from config where clau='entrada_fallides' and coalesce(valor, '') <> ''") == 0, 'entrar bé posa a zero els intents')
