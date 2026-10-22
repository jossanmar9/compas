<?php
// Repertori: catàleg de l'arxiu musical de la societat (obres, tipus, agrupacions, estat i on és el material físic)
// Copyright (C) 2026 José Vicente Sanjuan
// SPDX-License-Identifier: AGPL-3.0-or-later

require_once SRC_DIR . '/fulls.php';

/**
 * Número d'arxiu que es proposa per a una obra nova: el següent al de l'última obra que en tinga un
 * amb xifres al final («ARX-0027» → «ARX-0028»). Si encara no n'hi ha cap, no es proposa res.
 */
function seguent_numero_arxiu(): ?string
{
    $ultim = valor("SELECT numero_arxiu FROM obres WHERE numero_arxiu IS NOT NULL ORDER BY id DESC LIMIT 1");
    if (!$ultim || !preg_match('/^(.*?)(\d+)$/', (string)$ultim, $m)) {
        return null;
    }
    // El més alt amb el mateix prefix, per si l'última obra no era la del número més alt
    $max = 0;
    foreach (q('SELECT numero_arxiu FROM obres WHERE numero_arxiu LIKE ?', [$m[1] . '%'])->fetchAll() as $o) {
        if (preg_match('/^' . preg_quote($m[1], '/') . '(\d+)$/', (string)$o['numero_arxiu'], $x)) {
            $max = max($max, (int)$x[1]);
        }
    }
    for ($n = $max + 1; ; $n++) {
        $proposat = $m[1] . str_pad((string)$n, strlen($m[2]), '0', STR_PAD_LEFT);
        if (!valor('SELECT id FROM obres WHERE numero_arxiu = ?', [$proposat])) {
            return $proposat;
        }
    }
}

/** Desa les agrupacions que poden interpretar una obra */
function desa_agrupacions_obra(int $obraId, $agrupacions): void
{
    q('DELETE FROM obra_agrupacions WHERE obra_id = ?', [$obraId]);
    foreach (array_unique(array_map('intval', (array)$agrupacions)) as $a) {
        if ($a > 0 && valor('SELECT id FROM agrupacions WHERE id = ?', [$a])) {
            q('INSERT INTO obra_agrupacions(obra_id, agrupacio_id) VALUES(?, ?)', [$obraId, $a]);
        }
    }
}

/** A la fitxa d'una agrupació: quantes obres té al repertori */
function seccio_repertori_agrupacio(array $f): string
{
    if (!disponible('repertori')) {
        return '';
    }
    $n = (int)valor('SELECT COUNT(*) FROM obra_agrupacions WHERE agrupacio_id = ?', [$f['id']]);
    return '<section class="fill"><header><h2>🎼 ' . h(t('menu.repertori')) . ' <span class="compte">' . $n . '</span></h2>'
        . '<a class="boto petit secundari" href="' . h(url('repertori', ['agrupacio' => $f['id']])) . '">' . h(t('rep.veure_agrupacio')) . '</a></header></section>';
}

/** Obres del catàleg amb els filtres i l'ordre de la pantalla */
function obres_repertori(array $filtre): array
{
    $where = ['1=1'];
    $params = [];
    if ($filtre['q'] !== '') {
        $where[] = '(t.titol LIKE ? OR t.compositor LIKE ? OR t.arranjador LIKE ? OR t.numero_arxiu LIKE ? OR t.ubicacio LIKE ? OR t.material LIKE ? OR t.observacions LIKE ? OR tp.nom LIKE ?)';
        array_push($params, ...array_fill(0, 8, '%' . $filtre['q'] . '%'));
    }
    if ($filtre['tipus'] !== '') {
        $where[] = $filtre['tipus'] === 'cap' ? 't.tipus_id IS NULL' : 't.tipus_id = ?';
        if ($filtre['tipus'] !== 'cap') {
            $params[] = (int)$filtre['tipus'];
        }
    }
    if ($filtre['agrupacio'] !== '') {
        $where[] = $filtre['agrupacio'] === 'cap' ? 't.id NOT IN (SELECT obra_id FROM obra_agrupacions)'
            : 't.id IN (SELECT obra_id FROM obra_agrupacions WHERE agrupacio_id = ?)';
        if ($filtre['agrupacio'] !== 'cap') {
            $params[] = (int)$filtre['agrupacio'];
        }
    }
    if ($filtre['estat'] !== '') {
        $where[] = 't.estat = ?';
        $params[] = $filtre['estat'];
    }
    if ($filtre['dig'] !== '') {
        $where[] = 't.digitalitzada = ?';
        $params[] = (int)$filtre['dig'];
    }
    $obres = q("SELECT t.*, tp.nom AS tipus_nom, tp.ordre AS tipus_ordre,
            (SELECT group_concat(a.nom, ', ') FROM obra_agrupacions oa JOIN agrupacions a ON a.id = oa.agrupacio_id WHERE oa.obra_id = t.id) AS agrupacions
        FROM obres t LEFT JOIN tipus_obres tp ON tp.id = t.tipus_id WHERE " . implode(' AND ', $where), $params)->fetchAll();
    // Ordre natural i sense tindre en compte els accents («ARX-9» abans que «ARX-10», «Àngel» amb la «a»)
    $clau = match ($filtre['ordre']) {
        'compositor' => fn($o) => [normalitza_text((string)$o['compositor']) ?: '~', normalitza_text($o['titol'])],
        'numero' => fn($o) => [$o['numero_arxiu'] === null ? '~' : mb_strtolower($o['numero_arxiu']), normalitza_text($o['titol'])],
        'tipus' => fn($o) => [sprintf('%09d', $o['tipus_ordre'] ?? 999999999) . normalitza_text((string)$o['tipus_nom']), normalitza_text($o['titol'])],
        'ubicacio' => fn($o) => [mb_strtolower((string)$o['ubicacio']) ?: '~', normalitza_text($o['titol'])],
        default => fn($o) => [normalitza_text($o['titol']), normalitza_text((string)$o['compositor'])],
    };
    // Les claus es calculen una sola vegada per obra (no en cada comparació)
    $claus = array_map($clau, $obres);
    $ordre = array_keys($obres);
    usort($ordre, fn($a, $b) => strnatcasecmp($claus[$a][0], $claus[$b][0]) ?: strnatcasecmp($claus[$a][1], $claus[$b][1]));
    return array_map(fn($i) => $obres[$i], $ordre);
}

/** Columnes del CSV (els mateixos títols es reconeixen en importar-lo) */
function columnes_csv_repertori(): array
{
    return ['numero_arxiu' => t('camp.numero_arxiu'), 'titol' => t('camp.titol'), 'tipus' => t('rep.tipus_obra'), 'compositor' => t('camp.compositor'),
        'arranjador' => t('camp.arranjador'), 'agrupacions' => t('camp.agrupacions'), 'ubicacio' => t('camp.ubicacio'), 'material' => t('camp.material'),
        'estat' => t('camp.estat'), 'digitalitzada' => t('camp.digitalitzada'), 'observacions' => t('camp.observacions')];
}

function pagina_repertori(): void
{
    requereix('agrupacions.view');
    $filtre = ['q' => trim((string)get('q')), 'tipus' => (string)get('tipus'), 'agrupacio' => (string)get('agrupacio'),
        'estat' => in_array(get('estat'), ESTATS_OBRA, true) ? get('estat') : '', 'dig' => in_array(get('dig'), ['0', '1'], true) ? get('dig') : '',
        'ordre' => in_array(get('ordre'), ['titol', 'compositor', 'numero', 'tipus', 'ubicacio'], true) ? get('ordre') : 'titol'];
    $obres = obres_repertori($filtre);
    $trobades = count($obres);

    if (get('csv') === '1') {
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="repertori-' . date('Ymd') . '.csv"');
        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, array_values(columnes_csv_repertori()), ';', '"', '');
        foreach ($obres as $o) {
            fputcsv($out, [$o['numero_arxiu'], $o['titol'], $o['tipus_nom'], $o['compositor'], $o['arranjador'], $o['agrupacions'], $o['ubicacio'], $o['material'],
                t('opt.' . $o['estat']), $o['digitalitzada'] ? t('si') : t('no'), $o['observacions']], ';', '"', '');
        }
        audita('exporta', 'obres', null, 'csv');
        exit;
    }

    $tipus = ['cap' => '— ' . t('rep.sense_tipus') . ' —'] + opcions_ref('tipus_obres');
    $agrupacions = opcions_ref('agrupacions') + ['cap' => '— ' . t('rep.sense_agrupacio') . ' —'];
    $estats = array_combine(ESTATS_OBRA, array_map(fn($e) => t('opt.' . $e), ESTATS_OBRA));
    $ordres = ['titol' => t('camp.titol'), 'compositor' => t('camp.compositor'), 'numero' => t('camp.numero_arxiu'), 'tipus' => t('rep.tipus_obra'), 'ubicacio' => t('camp.ubicacio')];
    $filtrat = $filtre['q'] !== '' || $filtre['tipus'] !== '' || $filtre['agrupacio'] !== '' || $filtre['estat'] !== '' || $filtre['dig'] !== '';

    $html = '<form class="barra filtres-repertori no-imprimir" method="get"><input type="hidden" name="r" value="repertori">'
        . '<input type="search" name="q" value="' . h($filtre['q']) . '" placeholder="' . h(t('rep.cerca')) . '" autofocus>'
        . select_simple('tipus', $tipus, $filtre['tipus'], true, t('rep.tots_tipus'))
        . select_simple('agrupacio', $agrupacions, $filtre['agrupacio'], true, t('rep.totes_agrupacions'))
        . select_simple('estat', $estats, $filtre['estat'], true, t('rep.tots_estats'))
        . select_simple('dig', ['1' => t('rep.digitalitzades'), '0' => t('rep.no_digitalitzades')], $filtre['dig'], true, t('rep.digitalitzades_totes'))
        . '<label class="ordre">' . h(t('rep.ordena')) . ' ' . select_simple('ordre', $ordres, $filtre['ordre'], false) . '</label>'
        . '<button>' . h(t('filtrar')) . '</button>' . ($filtrat ? '<a class="boto secundari" href="' . h(url('repertori')) . '">' . h(t('rep.neteja')) . '</a>' : '') . '</form>';

    $html .= '<div class="barra no-imprimir">';
    if (pot('agrupacions.edit')) {
        $html .= '<a class="boto" href="' . h(url('edita', ['e' => 'obres', 'tornar' => url('repertori')])) . '">+ ' . h(t('rep.nova')) . '</a>';
        if (pot('persones.edit')) {
            $html .= '<a class="boto secundari" href="' . h(url('importa')) . '">⬆ ' . h(t('rep.importa')) . '</a>';
        }
    }
    $html .= '<a class="boto secundari" href="' . h(url('llista', ['e' => 'tipus_obres'])) . '">🏷 ' . h(t('ent.tipus_obres')) . '</a><span class="espai"></span>'
        . '<a class="boto secundari" href="' . h(url('repertori', array_filter($filtre + ['csv' => 1], fn($x) => $x !== ''))) . '">' . h(t('exporta_csv')) . '</a>'
        . '<button type="button" class="secundari" onclick="window.print()">🖨 ' . h(t('llist.imprimeix')) . '</button></div>';

    $total = (int)valor('SELECT COUNT(*) FROM obres');
    // Amb catàlegs molt grans, es mostra per pàgines; «Mostra-les totes» és per a imprimir-lo sencer
    $perPagina = 100;
    $pagines = (int)ceil($trobades / $perPagina);
    $pag = min(max(1, (int)get('p', '1')), max(1, $pagines));
    $totes = get('tot') === '1' || $pagines <= 1;
    $mostrades = $totes ? $obres : array_slice($obres, ($pag - 1) * $perPagina, $perPagina);
    $html .= '<div class="targetes no-imprimir">' . targeta((string)$total, t('rep.obres'), url('repertori'))
        . ($filtrat ? targeta((string)$trobades, t('rep.trobades')) : '')
        . targeta((string)valor("SELECT COUNT(*) FROM obres WHERE estat IN ('incomplet', 'en_revisio')"), t('rep.per_revisar'), url('repertori', ['estat' => 'incomplet']))
        . targeta((string)valor('SELECT COUNT(*) FROM obres WHERE digitalitzada = 1'), t('rep.digitalitzades'), url('repertori', ['dig' => 1])) . '</div>';

    // Capçalera per a la impressió: què s'ha filtrat
    $descr = array_filter([
        $filtre['q'] !== '' ? '«' . $filtre['q'] . '»' : null,
        $filtre['tipus'] !== '' ? ($tipus[$filtre['tipus']] ?? null) : null,
        $filtre['agrupacio'] !== '' ? ($agrupacions[$filtre['agrupacio']] ?? null) : null,
        $filtre['estat'] !== '' ? $estats[$filtre['estat']] : null,
        $filtre['dig'] !== '' ? ($filtre['dig'] === '1' ? t('rep.digitalitzades') : t('rep.no_digitalitzades')) : null,
    ]);
    $html .= '<div class="nomes-imprimir capcalera-repertori"><h2>' . h((config('societat_nom') ?: 'Compàs') . ' · ' . t('menu.repertori')) . '</h2><p>'
        . h(t('n_resultats', $trobades) . ($descr ? ' · ' . implode(' · ', $descr) : '') . ' · ' . data_llegible(date('Y-m-d'))) . '</p></div>';

    if (!$obres) {
        $html .= '<p class="buit">' . h($total ? t('rep.cap_trobada') : t('rep.buit')) . '</p>';
    } else {
        $html .= '<div class="taula-wrap"><table class="taula repertori"><thead><tr><th>' . h(t('rep.num')) . '</th><th>' . h(t('camp.titol')) . '</th><th>' . h(t('rep.tipus_obra'))
            . '</th><th>' . h(t('camp.compositor')) . '</th><th>' . h(t('camp.agrupacions')) . '</th><th>' . h(t('camp.ubicacio')) . '</th><th>' . h(t('camp.estat'))
            . '</th><th title="' . h(t('camp.digitalitzada')) . '">' . h(t('rep.dig')) . '</th></tr></thead><tbody>';
        foreach ($mostrades as $o) {
            $enllac = url('fitxa', ['e' => 'obres', 'id' => $o['id']]);
            $html .= '<tr data-href="' . h($enllac) . '"><td class="num-arxiu">' . h($o['numero_arxiu']) . '</td><td><a href="' . h($enllac) . '">' . h($o['titol']) . '</a>'
                . ($o['observacions'] ? ' <span class="nota-obra" title="' . h($o['observacions']) . '">💬</span>' : '') . '</td><td>' . h($o['tipus_nom']) . '</td><td>' . h($o['compositor'])
                . ($o['arranjador'] ? '<small class="arranjador">' . h(t('rep.arr', $o['arranjador'])) . '</small>' : '') . '</td><td>' . h($o['agrupacions']) . '</td><td>' . h($o['ubicacio'])
                . '</td><td><span class="estat-obra ' . h($o['estat']) . '">' . h(t('opt.' . $o['estat'])) . '</span></td><td class="centrat">' . ($o['digitalitzada'] ? '✔' : '') . '</td></tr>';
        }
        $html .= '</tbody></table></div>';
        if (!$totes) {
            $html .= '<div class="no-imprimir pagines-repertori">' . html_pagines($pag, $pagines)
                . '<a class="boto petit secundari" href="' . h(url('repertori', array_filter($filtre + ['tot' => 1], fn($x) => $x !== ''))) . '">' . h(t('rep.totes', $trobades)) . '</a>'
                . '<small>' . h(t('rep.paginat', $perPagina, $perPagina)) . '</small></div>';
        }
    }
    $html .= '<p class="nota no-imprimir">' . h(t('rep.nota')) . '</p>';
    pagina(t('menu.repertori'), $html);
}
