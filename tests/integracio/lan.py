# LAN: rols, permisos reals, comptes únics i sessions simultànies.
# SPDX-License-Identifier: AGPL-3.0-or-later
import concurrent.futures as _futures
import html as _html

PW = 'compas-proves-2026!'

persones_abans = n('select count(*) from persones')
h = P('r=copia', dict(accio='buida', confirmacio='BUIDAR', contrasenya='contrasenya-llarga1'))
ok(n('select count(*) from persones') == persones_abans and not err(h), 'LAN: ni l’administrador pot buidar dades mentre el servidor està compartit')

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

# 6 sessions de navegadors independents, enviant lectures i escriptures al mateix temps.
sessions = [sessio('gestor1'),sessio('gestor2')] + [sessio('consultor') for _ in range(4)]
def treball(i):
    o, c, _, _ = sessions[i]
    if i < 2:
        return req(o, 'r=edita&e=cursos', dict(_csrf=c, nom='LAN-' + str(i) + '-' + L, data_inici='2026-09-01', data_fi='2027-06-30', estat='actiu', actual='0'))
    return req(o, 'r=llista&e=persones')
with _futures.ThreadPoolExecutor(max_workers=6) as pool:
    resultats = list(pool.map(treball, range(6)))
ok(all(c == 200 and not err(h) for c,h in resultats), 'sis sessions simultànies: lectures i escriptures correctes')
ok(n("select count(*) from cursos where nom like 'LAN-%'") == 2, 'les dues escriptures simultànies es conserven')
ok(n('pragma integrity_check') == 'ok', 'integritat després de les sessions simultànies')

# Dues persones obrin la mateixa fitxa: el segon enviament no sobreescriu el primer.
versions=[]
for o,c,_,_ in sessions[:2]:
    h=req(o,'r=edita&e=aules&id=1')[1]
    versions.append(re.search(r'name="_revisio" value="([^"]+)"',h).group(1))
o,c,_,_=sessions[0]
ok(req(o,'r=edita&e=aules&id=1',dict(_csrf=c,_revisio=versions[0],nom='Aula LAN A',capacitat='20',observacions=''))[0]==200,'primera edició d’una fitxa compartida')
o,c,_,_=sessions[1]
ok(req(o,'r=edita&e=aules&id=1',dict(_csrf=c,_revisio=versions[1],nom='Aula LAN B',capacitat='20',observacions=''))[0]==409
    and n('select nom from aules where id=1')=='Aula LAN A','formulari antic: avís de conflicte sense perdre el primer canvi')

# El bloqueig d'un compte no bloqueja la resta de persones de la LAN.
for _ in range(5): sessio('gestor1','incorrecta')
_,_,_, bloquejat = sessio('gestor1')
ok('Espera 30' in bloquejat, 'el compte amb cinc errors queda temporalment bloquejat')
_,_,_, altre = sessio('gestor2')
ok('r=eixir' in altre, 'un altre compte continua entrant encara que el primer estiga bloquejat')

# Còpies de seguretat, també si coincideixen en el mateix segon.
P('r=copia', dict(accio='fes'))
P('r=copia', dict(accio='fes'))
ok(n('select count(*) from auditoria where accio=?','copia_feta') >= 3, 'còpies manuals en LAN disponibles')
files = list(Path(D).glob('copies/*-manual.db'))
ok(len(files) >= 2, 'còpies consecutives amb noms diferents, sense sobreescriure')
for fitxer in files[-2:]:
    with sqlite3.connect(fitxer) as copia: ok(copia.execute('pragma integrity_check').fetchone()[0] == 'ok', 'integritat de la còpia LAN')
ok('restaura_fitxer' not in G('r=copia'), 'en LAN no es mostra el formulari de restauració')

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

# Host i origen: un proxy o una capçalera falsa no convertix un client en el servidor.
for lan, remote, host, esperat in [
    ('0','192.168.1.50','127.0.0.1',403),
    ('1','192.168.1.50','192.168.1.20',200),
    ('1','8.8.8.8','192.168.1.20',403),
    ('1','192.168.1.50','evil.example',421)]:
    php = "require 'src/xarxa.php'; register_shutdown_function(function(){echo '|'.http_response_code();}); "
    php += "$_SERVER=['REMOTE_ADDR'=>'"+remote+"','HTTP_HOST'=>'"+host+"','SERVER_ADDR'=>'192.168.1.20','HTTP_X_FORWARDED_FOR'=>'127.0.0.1']; "
    php += "comprova_xarxa(); http_response_code(200); echo peticio_del_servidor()?'local':'remot';"
    r=subprocess.run(['php','-r',php],capture_output=True,text=True,env=dict(os.environ,COMPAS_LAN=lan,COMPAS_LAN_IP='192.168.1.20'))
    ok(r.stdout.endswith('|'+str(esperat)) and 'local' not in r.stdout, 'origen LAN validat: '+remote+' / '+host)

# La sessió del consultor no pot recuperar-se imposant una galeta inventada.
req_cookie=urllib.request.Request(B,headers={'Cookie':'compas=fixat-per-un-atacant'})
r=urllib.request.build_opener(urllib.request.ProxyHandler({})).open(req_cookie)
ok('compas=fixat-per-un-atacant' not in r.headers.get('Set-Cookie','') and 'compas=' in r.headers.get('Set-Cookie',''), 'session.use_strict_mode substituïx IDs inventats')


# La restauració no pot programar-se mentre hi ha clients LAN.
h=P('r=copia',dict(accio='restaura_llista',nom=files[-1].name))
ok(h.startswith('HTTP409') and not (Path(D)/'restaura.db').exists(), 'restauració LAN rebutjada sense preparar canvis de base de dades')

# La còpia periòdica és per peticions, i conserva els canvis pendents.
num_auto=len(list(Path(D).glob('copies/*-auto.db')))
db.execute("insert into config(clau,valor) values('copia_auto_marca','0') on conflict(clau) do update set valor='0'")
db.execute("insert into auditoria(accio,entitat,detall) values('modifica','aules','prova de copia periodica')");db.commit()
G('r=inici')
ok(len(list(Path(D).glob('copies/*-auto.db')))>num_auto, 'primera petició amb canvis després de 15 minuts fa una còpia automàtica')

# Els camps virtuals (com l'IBAN) també participen en la detecció de formularis antics.
pid=n("select persona_id from mandats where estat='actiu' limit 1")
_, antic=form('r=edita&e=persones&id='+str(pid))
iban_abans=n("select iban from mandats where persona_id=? and estat='actiu'",pid)
db.execute("update mandats set iban='DE89370400440532013000' where persona_id=? and estat='actiu'",(pid,));db.commit()
try:
    h=P('r=edita&e=persones&id='+str(pid),antic)
    ok(h.startswith('HTTP409') and n("select iban from mandats where persona_id=? and estat='actiu'",pid)=='DE89370400440532013000', 'un canvi bancari extern al registre principal no queda sobreescrit per un formulari antic')
finally:
    db.execute("update mandats set iban=? where persona_id=? and estat='actiu'",(iban_abans,pid));db.commit()

# Caducitat per inactivitat LAN, sense tancar les sessions dels altres comptes.
o,c,j,_=sessio('gestor2')
sid=next(x.value for x in j if x.name=='compas')
fitxer_sessio=Path(D)/'sessions'/('sess_'+sid)
sess=fitxer_sessio.read_text(encoding='utf-8')
sess=re.sub(r'activitat\|i:\d+;', 'activitat|i:'+str(int(time.time())-1900)+';', sess)
fitxer_sessio.write_text(sess,encoding='utf-8')
ok('autocomplete="username"' in req(o,'r=inici')[1], 'sessió LAN caducada després de 30 minuts sense activitat')
ok('r=eixir' in G('r=inici'), 'la caducitat d’un compte no tanca la sessió de l’administrador')
