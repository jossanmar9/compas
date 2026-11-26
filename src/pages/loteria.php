<?php
// Loteria: talonaris que es fan, paperets (participacions) que s'endú cada persona o es venen a la caixa,
// diners cobrats per forma de pagament i el que s'ha de pagar a l'administració de loteria
// Copyright (C) 2026 José Vicente Sanjuan
// SPDX-License-Identifier: AGPL-3.0-or-later

/** Paperets que s'han fet: talonaris × participacions per talonari (o null si encara no s'ha dit quants talonaris) */
function paperets_fets(array $lo): ?int
{
    return $lo['talonaris'] !== null && $lo['talonaris'] !== '' ? (int)$lo['talonaris'] * (int)$lo['participacions_talonari'] : null;
}

/** Talonari on va un número de participació */
function talonari_de(array $lo, int $n): int
{
    return intdiv($n - 1, max(1, (int)$lo['participacions_talonari'])) + 1;
}

/** Venuts d'una entrega: els que s'ha endut menys els que ha tornat */
function venuts_entrega(array $e): int
{
    return (int)$e['participacions'] - (int)$e['tornades'];
}

/** Una entrega encara sense liquidar: no ha tornat res ni ha pagat res */
function entrega_oberta(array $e): bool
{
    return $e['tornades'] === null && $e['import_pagat'] === null;
}

/** Avís al llistat d'entregues: sense liquidar o amb diners pendents */
function avis_entrega(array $e): ?string
{
    if (entrega_oberta($e)) {
        return t('loteria.avis_sense_liquidar');
    }
    $preu = (float)valor('SELECT preu FROM loteries WHERE id = ?', [$e['loteria_id']]);
    $falta = round(venuts_entrega($e) * $preu - (float)$e['import_pagat'], 2);
    return $falta > 0.004 ? t('loteria.avis_falta', euros($falta)) : null;
}

/** Comprova una entrega abans de desar-la; retorna l'error o null */
function error_entrega_loteria(array $d, ?int $id): ?string
{
    $lo = fila('SELECT * FROM loteries WHERE id = ?', [$d['loteria_id']]);
    if (!$lo) {
        return t('err.no_trobat');
    }
    $n = (int)$d['participacions'];
    if ($n < 1) {
        return t('err.entrega_participacions');
    }
    if ($d['tornades'] !== null && ($d['tornades'] < 0 || $d['tornades'] > $n)) {
        return t('err.entrega_tornades', $n);
    }
    if ($d['fins_a'] !== null && $d['des_de'] === null) {
        return t('err.entrega_rang');
    }
    if ($d['des_de'] !== null) {
        $de = (int)$d['des_de'];
        $fins = $d['fins_a'] !== null ? (int)$d['fins_a'] : $de + $n - 1;
        if ($de < 1 || $fins - $de + 1 !== $n) {
            return t('err.entrega_rang_n', $n);
        }
        $fets = paperets_fets($lo);
        if ($fets !== null && $fins > $fets) {
            return t('err.entrega_fora', $fets);
        }
        $xoc = fila("SELECT e.des_de, e.fins_a, COALESCE(" . NOM_PERSONA_SQL . ", ?) AS qui FROM loteria_entregues e LEFT JOIN persones p ON p.id = e.persona_id
            WHERE e.loteria_id = ? AND e.id IS NOT ? AND e.des_de IS NOT NULL AND e.des_de <= ? AND e.fins_a >= ?", [t('loteria.caixa'), $lo['id'], $id, $fins, $de]);
        if ($xoc) {
            return t('err.entrega_solapa', sprintf('%04d–%04d', $xoc['des_de'], $xoc['fins_a']), $xoc['qui']);
        }
    }
    if ($d['import_pagat'] !== null && $d['import_pagat'] < 0) {
        return t('err.import');
    }
    if ($d['import_pagat'] !== null && $d['import_pagat'] > 0 && $d['forma_pagament_id'] === null) {
        return t('err.entrega_forma');
    }
    return null;
}

/** Primer número lliure per a una entrega nova (el següent a l'últim repartit), o null si no se sap o ja no en queden */
function seguent_paperet(array $lo, int $quants): ?int
{
    $de = (int)valor('SELECT COALESCE(MAX(fins_a), 0) FROM loteria_entregues WHERE loteria_id = ?', [$lo['id']]) + 1;
    $fets = paperets_fets($lo);
    return $fets !== null && $de + $quants - 1 <= $fets ? $de : null;
}

/**
 * Tot el que cal saber d'un sorteig: paperets (fets, repartits, tornats, venuts, a la caixa) i diners
 * (el que s'hauria de cobrar, el cobrat per forma de pagament, el que s'ha de pagar a l'administració i el que hauríem de tindre).
 */
function resum_loteria(array $lo): array
{
    $entregues = q("SELECT e.*, " . NOM_PERSONA_SQL . " AS persona, f.nom AS forma, COALESCE(f.compte, 'caixa') AS compte FROM loteria_entregues e
        LEFT JOIN persones p ON p.id = e.persona_id LEFT JOIN formes_pagament f ON f.id = e.forma_pagament_id
        WHERE e.loteria_id = ? ORDER BY (e.des_de IS NULL), e.des_de, e.id", [$lo['id']])->fetchAll();
    $preu = (float)$lo['preu'];
    $r = ['entregues' => $entregues, 'fets' => paperets_fets($lo), 'repartits' => 0, 'tornats' => 0, 'venuts' => 0, 'oberts' => 0, 'fora' => 0,
        'hauriem' => 0.0, 'cobrat' => 0.0, 'per_forma' => [], 'per_compte' => ['caixa' => 0.0, 'banc' => 0.0]];
    foreach ($entregues as $e) {
        $r['repartits'] += (int)$e['participacions'];
        $r['tornats'] += (int)$e['tornades'];
        // Venuts: només els de les entregues liquidades; les obertes encara estan «fora» (no se sap quants vendran)
        if (entrega_oberta($e)) {
            $r['oberts']++;
            $r['fora'] += (int)$e['participacions'];
        } else {
            $r['venuts'] += venuts_entrega($e);
            $r['hauriem'] += venuts_entrega($e) * $preu;
        }
        if ((float)$e['import_pagat'] > 0) {
            $r['cobrat'] += (float)$e['import_pagat'];
            $forma = $e['forma'] ?? t('loteria.sense_forma');
            $r['per_forma'][$forma] = ($r['per_forma'][$forma] ?? 0) + (float)$e['import_pagat'];
            $r['per_compte'][$e['compte'] === 'banc' ? 'banc' : 'caixa'] += (float)$e['import_pagat'];
        }
    }
    $r['a_caixa'] = $r['fets'] !== null ? $r['fets'] - $r['repartits'] + $r['tornats'] : null;
    $r['sense_repartir'] = $r['fets'] !== null ? $r['fets'] - $r['repartits'] : null;
    $r['falta_cobrar'] = round($r['hauriem'] - $r['cobrat'], 2);
    // A l'administració es paguen els dècims comprats; si no s'han indicat, el que juguen els paperets venuts
    $r['amb_decims'] = (int)$lo['decims'] > 0;
    $r['a_pagar'] = $r['amb_decims'] ? (int)$lo['decims'] * (float)$lo['preu_decim'] : $r['venuts'] * (float)$lo['juga'];
    $r['pagat'] = (float)$lo['pagat_administracio'];
    $r['falta_pagar'] = round($r['a_pagar'] - $r['pagat'], 2);
    $compteAdmin = $lo['forma_pagament_administracio'] ? (valor('SELECT compte FROM formes_pagament WHERE id = ?', [$lo['forma_pagament_administracio']]) ?: 'banc') : 'banc';
    $r['per_compte'][$compteAdmin === 'caixa' ? 'caixa' : 'banc'] -= $r['pagat'];
    $r['tindre'] = round($r['cobrat'] - $r['pagat'], 2);
    $r['si_venen'] = $r['fora'] * $preu;
    $r['benefici'] = round($r['hauriem'] + $r['si_venen'] - $r['a_pagar'], 2);
    $r['juguen_fets'] = $r['fets'] !== null ? $r['fets'] * (float)$lo['juga'] : null;
    return $r;
}

function pagina_loteria(): void
{
    requereix('economia.view');
    $lo = fila('SELECT * FROM loteries WHERE id = ?', [(int)get('id')]);
    if (!$lo) {
        redirigeix('llista', ['e' => 'loteries']);
    }
    $id = (int)$lo['id'];
    $error = null;

    // Entrega de paperets a una persona (o venda a la caixa de l'entitat)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        requereix('economia.edit');
        $persona = (int)post('persona_id') ?: null;
        $quants = (int)post('quants');
        $de = trim((string)post('des_de')) !== '' ? (int)post('des_de') : null;
        if ($persona && !valor('SELECT id FROM persones WHERE id = ?', [$persona])) {
            $error = t('err.no_trobat');
        } else {
            db()->exec('BEGIN IMMEDIATE'); // perquè dos entregues alhora no agafen els mateixos números
            $de ??= seguent_paperet($lo, max(1, $quants));
            $d = ['loteria_id' => $id, 'persona_id' => $persona, 'participacions' => $quants, 'des_de' => $de, 'fins_a' => $de !== null ? $de + $quants - 1 : null,
                'tornades' => null, 'import_pagat' => null, 'forma_pagament_id' => null];
            $error = error_entrega_loteria($d, null);
            if ($error === null) {
                q('INSERT INTO loteria_entregues(loteria_id, persona_id, data_entrega, participacions, des_de, fins_a) VALUES(?, ?, ?, ?, ?, ?)',
                    [$id, $persona, date('Y-m-d'), $quants, $d['des_de'], $d['fins_a']]);
                audita('crea', 'loteria_entregues', (int)db()->lastInsertId(), (string)$quants);
                db()->exec('COMMIT');
                avis($d['des_de'] !== null ? t('loteria.entregats_rang', $quants, sprintf('%04d', $d['des_de']), sprintf('%04d', $d['fins_a'])) : t('loteria.entregats', $quants));
                redirigeix('loteria', ['id' => $id]);
            }
            db()->exec('ROLLBACK');
        }
    }

    $r = resum_loteria($lo);
    $mida = (int)$lo['participacions_talonari'];
    $html = $error ? '<div class="avis error">' . h($error) . '</div>' : '';
    $html .= '<div class="accions"><a class="boto secundari" href="' . h(url('fitxa', ['e' => 'loteries', 'id' => $id])) . '">' . h(t('loteria.fitxa')) . '</a>'
        . ($r['fets'] ? '<a class="boto secundari" target="_blank" href="' . h(url('participacions', ['loteria' => $id])) . '">🖨 ' . h(t('loteria.imprimeix_totes')) . '</a>' : '')
        . '<a class="boto secundari" href="' . h(url('llista', ['e' => 'formes_pagament'])) . '">💳 ' . h(t('ent.formes_pagament')) . '</a></div>';
    $html .= '<p>' . h(t('loteria.descripcio', $lo['numeros'], euros($lo['preu']), euros($lo['juga']), euros((float)$lo['preu'] - (float)$lo['juga']), data_llegible($lo['data_sorteig']) ?: '—')) . '</p>';
    if ($r['fets'] === null) {
        $html .= '<div class="avis info">' . h(t('loteria.sense_talonaris_fets')) . ' <a href="' . h(url('edita', ['e' => 'loteries', 'id' => $id])) . '">' . h(t('edita')) . '</a></div>';
    }

    // Paperets
    $html .= '<section class="fill"><header><h2>' . h(t('loteria.paperets')) . '</h2></header><div class="targetes">'
        . targeta($r['fets'] === null ? '—' : (string)$r['fets'], $r['fets'] === null ? t('loteria.fets') : t('loteria.fets_n', (int)$lo['talonaris'], $mida))
        . targeta((string)$r['repartits'], t('loteria.repartits_n', count(array_unique(array_filter(array_column($r['entregues'], 'persona_id'))))))
        . targeta((string)$r['tornats'], t('loteria.tornats'))
        . targeta((string)$r['venuts'], t('loteria.venuts'))
        . targeta((string)$r['fora'], t('loteria.fora', $r['oberts']))
        . targeta($r['a_caixa'] === null ? '—' : (string)$r['a_caixa'], $r['a_caixa'] === null ? t('loteria.a_caixa') : t('loteria.a_caixa_n', $r['sense_repartir'], $r['tornats']))
        . '</div></section>';

    // Diners: el que s'hauria de cobrar, el cobrat, l'administració i el que hauríem de tindre
    $formes = implode(' · ', array_map(fn($f, $v) => $f . ' ' . euros($v), array_keys($r['per_forma']), $r['per_forma']));
    $fila = fn(string $etq, string $valor, string $nota = '', string $classe = '') => '<tr' . ($classe ? ' class="' . $classe . '"' : '') . '><th>' . h($etq) . '</th><td class="num">' . h($valor) . '</td><td>' . h($nota) . '</td></tr>';
    $html .= '<section class="fill"><header><h2>' . h(t('loteria.diners')) . '</h2></header><div class="taula-wrap"><table class="taula diners-loteria"><tbody>'
        . $fila(t('loteria.d_hauriem'), euros($r['hauriem']), t('loteria.d_hauriem_n', $r['venuts'], euros($lo['preu'])))
        . $fila(t('loteria.d_cobrat'), euros($r['cobrat']), $formes)
        . $fila(t('loteria.d_falta_cobrar'), euros($r['falta_cobrar']), t('loteria.d_falta_cobrar_n'), $r['falta_cobrar'] > 0.004 ? 'dif-neg' : '')
        . ($r['fora'] ? $fila(t('loteria.d_fora'), euros($r['si_venen']), t('loteria.d_fora_n', $r['fora'], $r['oberts'])) : '')
        . $fila(t('loteria.d_a_pagar'), euros($r['a_pagar']), $r['amb_decims'] ? t('loteria.d_decims', (int)$lo['decims'], euros($lo['preu_decim'])) : t('loteria.d_sense_decims', $r['venuts'], euros($lo['juga'])))
        . $fila(t('loteria.d_pagat'), euros($r['pagat']), $lo['data_pagament_administracio'] ? data_llegible($lo['data_pagament_administracio']) : '')
        . $fila(t('loteria.d_falta_pagar'), euros($r['falta_pagar']), '', $r['falta_pagar'] > 0.004 ? 'dif-neg' : '')
        . $fila(t('loteria.d_tindre'), euros($r['tindre']), t('loteria.d_tindre_n', euros($r['per_compte']['caixa']), euros($r['per_compte']['banc'])), 'destacat')
        . $fila(t('loteria.d_benefici'), euros($r['benefici']), t($r['fora'] ? 'loteria.d_benefici_fora' : 'loteria.d_benefici_n'))
        . '</tbody></table></div>';
    if ($r['amb_decims'] && $r['juguen_fets'] !== null && abs($r['juguen_fets'] - $r['a_pagar']) > 0.004) {
        $html .= '<p class="nota">' . h(t('loteria.d_no_quadra', euros($r['juguen_fets']), euros($r['a_pagar']))) . '</p>';
    }
    $html .= '</section>';

    // Entregar paperets
    if (pot('economia.edit')) {
        $ops = '';
        foreach (opcions_ref('persones') as $pid => $nom) {
            $ops .= '<option value="' . (int)$pid . '">' . h($nom) . '</option>';
        }
        $seg = seguent_paperet($lo, 1);
        $html .= '<section class="fill"><header><h2>' . h(t('loteria.entrega')) . '</h2></header><form method="post" class="formulari en-fila">' . csrf_camp() . '<div class="camps">'
            . '<div class="camp"><label>' . h(t('loteria.a_qui')) . '</label><select name="persona_id" data-cerca="1"><option value="">' . h(t('loteria.caixa')) . '</option>' . $ops . '</select></div>'
            . '<div class="camp"><label>' . h(t('loteria.quants')) . '</label><input type="number" name="quants" min="1" max="5000" value="' . $mida . '" required></div>'
            . '<div class="camp"><label>' . h(t('loteria.des_de')) . '</label><input type="number" name="des_de" min="1"' . ($seg ? ' placeholder="' . sprintf('%04d', $seg) . '"' : '') . '></div></div>'
            . '<div class="accions"><button>🎟 ' . h(t('loteria.entrega_boto')) . '</button></div><small>' . h(t('loteria.entrega_ajuda')) . '</small></form></section>';
    }

    // Qui té paperets i què ha pagat
    if (!$r['entregues']) {
        $html .= '<p class="buit">' . h(t('loteria.sense_entregues')) . '</p>';
    } else {
        $preu = (float)$lo['preu'];
        $files = '';
        $tot = ['deu' => 0.0, 'pagat' => 0.0];
        foreach ($r['entregues'] as $e) {
            $deu = entrega_oberta($e) ? 0.0 : venuts_entrega($e) * $preu;
            $falta = $deu - (float)$e['import_pagat'];
            $tot['deu'] += $deu;
            $tot['pagat'] += (float)$e['import_pagat'];
            $u = url('fitxa', ['e' => 'loteria_entregues', 'id' => $e['id']]);
            $oberta = entrega_oberta($e);
            $files .= '<tr data-href="' . h($u) . '"><td><a href="' . h($u) . '">' . h($e['persona'] ?? t('loteria.caixa')) . '</a></td>'
                . '<td class="rang">' . ($e['des_de'] ? sprintf('%04d–%04d', $e['des_de'], $e['fins_a']) . ' <small>(' . h(talonari_de($lo, (int)$e['des_de']) === talonari_de($lo, (int)$e['fins_a']) ? t('loteria.p_talonari', talonari_de($lo, (int)$e['des_de']))
                    : t('loteria.talonaris_de', talonari_de($lo, (int)$e['des_de']), talonari_de($lo, (int)$e['fins_a']))) . ')</small>' : '—') . '</td>'
                . '<td class="num">' . (int)$e['participacions'] . '</td><td class="num">' . h((string)($e['tornades'] ?? '')) . '</td><td class="num">' . ($oberta ? '' : venuts_entrega($e)) . '</td>'
                . '<td class="num">' . ($oberta ? '' : h(euros($deu))) . '</td><td class="num">' . h($e['import_pagat'] === null ? '' : euros($e['import_pagat']))
                . ($e['forma'] ? '<small class="forma">' . h($e['forma']) . '</small>' : '') . '</td>'
                . '<td class="num' . (!$oberta && $falta > 0.004 ? ' dif-neg' : '') . '">' . ($oberta ? h(t('loteria.sense_liquidar')) : h(euros($falta))) . '</td></tr>';
        }
        $html .= '<div class="taula-wrap"><table class="taula loteria-resum"><thead><tr><th>' . h(t('loteria.col_persona')) . '</th><th>' . h(t('loteria.col_numeros')) . '</th><th class="num">'
            . h(t('loteria.col_paperets')) . '</th><th class="num">' . h(t('camp.tornades')) . '</th><th class="num">' . h(t('loteria.col_venuts')) . '</th><th class="num">' . h(t('loteria.col_deute'))
            . '</th><th class="num">' . h(t('loteria.col_pagat')) . '</th><th class="num">' . h(t('loteria.col_falta')) . '</th></tr></thead><tbody>'
            . $files . '</tbody><tfoot><tr><td colspan="2">' . h(t('total')) . '</td><td class="num">' . $r['repartits'] . '</td><td class="num">' . $r['tornats'] . '</td><td class="num">' . $r['venuts']
            . '</td><td class="num">' . h(euros($tot['deu'])) . '</td><td class="num">' . h(euros($tot['pagat'])) . '</td><td class="num">' . h(euros($tot['deu'] - $tot['pagat'])) . '</td></tr></tfoot></table></div>';
    }
    $html .= '<p class="nota">' . h(t('loteria.nota')) . '</p>';
    pagina($lo['nom'], $html, t('ent1.loteries'));
}

/** Participacions per a imprimir: totes les del sorteig, les d'una entrega o les d'un talonari */
function pagina_participacions(): void
{
    requereix('economia.view');
    $numeros = [];
    $lo = null;
    if (get('entrega') !== '') {
        $e = fila('SELECT * FROM loteria_entregues WHERE id = ?', [(int)get('entrega')]);
        $lo = $e ? fila('SELECT * FROM loteries WHERE id = ?', [$e['loteria_id']]) : null;
        if ($e && $e['des_de']) {
            $numeros = range((int)$e['des_de'], (int)$e['fins_a']);
        }
    } else {
        $lo = fila('SELECT * FROM loteries WHERE id = ?', [(int)get('loteria')]);
        if ($lo) {
            $mida = (int)$lo['participacions_talonari'];
            $fins = paperets_fets($lo) ?? (int)valor('SELECT COALESCE(MAX(fins_a), 0) FROM loteria_entregues WHERE loteria_id = ?', [$lo['id']]);
            if (get('talonari') !== '') {
                $tn = (int)get('talonari');
                $numeros = $tn >= 1 && ($tn - 1) * $mida < $fins ? range(($tn - 1) * $mida + 1, min($tn * $mida, $fins)) : [];
            } elseif ($fins > 0) {
                $numeros = range(1, $fins);
            }
        }
    }
    document_inici(t('loteria.participacions'), t('loteria.n_participacions', count($numeros)), t('loteria.consell'));
    if (!$lo || !$numeros) {
        echo '<p class="buit">' . h(t('cap_resultat')) . '</p>';
        document_fi();
        return;
    }
    $societat = config('societat_nom') ?: 'Compàs';
    $logo = url_logo();
    $donatiu = (float)$lo['preu'] - (float)$lo['juga'];
    $sorteig = t('loteria.sorteig_del', $lo['nom'], $lo['data_sorteig'] ? data_llegible($lo['data_sorteig']) : '');
    foreach (array_chunk($numeros, 8) as $full) {
        echo '<section class="full full-participacions">';
        foreach ($full as $n) {
            echo '<div class="participacio"><div class="p-cap">' . ($logo ? '<img src="' . h($logo) . '" alt="">' : '') . '<b>' . h($societat) . '</b></div>'
                . '<div class="p-sorteig">' . h($sorteig) . '</div>'
                . '<div class="p-numeros">' . h($lo['numeros']) . '</div>'
                . '<div class="p-imports"><span>' . h(t('loteria.p_juga')) . ' <b>' . h(euros($lo['juga'])) . '</b></span><span>' . h(t('loteria.p_donatiu')) . ' <b>' . h(euros($donatiu)) . '</b></span><span>'
                . h(t('loteria.p_preu')) . ' <b>' . h(euros($lo['preu'])) . '</b></span></div>'
                . '<div class="p-num">' . h(t('loteria.p_num', sprintf('%04d', $n))) . ' <small>(' . h(t('loteria.p_talonari', talonari_de($lo, $n))) . ')</small></div>'
                . '<div class="p-peu">' . nl2br(h($lo['peu'] ?: t('loteria.peu_defecte'))) . '</div></div>';
        }
        echo '</section>';
    }
    audita('imprimeix_participacions', 'loteries', $lo['id'], (string)count($numeros));
    document_fi();
}
