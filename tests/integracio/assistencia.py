# Fulls d'assistència en Excel: un full per agrupació, amb els membres d'alta, la fila de dates i les caselles per a la X.
# Copyright (C) 2026 José Vicente Sanjuan
# SPDX-License-Identifier: AGPL-3.0-or-later
# L'executa tests/integracio.py, que hi posa les funcions comunes (G, P, ok, n, db…).
import zipfile
import xml.etree.ElementTree as ET

NS = {'m': 'http://schemas.openxmlformats.org/spreadsheetml/2006/main'}

def llibre(q):
    d, cap = get(q, raw=True)
    z = zipfile.ZipFile(io.BytesIO(d))
    return d, cap, z

def text_full(z, i):
    """Tots els textos d'un full (cel·les en línia)"""
    x = ET.fromstring(z.read(f'xl/worksheets/sheet{i}.xml'))
    return x, [t.text or '' for t in x.iter('{%s}t' % NS['m'])]

banda = n("select id from agrupacions where nom='Banda'")
h = G('r=llista&e=agrupacions'); ok(not err(h) and 'href="?r=assistencia"' in h, 'botó per a totes al llistat')
h = G(f'r=fitxa&e=agrupacions&id={banda}'); ok(f'r=assistencia&amp;agrupacio={banda}' in h, 'botó a la fitxa de l agrupació')

# Una agrupació
d, cap, z = llibre(f'r=assistencia&agrupacio={banda}')
ok(d[:2] == b'PK' and 'spreadsheetml' in cap.get('Content-Type', ''), 'descàrrega xlsx')
ok('assistencia-banda.xlsx' in cap.get('Content-Disposition', ''), 'nom del fitxer')
ok(z.testzip() is None and '[Content_Types].xml' in z.namelist() and 'xl/workbook.xml' in z.namelist(), 'zip vàlid')
wb = ET.fromstring(z.read('xl/workbook.xml'))
fulls = [s.get('name') for s in wb.iter('{%s}sheet' % NS['m'])]
ok(fulls == ['Banda'], 'un sol full: %s' % fulls)
x, textos = text_full(z, 1)
membres = db.execute("""select p.nom, p.cognoms from membres t join persones p on p.id=t.persona_id
    where t.agrupacio_id=? and t.estat='actiu' and (t.data_baixa is null or t.data_baixa > date('now','localtime'))""", (banda,)).fetchall()
ok(membres and all(any(m['nom'] in t and m['cognoms'] in t for t in textos) for m in membres), 'hi ha tots els membres d alta (%d)' % len(membres))
ok(any('Banda' in t for t in textos) and any(t in ('Data', 'Fecha') for t in textos), 'títol i fila de la data')
formules = [f.text for f in x.iter('{%s}f' % NS['m'])]
ok(any('COUNTIF(D6:W6,"X")' in f for f in formules), 'total per membre')
ok(any(f.startswith('IF(D$4=""') for f in formules), 'assistents per data')
dv = list(x.iter('{%s}dataValidation' % NS['m']))
ok(len(dv) == 1 and dv[0].get('type') == 'list' and dv[0].find('m:formula1', NS).text == '"X"', 'llista X a les caselles')
ok(dv[0].get('sqref', '').startswith('D6:W'), 'rang de les caselles: %s' % dv[0].get('sqref'))
ok(x.find('.//m:conditionalFormatting', NS) is not None, 'format de les X')
ok(x.find('.//m:pane', NS).get('topLeftCell') == 'D6', 'files i columnes fixes')
ok(x.find('.//m:pageSetup', NS).get('orientation') == 'landscape', 'apaïsat')
ok("Print_Titles" in z.read('xl/workbook.xml').decode(), 'capçaleres repetides en imprimir')

# Qui ha causat baixa no ix
m = db.execute('select t.id, p.nom, p.cognoms from membres t join persones p on p.id=t.persona_id where t.agrupacio_id=? limit 1', (banda,)).fetchone()
db.execute("update membres set estat='baixa' where id=?", (m['id'],)); db.commit()
d, cap, z = llibre(f'r=assistencia&agrupacio={banda}')
ok(not any(t == f"{m['cognoms']}, {m['nom']}" for t in text_full(z, 1)[1]), 'un membre de baixa no ix al full')
db.execute("update membres set estat='actiu' where id=?", (m['id'],)); db.commit()

# Columnes a mida
d, cap, z = llibre(f'r=assistencia&agrupacio={banda}&columnes=8')
x, _ = text_full(z, 1)
ok(any('COUNTIF(D6:K6,"X")' in (f.text or '') for f in x.iter('{%s}f' % NS['m'])), '8 assajos')
d, cap, z = llibre(f'r=assistencia&agrupacio={banda}&columnes=999')
x, _ = text_full(z, 1)
ok(any('COUNTIF(D6:W6,"X")' in (f.text or '') for f in x.iter('{%s}f' % NS['m'])), 'columnes fora de rang: 20')

# Totes les agrupacions d'alta
d, cap, z = llibre('r=assistencia')
fulls = [s.get('name') for s in ET.fromstring(z.read('xl/workbook.xml')).iter('{%s}sheet' % NS['m'])]
actives = [r[0] for r in db.execute("select nom from agrupacions where estat='actiu'")]
ok(sorted(fulls) == sorted(actives) and len(fulls) >= 2, 'un full per agrupació: %s' % fulls)
ok(n("select count(*) from auditoria where accio='assistencia'") >= 1, 'queda a l auditoria')
h = G('r=assistencia&agrupacio=99999'); ok(not err(h) and 'avis error' in h, 'agrupació inexistent')
