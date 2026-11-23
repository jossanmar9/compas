# Interfície: menú curt i plegable, cercador de dalt, «Què vols fer?» a l'inici, ajuda de cada pantalla,
# fitxa de persona amb pestanyes i accions, «Més dades» als formularis i alta d'alumne en una pantalla.
# Copyright (C) 2026 José Vicente Sanjuan
# SPDX-License-Identifier: AGPL-3.0-or-later
# L'executa tests/integracio.py, que hi posa les funcions comunes (G, P, ok, n, db…).
import json as _json
import html as _html

ES = L == 'es'
h = G('r=inici')
nav = re.search(r'<nav[^>]*>.*?</nav>', h, re.S).group(0)
# Menú: el que es configura una vegada ja no hi és, però es troba a la seua pantalla
for e in ['tarifes', 'mandats', 'cursos', 'assignatures', 'aules']:
    ok(f'e={e}"' not in nav, f'menú: {e} no és al menú')
ok('r=moduls' not in nav and 'r=auditoria' not in nav and 'r=perfil' not in nav, 'menú: mòduls, registre i compte propi fora del menú')
ok(nav.count('<details class="grup-menu"') >= 5, 'menú: seccions plegables')
ok('data-actual="1"' in re.search(r'<nav[^>]*>.*?</nav>', G('r=llista&e=rebuts'), re.S).group(0), 'menú: la secció de la pantalla actual queda marcada')
hg = G('r=llista&e=grups')
ok('e=cursos' in hg and 'e=assignatures' in hg and 'e=aules' in hg, 'grups: enllaços a cursos, assignatures i aules')
he = G('r=economia')
ok('e=tarifes' in he and 'e=mandats' in he, 'economia: enllaços a tarifes i domiciliacions')
hc = G('r=configuracio')
ok('r=moduls' in hc and 'r=auditoria' in hc, 'configuració: enllaços a mòduls i registre')
ok('r=auditoria' in G('r=usuaris'), 'usuaris: enllaç al registre d activitat')
ok('class="actiu"' in re.search(r'<nav[^>]*>.*?</nav>', G('r=llista&e=tarifes'), re.S).group(0), 'tarifes: el menú marca Economia')

# Inici: «Què vols fer?» i els avisos junts
ok('class="que-fer"' in h and 'r=alta_alumne' in h and 'r=genera_rebuts' in h, 'inici: accions de cada dia')
ok('<details class="avisos-inici"' in h, 'inici: avisos en un bloc plegable')

# Ajuda de cada pantalla: porta a l'apartat del manual, i l'apartat existeix
def ajuda(q):
    m = re.search(r'class="ajuda-pagina" href="([^"]+)"', G(q))
    return _html.unescape(m.group(1)) if m else ''
for q, num in [('r=inici', '2'), ('r=llista&e=persones', '6'), ('r=llista&e=socis', '8'), ('r=genera_rebuts', '13-3'), ('r=llista&e=activitats', '18')]:
    a = ajuda(q)
    ok('#' + num + '-' in a, f'ajuda de {q}: {a}')
    ok(('id="' + a.split('#')[1] + '"') in G('r=ajuda'), f'ajuda de {q}: l apartat existeix al manual')
a = ajuda('r=copia')
ok('manual=admin' in a and ('id="' + a.split('#')[1] + '"') in G('r=ajuda&manual=admin'), 'ajuda de còpies: manual d administració')

# Cercador
josep = n("select id from persones where nom = 'Josep'")
r = _json.loads(G('r=cerca&json=1&q=martinez'))
ok(any('Josep' in x['text'] and 'r=fitxa&e=persones&id=%d' % josep in x['url'] for x in r), 'cerca: sense accents, i diu què és')
ok(any(('Soci' in x['sub'] or 'Socio' in x['sub']) for x in r if 'Josep' in x['text']), 'cerca: el número de soci a la proposta')
hs = G('r=cerca&q=101')
ok('Martínez Pastor, Josep' in hs and 'class="fitxa"' in hs, 'cerca: pel número de soci va directament a la fitxa')
hs = G('r=cerca&q=600+000+012')
ok('Martínez Pastor, Josep' in hs, 'cerca: pel telèfon')
hs = G('r=cerca&q=zzzzqqq')
ok('class="buit"' in hs and 'r=edita&amp;e=persones' in hs, 'cerca: sense resultats, proposa crear la persona')
hs = G('r=cerca&q=a')
ok('resultats-cerca' in hs and not err(hs), 'cerca: molts resultats')
ok(_json.loads(G('r=cerca&json=1&q=')) == [], 'cerca: buida')
ok('class="cerca-global"' in h, 'cerca: el camp és dalt')

# Fitxa de persona: accions directes, pestanyes (amb JavaScript) i subapartats buits com a enllaços
hf = G(f'r=fitxa&e=persones&id={josep}')
ok('accions-persona' in hf and 'r=alta_alumne&amp;persona=%d' % josep in hf, 'fitxa: alta com a alumne d un clic')
ok('e=membres&amp;f%5Bpersona_id%5D=' + str(josep) in hf, 'fitxa: afegir a un grup')
ok(hf.count('class="fill panell-fitxa"') >= 3 and 'class="afegir-fills"' in hf, 'fitxa: subapartats amb dades i enllaços per als buits')
pau = n("select id from persones where nom = 'Pau'")
ok('r=alta_alumne' not in G(f'r=fitxa&e=persones&id={pau}') and 'e=matricules' in G(f'r=fitxa&e=persones&id={pau}'), 'fitxa: qui ja és alumne té «nova matrícula»')

# Formularis: «Més dades»
hn = G('r=edita&e=persones')
md = re.search(r'<details class="mes-dades".*?</details>', hn, re.S)
ok(md and 'name="provincia"' in md.group(0) and 'name="observacions"' in md.group(0) and 'name="nom"' not in md.group(0), 'formulari: província i observacions plegades')
ok(' open' not in md.group(0)[:40], 'formulari: plegat en una persona nova')
hx = G(f'r=edita&e=persones&id={josep}')
ok('<details class="mes-dades" open' in hx, 'formulari: obert si ja hi ha alguna cosa')

# Alta d'alumne en una pantalla
curs = n('select id from cursos where actual = 1')
grups = [r[0] for r in db.execute('select id from grups where curs_id = ? order by id limit 2', (curs,))]
base = dict(a_nom='Lluna', a_cognoms='Ferrer Vidal', a_data_naixement='2016-05-04', a_document='', a_telefon='', a_email='', a_adreca='Carrer Nou, 3',
            a_codi_postal='12000', a_poblacio='Vilanova', a_consentiment_imatge='autoritza', t_relacio='mare', data_alta='2026-09-10')
nalumnes = n('select count(*) from alumnes')
h1 = P('r=alta_alumne', dict(base, t_mode='nova', t_nom='Sara', t_cognoms='Vidal Roig', t_data_naixement='1984-02-02', t_document='12345678Z',
                             t_telefon='600 111 222', t_email='sara@exemple.org', t_mateixa_casa='1', **{'grups[]': grups}))
al = n("select a.id from alumnes a join persones p on p.id = a.persona_id where p.nom = 'Lluna'")
ok(al and n('select count(*) from alumnes') == nalumnes + 1, 'alta: alumne creat')
ok(n("select count(*) from tutories t join persones p on p.id = t.tutor_id where t.alumne_id = ? and p.nom = 'Sara' and t.principal = 1 and t.tipus_relacio = 'mare'", al) == 1, 'alta: la mare, tutora principal')
ok(n("select adreca from persones where nom = 'Sara'") == 'Carrer Nou, 3', 'alta: la mare viu a la mateixa adreça')
ok(n('select count(*) from matricula_grups mg join matricules m on m.id = mg.matricula_id where m.alumne_id = ? and m.curs_id = ?', al, curs) == len(grups), 'alta: matrícula amb els grups')
ok('Ferrer Vidal, Lluna' in h1 and 'class="avis' in h1, 'alta: porta a la fitxa de l alumne')
# Germà amb la mateixa mare, que ja està a Compàs
sara = n("select id from persones where nom = 'Sara'")
P('r=alta_alumne', dict(base, a_nom='Nil', a_data_naixement='2018-01-01', t_mode='existent', t_persona=sara))
nil = n("select a.id from alumnes a join persones p on p.id = a.persona_id where p.nom = 'Nil' and p.cognoms = 'Ferrer Vidal'")
ok(nil and n('select tutor_id from tutories where alumne_id = ?', nil) == sara and n("select count(*) from persones where nom = 'Sara'") == 1, 'alta: germà amb la mare que ja hi és')
ok(n('select count(*) from matricules where alumne_id = ?', nil) == 0, 'alta: sense grups, sense matrícula')
# Menor sense família: no
hm = P('r=alta_alumne', dict(base, a_nom='Iris', t_mode='cap'))
ok('class="avis error"' in hm and n("select count(*) from persones where nom = 'Iris'") == 0, 'alta: un menor necessita la família')
# Major d'edat sense família, amb DNI i telèfon
ha = P('r=alta_alumne', dict(base, a_nom='Gemma', a_data_naixement='1990-03-03', a_document='87654321X', a_telefon='600 333 444', t_mode='cap'))
ok(n("select count(*) from alumnes a join persones p on p.id = a.persona_id where p.nom = 'Gemma'") == 1, 'alta: major d edat sense família')
hb = P('r=alta_alumne', dict(base, a_nom='Pere', a_data_naixement='1990-03-03', t_mode='cap'))
ok('class="avis error"' in hb and n("select count(*) from persones where nom = 'Pere'") == 0, 'alta: un major d edat necessita DNI i telèfon')
# Duplicada: proposa fer servir la que ja hi és
hd = P('r=alta_alumne', dict(base, a_nom='Gemma', a_data_naixement='1990-03-03', a_document='87654321X', a_telefon='600 333 444', t_mode='cap'))
ok('class="avis error"' in hd and 'persona=' in hd, 'alta: persona repetida')
# Una persona que ja hi és, des de la seua fitxa
hp = G(f'r=alta_alumne&persona={josep}')
ok('persona-triada' in hp and 'Martínez Pastor' in hp, 'alta: d una persona existent')
P('r=alta_alumne', dict(a_persona=josep, t_mode='cap', t_relacio='mare', data_alta='2026-09-10', **{'grups[]': grups[:1]}))
ok(n('select count(*) from alumnes where persona_id = ?', josep) == 1, 'alta: persona existent feta alumne')
hj = G(f'r=alta_alumne&persona={josep}')
ok('r=fitxa' in hj or 'class="fitxa"' in hj, 'alta: si ja és alumne, porta a la seua fitxa')
ok(not err(G('r=alta_alumne')), 'alta: formulari sense errors')

# Accessibilitat: cada camp té un nom per als lectors de pantalla, salt al contingut, errors anunciats i cercador com a combobox
from html.parser import HTMLParser as _HP

class _Camps(_HP):
    def __init__(self):
        super().__init__()
        self.dins_label = 0
        self.fors = set()
        self.camps = []

    def handle_starttag(self, tag, attrs):
        a = dict(attrs)
        if tag == 'label':
            self.dins_label += 1
            if a.get('for'):
                self.fors.add(a['for'])
        elif tag in ('input', 'select', 'textarea') and a.get('type') not in ('hidden', 'submit', 'button'):
            self.camps.append((a, self.dins_label > 0))

    def handle_endtag(self, tag):
        if tag == 'label' and self.dins_label:
            self.dins_label -= 1

def sense_nom(q):
    p = _Camps()
    p.feed(G(q))
    return [a.get('name') or a.get('id') or '?' for a, dins in p.camps if not dins and not a.get('aria-label') and a.get('id') not in p.fors]

grup = n('select id from grups where curs_id = ? order by id limit 1', curs)
for q in ['r=inici', 'r=configuracio', 'r=copia', 'r=comunicats', 'r=caixa', 'r=edita&e=persones', 'r=edita&e=socis', 'r=alta_alumne', 'r=perfil',
          'r=usuaris', 'r=horari', 'r=repertori', 'r=llista&e=persones', 'r=remeses', 'r=genera_rebuts', f'r=notes&grup={grup}', 'r=llistats', 'r=importa']:
    ok(sense_nom(q) == [], f'accessibilitat: camps amb nom a {q} {sense_nom(q)}')
ok('class="salta" href="#contingut"' in h and 'id="contingut"' in h, 'accessibilitat: salt al contingut')
ok('role="combobox"' in h and 'aria-controls="propostes-cerca"' in h and 'role="listbox"' in h, 'accessibilitat: el cercador és un combobox')
ok('aria-expanded="false" aria-controls="menu-principal"' in h, 'accessibilitat: botó del menú')
ok('class="avis error" role="alert"' in hm, 'accessibilitat: els errors s anuncien')
