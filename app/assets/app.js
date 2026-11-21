// Compàs — © 2026 José Vicente Sanjuan · SPDX-License-Identifier: AGPL-3.0-or-later
document.addEventListener('DOMContentLoaded', () => {
    // Files clicables als llistats
    document.querySelectorAll('tr[data-href]').forEach(tr => {
        tr.addEventListener('click', e => {
            if (!e.target.closest('a, button, input, select, textarea')) location.href = tr.dataset.href;
        });
    });

    // Desplegables llargs: camp per a filtrar les opcions
    document.querySelectorAll('select[data-cerca]').forEach(sel => {
        const cerca = document.createElement('input');
        cerca.type = 'search';
        cerca.className = 'filtre-select';
        cerca.placeholder = '🔍';
        sel.parentNode.insertBefore(cerca, sel);
        const normal = s => s.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();
        cerca.addEventListener('input', () => {
            const q = normal(cerca.value);
            let primer = null;
            for (const o of sel.options) {
                const ok = !o.value || normal(o.text).includes(q);
                o.hidden = !ok;
                if (ok && o.value && !primer) primer = o;
            }
            if (q && primer && (sel.selectedIndex < 1 || sel.options[sel.selectedIndex].hidden)) sel.value = primer.value;
        });
    });

    // Camps de relació amb moltes opcions: s'escriu i el servidor proposa (societats amb milers de persones)
    document.querySelectorAll('input[data-cerca-ref]').forEach(inp => {
        const ocult = inp.form.querySelector('input[type="hidden"][name="' + inp.dataset.camp + '"]');
        const llista = inp.parentNode.querySelector('.resultats-ref');
        let temps = null, actiu = -1, triat = inp.value;
        const tanca = () => { llista.hidden = true; actiu = -1; };
        const tria = li => { ocult.value = li.dataset.id; inp.value = triat = li.textContent; tanca(); };
        const marca = i => {
            const els = llista.querySelectorAll('li');
            if (!els.length) return;
            actiu = (i + els.length) % els.length;
            els.forEach((li, j) => li.classList.toggle('actiu', j === actiu));
        };
        inp.addEventListener('input', () => {
            if (inp.value !== triat) ocult.value = '';
            clearTimeout(temps);
            const q = inp.value.trim();
            if (q.length < 2) { tanca(); return; }
            temps = setTimeout(async () => {
                const r = await fetch('?r=cerca_ref&e=' + encodeURIComponent(inp.dataset.cercaRef) + '&q=' + encodeURIComponent(q));
                const dades = r.ok ? await r.json() : [];
                llista.replaceChildren(...dades.map(d => {
                    const li = document.createElement('li');
                    li.textContent = d.text;
                    li.dataset.id = d.id;
                    return li;
                }));
                if (!dades.length) llista.append(Object.assign(document.createElement('li'), { textContent: '—', className: 'buit' }));
                llista.hidden = false;
                actiu = -1;
            }, 200);
        });
        llista.addEventListener('mousedown', e => { const li = e.target.closest('li[data-id]'); if (li) { e.preventDefault(); tria(li); } });
        inp.addEventListener('keydown', e => {
            if (llista.hidden) return;
            if (e.key === 'ArrowDown') { e.preventDefault(); marca(actiu + 1); }
            else if (e.key === 'ArrowUp') { e.preventDefault(); marca(actiu - 1); }
            else if (e.key === 'Enter' && actiu >= 0) { e.preventDefault(); tria(llista.querySelectorAll('li')[actiu]); }
            else if (e.key === 'Escape') tanca();
        });
        inp.addEventListener('blur', () => setTimeout(() => { tanca(); if (!ocult.value) inp.value = triat = ''; }, 150));
    });

    // DNI i telèfon obligatoris només per a majors d'edat
    const naix = document.querySelector('input[name="data_naixement"]');
    const majors = document.querySelectorAll('input[data-major]');
    const nomesMajors = document.querySelectorAll('[data-nomes-major]');
    if (naix && (majors.length || nomesMajors.length)) {
        const actualitza = () => {
            let major = false;
            if (naix.value) {
                const d = new Date(naix.value + 'T00:00:00'), limit = new Date();
                limit.setFullYear(limit.getFullYear() - 18);
                major = d <= limit;
            }
            majors.forEach(i => {
                i.required = major;
                const marca = i.closest('.camp')?.querySelector('.obl-major');
                if (marca) marca.hidden = !major;
            });
            // L'IBAN només per als majors d'edat: amagat i sense enviar per als menors
            nomesMajors.forEach(c => {
                c.hidden = !major;
                c.querySelectorAll('input').forEach(i => { i.disabled = !major; });
            });
        };
        naix.addEventListener('input', actualitza);
        naix.addEventListener('change', actualitza);
        actualitza();
    }

    // Alta d'alumne en una pantalla: el DNI i el telèfon de cada persona segons la seua edat, i la família només si és menor
    const esMajor = v => { if (!v) return false; const d = new Date(v + 'T00:00:00'), l = new Date(); l.setFullYear(l.getFullYear() - 18); return d <= l; };
    const formAlta = document.querySelector('form.alta');
    if (formAlta) {
        const naixDe = g => g.querySelector('[data-naixement], input[name$="data_naixement"]');
        const tutor = formAlta.querySelector('[data-tutor]');
        const modes = [...formAlta.querySelectorAll('input[name="t_mode"]')];
        const refresca = () => {
            formAlta.querySelectorAll('[data-persona]').forEach(g => {
                const major = esMajor(naixDe(g)?.value);
                g.querySelectorAll('input[data-major]').forEach(i => {
                    i.required = major && !i.disabled;
                    const m = i.closest('.camp')?.querySelector('.obl-major');
                    if (m) m.hidden = !major;
                });
            });
            const alumneMajor = esMajor(naixDe(formAlta.querySelector('[data-persona="a"]'))?.value);
            tutor.querySelectorAll('.si-major').forEach(x => { x.hidden = !alumneMajor; });
            if (!alumneMajor && modes.find(m => m.checked)?.value === 'cap') modes[0].checked = true;
            const mode = modes.find(m => m.checked)?.value;
            tutor.querySelectorAll('.mode-tutor').forEach(b => {
                const visible = b.dataset.mode === mode;
                b.hidden = !visible;
                b.querySelectorAll('input, select').forEach(i => { i.disabled = !visible; });
            });
            tutor.querySelector('select[name="t_relacio"]').disabled = mode === 'cap';
            // Després d'activar o desactivar, torna a posar els obligatoris de la persona nova
            formAlta.querySelectorAll('[data-persona="t"] input[data-major]').forEach(i => {
                i.required = !i.disabled && esMajor(naixDe(formAlta.querySelector('[data-persona="t"]'))?.value);
            });
        };
        formAlta.addEventListener('input', refresca);
        formAlta.addEventListener('change', refresca);
        refresca();
    }

    // Uniformes: la llista de talles segueix l'article triat
    document.querySelectorAll('input[data-talles]').forEach(inp => {
        const talles = JSON.parse(inp.dataset.talles || '{}');
        const llista = document.getElementById(inp.getAttribute('list'));
        const article = inp.form && inp.form.querySelector('select[name="uniforme_id"]');
        if (!llista || !article) return;
        const posa = () => {
            const t = talles[article.value];
            if (!t) return;
            llista.replaceChildren(...t.map(x => Object.assign(document.createElement('option'), { value: x })));
        };
        article.addEventListener('change', posa);
        posa();
        // En una venda nova, l'import es proposa amb el preu de l'article
        const preus = JSON.parse(inp.dataset.preus || '{}');
        const imp = inp.form.querySelector('input[name="import"]'), tipus = inp.form.querySelector('select[name="tipus"]'), qt = inp.form.querySelector('input[name="quantitat"]');
        if (imp && tipus && qt) {
            let proposat = imp.value === '';
            const preu = () => {
                if (!proposat || tipus.value !== 'venda' || preus[article.value] == null) return;
                imp.value = (preus[article.value] * (parseInt(qt.value, 10) || 1)).toFixed(2).replace('.', ',');
            };
            imp.addEventListener('input', () => { proposat = false; });
            [article, tipus, qt].forEach(x => x.addEventListener('change', preu));
        }
    });

    // Caselles que depenen d'un altre camp: en triar l'organisme d'una subvenció es marquen els requisits que demana normalment
    document.querySelectorAll('.opcions[data-segons]').forEach(caixa => {
        const camp = caixa.closest('form').querySelector('[name="' + caixa.dataset.segons + '"]');
        if (!camp) return;
        const mapa = JSON.parse(caixa.dataset.mapa || '{}');
        camp.addEventListener('change', () => {
            const marcar = (mapa[camp.value] || []).map(String);
            caixa.querySelectorAll('input[type="checkbox"]').forEach(c => { c.checked = marcar.includes(c.value); });
        });
    });

    // Seccions del menú plegades: es recorden en este navegador (la de la pantalla actual sempre s'obri)
    const llegeix = (clau, defecte) => { try { return JSON.parse(localStorage.getItem(clau)) ?? defecte; } catch (e) { return defecte; } };
    const desa = (clau, v) => { try { localStorage.setItem(clau, JSON.stringify(v)); } catch (e) { /* sense emmagatzematge: no es recorda */ } };
    const plegats = llegeix('compas-menu-plegat', {});
    document.querySelectorAll('details.grup-menu').forEach(d => {
        const g = d.dataset.grup;
        if (g in plegats && !d.dataset.actual) d.open = !plegats[g];
        d.addEventListener('toggle', () => { plegats[g] = !d.open; desa('compas-menu-plegat', plegats); });
    });

    // Fitxes amb molts subapartats: en pestanyes (Dades, Socis, Rebuts…). Sense JavaScript es veu tot seguit.
    document.querySelectorAll('.fitxa-contingut').forEach(cont => {
        const panells = [...cont.querySelectorAll(':scope > .panell-fitxa')];
        if (panells.length < 3) return;
        const barra = document.createElement('div');
        barra.className = 'pestanyes-fitxa';
        barra.setAttribute('role', 'tablist');
        const botons = panells.map(p => {
            const b = document.createElement('button');
            b.type = 'button';
            b.setAttribute('role', 'tab');
            b.setAttribute('aria-controls', p.id);
            b.textContent = p.dataset.titol;
            if (p.dataset.compte) b.append(Object.assign(document.createElement('span'), { className: 'compte', textContent: p.dataset.compte }));
            p.setAttribute('role', 'tabpanel');
            barra.append(b);
            return b;
        });
        const mostra = (i, focus) => {
            panells.forEach((p, j) => { p.hidden = j !== i; });
            botons.forEach((b, j) => { b.setAttribute('aria-selected', j === i ? 'true' : 'false'); b.tabIndex = j === i ? 0 : -1; });
            if (focus) botons[i].focus();
            history.replaceState(null, '', i ? '#' + panells[i].id : location.pathname + location.search);
        };
        botons.forEach((b, i) => {
            b.addEventListener('click', () => mostra(i));
            b.addEventListener('keydown', e => {
                if (e.key === 'ArrowRight' || e.key === 'ArrowLeft') { e.preventDefault(); mostra((i + (e.key === 'ArrowRight' ? 1 : -1) + botons.length) % botons.length, true); }
            });
        });
        cont.classList.add('amb-pestanyes');
        cont.prepend(barra);
        const inicial = panells.findIndex(p => '#' + p.id === location.hash);
        mostra(Math.max(0, inicial));
    });

    // Blocs plegables que es recorden (els avisos de l'inici)
    document.querySelectorAll('details[data-recorda]').forEach(d => {
        const clau = 'compas-plegat-' + d.dataset.recorda;
        if (llegeix(clau, false)) d.open = false;
        d.addEventListener('toggle', () => desa(clau, !d.open));
    });

    // Cercador de dalt: propostes mentre s'escriu; «/» hi porta des de qualsevol pantalla
    const cercador = document.querySelector('.cerca-global');
    if (cercador) {
        const inp = cercador.querySelector('input[type="search"]'), llista = cercador.querySelector('.propostes-cerca');
        let temps = null, actiu = -1, ultima = '';
        const tanca = () => { llista.hidden = true; actiu = -1; };
        const marca = i => {
            const els = llista.querySelectorAll('a');
            if (!els.length) return;
            actiu = (i + els.length) % els.length;
            els.forEach((a, j) => a.classList.toggle('actiu', j === actiu));
        };
        inp.addEventListener('input', () => {
            clearTimeout(temps);
            const q = inp.value.trim();
            if (q.length < 2) { tanca(); return; }
            temps = setTimeout(async () => {
                ultima = q;
                const r = await fetch('?r=cerca&json=1&q=' + encodeURIComponent(q));
                const dades = r.ok ? await r.json() : [];
                if (q !== ultima) return;
                let grup = '';
                const els = [];
                dades.forEach(d => {
                    if (d.grup !== grup) { grup = d.grup; els.push(Object.assign(document.createElement('li'), { className: 'grup', textContent: grup })); }
                    const li = document.createElement('li'), a = document.createElement('a');
                    a.href = d.url;
                    a.append(Object.assign(document.createElement('b'), { textContent: d.text }));
                    if (d.sub) a.append(Object.assign(document.createElement('span'), { textContent: d.sub }));
                    li.append(a);
                    els.push(li);
                });
                if (!dades.length) els.push(Object.assign(document.createElement('li'), { className: 'buit', textContent: cercador.dataset.res || '—' }));
                llista.replaceChildren(...els);
                llista.hidden = false;
                actiu = -1;
            }, 180);
        });
        inp.addEventListener('keydown', e => {
            if (llista.hidden) return;
            if (e.key === 'ArrowDown') { e.preventDefault(); marca(actiu + 1); }
            else if (e.key === 'ArrowUp') { e.preventDefault(); marca(actiu - 1); }
            else if (e.key === 'Enter' && actiu >= 0) { e.preventDefault(); llista.querySelectorAll('a')[actiu].click(); }
            else if (e.key === 'Escape') tanca();
        });
        inp.addEventListener('blur', () => setTimeout(tanca, 150));
        document.addEventListener('keydown', e => {
            if (e.key === '/' && !e.target.closest('input, textarea, select, [contenteditable]')) { e.preventDefault(); inp.focus(); inp.select(); }
        });
    }

    // Menú en pantalles menudes
    const boto = document.querySelector('.obre-menu');
    if (boto) boto.addEventListener('click', () => boto.closest('.lateral').classList.toggle('obert'));

    // Avís si s'abandona un formulari modificat
    document.querySelectorAll('form.formulari').forEach(f => {
        let canviat = false;
        f.addEventListener('input', () => canviat = true);
        f.addEventListener('submit', () => canviat = false);
        window.addEventListener('beforeunload', e => { if (canviat) { e.preventDefault(); e.returnValue = ''; } });
    });
});
