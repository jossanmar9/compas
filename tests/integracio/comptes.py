# Comptes: rols, permisos reals del consultor, noms ASCII, sessions i accés només des d'este ordinador.
# SPDX-License-Identifier: AGPL-3.0-or-later
import html as _html

PW = 'compas-proves-2026!'

def compte(nom, rol='consultor'):
    return P('r=usuaris&id=nou', dict(usuari=nom, rol=rol, actiu='1', idioma=L,
        contrasenya=PW, contrasenya2=PW))

for nom, rol in [('consultor', 'consultor'), ('gestor1', 'administratiu'), ('gestor2', 'administratiu')]:
    h = compte(nom, rol)
    ok(not err(h) and n('select count(*) from usuaris where usuari=?', nom) == 1, 'crea compte ' + nom)

abans = n('select count(*) from usuaris')
h = compte('ADMIN', 'admin')
ok(n('select count(*) from usuaris') == abans and ('ja existeix' in h or 'ya existe' in h), 'nom duplicat: missatge específic i cap alta')
for nom in ['Àlex', 'raül', 'iñaki', 'franç', 'nom amb espais']:
    h = compte(nom)
    ok(n('select count(*) from usuaris') == abans and not err(h), 'rebutja nom no ASCII: ' + nom)

# L'admin canvia la contrasenya de qui vulga, però es manté el seu compte i la resta de sessions.
def sessio(nom, contrasenya=PW):
    jar = http.cookiejar.CookieJar()
    o = urllib.request.build_opener(urllib.request.ProxyHandler({}), urllib.request.HTTPCookieProcessor(jar))
    h = o.open(B).read().decode()
    c = re.search(r'name="_csrf" value="([^"]+)"', h).group(1)
    h = o.open(B + '?r=entrada', urllib.parse.urlencode(dict(_csrf=c, usuari=nom, contrasenya=contrasenya)).encode()).read().decode()
    return o, c, jar, h

def req(o, q, dades=None):
    try:
        r = o.open(B + '?' + q, urllib.parse.urlencode(dades).encode() if dades is not None else None)
        return r.status, r.read().decode('utf-8', 'replace')
    except urllib.error.HTTPError as e:
        return e.code, e.read().decode('utf-8', 'replace')

consultor, token, jar, h = sessio('consultor')
ok('r=eixir' in h and 'r=usuaris' not in h, 'el consultor entra sense opcions administratives')
for e in ['persones','socis','alumnes','grups','instruments','cessions','manteniments']:
    c, h = req(consultor, 'r=llista&e=' + e)
    ok(c == 200 and not err(h) and 'csv=1' not in h, 'consulta sense exportació: ' + e)
for e in ['persones','socis','alumnes','instruments']:
    c, h = req(consultor, 'r=fitxa&e=' + e + '&id=1')
    ok(c == 200 and not err(h) and 'IBAN' not in h and 'r=elimina' not in h, 'fitxa limitada: ' + e)

prohibides = ['r=llista&e=mandats','r=llista&e=rebuts','r=llista&e=entitats_federades',
    'r=cerca_ref&e=mandats&q=1','r=economia','r=caixa','r=copia','r=usuaris','r=configuracio',
    'r=llista&e=persones&csv=1','r=butlleti','r=consentiment&persona=1','r=certificat&tipus=soci&id=1',
    'r=llistats','r=document_fitxer&id=1','r=manual_pdf','r=edita&e=persones&id=1',
    'r=activitat_llista&id=1','r=rgpd','r=full_alta','r=comunicats']
for q in prohibides:
    ok(req(consultor, q)[0] == 403, 'consultor: prohibida també per URL ' + q)
nom_abans = n('select nom from persones where id=1')
ok(req(consultor, 'r=edita&e=persones&id=1', dict(_csrf=token,nom='CANVI NO AUTORITZAT'))[0] == 403
    and n('select nom from persones where id=1') == nom_abans, 'el POST directe del consultor no modifica dades')
ok(req(consultor, 'r=elimina&e=persones&id=1', dict(_csrf=token))[0] == 403, 'el consultor no elimina per POST')


# Un canvi de contrasenya invalida les sessions anteriors d'eixe compte.
cid = n('select id from usuaris where usuari=?','consultor')
P('r=usuaris&id=' + str(cid), dict(usuari='consultor',rol='consultor',actiu='1',idioma=L,contrasenya='nova-compas-2026!',contrasenya2='nova-compas-2026!'))
ok('autocomplete="username"' in req(consultor,'r=inici')[1], 'la sessió antiga caduca després del canvi de contrasenya')

# La regla de no accents es manté també davant un UPDATE SQL directe.
try:
    db.execute('update usuaris set usuari=? where id=?',('Àlex',cid));db.commit();valid=False
except sqlite3.IntegrityError:
    db.rollback();valid=True
ok(valid, 'la base de dades impedix noms amb accents també en editar')


# Migració d'una base de dades antiga: ni comptes ni contrasenyes es perden i els noms no col·lisionen.
db.execute('drop trigger usuaris_nom_ascii_insert')
db.execute('drop trigger usuaris_nom_ascii_update')
hash_antic = n('select contrasenya from usuaris where usuari=?', 'gestor2')
ids=[]
for nom in ['alex','Àlex','àlex']:
    ids.append(db.execute('insert into usuaris(usuari,contrasenya) values(?,?)',(nom,hash_antic)).lastrowid)
db.execute('pragma user_version=1');db.commit()
G('r=inici')
noms=[n('select usuari from usuaris where id=?',i) for i in ids]
ok(noms==['alex','alex-2','alex-3'], 'migració sense accents ni col·lisions de noms antics')
ok(all(n('select contrasenya from usuaris where id=?',i)==hash_antic for i in ids), 'la migració conserva identificadors i contrasenyes')
ok(n("select count(*) from auditoria where accio='migra_usuari'")==2, 'la migració anota els noms antics i nous')

# La sessió del consultor no pot recuperar-se imposant una galeta inventada.
req_cookie=urllib.request.Request(B,headers={'Cookie':'compas=fixat-per-un-atacant'})
r=urllib.request.build_opener(urllib.request.ProxyHandler({})).open(req_cookie)
ok('compas=fixat-per-un-atacant' not in r.headers.get('Set-Cookie','') and 'compas=' in r.headers.get('Set-Cookie',''), 'session.use_strict_mode substituïx IDs inventats')

# Només este ordinador: cap altra adreça ni cap nom de fora, encara que porte capçaleres de proxy.
for remote, host, esperat in [
    ('127.0.0.1', '127.0.0.1', 200),
    ('127.0.0.1', 'localhost', 200),
    ('192.168.1.50', '127.0.0.1', 403),
    ('8.8.8.8', 'localhost', 403),
    ('127.0.0.1', 'evil.example', 421)]:
    php = "require 'src/xarxa.php'; register_shutdown_function(function(){echo '|'.http_response_code();}); "
    php += "$_SERVER=['REMOTE_ADDR'=>'"+remote+"','HTTP_HOST'=>'"+host+"','HTTP_X_FORWARDED_FOR'=>'127.0.0.1']; "
    php += "comprova_xarxa(); http_response_code(200);"
    r=subprocess.run(['php','-r',php],capture_output=True,text=True,env=dict(os.environ,COMPAS_LAN='1',COMPAS_LAN_IP='192.168.1.20'))
    ok(r.stdout.endswith('|'+str(esperat)), 'accés local: '+remote+' / '+host+' → '+str(esperat))

# Ja no hi ha mode de xarxa: ni panell a l'inici ni pantalla de configuració
ok('xarxa_local' not in G('r=inici'), 'l\'inici no mostra cap opció de xarxa')
ok(G('r=xarxa_local').startswith('HTTP404'), 'la pantalla de xarxa local ja no existix')
ok(n("select count(*) from sqlite_master where name='entrada_intents'") == 0, 'la taula d\'intents per IP ja no hi és')
