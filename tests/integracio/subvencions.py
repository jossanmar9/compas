# Subvencions: organismes i requisits editables, com es justifica cada una, cobraments, despeses imputades, relació, còpia per a l'any següent i avisos
# Copyright (C) 2026 José Vicente Sanjuan
# SPDX-License-Identifier: AGPL-3.0-or-later
# L'executa tests/integracio.py, que hi posa les funcions comunes (G, P, ok, n, db…).
import datetime as _dt
from zoneinfo import ZoneInfo as _Zona

avui = _dt.datetime.now(_Zona(os.environ.get('COMPAS_ZONA') or 'Europe/Madrid')).date()  # el dia de Compàs (PHP), no el de l'ordinador de proves
dia = lambda d: (avui + _dt.timedelta(days=d)).isoformat()
ES = L == 'es'

# Valors inicials, en l'idioma de la instal·lació
orgs = [r[0] for r in db.execute('select nom from organismes order by ordre')]
ok(orgs == (['Generalitat', 'Diputación', 'Ayuntamiento', 'Federación', 'Otros'] if ES else ['Generalitat', 'Diputació', 'Ajuntament', 'Federació', 'Altres']), f'organismes inicials: {orgs}')
ok(n('select count(*) from requisits_justificacio') == 10, 'requisits de justificació inicials')
dip = n("select id from organismes where ordre = 20")
ajt = n("select id from organismes where ordre = 30")
reqs_dip = [r[0] for r in db.execute('select requisit_id from organisme_requisits where organisme_id = ? order by requisit_id', [dip])]
ok(len(reqs_dip) == 8, 'la Diputació demana normalment 8 requisits')

# Dades d'exemple: una concedida a mig justificar, una per demanar i una de l'any passat tancada
ok(n('select count(*) from subvencions') == 3 and n("select count(*) from subvencions where estat = 'prevista'") == 1, 'subvencions d\'exemple')
ok(n('select sum(import) from subvencio_despeses') == 1535.5 and n('select count(*) from moviments where proveidor is not null') >= 2, 'despeses d\'exemple imputades, amb proveïdor')
h = G('r=inici'); ok('Institut Valencià de Cultura' in h or 'Instituto Valenciano de Cultura' in h, 'l\'inici avisa de la que cal demanar prompte')

# Pantalles
for q in ['r=llista&e=subvencions', 'r=llista&e=organismes', 'r=llista&e=requisits_justificacio', 'r=edita&e=subvencions', f'r=fitxa&e=organismes&id={dip}']:
    h = G(q); ok(not err(h) and 'HTTP' not in h[:8], f'pantalla {q}')
h = G('r=edita&e=subvencions'); ok('data-segons="organisme_id"' in h and re.search(r'data-mapa="\{&quot;%d&quot;:\[' % n('select min(organisme_id) from organisme_requisits'), h), 'el formulari sap quins requisits demana cada organisme (llistes)')
ok('e=subvencions' in G('r=inici'), 'al menú')

# Una subvenció concedida de la Diputació, amb els seus requisits
base = dict(nom='Ajudes a societats musicals', organisme_id=dip, exercici=str(avui.year), expedient='DIP-2026-123', objecte='Escola de música', estat='concedida',
            data_limit_sollicitud='', data_sollicitud=dia(-120), data_resolucio=dia(-30), data_limit_justificacio=dia(10), data_justificacio='',
            import_pressupost='3000', import_sollicitat='2000', import_concedit='1000', import_justificar='1.500,00', observacions='')
P('r=edita&e=subvencions', dict(base, **{'requisits[]': [''] + [str(r) for r in reqs_dip]}))
sid = n("select id from subvencions where expedient = 'DIP-2026-123'")
ok(sid and n('select import_justificar from subvencions where id = ?', sid) == 1500, 'subvenció creada (import amb coma)')
ok(n('select count(*) from subvencio_requisits where subvencio_id = ?', sid) == 8, 'amb els 8 requisits')
h = G(f'r=fitxa&e=subvencions&id={sid}')
ok(not err(h) and '0/8' in h and 'llista-requisits' in h and ('Queden 10 dies' in h or 'Quedan 10 días' in h), 'fitxa: requisits 0/8 i el termini de justificació')

# Marcar requisits fets; en canviar la llista, els fets que es mantenen no es perden
P(f'r=subvencio_requisits&id={sid}', {'fet[]': [str(r) for r in reqs_dip[:3]]})
ok(n('select count(*) from subvencio_requisits where subvencio_id = ? and fet = 1 and data_fet = ?', sid, avui.isoformat()) == 3, '3 requisits fets, amb la data')
ok('3/8' in G(f'r=fitxa&e=subvencions&id={sid}'), 'la fitxa diu 3/8')
nou_req = n('select id from requisits_justificacio where id not in (%s) order by id limit 1' % ','.join(map(str, reqs_dip)))
P(f'r=edita&e=subvencions&id={sid}', dict(base, **{'requisits[]': [''] + [str(r) for r in reqs_dip[1:]] + [str(nou_req)]}))
ok(n('select count(*) from subvencio_requisits where subvencio_id = ?', sid) == 8 and n('select count(*) from subvencio_requisits where subvencio_id = ? and fet = 1', sid) == 2,
   'en llevar un requisit fet i afegir-ne un altre, els altres fets es conserven')

# Cobraments: entren sols al llibre de caixa
P('r=edita&e=subvencio_cobraments', dict(subvencio_id=sid, data=avui.isoformat(), concepte='Bestreta', compte='banc', observacions='', **{'import': '600'}))
h = G(f'r=caixa&any={avui.year}'); ok(('Subvenció: Ajudes a societats musicals (Bestreta)' in h or 'Subvención: Ajudes a societats musicals (Bestreta)' in h) and '600,00' in h, 'el cobrament surt al llibre de caixa')
h = G(f'r=fitxa&e=subvencions&id={sid}'); ok('400,00' in h, 'falten 400 € per cobrar')

# Despeses del llibre de caixa imputades
def despesa(concepte, imp, prov='Música Llorens SL', nif='B12345678', fra='F-2026-001'):
    P('r=edita&e=moviments', dict(data=avui.isoformat(), tipus='despesa', compte='banc', categoria='material', concepte=concepte, justificant=fra, proveidor=prov, nif=nif, observacions='', **{'import': imp}))
    return n('select max(id) from moviments')
m1 = despesa('Partitures per a l\'escola', '900')
m2 = despesa('Professorat de llenguatge', '1000', 'Escola de Música SL', 'B87654321', 'F-77')
P('r=edita&e=subvencio_despeses', dict(subvencio_id=sid, moviment_id=m1, observacions='', **{'import': ''}))
ok(n('select import from subvencio_despeses where moviment_id = ?', m1) == 900, 'sense import, s\'imputa tota la despesa')
P('r=edita&e=subvencio_despeses', dict(subvencio_id=sid, moviment_id=m2, observacions='', **{'import': '400'}))
h = G(f'r=fitxa&e=subvencions&id={sid}'); ok('1.300,00' in h and '200,00' in h, 'justificats 1.300 €, en falten 200')
# Una altra subvenció (de l'Ajuntament) no pot imputar més del que queda d'una factura
P('r=edita&e=subvencions', dict(base, nom='Conveni escola', organisme_id=ajt, expedient='AJT-1', estat='concedida', import_concedit='800', import_justificar='', **{'requisits[]': ['']}))
sid2 = n("select id from subvencions where expedient = 'AJT-1'")
h = P('r=edita&e=subvencio_despeses', dict(subvencio_id=sid2, moviment_id=m1, observacions='', **{'import': ''}))
ok(n('select count(*) from subvencio_despeses where subvencio_id = ?', sid2) == 0 and ('imputada sencera' in h or 'imputado entero' in h), 'una factura ja imputada sencera no es pot tornar a imputar')
h = P('r=edita&e=subvencio_despeses', dict(subvencio_id=sid2, moviment_id=m2, observacions='', **{'import': '700'}))
ok(n('select count(*) from subvencio_despeses where subvencio_id = ?', sid2) == 0 and '600,00' in h, 'ni per més del que en queda (600 €)')
P('r=edita&e=subvencio_despeses', dict(subvencio_id=sid2, moviment_id=m2, observacions='', **{'import': ''}))
ok(n('select import from subvencio_despeses where subvencio_id = ?', sid2) == 600, 'en blanc, s\'imputa el que queda (600 €)')
h = P(f'r=edita&e=moviments&id={m2}', dict(data=avui.isoformat(), tipus='despesa', compte='banc', categoria='material', concepte='Professorat', justificant='F-77', proveidor='x', nif='', observacions='', **{'import': '500'}))
ok(n('select import from moviments where id = ?', m2) == 1000, 'no es pot baixar l\'import d\'una despesa per davall del que hi ha imputat')
h = G(f'r=fitxa&e=moviments&id={m2}'); ok('e=subvencio_despeses' in h, 'la fitxa de la despesa mostra a quines subvencions està imputada')

# Relació de despeses (per a imprimir i en CSV)
h = G(f'r=subvencio_relacio&id={sid}')
ok(not err(h) and 'B12345678' in h and 'F-2026-001' in h and 'Música Llorens SL' in h and '1.300,00' in h and 'DIP-2026-123' in h, 'relació de despeses amb proveïdor, NIF, factura i total')
c = G(f'r=subvencio_relacio&id={sid}&csv=1').splitlines()
ok(len(c) == 4 and 'F-77' in c[2] and c[3].endswith('1300,00'), f'relació en CSV: {c[-1]}')

# Documents lligats a la subvenció
P('r=edita&e=documents', dict(titol='Resolució de la Diputació', tipus='subvencio', persona_id='', data_document=avui.isoformat(), data_caducitat='', subvencio_id=sid, observacions=''))
ok('Resolució de la Diputació' in G(f'r=fitxa&e=subvencions&id={sid}'), 'el document surt a la fitxa de la subvenció')

# Avís a l'inici
h = G('r=inici'); ok(('Subvencions: terminis' in h or 'Subvenciones: plazos' in h) and 'Ajudes a societats musicals' in h, 'avís de termini a l\'inici')

# Còpia per a l'any següent
P(f'r=subvencio_duplica&id={sid}', {})
s3 = n('select max(id) from subvencions')
f3 = db.execute('select exercici, estat, import_concedit, data_limit_justificacio from subvencions where id = ?', [s3]).fetchone()
ok(f3[0] == avui.year + 1 and f3[1] == 'prevista' and f3[2] is None and f3[3] == (_dt.date.fromisoformat(dia(10)).replace(year=avui.year + 1)).isoformat(), f'còpia per a l\'any següent, amb les dates un any més tard: {tuple(f3)}')
ok(n('select count(*) from subvencio_requisits where subvencio_id = ?', s3) == 8 and n('select count(*) from subvencio_requisits where subvencio_id = ? and fet = 1', s3) == 0, 'amb els mateixos requisits, sense fer')
ok(n('select count(*) from subvencio_cobraments where subvencio_id = ?', s3) == 0 and n('select count(*) from subvencio_despeses where subvencio_id = ?', s3) == 0, 'sense cobraments ni despeses')

# Organismes i requisits editables
h = P(f'r=elimina&e=organismes&id={dip}', {}); ok(n('select count(*) from organismes where id = ?', dip) == 1, 'no es pot eliminar un organisme amb subvencions')
P('r=edita&e=organismes', {'nom': 'Ministeri de Cultura', 'ordre': '15', 'estat': 'actiu', 'observacions': '', 'requisits[]': ['', str(reqs_dip[0]), str(nou_req)]})
min_id = n("select id from organismes where nom = 'Ministeri de Cultura'")
ok(n('select count(*) from organisme_requisits where organisme_id = ?', min_id) == 2, 'organisme nou amb com es justifica normalment')
P('r=edita&e=requisits_justificacio', dict(nom='Annex IV signat', descripcio='Model de la convocatòria', ordre='5', estat='actiu', observacions=''))
r_nou = n("select id from requisits_justificacio where nom = 'Annex IV signat'"); ok(r_nou, 'requisit nou')
ok('Annex IV signat' in G('r=edita&e=subvencions'), 'el requisit nou es pot triar')
h = P(f'r=elimina&e=requisits_justificacio&id={reqs_dip[1]}', {}); ok(n('select count(*) from requisits_justificacio where id = ?', reqs_dip[1]) == 1, 'no es pot eliminar un requisit en ús')
nom_baixa = db.execute('select nom from requisits_justificacio where id = ?', [reqs_dip[1]]).fetchone()[0]
P(f'r=edita&e=requisits_justificacio&id={reqs_dip[1]}', dict(nom=nom_baixa, descripcio='', ordre='20', estat='baixa', observacions=''))
ok(nom_baixa not in G('r=edita&e=subvencions') and nom_baixa in G(f'r=edita&e=subvencions&id={sid}'), 'un requisit de baixa no ix en les noves però sí en les que el tenen')
P(f'r=elimina&e=requisits_justificacio&id={r_nou}', {}); ok(n('select count(*) from requisits_justificacio where id = ?', r_nou) == 0, 'un requisit sense ús es pot eliminar')

# Llistat amb filtres i suma
for f in ['en_curs', 'per_justificar', 'per_cobrar']:
    h = G(f'r=llista&e=subvencions&filtre={f}'); ok(not err(h) and 'Ajudes a societats musicals' in h, f'filtre {f}')
ok('Conveni escola' in G('r=llista&e=subvencions&filtre=per_cobrar') and 'Conveni escola' not in G('r=llista&e=subvencions&filtre=en_curs&q=xyz'), 'filtre per cobrar')

# L'usuari/ària també ho pot fer
cj2 = http.cookiejar.CookieJar(); op2 = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(cj2))
h2 = op2.open(B).read().decode(); c2 = re.search(r'name="_csrf" value="([^"]+)"', h2).group(1)
op2.open(B + '?r=entrada', urllib.parse.urlencode(dict(_csrf=c2, usuari='secre', contrasenya='contrasenya-llarga2')).encode()).read()
h2 = op2.open(B + f'?r=fitxa&e=subvencions&id={sid}').read().decode()
ok('subvencio_requisits' in h2 and 'r=edita' in h2, 'l\'usuari/ària veu i edita les subvencions')

# Mòdul desactivable
P('r=moduls', {'moduls[]': ['escola', 'agrupacions', 'repertori', 'instruments', 'uniformes', 'economia', 'loteria', 'caixa', 'junta', 'documents', 'comunicats'],
               'avisos[]': ['copia_externa', 'junta_renovacio', 'documents_caducitat', 'certificat_delictes', 'cessions_vencudes', 'curs_actual', 'subvencions_terminis']})
h = G('r=inici'); ok('e=subvencions' not in h and 'Ajudes a societats musicals' not in h, 'desactivat: fora del menú i de l\'inici')
ok('HTTP404' in G('r=llista&e=subvencions'), 'desactivat: el llistat no s\'obri')
ok('Bestreta' not in G(f'r=caixa&any={avui.year}'), 'desactivat: els cobraments no compten a la caixa')
P('r=moduls', {'moduls[]': ['escola', 'agrupacions', 'repertori', 'instruments', 'uniformes', 'economia', 'loteria', 'caixa', 'junta', 'documents', 'comunicats', 'subvencions'],
               'avisos[]': ['copia_externa', 'junta_renovacio', 'documents_caducitat', 'certificat_delictes', 'cessions_vencudes', 'curs_actual', 'subvencions_terminis']})
ok('e=subvencions' in G('r=inici'), 'tornar a activar-lo')

# Eliminar una subvenció esborra els cobraments, les despeses imputades i els requisits (no els moviments ni els documents)
P(f'r=elimina&e=subvencions&id={sid2}', {})
ok(n('select count(*) from subvencio_despeses where subvencio_id = ?', sid2) == 0 and n('select count(*) from moviments where id = ?', m2) == 1, 'eliminar una subvenció no esborra la despesa de la caixa')
