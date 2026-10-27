# Tipus d'entitat: s'instal·la cada tipus amb les seues dades d'exemple i es recorren totes les pantalles.
# Es comprova el vocabulari, els apartats que s'activen, les categories, les activitats, les llicències,
# les entitats federades, els càrrecs de festa i el canvi de tipus des de la configuració.
# Copyright (C) 2026 José Vicente Sanjuan
# SPDX-License-Identifier: AGPL-3.0-or-later
# L'executa tests/integracio.py, que hi posa les funcions comunes (ok, Compas, ERR_PHP…).
import html as _html

ES = L == 'es'
TIPUS = ['societat_musical', 'club_esportiu', 'colla', 'muixeranga', 'colla_castellera', 'associacio_cultural', 'ampa', 'falla', 'gaiata', 'foguera', 'comparsa', 'penya', 'federacio']

# Textos que ha de dur el menú de cada tipus (i que no ha de dur)
MENU = {
    'societat_musical': (['Alumnat' if not ES else 'Alumnado', 'Repertori' if not ES else 'Repertorio', 'Qualificacions' if not ES else 'Calificaciones'], ['Llicències', 'Licencias']),
    'club_esportiu': (['Esportistes' if not ES else 'Deportistas', 'Equips' if not ES else 'Equipos', 'Llicències' if not ES else 'Licencias', 'Equipacions' if not ES else 'Equipaciones'],
                      ['Repertori', 'Repertorio', 'Butlletins', 'Boletines']),
    'colla': (['Seccions' if not ES else 'Secciones', 'Membres de la colla' if not ES else 'Miembros de la colla'], ['Llicències', 'Licencias']),
    'muixeranga': (['Seccions' if not ES else 'Secciones', 'Muixeranguers i muixerangueres' if not ES else 'Muixeranguers y muixerangueres'], ['Repertori', 'Repertorio']),
    'colla_castellera': (['Seccions' if not ES else 'Secciones', 'Castellers i castelleres' if not ES else 'Castellers y castelleres'], ['Repertori', 'Repertorio']),
    'associacio_cultural': (['Tallers i cursos' if not ES else 'Talleres y cursos', 'Participants' if not ES else 'Participantes'], ['Repertori', 'Repertorio']),
    'ampa': (['Famílies associades' if not ES else 'Familias asociadas', 'Activitats extraescolars' if not ES else 'Actividades extraescolares'], ['Loteria', 'Lotería']),
    'falla': (['Fallers i falleres' if not ES else 'Falleros y falleras', 'Càrrecs de festa' if not ES else 'Cargos de fiesta'], ['Alumnat', 'Alumnado', 'Repertori', 'Repertorio']),
    'gaiata': (['Membres de la comissió' if not ES else 'Miembros de la comisión', 'Càrrecs de festa' if not ES else 'Cargos de fiesta'], ['Repertori', 'Repertorio']),
    'foguera': (['Foguerers i foguereres' if not ES else 'Foguerers y foguereras'], ['Repertori', 'Repertorio']),
    'comparsa': (['Comparsistes' if not ES else 'Comparsistas', 'Esquadres' if not ES else 'Escuadras', 'Vestits i roba' if not ES else 'Trajes y ropa'], ['Repertori', 'Repertorio']),
    'penya': (['Activitats' if not ES else 'Actividades', 'Loteria' if not ES else 'Lotería'], ['Repertori', 'Repertorio', 'Subvencions', 'Subvenciones']),
    'federacio': (['Entitats federades' if not ES else 'Entidades federadas'], ['Loteria', 'Lotería', 'Repertori', 'Repertorio']),
}


def recorre(c, ok, tipus):
    """Totes les pantalles del menú, cada llistat amb una fitxa i el formulari de nou element"""
    d = c.db()
    inici = c.obri('r=inici')
    ok('class="menu' in inici or '<nav' in inici, f'{tipus}: hi ha menú')
    nav = re.search(r'<nav.*?</nav>', inici, re.S)
    nav = nav.group(0) if nav else inici
    menu = _html.unescape(re.sub(r'<[^>]+>', ' ', nav))
    deu, no = MENU[tipus]
    for x in deu:
        ok(x in menu, f'{tipus}: el menú diu «{x}»')
    for x in no:
        ok(x not in menu, f'{tipus}: el menú no diu «{x}»')
    enllacos = sorted(set(_html.unescape(x) for x in re.findall(r'href="\?(r=[^"#]+)"', nav)))
    vistes = 0
    for q in enllacos:
        if 'r=eixir' in q or 'r=manual_pdf' in q:
            continue
        h = c.obri(q)
        bo = not h.startswith('HTTP') and not any(x in h for x in ERR_PHP)
        ok(bo, f'{tipus}: pantalla {q}')
        vistes += 1
        m = re.match(r'r=llista&e=(\w+)', q)
        if m and bo:
            e = m.group(1)
            f = re.search(r'href="\?r=fitxa&amp;e=' + e + r'&amp;id=(\d+)"', h)
            if f:
                hf = c.obri(f'r=fitxa&e={e}&id={f.group(1)}')
                ok(not hf.startswith('HTTP') and not any(x in hf for x in ERR_PHP), f'{tipus}: fitxa de {e}')
                # Els documents per a imprimir de la fitxa
                for doc in re.findall(r'target="_blank" href="\?([^"]+)"', hf)[:3]:
                    hd = c.obri(_html.unescape(doc))
                    ok(not hd.startswith('HTTP') and not any(x in hd for x in ERR_PHP), f'{tipus}: document {_html.unescape(doc)}')
            hn = c.obri(f'r=edita&e={e}')
            ok(not hn.startswith('HTTP') and not any(x in hn for x in ERR_PHP), f'{tipus}: formulari nou de {e}')
            hc = c.obri(f'r=llista&e={e}&csv=1')
            ok(not hc.startswith('HTTP') and not any(x in hc for x in ERR_PHP), f'{tipus}: CSV de {e}')
    ok(vistes >= 12, f'{tipus}: s\'han vist {vistes} pantalles')
    # Llistats
    hl = c.obri('r=llistats')
    for inf in sorted(set(re.findall(r'informe=(\w+)"', hl))):
        hi = c.obri(f'r=llistats&informe={inf}')
        ok(not hi.startswith('HTTP') and not any(x in hi for x in ERR_PHP), f'{tipus}: informe {inf}')
        if inf != 'etiquetes':
            hp = c.obri(f'r=llistats&informe={inf}&imprimir=1')
            ok(not any(x in hp for x in ERR_PHP), f'{tipus}: informe {inf} per a imprimir')
    # Genera rebuts i economia
    for q in ['r=genera_rebuts', 'r=economia', 'r=caixa', 'r=moduls', 'r=configuracio', 'r=ajuda']:
        h = c.obri(q)
        ok(not h.startswith('HTTP') and not any(x in h for x in ERR_PHP), f'{tipus}: pantalla {q}')
    d.close()


for tipus in TIPUS:
    c = Compas(L, True, tipus)
    try:
        d = c.db()
        t = d.execute("select valor from config where clau = 'tipus_entitat'").fetchone()
        ok(t and t[0] == tipus, f'{tipus}: s\'ha instal·lat amb el tipus')
        desact = (d.execute("select valor from config where clau = 'moduls_desactivats'").fetchone() or [''])[0] or ''
        if tipus == 'societat_musical':
            ok('repertori' not in desact and 'escola' not in desact and 'llicencies' in desact, f'{tipus}: mòduls de música actius ({desact})')
        if tipus == 'club_esportiu':
            ok('repertori' in desact and 'llicencies' not in desact, f'club: sense repertori i amb llicències ({desact})')
            ok(d.execute("select valor from config where clau = 'mes_exercici'").fetchone()[0] == '9', 'club: la temporada comença al setembre')
            ok(d.execute('select count(*) from categories').fetchone()[0] == 8, 'club: 8 categories esportives')
            ok(d.execute('select count(*) from llicencies').fetchone()[0] >= 5, 'club: llicències d\'exemple')
            h = c.obri('r=inici')
            ok(('Llicències o revisions mèdiques' in h) or ('Licencias o revisiones médicas' in h), 'club: l\'inici avisa de les revisions mèdiques')
            h = c.obri('r=llista&e=llicencies&filtre=llic_caduquen')
            ok(h.count('fila-avis') >= 2, 'club: el filtre de caducitat les troba')
            # La categoria a la fitxa d'una persona menor
            pid = d.execute("select id from persones where nom = 'Nil'").fetchone()[0]
            h = c.obri(f'r=fitxa&e=persones&id={pid}')
            ok(('Categoria' in h or 'Categoría' in h) and any(x in h for x in ['Prebenjamí', 'Prebenjamín', 'Benjamí', 'Benjamín']), 'club: la fitxa diu la categoria')
            h = c.obri('r=llistats&informe=categories&qui=persones')
            ok('Aleví' in h or 'Alevín' in h, 'club: informe per categories')
            # Notes i butlletins no hi són
            ok('r=notes' not in c.obri('r=inici'), 'club: sense notes ni butlletins')
            # Els períodes de les quotes van per temporada (setembre-agost)
            h = c.obri('r=genera_rebuts')
            ok(re.search(r'\d{4}-\d{4}', h), 'club: l\'exercici es diu 2026-2027')
        if tipus in ('falla', 'gaiata', 'foguera', 'comparsa'):
            ok(d.execute('select count(*) from carrecs_festa').fetchone()[0] == 4, f'{tipus}: càrrecs de festa d\'exemple')
            cid = d.execute('select id from carrecs_festa order by id limit 1').fetchone()[0]
            h = c.obri(f'r=certificat&tipus=carrec_festa&id={cid}')
            ok(not any(x in h for x in ERR_PHP) and ('de l\'exercici' in _html.unescape(h) or 'del ejercicio' in h), f'{tipus}: certificat del càrrec')
            h = c.obri('r=edita&e=carrecs_festa')
            carrec0 = {'falla': 'Fallera Ma', 'gaiata': 'Madrina', 'foguera': 'Bell', 'comparsa': 'Capit'}[tipus]
            ok('<datalist' in h and carrec0 in h, f'{tipus}: el formulari suggerix els càrrecs del tipus')
        if tipus == 'falla':
            ok(d.execute("select valor from config where clau = 'mes_exercici'").fetchone()[0] == '4', 'falla: l\'exercici comença a l\'abril')
        if tipus == 'federacio':
            ok(d.execute('select count(*) from entitats_federades').fetchone()[0] == 4, 'federació: entitats d\'exemple')
            ok(d.execute("select count(*) from rebuts where tipus = 'quota_entitat'").fetchone()[0] == 3, 'federació: rebuts de quota (una entitat no té representant)')
            h = c.obri('r=genera_rebuts&tipus=federades')
            ok(('sense' in h.lower() or 'sin' in h.lower()) and not any(x in h for x in ERR_PHP), 'federació: previsualització de quotes')
        # Activitats: inscripcions, places i rebuts
        aid = d.execute("select id from activitats where preu is not null").fetchone()[0]
        ok(d.execute('select count(*) from activitat_inscripcions where activitat_id = ? and rebut_id is not null', [aid]).fetchone()[0] == 2, f'{tipus}: dos inscripcions ja tenen rebut')
        h = c.obri(f'r=genera_rebuts&tipus=activitat&activitat={aid}')
        ok(not any(x in h for x in ERR_PHP) and h.count('estat-imp nova') == 4, f'{tipus}: en falten quatre per crear')
        c.envia(f'r=genera_rebuts&tipus=activitat&activitat={aid}', {'data': d.execute("select date('now','localtime')").fetchone()[0]})
        ok(d.execute('select count(*) from activitat_inscripcions where activitat_id = ? and rebut_id is null', [aid]).fetchone()[0] == 0, f'{tipus}: totes les inscripcions amb rebut')
        ok(d.execute("select count(*) from rebuts where tipus = 'activitat'").fetchone()[0] == 6, f'{tipus}: sis rebuts d\'activitat')
        h = c.obri(f'r=activitat_llista&id={aid}')
        ok(not any(x in h for x in ERR_PHP) and 'casella' in h, f'{tipus}: llista d\'inscrits per a imprimir')
        recorre(c, ok, tipus)
        d.close()
        c.registre.flush()
        for linia in Path(c.dades, 'servidor.log').read_text(errors='replace').splitlines():
            if any(x in linia for x in ERR_PHP) and 'in Unknown on line 0' not in linia:
                ok(False, f'{tipus}: PHP {linia.strip()}')
    finally:
        fails.extend(f'{tipus}: {x}' for x in c.fallades)
        c.tanca()

# Les places: no es pot inscriure més gent de la que cap
c = Compas(L, True, 'penya')
try:
    d = c.db()
    aid = d.execute("select id from activitats where places is not null").fetchone()[0]
    d.execute('update activitats set places = 9 where id = ?', [aid])
    d.commit()
    lliure = d.execute("select id from persones where id not in (select persona_id from activitat_inscripcions where activitat_id = ?) order by id limit 2", [aid]).fetchall()
    h = c.envia('r=edita&e=activitat_inscripcions', dict(activitat_id=aid, persona_id=lliure[0][0], places='3', import_='', data='', rebut_id='', observacions=''))
    ok('queden 2' in h or 'quedan 2' in h, 'no es pot passar de les places')
    h = c.envia('r=edita&e=activitat_inscripcions', dict(activitat_id=aid, persona_id=lliure[0][0], places='2', data='', rebut_id='', observacions='', **{'import': ''}))
    imp = d.execute('select import from activitat_inscripcions where activitat_id = ? and persona_id = ?', [aid, lliure[0][0]]).fetchone()
    ok(imp and imp[0] == 36, f'l\'import es calcula sol: places × preu ({imp})')
    h = c.envia('r=edita&e=activitat_inscripcions', dict(activitat_id=aid, persona_id=lliure[0][0], places='1', data='', rebut_id='', observacions='', **{'import': ''}))
    ok('ja està inscrita' in h or 'ya está inscrita' in h, 'no es pot inscriure dos voltes la mateixa persona')
    # Canviar de tipus des de la configuració: el vocabulari canvia i, si es marca, els mòduls
    h, f = c.obri('r=configuracio'), None
    ok('name="tipus_entitat"' in h and 'name="mes_exercici"' in h, 'la configuració té el tipus i el mes de l\'exercici')
    camps = dict(re.findall(r'<input[^>]*name="([^"]+)"[^>]*value="([^"]*)"', h))
    for nm, cos in re.findall(r'<select[^>]*name="([^"]+)"[^>]*>(.*?)</select>', h, re.S):
        m = re.search(r'<option value="([^"]*)" selected', cos)
        camps[nm] = m.group(1) if m else ''
    for nm, cos in re.findall(r'<textarea[^>]*name="([^"]+)"[^>]*>(.*?)</textarea>', h, re.S):
        camps[nm] = _html.unescape(cos)
    camps.update(tipus_entitat='club_esportiu', moduls_tipus='1')
    camps.pop('logo', None)
    c.envia('r=configuracio', camps)
    ok(d.execute("select valor from config where clau = 'tipus_entitat'").fetchone()[0] == 'club_esportiu', 'el tipus ha canviat')
    h = c.obri('r=inici')
    ok(('Llicències' in h or 'Licencias' in h) and ('Esportistes' in h or 'Deportistas' in h), 'el menú ja és el d\'un club')
    ok(d.execute('select count(*) from activitats').fetchone()[0] == 3, 'les dades no es toquen')
    ok(d.execute("select valor from config where clau = 'mes_exercici'").fetchone()[0] == '9', 'i l\'exercici ja comença al setembre')
    ok('repertori' in (d.execute("select valor from config where clau = 'moduls_desactivats'").fetchone()[0] or '') , 'amb els apartats d\'un club')
    d.close()
finally:
    fails.extend(c.fallades)
    c.tanca()
