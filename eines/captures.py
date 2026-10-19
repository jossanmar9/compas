#!/usr/bin/env python3
"""
Genera les captures de pantalla dels manuals (docs/imatges/ca i docs/imatges/es).

Arranca Compàs amb PHP i dades d'exemple en una carpeta temporal, fa cada procés del manual
amb un navegador sense interfície i guarda una captura de cada pas. El botó o camp que cal
prémer es ressalta en taronja.

Requisits: PHP 8.1+ (pdo_sqlite, mbstring), Python 3, Pillow i Playwright:
    pip install playwright pillow && playwright install chromium
Ús (des de l'arrel del repositori):
    python3 eines/captures.py

SPDX-License-Identifier: GPL-2.0-or-later
"""
import asyncio
import io
import os
import re
import shutil
import socket
import subprocess
import sys
import tempfile
import time
from datetime import date
from pathlib import Path
from urllib.parse import quote

from PIL import Image, ImageDraw, ImageFont
from playwright.async_api import async_playwright

ARREL = Path(__file__).resolve().parent.parent
EIXIDA = ARREL / 'docs' / 'imatges'
AMPLE, ALT = 1280, 800
ESCALA = 1.5
QUALITAT = 80
RESSALT = '3px solid #ff6a00'

TEXTOS = {
    'ca': dict(societat="Societat Musical d'Exemple", carpeta='E:\\Copies Compàs', poble="Poble d'Exemple",
               grups=['Llenguatge musical · 1r A', 'Clarinet · Individual'], percussio='Percussió', clarinet='Clarinet',
               provincia='Castelló', adreca='Carrer Major, 7', bo='Bo'),
    'es': dict(societat='Sociedad Musical de Ejemplo', carpeta='E:\\Copias Compàs', poble='Pueblo de Ejemplo',
               grups=['Lenguaje musical · 1.º A', 'Clarinete · Individual'], percussio='Percusión', clarinet='Clarinete',
               provincia='Castellón', adreca='Calle Mayor, 7', bo='Bueno'),
}


def port_lliure() -> int:
    with socket.socket() as s:
        s.bind(('127.0.0.1', 0))
        return s.getsockname()[1]


def logo_exemple(desti: Path) -> None:
    """Un logotip inventat per a la societat d'exemple"""
    im = Image.new('RGBA', (400, 400), (0, 0, 0, 0))
    d = ImageDraw.Draw(im)
    d.ellipse((10, 10, 390, 390), fill='#7a1f2b')
    d.ellipse((40, 40, 360, 360), outline='#f2c14e', width=10)
    try:
        f = ImageFont.truetype('DejaVuSans-Bold.ttf', 120)
    except OSError:
        f = ImageFont.load_default()
    d.text((200, 200), 'SME', fill='#f2c14e', font=f, anchor='mm')
    im.save(desti)


class Captures:
    def __init__(self, pagina, url, idioma):
        self.p = pagina
        self.url = url
        self.l = idioma
        self.dir = EIXIDA / idioma
        self.dir.mkdir(parents=True, exist_ok=True)

    async def ves(self, ruta: str):
        await self.p.goto(self.url + ruta)
        await self.p.wait_for_load_state('networkidle')

    async def ressalta(self, *selectors):
        for sel in selectors:
            await self.p.locator(sel).first.evaluate(
                "(e, o) => { e.dataset.ressalt = 1; e.style.outline = o; e.style.outlineOffset = '3px'; e.scrollIntoViewIfNeeded && e.scrollIntoViewIfNeeded(); }", RESSALT)

    async def lleva_ressalt(self):
        await self.p.evaluate("document.querySelectorAll('[data-ressalt]').forEach(e => { e.style.outline = ''; delete e.dataset.ressalt; })")

    async def foto(self, nom: str, *ressalts, zona: str | None = 'main', alt_max: int = 1000):
        """Captura. zona=None: tota la finestra; zona='main': sense el menú lateral."""
        if ressalts:
            await self.ressalta(*ressalts)
        await self.p.evaluate("window.scrollTo(0, 0)")
        if zona:
            caixa = await self.p.locator(zona).first.bounding_box()
            alt = min(caixa['height'], alt_max)
            # Si la zona no cap, s'allarga la finestra en lloc de fer una captura de pàgina sencera
            necessari = int(caixa['y'] + alt) + 1
            if necessari > ALT:
                await self.p.set_viewport_size({'width': AMPLE, 'height': necessari})
                caixa = await self.p.locator(zona).first.bounding_box()
            png = await self.p.screenshot(clip=dict(x=caixa['x'], y=caixa['y'], width=caixa['width'], height=alt))
            await self.p.set_viewport_size({'width': AMPLE, 'height': ALT})
        else:
            png = await self.p.screenshot()
        im = Image.open(io.BytesIO(png)).convert('RGB')
        im.save(self.dir / f'{nom}.jpg', 'JPEG', quality=QUALITAT, optimize=True, progressive=True)
        await self.lleva_ressalt()
        print(f'  {self.l}/{nom}.jpg  {im.size[0]}x{im.size[1]}')

    async def omple(self, camps: dict):
        for nom, valor in camps.items():
            el = self.p.locator(f'[name="{nom}"]').last
            tipus = await el.evaluate('e => e.tagName + ":" + (e.type || "")')
            if tipus.startswith('SELECT'):
                valors = await el.evaluate('e => [...e.options].map(o => o.value)')
                if str(valor) in valors:
                    await el.select_option(value=str(valor))
                else:
                    await el.select_option(label=str(valor))
            elif tipus.endswith('checkbox'):
                await el.set_checked(bool(valor))
            else:
                await el.fill(str(valor))

    async def desa(self):
        await self.p.locator('.contingut form .accions button:not(.perill):not(.secundari), .caixa form button').first.click()
        await self.p.wait_for_load_state('networkidle')
        errors = await self.p.locator('.avis.error').all_inner_texts()
        if errors:
            raise RuntimeError(f'Error en desar ({self.p.url}): {errors}')

    def id_actual(self) -> str:
        return re.search(r'[?&]id=(\d+)', self.p.url).group(1)

    async def boto_afig(self, entitat: str):
        """Selector del botó «+ Afig» d'un apartat de la fitxa"""
        return f'section.fill a.boto[href*="e={entitat}"]'


async def fes_captures(l: str):
    t = TEXTOS[l]
    dades = Path(tempfile.mkdtemp(prefix='compas-captures-'))
    port = port_lliure()
    entorn = dict(os.environ, COMPAS_DADES=str(dades), COMPAS_TOKEN='captures')
    srv = subprocess.Popen(['php', '-S', f'127.0.0.1:{port}', '-t', 'app', 'app/index.php'], cwd=ARREL, env=entorn,
                           stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
    time.sleep(1.2)
    url = f'http://127.0.0.1:{port}/'
    logo = dades / 'logo-exemple.png'
    logo_exemple(logo)
    avui = date.today().isoformat()
    try:
        async with async_playwright() as pw:
            regio = 'es_ES.UTF-8' if l == 'es' else 'ca_ES.UTF-8'
            nav = await pw.chromium.launch(args=[f'--lang={"es-ES" if l == "es" else "ca-ES"}'],
                                           env=dict(os.environ, LANG=regio, LC_ALL=regio, LANGUAGE=l))
            ctx = await nav.new_context(viewport={'width': AMPLE, 'height': ALT}, device_scale_factor=ESCALA,
                                        locale='es-ES' if l == 'es' else 'ca-ES')
            p = await ctx.new_page()
            c = Captures(p, url, l)

            # --- Primera execució ---
            await c.ves(f'?r=idioma&l={l}&tornar=/')
            await c.omple({'societat_nom': t['societat'], 'usuari': 'admin', 'contrasenya': 'contrasenya1',
                           'contrasenya2': 'contrasenya1', 'usuari2': 'secretaria', 'contrasenya_b': 'contrasenya2',
                           'contrasenya_b2': 'contrasenya2', 'exemple': True})
            await c.foto('01-configuracio-inicial', 'button', zona='.caixa', alt_max=1400)
            await c.desa()

            # Inici i configuració
            await c.foto('02-inici', zona=None)
            await c.ves('?r=configuracio')
            await p.set_input_files('input[name="logo"]', str(logo))
            await c.omple({'societat_adreca': 'Plaça Major, 1' if l == 'ca' else 'Plaza Mayor, 1', 'societat_telefon': '964 000 000', 'societat_email': 'escola@exemple.org'})
            await c.foto('03-configuracio', 'input[name="logo"]')
            await c.desa()

            # --- Persones ---
            await c.ves('?r=llista&e=persones')
            await c.foto('04-persones-llista', 'a.boto[href*="r=edita"]', zona=None)
            await c.ves('?r=edita&e=persones')
            await c.omple({'nom': 'Alumne7', 'cognoms': 'Exemple Prova', 'data_naixement': '2016-03-14', 'document': '',
                           'telefon': '600 000 007', 'email': 'familia7@exemple.org', 'adreca': t['adreca'],
                           'codi_postal': '12000', 'poblacio': t['poble'], 'provincia': t['provincia']})
            await c.foto('05-persona-nova', 'form .accions button')
            await c.desa()
            persona = c.id_actual()
            await c.foto('06-persona-fitxa', await c.boto_afig('alumnes'), alt_max=1300)

            # --- Alta d'alumne ---
            await p.locator(await c.boto_afig('alumnes')).first.click()
            await p.wait_for_load_state('networkidle')
            await c.foto('07-alumne-nou', 'form .accions button')
            await c.desa()
            await c.foto('08-alumne-fitxa', await c.boto_afig('tutories'), alt_max=1300)

            # Tutor legal
            await p.locator(await c.boto_afig('tutories')).first.click()
            await p.wait_for_load_state('networkidle')
            await c.omple({'tutor_id': 'Exemple, Tutor1', 'tipus_relacio': 'mare', 'principal': True})
            await c.foto('09-tutor-nou', 'select[name="tutor_id"]')
            await c.desa()

            # Matrícula
            await p.locator(await c.boto_afig('matricules')).first.click()
            await p.wait_for_load_state('networkidle')
            await c.foto('10-matricula-nova', 'select[name="curs_id"]')
            await c.desa()
            await c.foto('11-matricula-fitxa', await c.boto_afig('matricula_grups'))
            matricula = c.id_actual()
            for i, grup in enumerate(t['grups']):
                await c.ves(f'?r=edita&e=matricula_grups&f[matricula_id]={matricula}&tornar=' + quote(f'?r=fitxa&e=matricules&id={matricula}', safe=''))
                opcio = await p.locator('select[name="grup_id"] option').filter(has_text=grup).first.get_attribute('value')
                await c.omple({'grup_id': opcio})
                if i == 0:
                    await c.foto('12-inscripcio-grup', 'select[name="grup_id"]')
                await c.desa()
            await c.foto('13-matricula-grups', alt_max=900)

            # Resum de l'alumne
            alumne = await p.locator('a[href*="e=alumnes&id="]').first.get_attribute('href')
            await c.ves(alumne.lstrip('/'))
            await c.foto('14-alumne-resum', 'section.fill header a.boto[target="_blank"]', alt_max=1100)

            # --- Soci ---
            await c.ves(f'?r=fitxa&e=persones&id={persona}')
            await p.locator(await c.boto_afig('socis')).first.click()
            await p.wait_for_load_state('networkidle')
            await c.omple({'numero': '120', 'tipus': 'juvenil'})
            await c.foto('15-soci-nou', 'select[name="tipus"]')
            await c.desa()
            enllac = await p.locator('section.fill a[href*="e=socis&id="]').first.get_attribute('href')
            soci = re.search(r'id=(\d+)', enllac).group(1)
            await c.ves('?r=llista&e=socis')
            await c.foto('16-socis-llista', 'input[type="search"]', zona=None)
            await c.ves(f'?r=edita&e=socis&id={soci}')
            await c.omple({'estat': 'baixa', 'data_baixa': avui})
            await c.foto('17-soci-baixa', 'select[name="estat"]', 'input[name="data_baixa"]')

            # --- Músic d'una agrupació ---
            await c.ves(f'?r=fitxa&e=persones&id={persona}')
            await p.locator(await c.boto_afig('membres')).first.click()
            await p.wait_for_load_state('networkidle')
            agr = await p.locator('select[name="agrupacio_id"] option').nth(2).get_attribute('value')
            await c.omple({'agrupacio_id': agr, 'instrument_veu': t['clarinet'], 'funcio': 'membre'})
            await c.foto('18-membre-nou', 'select[name="agrupacio_id"]', 'input[name="instrument_veu"]')
            await c.desa()
            await c.ves(f'?r=fitxa&e=agrupacions&id={agr}')
            await c.foto('19-agrupacio-fitxa', await c.boto_afig('membres'))

            # --- Professorat ---
            await c.ves('?r=edita&e=persones')
            await c.omple({'nom': 'Professor3', 'cognoms': 'Exemple', 'telefon': '600 000 103'})
            await c.desa()
            await p.locator(await c.boto_afig('professors')).first.click()
            await p.wait_for_load_state('networkidle')
            await c.foto('20-professor-nou', 'form .accions button')
            await c.desa()

            # --- Cursos, assignatures i grups ---
            await c.ves('?r=fitxa&e=cursos&id=1')
            await c.foto('21-curs-fitxa', await c.boto_afig('periodes'), alt_max=1000)
            await c.ves('?r=edita&e=grups')
            assig = await p.locator('select[name="assignatura_id"] option').filter(has_text=t['percussio']).first.get_attribute('value')
            prof = await p.locator('select[name="professor_id"] option').filter(has_text='Professor3').first.get_attribute('value')
            await c.omple({'assignatura_id': assig, 'nom': 'Individual', 'professor_id': prof, 'aula_id': '2'})
            await c.foto('22-grup-nou', 'form .accions button')
            await c.desa()
            await p.locator(await c.boto_afig('horaris')).first.click()
            await p.wait_for_load_state('networkidle')
            await c.omple({'dia': '2', 'hora_inici': '17:00', 'hora_fi': '18:00'})
            await c.foto('23-horari-nou', 'select[name="dia"]')
            await c.desa()
            await c.foto('24-grup-fitxa', await c.boto_afig('horaris'))
            await c.ves('?r=horari')
            await c.foto('25-horari', zona=None)

            # --- Assistència, notes i butlletins ---
            await c.ves(f'?r=assistencia&grup=1&data={avui}')
            primer = p.locator('input[type="radio"][value="F"]').first
            await primer.check()
            await p.locator('input[name^="obs["]').first.fill('Avisa la família' if l == 'ca' else 'Avisa la familia')
            await c.foto('26-assistencia', 'button[data-tots]', 'form.assistencia div.accions button')
            await c.desa()
            await c.ves('?r=notes&grup=1')
            notes = p.locator('input.nota')
            for i in range(await notes.count()):
                await notes.nth(i).fill(['7', '8,5', 'Notable', '6', '9', '7,5', '8'][i % 7])
            await p.locator('textarea[name^="obs["]').first.fill(
                'Ha millorat molt en lectura rítmica.' if l == 'ca' else 'Ha mejorado mucho en lectura rítmica.')
            await c.foto('27-qualificacions', 'select[name="periode"]', 'form .accions button')
            await c.desa()
            await c.ves('?r=butlleti')
            await c.foto('28-butlletins', 'form.formulari .accions button')
            await c.ves('?r=butlleti&imprimir=1&curs=1&periode=1&ambit=alumne&alumne=1')
            await c.foto('29-butlleti-impres', zona='.full', alt_max=1150)

            # --- Instruments ---
            await c.ves('?r=edita&e=instruments')
            await c.omple({'inventari': 'INV-005', 'tipus': t['clarinet'], 'marca': 'Yamaha', 'model': 'YCL-255',
                           'num_serie': '123456', 'estat': 'bo', 'ubicacio': 'Magatzem' if l == 'ca' else 'Almacén', 'data_adquisicio': '2024-09-01'})
            await c.foto('30-instrument-nou', 'form .accions button')
            await c.desa()
            await p.locator(await c.boto_afig('cessions')).first.click()
            await p.wait_for_load_state('networkidle')
            per = await p.locator('select[name="persona_id"] option').filter(has_text='Alumne7').first.get_attribute('value')
            await c.omple({'persona_id': per, 'data_prevista': f'{date.today().year + 1}-06-30', 'estat_entrega': t['bo'], 'fianca': '50'})
            await c.foto('31-cessio-nova', 'select[name="persona_id"]', 'input[name="data_prevista"]')
            await c.desa()
            enllac = await p.locator('section.fill a[href*="e=cessions&id="]').first.get_attribute('href')
            cessio = re.search(r'id=(\d+)', enllac).group(1)
            await c.ves(f'?r=edita&e=cessions&id={cessio}')
            await c.omple({'data_retorn': avui, 'estat_retorn': t['bo']})
            await c.foto('32-cessio-retorn', 'input[name="data_retorn"]', 'input[name="estat_retorn"]')
            await c.ves('?r=llista&e=cessions&filtre=actives')
            await c.foto('33-cessions-llista', 'select[name="filtre"]', zona=None)

            # --- Còpies ---
            await c.ves('?r=copia')
            await c.p.locator('form button').filter(has_text=re.compile('ara|ahora')).first.click()
            await p.wait_for_load_state('networkidle')
            await c.foto('34-copies', 'form:has(input[value="fes"]) button', alt_max=900)
            await c.p.locator('input[name="copia_externa"]').fill(t['carpeta'])
            await c.ressalta('input[name="copia_externa"]')
            await c.p.locator('input[name="copia_externa"]').scroll_into_view_if_needed()
            await p.set_viewport_size({'width': AMPLE, 'height': 2400})
            caixa = await p.locator('section.fill:has(input[name="copia_externa"])').bounding_box()
            png = await p.screenshot(clip=caixa)
            Image.open(io.BytesIO(png)).convert('RGB').save(c.dir / '35-copies-carpeta.jpg', 'JPEG', quality=QUALITAT, optimize=True)
            print(f'  {l}/35-copies-carpeta.jpg')
            await c.lleva_ressalt()
            caixa = await p.locator('table.taula').first.bounding_box()
            png = await p.screenshot(clip=caixa)
            await p.set_viewport_size({'width': AMPLE, 'height': ALT})
            Image.open(io.BytesIO(png)).convert('RGB').save(c.dir / '36-copies-llista.jpg', 'JPEG', quality=QUALITAT, optimize=True)
            print(f'  {l}/36-copies-llista.jpg')

            # --- Usuaris i compte ---
            await c.ves('?r=usuaris&id=2')
            await c.foto('37-usuari', 'select[name="rol"]', 'input[name="contrasenya"]')
            await c.ves('?r=perfil')
            await c.foto('38-meu-compte', '.usuari > a', zona=None)
            await c.ves('?r=ajuda')
            await c.foto('39-ajuda', '.accions a.boto', zona=None)

            # --- Entrada ---
            ctx2 = await nav.new_context(viewport={'width': AMPLE, 'height': ALT}, device_scale_factor=ESCALA)
            p2 = await ctx2.new_page()
            await p2.goto(url + f'?r=idioma&l={l}&tornar=/')
            await p2.locator('input[name="usuari"]').fill('secretaria')
            c2 = Captures(p2, url, l)
            await c2.foto('00-entrada', zona='.caixa')
            await nav.close()
    finally:
        srv.terminate()
        srv.wait()
        shutil.rmtree(dades, ignore_errors=True)


async def main():
    idiomes = sys.argv[1:] or ['ca', 'es']
    for l in idiomes:
        print(f'Captures en «{l}»:')
        shutil.rmtree(EIXIDA / l, ignore_errors=True)
        await fes_captures(l)


if __name__ == '__main__':
    asyncio.run(main())
