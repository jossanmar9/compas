# Buidatge real en una instal·lació temporal: no toca les dades de l'usuari.
# SPDX-License-Identifier: AGPL-3.0-or-later
from pathlib import Path

abans = n('select count(*) from persones')
comptes = [tuple(x) for x in db.execute('select id, usuari, contrasenya, actiu, idioma, creat from usuaris order by id')]
permisos = [tuple(x) for x in db.execute('select * from usuari_rols order by usuari_id, rol_id')]
nom = n("select valor from config where clau='societat_nom'")
cataleg = [tuple(x) for x in db.execute('select * from tipus_socis order by id')]
ok(abans > 0, 'hi ha dades de prova')
for camps in [dict(confirmacio='BUIDAR', contrasenya='incorrecta'),
              dict(confirmacio='NO', contrasenya='contrasenya-llarga1')]:
    h = P('r=copia', dict(accio='buida', **camps))
    ok(n('select count(*) from persones') == abans and not err(h), 'sense confirmació i contrasenya correctes no buida')

# El compte administratiu no pot buidar, encara que envie una petició directa.
jar = http.cookiejar.CookieJar()
o = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(jar))
h = o.open(B).read().decode()
token = re.search(r'name="_csrf" value="([^"]+)"', h).group(1)
o.open(B + '?r=entrada', urllib.parse.urlencode(dict(_csrf=token, usuari='secre', contrasenya='contrasenya-llarga2')).encode()).read()
try:
    o.open(B + '?r=copia', urllib.parse.urlencode(dict(_csrf=token, accio='buida', confirmacio='BUIDAR', contrasenya='contrasenya-llarga2')).encode())
    ok(False, 'administratiu rebutjat')
except urllib.error.HTTPError as e:
    ok(e.code == 403, 'administratiu rebutjat amb 403')
ok(n('select count(*) from persones') == abans, 'administratiu no modifica dades')

# Si hi ha un error a mitjan operació, SQLite recupera totes les files.
db.execute("CREATE TRIGGER prova_buidatge BEFORE DELETE ON persones BEGIN SELECT RAISE(ABORT, 'prova rollback'); END")
db.commit()
h = P('r=copia', dict(accio='buida', confirmacio='BUIDAR', contrasenya='contrasenya-llarga1'))
ok(n('select count(*) from persones') == abans and n('select count(*) from socis') > 0, 'un error desfà tot el buidatge')
db.execute('DROP TRIGGER prova_buidatge')
db.commit()

h = P('r=copia', dict(accio='buida', confirmacio='BUIDAR', contrasenya='contrasenya-llarga1'))
ok(not err(h) and 'r=eixir' not in h, 'buidatge correcte tanca la sessió')
for taula in ['persones', 'socis', 'alumnes', 'membres', 'rebuts', 'moviments', 'documents', 'document_fitxers', 'activitats', 'tarifes']:
    ok(n('select count(*) from ' + taula) == 0, 'esborra ' + taula)
ok([tuple(x) for x in db.execute('select id, usuari, contrasenya, actiu, idioma, creat from usuaris order by id')] == comptes, 'conserva comptes i contrasenyes')
ok([tuple(x) for x in db.execute('select * from usuari_rols order by usuari_id, rol_id')] == permisos, 'conserva permisos')
ok(n("select valor from config where clau='societat_nom'") == nom, 'conserva configuració')
ok([tuple(x) for x in db.execute('select * from tipus_socis order by id')] == cataleg, 'conserva catàlegs')
ok(not list(db.execute('pragma foreign_key_check')), 'integritat referencial correcta')
ok(n("select count(*) from auditoria where accio='dades_buidades'") == 1, 'registra el buidatge')
ok(n("select valor from config where clau='copia_ultim_canvi'") == '0', 'reinicia la marca de còpies després de buidar l’auditoria')
copies = list((Path(D) / 'copies').glob('*-manual.db'))
ok(len(copies) == 2, 'crea còpia prèvia i conserva també la de l’intent fallit')
with sqlite3.connect(copies[0]) as copia:
    ok(copia.execute('select count(*) from persones').fetchone()[0] == abans, 'la còpia conserva les dades esborrades')
token = re.search(r'name="_csrf" value="([^"]+)"', h).group(1)
h = op.open(B + '?r=entrada', urllib.parse.urlencode(dict(_csrf=token, usuari='admin', contrasenya='contrasenya-llarga1')).encode()).read().decode()
ok('r=eixir' in h and not err(h), 'es pot tornar a entrar amb el compte conservat')
