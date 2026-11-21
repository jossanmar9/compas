# Dades d'exemple d'una entitat gran: s'instal·la cada tipus amb «Una entitat gran», es comprova que hi ha un volum
# realista, que les dates van amb el dia d'avui (res en el futur que no haja de ser-ho) i es recorren les pantalles
# mirant que no hi haja errors i que cap no tarde massa amb centenars de persones i milers de rebuts.
# En valencià es fan tots els tipus; en castellà, la meitat (per temps).
# Copyright (C) 2026 José Vicente Sanjuan
# SPDX-License-Identifier: AGPL-3.0-or-later
# L'executa tests/integracio.py, que hi posa les funcions comunes (ok, Compas, ERR_PHP…).
import datetime as _dt
import html as _html
import time as _time

TIPUS = ['societat_musical', 'club_esportiu', 'colla', 'muixeranga', 'colla_castellera', 'associacio_cultural', 'ampa', 'falla', 'gaiata', 'foguera', 'comparsa', 'penya', 'federacio']
if L == 'es':
    TIPUS = TIPUS[1::2] + ['societat_musical']
# Mínims que ha de tindre cada tipus: socis d'alta, rebuts i persones
MINIMS = {'federacio': (35, 150, 100), 'colla': (150, 500, 200), 'penya': (180, 400, 190)}
LIMIT = 3.0  # segons per pantalla, com a molt
AVUI = _dt.date.today().isoformat()
lentes = []

for tipus in TIPUS:
    t0 = _time.time()
    c = Compas(L, True, tipus, 'gran')
    try:
        ok(_time.time() - t0 < 60, f'{tipus}: la instal·lació amb l\'entitat gran tarda menys d\'un minut ({_time.time() - t0:.1f} s)')
        d = c.db()
        v = lambda q, *a: d.execute(q, a).fetchone()[0]
        ms, mr, mp = MINIMS.get(tipus, (200, 500, 240))
        ok(v("select count(*) from socis where estat = 'actiu'") >= ms, f'{tipus}: socis d\'alta ({v("select count(*) from socis where estat = ?", "actiu")})')
        ok(v('select count(*) from rebuts') >= mr, f'{tipus}: rebuts ({v("select count(*) from rebuts")})')
        ok(v('select count(*) from persones') >= mp, f'{tipus}: persones ({v("select count(*) from persones")})')
        ok(v("select valor from config where clau = 'societat_nom'") == 'Societat Prova', f'{tipus}: es manté el nom que s\'ha posat')
        ok(v("select valor from config where clau = 'dades_exemple'") == '1', f'{tipus}: queda marcat que són dades d\'exemple')
        # Les dates van amb hui: cap rebut ni moviment ni reunió feta en el futur
        ok(v('select count(*) from rebuts where data_emissio > ?', AVUI) == 0, f'{tipus}: cap rebut emés en el futur')
        ok(v('select count(*) from moviments where data > ?', AVUI) == 0, f'{tipus}: cap moviment de caixa en el futur')
        ok(v("select count(*) from reunions where estat = 'celebrada' and data > ?", AVUI) == 0, f'{tipus}: cap reunió feta en el futur')
        ok(v("select count(*) from activitats where data > ?", AVUI) >= 1 and v("select count(*) from activitats where estat = 'realitzada'") >= 1,
           f'{tipus}: activitats fetes i per fer')
        ok(v("select count(*) from rebuts where estat = 'pagat'") > v("select count(*) from rebuts where estat <> 'pagat'"), f'{tipus}: la majoria de rebuts estan cobrats')
        ok(v("select count(*) from rebuts where estat in ('per_cobrar', 'retornat')") >= 1, f'{tipus}: n\'hi ha de pendents o retornats')
        ok(v("select count(*) from persones where nom like '%Exemple%' or cognoms like '%Exemple%' or cognoms like '%Ejemplo%'") == 0, f'{tipus}: noms de persones de veres')
        if tipus in ('falla', 'gaiata', 'foguera', 'comparsa'):
            ok(v('select count(distinct exercici) from carrecs_festa') >= 5, f'{tipus}: càrrecs de festa de diversos exercicis')
        if tipus == 'club_esportiu':
            ok(v("select count(*) from llicencies where estat = 'actiu'") >= 150 and v('select count(*) from llicencies where revisio_medica < ?', AVUI) >= 1,
               'club: llicències, i alguna revisió mèdica caducada')
        if tipus == 'federacio':
            ok(v("select count(*) from entitats_federades where estat = 'actiu'") >= 50, 'federació: entitats federades')
        # Totes les pantalles del menú, la primera fitxa de cada llistat i els llistats
        h = c.obri('r=inici')
        nav = re.search(r'<nav.*?</nav>', h, re.S)
        nav = nav.group(0) if nav else h
        qs = sorted(set(_html.unescape(x) for x in re.findall(r'href="\?(r=[^"#]+)"', nav)))
        qs = [q for q in qs if 'r=eixir' not in q and 'r=manual_pdf' not in q] + ['r=inici', 'r=genera_rebuts', 'r=economia', 'r=caixa', 'r=llistats']
        for q in qs:
            t1 = _time.time()
            h = c.obri(q)
            dt = _time.time() - t1
            ok(not h.startswith('HTTP') and not any(x in h for x in ERR_PHP), f'{tipus}: pantalla {q}')
            if dt > LIMIT:
                lentes.append(f'{tipus} {q} {dt:.1f} s')
            m = re.match(r'r=llista&e=(\w+)$', q)
            f = m and re.search(r'href="\?r=fitxa&amp;e=' + m.group(1) + r'&amp;id=(\d+)"', h)
            if f:
                t1 = _time.time()
                hf = c.obri(f'r=fitxa&e={m.group(1)}&id={f.group(1)}')
                ok(not hf.startswith('HTTP') and not any(x in hf for x in ERR_PHP), f'{tipus}: fitxa de {m.group(1)}')
                if _time.time() - t1 > LIMIT:
                    lentes.append(f'{tipus} fitxa {m.group(1)} {_time.time() - t1:.1f} s')
        for inf in sorted(set(re.findall(r'informe=(\w+)"', c.obri('r=llistats')))):
            t1 = _time.time()
            hi = c.obri(f'r=llistats&informe={inf}&imprimir=1')
            ok(not any(x in hi for x in ERR_PHP), f'{tipus}: informe {inf}')
            if _time.time() - t1 > LIMIT:
                lentes.append(f'{tipus} informe {inf} {_time.time() - t1:.1f} s')
        log = open(Path(c.dades) / 'servidor.log', encoding='utf-8', errors='replace').read()
        avisos = [x for x in log.splitlines() if any(e in x for e in ERR_PHP)]
        ok(not avisos, f'{tipus}: sense avisos de PHP en el registre' + (f' ({avisos[0][-160:]})' if avisos else ''))
    finally:
        c.tanca()
ok(not lentes, 'cap pantalla tarda més de %.0f s%s' % (LIMIT, (': ' + '; '.join(lentes)) if lentes else ''))
