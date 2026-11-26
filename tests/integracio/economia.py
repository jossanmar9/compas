# Economia: tarifes, rebuts, remeses SEPA, domiciliacions, loteria i permisos
# Copyright (C) 2026 José Vicente Sanjuan
# SPDX-License-Identifier: AGPL-3.0-or-later
# L'executa tests/integracio.py, que hi posa les funcions comunes (G, P, ok, n, db…).

ok(n('select count(*) from tarifes')==7, 'tarifes exemple')
ok(n('select count(*) from mandats')==5, 'mandats exemple')
ok(n('select count(*) from rebuts')>=10, f"rebuts exemple: {n('select count(*) from rebuts')}")
ok(n("select count(*) from rebuts where tipus='matricula'")==3, 'matrícula: un rebut per família (3 tutors)')
# Comprovar línies de descompte i total
for r in db.execute('select * from rebuts order by id'):
    s=n('select round(sum(import),2) from rebut_linies where rebut_id=?', r['id'])
    if abs((s or 0)-r['import'])>0.001: fails.append(f"total rebut {r['numero']}")
ok(True,'totals = suma de línies')
desc=db.execute("select concepte, import from rebut_linies where import<0").fetchall()
# Pàgines
for q in ['r=economia','r=llista&e=rebuts','r=llista&e=rebuts&filtre=per_cobrar','r=llista&e=tarifes','r=llista&e=mandats','r=remeses','r=genera_rebuts','r=llista&e=loteries','r=economia_config','r=inici','r=llista&e=loteria_entregues','r=llista&e=formes_pagament']:
    h=get(q); ok('err.inesperat' not in h and 'Error inesperat' not in h and 'class="avis error"' not in h, 'pàgina '+q)
    leftovers=re.findall(r'>((?:[a-z_]+\.)+[a-z_0-9]+)<', h)
    if leftovers: print('   claus sense traduir?', set(leftovers))
# Generar quotes (previsualització i repetició)
any_=time.strftime('%Y'); T='T%d'%((int(time.strftime('%m'))+2)//3)
h=get(f'r=genera_rebuts&tipus=socis&any={any_}&periode={T}')
ok('Ja generat' in h or 'Ya generado' in h, 'previsualització marca ja generats')
nabans=n('select count(*) from rebuts')
post(f'r=genera_rebuts&tipus=socis&any={any_}&periode={T}', dict(data=time.strftime('%Y-%m-%d')))
ok(n('select count(*) from rebuts')==nabans, 'no duplica')
# Generar trimestre següent
T2='T1' if T=='T4' else 'T%d'%(int(T[1])+1); any2=str(int(any_)+1) if T=='T4' else any_
post(f'r=genera_rebuts&tipus=socis&any={any2}&periode={T2}', dict(data=time.strftime('%Y-%m-%d')))
nous=n('select count(*) from rebuts')-nabans
ok(nous==8, f'quotes del trimestre següent: {nous} (5 músics + 3 tutors)')
# Escola: avaluació
curs=db.execute('select * from cursos').fetchone(); per=db.execute('select id from periodes order by ordre').fetchone()[0]
h=get(f"r=genera_rebuts&tipus=escola&curs={curs['id']}&periode=P{per}")
ok('sense_tarifa' not in h, 'escola per avaluació (sense tarifes trimestrals) no peta')
# Config SEPA
h=post('r=economia_config', dict(sepa_nom='', sepa_nif='G12345678', sepa_iban='ES91 2100 0418 4502 0005 1332', sepa_bic='CAIXESBBXXX', sepa_sufix='000', sepa_format='pain.008.001.02', descompte_soci='10', descompte_germans='5', rebut_peu='Associació inscrita'))
ok(n("select valor from config where clau='sepa_iban'")=='ES9121000418450200051332', 'config SEPA desada')
h=post('r=economia_config', dict(sepa_nom='', sepa_nif='G12345678', sepa_iban='ES91 2100 0418 4502 0005 1333', sepa_bic='', sepa_sufix='000', sepa_format='pain.008.001.02', descompte_soci='10', descompte_germans='5', rebut_peu=''))
ok('class="avis error"' in h, 'IBAN incorrecte rebutjat')
# Remesa
h=get('r=remeses')
ids=re.findall(r'name="rebut\[\]" value="(\d+)"', h)
ok(len(ids)>0, f'rebuts per a remesa: {len(ids)}')
data_c=time.strftime('%Y-%m-%d', time.localtime(time.time()+7*86400))
h=post('r=remeses', {'rebut[]': ids, 'data_cobrament': data_c, 'observacions': 'prova'})
rid=n('select max(id) from remeses')
ok(rid is not None and n('select count(*) from rebuts where remesa_id=?', rid)==len(ids), 'remesa creada')
xmlb,hd=get(f'r=remesa_xml&id={rid}', raw=True)
open(S+"/remesa.xml","wb").write(xmlb)
ok(b'<SeqTp>FRST</SeqTp>' in xmlb and b"pain.008.001.02" in xmlb, 'XML amb FRST')
import xml.dom.minidom; xml.dom.minidom.parseString(xmlb)
post(f'r=remesa&id={rid}&accio=cobrada', {})
ok(n("select count(*) from rebuts where remesa_id=? and estat='pagat'", rid)==len(ids), 'remesa cobrada → pagats')
ok(n("select count(*) from mandats where ultim_us is not null")>0, 'mandats amb últim ús')
xml2,_=get(f'r=remesa_xml&id={rid}', raw=True)
ok(b'<SeqTp>RCUR</SeqTp>' in xml2, 'després de cobrada, RCUR')
# Retornat i nova remesa
r1=db.execute("select id from rebuts where remesa_id=? limit 1",(rid,)).fetchone()[0]
post(f'r=rebut_accio&id={r1}&accio=retornat', {})
ok(n('select estat from rebuts where id=?', r1)=='retornat', 'rebut retornat')
h=get('r=remeses'); ok(f'value="{r1}"' in h, 'el retornat torna a estar disponible per a remesa')
# Pagat en efectiu
post(f'r=rebut_accio&id={r1}&accio=pagat', dict(forma='efectiu', data=time.strftime('%Y-%m-%d')))
ok(n('select estat from rebuts where id=?', r1)=='pagat' and n('select forma_pagament from rebuts where id=?', r1)=='efectiu', 'pagat en efectiu')
# Format 08
post('r=economia_config', dict(sepa_nom='', sepa_nif='G12345678', sepa_iban='ES9121000418450200051332', sepa_bic='', sepa_sufix='000', sepa_format='pain.008.001.08', descompte_soci='10', descompte_germans='5', rebut_peu=''))
x8,_=get(f'r=remesa_xml&id={rid}', raw=True); open(S+'/remesa08.xml','wb').write(x8)
ok(b'pain.008.001.08' in x8 and b'NOTPROVIDED' in x8, 'XML 001.08 sense BIC')
# Documents
for q in [f'r=rebut_doc&id={r1}', 'r=rebut_doc', 'r=mandat_doc&id=1', 'r=participacions&loteria=1', 'r=participacions&loteria=1&talonari=2', 'r=participacions&entrega=1', 'r=loteria&id=1', f'r=remesa&id={rid}']:
    h=get(q); ok('Error inesperat' not in h and 'Error inesperado' not in h and '<pre class="detall">' not in h, 'document '+q)
h=get('r=participacions&loteria=1'); ok(h.count('class="participacio"')==300, 'participacions: els 6 talonaris fets × 50')
ht=get('r=participacions&loteria=1&talonari=2'); ok(ht.count('class="participacio"')==50 and '0051' in ht and '0100' in ht and '0101' not in ht, 'participacions d un talonari')
ok(get('r=participacions&entrega=4').count('class="participacio"')==75, 'participacions d una entrega (talonari i mig)')
h=get('r=loteria&id=1'); ok('dif-neg' in h, 'loteria mostra el que falta cobrar en roig')
# Resum: paperets i diners (exemple: 300 fets; 270 repartits; 12 tornats; 60 dècims × 20 €; cobrat 250 + 180 + 350)
import re as _re
ok('>300<' in h and '>270<' in h and '>12<' in h, 'paperets: fets, repartits i tornats')
ok('>42<' in h, 'paperets a la caixa: 300 − 270 + 12')
ok('1.200,00' in h and '780,00' in h, 'administració (60 × 20 €) i cobrat')
ok(_re.search(r'class="destacat"><th>[^<]*</th><td class="num">780,00', h) is not None, 'diners que hauríem de tindre: el cobrat (encara no s ha pagat l administració)')
# Entregar paperets solts: els números següents
pid=n('select id from persones limit 1')
post('r=loteria&id=1', dict(persona_id=pid, quants=20, des_de=''))
e=db.execute('select * from loteria_entregues order by id desc limit 1').fetchone()
ok(e['participacions']==20 and e['des_de']==271 and e['fins_a']==290 and e['persona_id']==pid, 'entrega de 20 paperets solts amb els números següents')
# Venda a la caixa, sense persona
post('r=loteria&id=1', dict(persona_id='', quants=5, des_de=''))
ok(n('select count(*) from loteria_entregues where persona_id is null')==2, 'venda a la caixa sense persona')
# No es poden repetir números ni passar dels fets
nent=n('select count(*) from loteria_entregues')
h=post('r=loteria&id=1', dict(persona_id=pid, quants=10, des_de='100'))
ok('class="avis error"' in h and n('select count(*) from loteria_entregues')==nent, 'números que ja té algú: error')
h=post('r=loteria&id=1', dict(persona_id=pid, quants=100, des_de='250'))
ok('class="avis error"' in h and n('select count(*) from loteria_entregues')==nent, 'més paperets dels que s han fet: error')
# Liquidar una entrega: tornats, diners i forma de pagament (Bizum va al banc del llibre de caixa)
bizum=n("select id from formes_pagament where codi='bizum'")
eid=e['id']
h=post(f'r=edita&e=loteria_entregues&id={eid}', dict(loteria_id=1, persona_id=pid, data_entrega='2026-10-20', participacions=20, des_de=271, fins_a=290, tornades=4, import_pagat='80', forma_pagament_id='', data_pagament='', observacions=''))
ok('class="avis error"' in h, 'diners sense forma de pagament: error')
post(f'r=edita&e=loteria_entregues&id={eid}', dict(loteria_id=1, persona_id=pid, data_entrega='2026-10-20', participacions=20, des_de=271, fins_a=290, tornades=4, import_pagat='80', forma_pagament_id=bizum, data_pagament='2026-11-02', observacions=''))
ok(n('select import_pagat from loteria_entregues where id=?', eid)==80, 'entrega liquidada')
hc=get('r=caixa&any=2026&compte=banc'); ok('Bizum' in hc and '80,00' in hc, 'el pagament amb Bizum entra al banc del llibre de caixa')
# Pagament a l'administració: ix com a despesa
lo=db.execute('select * from loteries where id=1').fetchone()
transf=n("select id from formes_pagament where codi='transferencia'")
post('r=edita&e=loteries&id=1', {k: (lo[k] if lo[k] is not None else '') for k in lo.keys() if k != 'id'} | dict(pagat_administracio='1200', data_pagament_administracio='2026-11-05', forma_pagament_administracio=transf))
h=get('r=loteria&id=1'); ok(n('select pagat_administracio from loteries where id=1')==1200 and '1.200,00' in h, 'pagat a l administració')
hc=get('r=caixa&any=2026&compte=banc'); ok(('administració' in hc or 'administración' in hc) and '1.200,00' in hc, 'el pagament a l administració ix al llibre de caixa (com a despesa)')
# Formes de pagament: se n'afig una de nova
post('r=edita&e=formes_pagament', dict(nom='Targeta', compte='banc', ordre='40', estat='actiu'))
ok(n("select count(*) from formes_pagament where nom='Targeta'")==1 and 'Targeta' in get(f'r=edita&e=loteria_entregues&id={eid}'), 'forma de pagament nova')
h=post(f'r=elimina&e=formes_pagament&id={bizum}', {})
ok(n('select count(*) from formes_pagament where id=?', bizum)==1, 'una forma ja usada no s elimina')
# Mandat: IBAN incorrecte via CRUD
h=post('r=edita&e=mandats', dict(persona_id=pid, titular='', iban='ES00 1234', bic='', referencia='', data_signatura='2026-01-01', estat='actiu', observacions=''))
ok('IBAN' in h and 'class="avis error"' in h, 'CRUD mandat valida IBAN')
# Línia nova actualitza total
rb=db.execute("select id, import from rebuts where estat='per_cobrar' limit 1").fetchone()
post('r=edita&e=rebut_linies', dict(rebut_id=rb['id'], concepte='Extra', import_='5', alumne_id='', ordre='9') | {'import':'5'})
ok(abs(n('select import from rebuts where id=?', rb['id'])-(rb['import']+5))<0.001, 'afegir línia recalcula total')
lid=n('select max(id) from rebut_linies')
post(f'r=elimina&e=rebut_linies&id={lid}', {})
ok(abs(n('select import from rebuts where id=?', rb['id'])-rb['import'])<0.001, 'eliminar línia recalcula total')
# Rebut manual sense número → número automàtic
post('r=edita&e=rebuts', dict(numero='', persona_id=pid, concepte='Donatiu', tipus='altres', periode='', data_emissio=time.strftime('%Y-%m-%d'), import_='', forma_pagament='efectiu', estat='per_cobrar', data_pagament='', mandat_id='', remesa_id='', observacions='') | {'import':'20'})
ok(re.match(r'\d{4}/\d{4}', n('select numero from rebuts order by id desc limit 1') or ''), 'rebut manual numerat')
# Permisos administratiu
cj2=http.cookiejar.CookieJar(); op2=urllib.request.build_opener(urllib.request.HTTPCookieProcessor(cj2))
h=op2.open(B).read().decode(); c2=re.search(r'name="_csrf" value="([^"]+)"',h).group(1)
op2.open(B+'?r=entrada', urllib.parse.urlencode(dict(_csrf=c2, usuari='secre', contrasenya='contrasenya-llarga2')).encode()).read()
h=op2.open(B+'?r=economia').read().decode(); ok('Economia' in h or 'Economía' in h, 'administratiu veu economia')
try:
    h=op2.open(B+'?r=economia_config').read().decode(); ok(False,'administratiu no pot configurar')
except urllib.error.HTTPError as e: ok(e.code==403, 'administratiu no pot configurar (403)')
# Manuals: l'administrador/a veu les dues pestanyes; l'usuari/ària, només el manual d'usuari
adm=re.compile(r'6\. Actuali(?:tz|z)ar Compàs')
h=G('r=ajuda'); ok('manual=admin' in h and not adm.search(h), 'ajuda: pestanya del manual d\'administració i manual d\'usuari per defecte')
h=G('r=ajuda&manual=admin'); ok(adm.search(h), 'ajuda: l\'administrador/a veu el manual d\'administració')
h=op2.open(B+'?r=ajuda&manual=admin').read().decode(); ok('manual=admin' not in h and not adm.search(h) and 'manual-administraci' not in h, 'ajuda: l\'usuari/ària no veu el manual d\'administració')
