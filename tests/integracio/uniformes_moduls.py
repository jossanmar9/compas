# Tipus de soci, uniformes, mòduls i avisos
# Copyright (C) 2026 José Vicente Sanjuan
# SPDX-License-Identifier: AGPL-3.0-or-later
# L'executa tests/integracio.py, que hi posa les funcions comunes (G, P, ok, n, db…).

# Tipus de soci
noms=[r[0] for r in db.execute('select nom from tipus_socis order by ordre')]
ok(noms==(['Ordinari','Protector','Juvenil',"D'honor"] if L=='ca' else ['Ordinario','Protector','Juvenil','De honor']), 'tipus inicials en l idioma de la instal·lació: %s'%noms)
ok(n("select count(*) from socis where tipus not in (select cast(id as text) from tipus_socis)")==0, 'socis amb id de tipus')
ok(n("select count(*) from tarifes where tipus='quota_soci' and tipus_soci is not null and tipus_soci not in (select cast(id as text) from tipus_socis)")==0, 'tarifes amb id de tipus')
h=G('r=llista&e=socis'); ok(not err(h) and ('Ordinari' in h or 'Ordinario' in h) and 'e=tipus_socis' in h, 'llistat de socis amb el nom del tipus i el botó')
h=P('r=edita&e=tipus_socis', dict(nom='Familiar', descripcio='Unitat familiar', ordre='25', full_alta='1', estat='actiu', observacions=''))
tid=n("select id from tipus_socis where nom='Familiar'"); ok(tid, 'tipus nou creat')
h=G('r=edita&e=socis&id=1'); ok('>Familiar<' in h, 'el tipus nou es pot triar a la fitxa del soci')
h=G('r=full_alta'); ok('Familiar' in h and 'Unitat familiar' in h and ("D'honor" not in h and 'De honor' not in h), 'full d alta amb els tipus marcats')
ordinari=n("select id from tipus_socis where codi='ordinari'")
h=P(f'r=elimina&e=tipus_socis&id={ordinari}', {}); ok(n('select count(*) from tipus_socis where id=?',ordinari)==1, 'no es pot eliminar un tipus en ús')
P(f'r=elimina&e=tipus_socis&id={tid}', {}); ok(n('select count(*) from tipus_socis where id=?',tid)==0, 'es pot eliminar un tipus sense socis')
h=G(f'r=llistats&informe=socis&tipus={ordinari}'); ok(not err(h), 'llistat de socis per tipus')
h=G('r=llistats&informe=memoria'); ok(not err(h) and ('Ordinari' in h or 'Ordinario' in h), 'memòria amb els noms dels tipus')
h=G('r=certificat&tipus=soci&id=1'); ok(not err(h) and ('ordinari' in h.lower()), 'certificat de soci amb el tipus')
h=G('r=genera_rebuts'); ok(not err(h), 'generar rebuts')
# Migració d'una base de dades antiga
db.execute("delete from config where clau='tipus_socis_inicials'"); ids={r[1]:r[0] for r in db.execute('select id,codi from tipus_socis')}
for c,i in ids.items():
    if c: db.execute('update socis set tipus=? where tipus=?',(c,str(i))); db.execute('update tarifes set tipus_soci=? where tipus_soci=?',(c,str(i)))
db.execute('delete from tipus_socis'); db.execute('pragma user_version = 0'); db.commit()  # com si fora d'una versió anterior
ok(n("select count(*) from socis where tipus='ordinari'")>0, 'preparada la base de dades amb codis antics')
G('r=inici')
ok(n('select count(*) from tipus_socis')==4 and n("select count(*) from socis where tipus in ('ordinari','protector')")==0 and n("select count(*) from tarifes where tipus_soci in ('ordinari','protector')")==0, 'migració dels codis antics')
ok(n("select count(*) from socis s join tipus_socis t on cast(t.id as text)=s.tipus where t.codi='protector'")==3, 'els protectors continuen sent protectors')
# Uniformes
h=G('r=uniformes'); ok(not err(h) and 'existencies' in h, 'pantalla d uniformes')
polo=n("select id from uniformes where tipus='polo'"); ds=n("select id from uniformes where tipus='dessuadora'"); mot=n("select id from uniformes where tipus='motxilla'")
h=G(f'r=fitxa&e=uniformes&id={polo}'); ok(not err(h), 'fitxa del polo')
m=re.search(r'<td>M</td><td class="num[^"]*">(-?\d+)</td><td class="num[^"]*">(-?\d+)</td><td class="num[^"]*">(-?\d+)</td>', h)
ok(m and m.groups()==('9','0','9'), 'existències de la talla M: %s'%(m and m.groups(),))
h=G(f'r=fitxa&e=uniformes&id={ds}'); m=re.search(r'<td>12</td>(.*?)</tr>', h); ok(m and re.findall(r'>(-?\d+)<', m.group(1))==['4','1','3'], 'préstec de dessuadora 12: %s'%(m and re.findall(r'>(-?\d+)<', m.group(1)),))
pid=n('select id from persones limit 1')
h=P('r=edita&e=uniforme_lliuraments', dict(uniforme_id=polo, persona_id=pid, talla='xs', quantitat='1', data='2026-10-21', tipus='venda', **{'import':'18'}, data_retorn='', observacions=''))
ok('avis error' in h and n('select count(*) from uniforme_lliuraments where talla=?','XS')==0, 'talla que no és de l article')
h=P('r=edita&e=uniforme_lliuraments', dict(uniforme_id=polo, persona_id=pid, talla='', quantitat='1', data='2026-10-21', tipus='venda', **{'import':'18'}, data_retorn='', observacions=''))
ok('avis error' in h, 'cal triar talla si l article en té')
h=P('r=edita&e=uniforme_lliuraments', dict(uniforme_id=mot, persona_id=pid, talla='', quantitat='0', data='2026-10-21', tipus='venda', **{'import':''}, data_retorn='', observacions=''))
ok('avis error' in h, 'quantitat zero')
h=P('r=edita&e=uniforme_lliuraments', dict(uniforme_id=polo, persona_id=pid, talla=' xxl ', quantitat='2', data='2026-10-21', tipus='venda', **{'import':'36'}, data_retorn='', observacions=''))
lid=n("select max(id) from uniforme_lliuraments"); ok(n('select talla from uniforme_lliuraments where id=?',lid)=='XXL', 'talla normalitzada')
h=G(f'r=fitxa&e=uniforme_lliuraments&id={lid}'); ok('avis atencio' in h, 'avís de talla en negatiu (2 comprades, 3 lliurades)')
h=P('r=edita&e=uniforme_lliuraments', dict(uniforme_id=polo, persona_id=pid, talla='S', quantitat='1', data='2026-10-21', tipus='venda', **{'import':''}, data_retorn='2026-10-22', observacions=''))
ok('avis error' in h, 'devolució només en préstecs')
pr=n("select id from uniforme_lliuraments where tipus='prestec'")
row=db.execute('select uniforme_id,persona_id,talla,quantitat,data from uniforme_lliuraments where id=?',(pr,)).fetchone()
P(f'r=edita&e=uniforme_lliuraments&id={pr}', dict(uniforme_id=row[0], persona_id=row[1], talla=row[2], quantitat=row[3], data=row[4], tipus='prestec', **{'import':''}, data_retorn=time.strftime('%Y-%m-%d'), observacions=''))
h=G(f'r=fitxa&e=uniformes&id={ds}'); m=re.search(r'<td>12</td>(.*?)</tr>', h); ok(m and re.findall(r'>(-?\d+)<', m.group(1))==['4','0','4'], 'el préstec tornat torna a estar disponible')
h=G(f'r=edita&e=uniforme_compres'); ok('data-talles' in h and '<datalist' in h, 'formulari de compra amb les talles')
P(f'r=edita&e=uniformes&id={mot}', dict(nom='Motxilla', tipus='motxilla', talles='s, m;L, m', preu='', estat='actiu', observacions=''))
ok(n('select talles from uniformes where id=?',mot)=='S, M, L', 'talles netejades')
h=G(f'r=fitxa&e=persones&id={pid}'); ok('uniforme_lliuraments' in h, 'lliuraments a la fitxa de la persona')
h=G('r=rgpd'); ok('Uniformes' in h, 'activitat de tractament d uniformes')
# Mòduls i avisos
h=G('r=moduls'); ok(not err(h) and 'moduls[]' in h, 'pantalla de mòduls')
tots=['escola','agrupacions','repertori','instruments','uniformes','economia','loteria','caixa','junta','documents','comunicats']
avisos=['copia_externa','junta_renovacio','documents_caducitat','certificat_delictes','cessions_vencudes','curs_actual']
h=G('r=inici'); ok('r=junta' in h and 'caduquen' in h.lower() or 'caducan' in h.lower(), 'avisos a l inici abans de desactivar')
P('r=moduls', {'moduls[]':[m for m in tots if m not in ('escola','uniformes')], 'avisos[]':[a for a in avisos if a not in ('junta_renovacio','documents_caducitat','copia_externa')]})
ok(n("select valor from config where clau='moduls_desactivats'")=='escola,uniformes', 'mòduls desactivats desats')
h=G('r=inici'); ok(not err(h) and 'e=alumnes' not in h and 'r=uniformes' not in h and 'class="avis atencio">⚠ ' not in h.split('targetes')[0], 'menú i inici sense els mòduls ni els avisos')
ok('carpeta externa' not in h.lower(), 'avís de carpeta externa amagat')
h=G('r=llista&e=alumnes'); ok('HTTP404' in h and 'moduls' in h, 'llistat d un mòdul desactivat')
h=G('r=uniformes'); ok('HTTP404' in h, 'pantalla d un mòdul desactivat')
h=G(f'r=fitxa&e=persones&id={pid}'); ok('uniforme_lliuraments' not in h and 'e=tutories' not in h, 'fitxa de persona sense els mòduls desactivats')
h=G('r=comunicats'); ok('value="professorat"' not in h and 'value="families"' not in h, 'comunicats sense grups de l escola')
h=G('r=llistats'); ok('alumnes_grup' not in h, 'llistats sense els de l escola')
h=G('r=llistats&informe=memoria'); ok(not err(h), 'memòria sense escola')
h=G('r=rgpd'); ok('Uniformes' not in h, 'RGPD sense els mòduls desactivats')
P('r=moduls', {'moduls[]':tots, 'avisos[]':avisos}); h=G('r=inici'); ok('e=alumnes' in h and n("select valor from config where clau='moduls_desactivats'") is None, 'tornar a activar-ho tot')
