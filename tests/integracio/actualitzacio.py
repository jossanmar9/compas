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
