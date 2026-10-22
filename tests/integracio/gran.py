# Societats grans: camp de persona amb cerca, paginació del repertori i dels llistats
# Copyright (C) 2026 José Vicente Sanjuan
# SPDX-License-Identifier: AGPL-3.0-or-later
# L'executa tests/integracio.py, que hi posa les funcions comunes (G, P, ok, n, db…).

import json as _json
db.executemany("insert into persones(nom, cognoms, data_naixement, actiu) values(?, ?, '1980-01-01', 1)",
               [(f'Nom{i}', 'Martí Ferrer' if i % 7 == 0 else 'Garcia Soler') for i in range(600)])
tp = n('select id from tipus_obres limit 1')
db.executemany("insert into obres(titol, tipus_id, numero_arxiu, estat) values(?, ?, ?, 'complet')", [(f'Obra gran {i}', tp, f'G-{i:04d}') for i in range(250)])
db.commit()
h = G('r=edita&e=socis')
ok('data-cerca-ref="persones"' in h and h.count('<option') < 50, 'amb moltes persones, el camp es busca escrivint')
r = _json.loads(G('r=cerca_ref&e=persones&q=marti+ferrer'))
ok(len(r) == 30 and all('Martí Ferrer' in x['text'] for x in r), 'la cerca troba sense accents i en torna 30 com a molt')
ok(_json.loads(G('r=cerca_ref&e=persones&q=')) == [], 'sense text no torna res')
pid = r[0]['id']
P('r=edita&e=socis', dict(persona_id=pid, numero='7777', tipus='', data_alta='2026-01-01', data_baixa='', estat='actiu', observacions=''))
ok(n("select persona_id from socis where numero='7777'") == pid, 'es desa el soci triat amb la cerca')
h = G('r=repertori')
ok('class="pagines"' in h and 'tot=1' in h and h.count('class="num-arxiu"') == 100, 'el repertori gran es mostra per pàgines')
ok(G('r=repertori&tot=1').count('class="num-arxiu"') == n('select count(*) from obres'), 'i es pot mostrar sencer per a imprimir')
h = G('r=llista&e=persones&p=5')
ok('class="salt"' in h, 'els llistats llargs no mostren totes les pàgines')

# Ordenar els llistats fent clic a les capçaleres
import html as _html
def _col(q, i):
    h = G(q)
    return [_html.unescape(re.sub('<[^>]+>', '', re.findall(r'<td>(.*?)</td>', f, re.S)[i])).replace('⚠', '').strip()
            for f in re.findall(r'<tr data-href[^>]*>(.*?)</tr>', h, re.S)], h
pid0 = n("select min(id) from persones")
for k, num in enumerate(['10', '2', '1', '30', '3']):
    db.execute("insert into persones(nom, cognoms, data_naixement) values(?, ?, '1980-01-01')", [['Òscar', 'Zoe', 'Àlex', 'alba', 'Bea'][k], 'Ordre Prova'])
    db.execute("insert into socis(persona_id, numero, data_alta) values(?, ?, '2020-01-01')", [n('select max(id) from persones'), 'T' + num])
db.commit()
v, h = _col('r=llista&e=socis&q=Ordre+Prova&o=numero', 1)
ok(v == ['T1', 'T2', 'T3', 'T10', 'T30'], f'socis per número, en ordre natural: {v}')
ok('aria-sort="ascending"' in h and 'o=numero' in h, 'la capçalera marca la columna i el sentit')
v, _ = _col('r=llista&e=socis&q=Ordre+Prova&o=numero&d=1', 1)
ok(v == ['T30', 'T10', 'T3', 'T2', 'T1'], 'i al revés')
v, h = _col('r=llista&e=socis&q=Ordre+Prova', 1)
ok(v[0] == 'T30' and 'aria-sort="descending"' in h, 'en tornar al llistat es manté l\'ordre triat')
ok(G('r=llista&e=socis&q=Ordre+Prova&o=numero&d=1&csv=1').split('\n')[1].split(';')[2] == 'T30', 'l\'exportació CSV segueix el mateix ordre')
v, h = _col('r=llista&e=socis&q=Ordre+Prova&o=-', 1)
ok('aria-sort' not in h, 'es pot tornar a l\'ordre de sempre')
v, _ = _col('r=llista&e=persones&q=Ordre+Prova&o=nom', 0)
ok(v == ['alba', 'Àlex', 'Bea', 'Òscar', 'Zoe'], f'els noms amb accent i minúscula van al seu lloc: {v}')
v, _ = _col('r=llista&e=socis&q=Ordre+Prova&o=persona_id&d=1', 0)
ok(len(v) == 5 and v[0].endswith('Zoe'), 'per persona (cognoms i nom), també al revés')
ok('aria-sort' not in G('r=llista&e=socis&o=inexistent'), 'una columna que no existeix no fa res')
