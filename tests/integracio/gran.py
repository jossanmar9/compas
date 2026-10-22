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
