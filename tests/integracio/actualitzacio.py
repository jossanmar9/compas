# Actualització de la base de dades: només es prepara una vegada per versió, amb una còpia abans
# Copyright (C) 2026 José Vicente Sanjuan
# SPDX-License-Identifier: AGPL-3.0-or-later
# L'executa tests/integracio.py, que hi posa les funcions comunes (G, P, ok, n, db…).

import glob as _glob
versio = n('pragma user_version')
ok(versio > 0, 'la base de dades apunta la versió de l esquema')
copies = lambda: _glob.glob(D + '/copies/*-abans-actualitzar.db')
abans = len(copies())
G('r=inici')
G('r=llista&e=persones')
ok(len(copies()) == abans and n('pragma user_version') == versio, 'sense canvis de versió no es repeteix ni es copia res')
db.execute('pragma user_version = 1')
db.commit()
G('r=inici')
ok(n('pragma user_version') == versio, 'una base de dades d una versió anterior s actualitza')
ok(len(copies()) == abans + 1, 'i abans es fa una còpia «abans d actualitzar»')
h = G('r=copia')
ok(('Abans d' in h and 'actualitzar' in h) or 'Antes de actualizar' in h, 'la còpia apareix a la llista')

# 2.0: una base de dades d'abans dels tipus d'entitat es queda com a societat musical, sense els apartats nous que no li calen
db.execute("delete from config where clau in ('tipus_entitat', 'moduls_desactivats')")
db.execute("insert into config(clau, valor) values('moduls_desactivats', 'loteria')")
db.execute('pragma user_version = 1')
db.commit()
G('r=inici')
ok(n("select valor from config where clau = 'tipus_entitat'") == 'societat_musical', 'una instal·lació anterior és una societat musical')
des = n("select valor from config where clau = 'moduls_desactivats'").split(',')
ok('loteria' in des and 'llicencies' in des and 'entitats' in des and 'carrecs_festa' in des and 'activitats' not in des and 'escola' not in des,
   f'conserva els mòduls que tenia desactivats i no activa els que no li calen ({des})')
db.execute("update config set valor = null where clau = 'moduls_desactivats'")
db.commit()

# 2.8: els talonaris antics (un sencer per persona) passen a entregues de paperets, amb els seus números i diners
db.execute("insert into loteries(nom, numeros, preu, juga, participacions_talonari) values('Antiga', '11111', 5, 4, 50)")
lant = n('select max(id) from loteries')
pa = n('select id from persones order by id limit 1')
db.execute("insert into talonaris(loteria_id, numero, persona_id, data_entrega, venudes, tornades, import_liquidat, data_liquidacio) values(?, 1, ?, '2025-10-01', 40, 10, 200, '2025-12-01')", (lant, pa))
db.execute("insert into talonaris(loteria_id, numero, persona_id, data_entrega) values(?, 2, ?, '2025-10-01')", (lant, pa))
db.execute("insert into talonaris(loteria_id, numero) values(?, 3)", (lant,))
db.execute("delete from loteria_entregues")
db.execute("delete from config where clau = 'talonaris_migrats'")
db.execute('pragma user_version = 1')
db.commit()
G('r=inici')
ents = db.execute('select * from loteria_entregues where loteria_id = ? order by des_de', (lant,)).fetchall()
ok(len(ents) == 2 and ents[0]['des_de'] == 1 and ents[0]['fins_a'] == 50 and ents[0]['tornades'] == 10 and ents[0]['import_pagat'] == 200
   and ents[1]['des_de'] == 51 and ents[1]['tornades'] is None, 'els talonaris antics passen a entregues amb els números i els diners')
ok(n("select f.codi from loteria_entregues e join formes_pagament f on f.id = e.forma_pagament_id where e.loteria_id = ? and e.import_pagat = 200", lant) == 'efectiu', 'els diners antics, en efectiu')
ok(n('select talonaris from loteries where id = ?', lant) == 3, 'el sorteig recorda quants talonaris s havien fet')
h = G(f'r=loteria&id={lant}')
ok('>60<' in h and not err(h), 'paperets a la caixa: 150 fets − 100 repartits + 10 tornats')
