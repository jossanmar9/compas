// Compàs — SPDX-License-Identifier: GPL-2.0-or-later
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

    // Assistència: marcar-ho tot
    document.querySelectorAll('[data-tots]').forEach(b => {
        b.addEventListener('click', () => {
            b.closest('form').querySelectorAll('input[type=radio][value="' + b.dataset.tots + '"]').forEach(r => r.checked = true);
        });
    });

    // Menú en pantalles menudes
    const boto = document.querySelector('.obre-menu');
    if (boto) boto.addEventListener('click', () => boto.closest('.lateral').classList.toggle('obert'));

    // Avís si s'abandona un formulari modificat
    document.querySelectorAll('form.formulari, form.assistencia').forEach(f => {
        let canviat = false;
        f.addEventListener('input', () => canviat = true);
        f.addEventListener('submit', () => canviat = false);
        window.addEventListener('beforeunload', e => { if (canviat) { e.preventDefault(); e.returnValue = ''; } });
    });
});
