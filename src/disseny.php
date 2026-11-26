<?php
// Plantilla de pàgina i menú
// Copyright (C) 2026 José Vicente Sanjuan
// SPDX-License-Identifier: AGPL-3.0-or-later

/**
 * El menú lateral: només el que es fa servir cada dia. El que es configura una vegada (cursos, assignatures i aules;
 * tarifes i domiciliacions; mòduls i registre d'activitat; el compte propi) està dins de la pantalla on es fa servir
 * (vegeu menu_relacionat()), i cada secció es pot plegar.
 */
function menu(): array
{
    return [
        ['inici', 'menu.inici', null, '⌂'],
        ['persones', 'ent.persones', 'persones.view', '👤'],
        ['socis', 'ent.socis', 'socis.view', '★'],
        ['llicencies', 'ent.llicencies', 'socis.view', '🪪'],
        ['entitats_federades', 'ent.entitats_federades', 'socis.view', '🏢'],
        ['_escola', 'menu.escola', null, null],
        ['alumnes', 'ent.alumnes', 'escola.view', '🎓'],
        ['professors', 'ent.professors', 'escola.view', '🧑‍🏫'],
        ['matricules', 'ent.matricules', 'escola.view', '📝'],
        ['grups', 'ent.grups', 'escola.view', '👥'],
        ['horari', 'menu.horari', 'escola.view', '🕑'],
        ['notes', 'menu.notes', 'escola.notes', '✎'],
        ['butlleti', 'menu.butlletins', 'escola.view', '🖨'],
        ['_agrup', 'menu.agrupacions', null, null],
        ['agrupacions', 'ent.agrupacions', 'agrupacions.view', '🎺'],
        ['membres', 'ent.membres', 'agrupacions.view', '🎶'],
        ['repertori', 'menu.repertori', 'agrupacions.view', '🎼'],
        ['_instr', 'menu.material', null, null],
        ['instruments', 'ent.instruments', 'instruments.view', '🎷'],
        ['cessions', 'ent.cessions', 'instruments.view', '⇄'],
        ['uniformes', 'menu.uniformes', 'instruments.view', '👕'],
        ['_economia', 'menu.economia', null, null],
        ['economia', 'menu.resum_economic', 'economia.view', '€'],
        ['rebuts', 'ent.rebuts', 'economia.view', '🧾'],
        ['genera_rebuts', 'genera.titol', 'economia.edit', '＋'],
        ['remeses', 'menu.remeses', 'economia.view', '🏦'],
        ['loteries', 'ent.loteries', 'economia.view', '🎟'],
        ['subvencions', 'ent.subvencions', 'economia.view', '🤝'],
        ['caixa', 'menu.caixa', 'economia.view', '📒'],
        ['_societat', 'menu.societat', null, null],
        ['activitats', 'ent.activitats', 'societat.view', '🗓'],
        ['junta', 'menu.junta', 'societat.view', '🏛'],
        ['carrecs_festa', 'ent.carrecs_festa', 'societat.view', '👑'],
        ['reunions', 'ent.reunions', 'societat.view', '📖'],
        ['documents', 'ent.documents', 'societat.view', '📁'],
        ['comunicats', 'menu.comunicats', 'societat.view', '✉'],
        ['_docs', 'menu.documents', null, null],
        ['llistats', 'menu.llistats', null, '📋'],
        ['importa', 'menu.importa', 'persones.edit', '⬆'],
        ['_admin', 'menu.administracio', null, null],
        ['usuaris', 'menu.usuaris', 'usuaris.manage', '🔑'],
        ['configuracio', 'menu.configuracio', 'config.manage', '⚙'],
        ['copia', 'menu.copia', 'copia.manage', '💾'],
        ['ajuda', 'menu.ajuda', null, '?'],
    ];
}

/**
 * L'apartat del manual que explica la pantalla actual (per al botó «?» de dalt).
 * Es busca pel número de l'apartat, que és el mateix en valencià i en castellà; el que és d'administració va al manual d'administració.
 */
function enllac_ajuda(): ?string
{
    $r = get('r', 'inici');
    $clau = in_array($r, ['llista', 'fitxa', 'edita'], true) ? get('e') : $r;
    $usuari = [
        'inici' => '2', 'cerca' => '2', 'persones' => '6', 'socis' => '8', 'tipus_socis' => '8', 'full_alta' => '8', 'alumnes' => '7', 'alta_alumne' => '7', 'tutories' => '7',
        'matricules' => '7', 'cursos' => '5.1', 'periodes' => '5.1', 'assignatures' => '5.2', 'aules' => '5.2', 'professors' => '5.3', 'grups' => '5.4', 'horaris' => '5.4',
        'horari' => '5.5', 'passa_curs' => '5.6', 'notes' => '10', 'butlleti' => '11', 'agrupacions' => '9', 'membres' => '9', 'assistencia' => '9', 'repertori' => '9',
        'obres' => '9', 'instruments' => '12', 'cessions' => '12', 'manteniments' => '12', 'uniformes' => '12', 'uniforme_compres' => '12', 'uniforme_lliuraments' => '12',
        'economia' => '13', 'tarifes' => '13.1', 'genera_rebuts' => '13.3', 'mandats' => '13.4', 'remeses' => '13.5', 'remesa' => '13.5', 'rebuts' => '13.6',
        'caixa' => '13.7', 'moviments' => '13.7', 'categories_caixa' => '13.7', 'subvencions' => '13.8', 'loteries' => '14', 'loteria' => '14', 'loteria_entregues' => '14', 'formes_pagament' => '14', 'junta' => '15.1', 'carrecs' => '15.1',
        'reunions' => '15.2', 'documents' => '16', 'rgpd' => '16', 'comunicats' => '17', 'activitats' => '18', 'activitat_inscripcions' => '18', 'categories' => '19',
        'llicencies' => '20', 'carrecs_festa' => '21', 'entitats_federades' => '22', 'llistats' => '23', 'certificat' => '23', 'importa' => '24', 'perfil' => '26',
    ];
    $admin = ['configuracio' => '2', 'moduls' => '2', 'economia_config' => '3', 'copia' => '4', 'usuaris' => '5', 'auditoria' => '5'];
    $manual = isset($admin[$clau]) && pot('config.manage') ? 'admin' : 'usuari';
    $num = $manual === 'admin' ? $admin[$clau] : ($usuari[$clau] ?? null);
    if ($num === null || $r === 'ajuda') {
        return null;
    }
    $es = idioma() === 'es';
    $fitxer = DOCS_DIR . '/' . ($manual === 'admin' ? ($es ? 'manual-administracion' : 'manual-administracio') : ($es ? 'manual-usuario' : 'manual-usuari')) . '.md';
    $md = is_file($fitxer) ? (string)file_get_contents($fitxer) : '';
    $nivell = str_contains($num, '.') ? '###' : '##';
    if (!preg_match('/^' . $nivell . ' ' . preg_quote($num, '/') . '\.? (.+)$/m', $md, $m)) {
        return url('ajuda', $manual === 'admin' ? ['manual' => 'admin'] : []);
    }
    // El mateix identificador que posa markdown_simple() als títols
    $id = trim(preg_replace('/[^\p{L}\p{N}]+/u', '-', mb_strtolower($num . ($nivell === '##' ? '. ' : ' ') . $m[1])), '-');
    return url('ajuda', $manual === 'admin' ? ['manual' => 'admin'] : []) . '#' . $id;
}

/** Botons a les pantalles que fan servir les coses que ja no estan al menú (on es configuren) */
function menu_relacionat(string $quin): string
{
    $grups = [
        'escola' => [['cursos', 'ent.cursos', 'escola.view', '📅'], ['assignatures', 'ent.assignatures', 'escola.view', '♪'], ['aules', 'ent.aules', 'escola.view', '🚪']],
        'economia' => [['tarifes', 'ent.tarifes', 'economia.view', '🏷'], ['mandats', 'ent.mandats', 'economia.view', '✍']],
        'admin' => [['moduls', 'menu.moduls', 'config.manage', '🧩'], ['auditoria', 'menu.auditoria', 'auditoria.view', '📜']],
    ];
    $html = '';
    foreach ($grups[$quin] ?? [] as [$clau, $etq, $cap, $icona]) {
        if (pot($cap) && disponible($clau) && (!es_consultor() || consultor_pot_ruta($clau))) {
            $html .= '<a class="boto secundari" href="' . h(enllac_menu($clau)) . '">' . icona_menu($clau, $icona) . ' ' . h(t($etq)) . '</a>';
        }
    }
    return $html;
}

/** Les icones de música només per a les entitats musicals */
function icona_menu(string $clau, string $icona): string
{
    if (in_array(tipus_entitat(), ['societat_musical', 'colla'], true)) {
        return $icona;
    }
    $esport = tipus_entitat() === 'club_esportiu';
    return [
        'agrupacions' => $esport ? '🏅' : '👥', 'membres' => '🙋', 'instruments' => '📦', 'assignatures' => $esport ? '⚽' : '◆',
        'alumnes' => $esport ? '🏃' : '🎓', 'professors' => $esport ? '📣' : '🧑‍🏫',
    ][$clau] ?? $icona;
}

function enllac_menu(string $clau): string
{
    // «remeses» té pantalla pròpia (preparar i descarregar), encara que també siga una entitat
    return isset(entitats()[$clau]) && !in_array($clau, ['remeses', 'comunicats', 'uniformes'], true) ? url('llista', ['e' => $clau]) : url($clau);
}

function html_menu(): string
{
    $actual = get('r', 'inici');
    if (in_array($actual, ['llista', 'fitxa', 'edita'], true)) {
        $actual = get('e');
    }
    $actual = ['remesa' => 'remeses', 'loteria' => 'loteries', 'loteria_entregues' => 'loteries', 'formes_pagament' => 'loteries', 'rebut_linies' => 'rebuts', 'economia_config' => 'economia',
        'carrecs' => 'junta', 'moviments' => 'caixa', 'categories_caixa' => 'caixa', 'rgpd' => 'documents',
        'uniforme_compres' => 'uniformes', 'uniforme_lliuraments' => 'uniformes', 'tipus_socis' => 'socis', 'full_alta' => 'socis', 'obres' => 'repertori', 'tipus_obres' => 'repertori',
        'organismes' => 'subvencions', 'requisits_justificacio' => 'subvencions', 'subvencio_cobraments' => 'subvencions', 'subvencio_despeses' => 'subvencions',
        'activitat_inscripcions' => 'activitats', 'categories' => 'socis',
        'cursos' => 'grups', 'periodes' => 'grups', 'assignatures' => 'grups', 'aules' => 'grups', 'passa_curs' => 'grups', 'alta_alumne' => 'alumnes',
        'mandats' => 'economia', 'tarifes' => 'economia', 'economia_config' => 'economia', 'moduls' => 'configuracio', 'auditoria' => 'usuaris'][$actual] ?? $actual;
    // Seccions plegables (app.js recorda quines s'han plegat); la de la pantalla actual sempre està oberta
    $seccions = [['', '', []]];
    foreach (menu() as [$clau, $etq, $cap, $icona]) {
        if ($clau[0] === '_') {
            $seccions[] = [$clau, t($etq), []];
            continue;
        }
        if ((es_consultor() && !consultor_pot_ruta($clau)) || ($cap && !pot($cap)) || !disponible($clau) || (in_array($clau, ['notes', 'butlleti'], true) && !te_notes())) {
            continue;
        }
        $seccions[array_key_last($seccions)][2][] = '<li><a href="' . h(enllac_menu($clau)) . '"' . ($clau === $actual ? ' class="actiu" aria-current="page"' : '') . '><span class="ic">'
            . h(icona_menu($clau, $icona)) . '</span>' . h(t($etq)) . '</a></li>';
    }
    $html = '';
    foreach ($seccions as [$clau, $nom, $items]) {
        if (!$items) {
            continue;
        }
        if ($clau === '') {
            $html .= '<ul>' . implode('', $items) . '</ul>';
            continue;
        }
        $obert = str_contains(implode('', $items), 'class="actiu"');
        $html .= '<details class="grup-menu" data-grup="' . h(substr($clau, 1)) . '"' . ($obert || $clau !== '_admin' ? ' open' : '') . ($obert ? ' data-actual="1"' : '')
            . '><summary>' . h($nom) . '</summary><ul>' . implode('', $items) . '</ul></details>';
    }
    return $html;
}

function html_avisos(): string
{
    $html = '';
    foreach ($_SESSION['avisos'] ?? [] as [$tipus, $text]) {
        $html .= '<div class="avis ' . h($tipus) . '">' . h($text) . '</div>';
    }
    unset($_SESSION['avisos']);
    return $html;
}

/**
 * Accessibilitat del contingut de cada pantalla, en un sol lloc:
 * - cada <label> sense «for» queda unit al camp que el seguix (els lectors de pantalla diuen el nom del camp
 *   i un clic en l'etiqueta hi porta);
 * - els errors s'anuncien de seguida (role="alert") i els avisos de «fet» quan es pot (role="status").
 */
function accessible(string $html): string
{
    static $n = 0;
    $html = preg_replace_callback(
        '~<label>((?:(?!</label>).)*)</label>((?:(?!<label|</div>|</td>|<(?:input|select|textarea)\b).)*)<(input|select|textarea)\b([^>]*)>~s',
        function (array $m) use (&$n): string {
            [$tot, $text, $mig, $tag, $attrs] = $m;
            if (preg_match('/\btype="(hidden|radio|checkbox|submit|button)"/', $attrs)) {
                return $tot;
            }
            if (preg_match('/\bid="([^"]+)"/', $attrs, $id)) {
                $id = $id[1];
            } else {
                $id = 'camp-' . ++$n;
                $attrs = ' id="' . $id . '"' . $attrs;
            }
            return '<label for="' . $id . '">' . $text . '</label>' . $mig . '<' . $tag . $attrs . '>';
        },
        $html
    ) ?? $html;
    return str_replace(['<div class="avis error">', '<div class="avis ok">'], ['<div class="avis error" role="alert">', '<div class="avis ok" role="status">'], $html);
}

function capcalera_html(string $titol): string
{
    $societat = config('societat_nom') ?: 'Compàs';
    return '<!doctype html><html lang="' . h(idioma() === 'ca' ? 'ca-valencia' : 'es') . '"><head><meta charset="utf-8">'
        . '<meta name="viewport" content="width=device-width, initial-scale=1">'
        . '<title>' . h($titol) . ' · ' . h($societat) . '</title>'
        . '<link rel="icon" href="assets/icona.png"><link rel="stylesheet" href="assets/estil.css?v=' . COMPAS_VERSIO . '">'
        . '<script src="assets/app.js?v=' . COMPAS_VERSIO . '" defer></script></head>';
}

function pagina(string $titol, string $cos, ?string $subtitol = null): void
{
    $u = usuari();
    if (!$u) {
        pagina_simple($titol, $cos);
        return;
    }
    $societat = config('societat_nom') ?: 'Compàs';
    $idiomes = '';
    foreach (IDIOMES as $k => $n) {
        $idiomes .= $k === idioma() ? '<strong>' . IDIOMES_CURT[$k] . '</strong>'
            : '<a href="' . h(url('idioma', ['l' => $k, 'tornar' => $_SERVER['REQUEST_URI'] ?? ''])) . '" title="' . h($n) . '">' . IDIOMES_CURT[$k] . '</a>';
    }
    echo capcalera_html($titol);
    echo '<body><a class="salta" href="#contingut">' . h(t('salta_contingut')) . '</a><div class="app">';
    $logo = url_logo();
    echo '<aside class="lateral"><a class="marca" href="?"><img src="' . h($logo ?? 'assets/icona.png') . '" alt="" class="' . ($logo ? 'logo-societat' : '') . '"><span><b>' . h($societat)
        . '</b><small>Compàs</small></span></a><button class="obre-menu" type="button" aria-label="' . h(t('menu.obri')) . '" aria-expanded="false" aria-controls="menu-principal">☰</button>'
        . '<nav id="menu-principal" aria-label="' . h(t('menu.principal')) . '">' . html_menu() . '</nav></aside>';
    $ajuda = enllac_ajuda();
    echo '<main><header class="superior"><div class="titol-pagina"><div>' . ($subtitol ? '<small class="sub">' . h($subtitol) . '</small>' : '') . '<h1>' . h($titol) . '</h1></div>'
        . ($ajuda ? '<a class="ajuda-pagina" href="' . h($ajuda) . '" title="' . h(t('ajuda.pagina')) . '" aria-label="' . h(t('ajuda.pagina')) . '">?</a>' : '') . '</div>';
    if (pot_cercar()) {
        echo '<form class="cerca-global" method="get" role="search" data-res="' . h(t('cerca.cap')) . '" data-n="' . h(t('a11y.resultats')) . '"><input type="hidden" name="r" value="cerca"><input type="search" name="q" autocomplete="off"'
            . ' role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="propostes-cerca" value="'
            . h(get('r') === 'cerca' ? get('q') : '') . '" placeholder="' . h(t('cerca.placeholder_curt')) . '" aria-label="' . h(t('cerca.titol')) . '" title="' . h(t('cerca.drecera'))
            . '"><ul class="propostes-cerca" id="propostes-cerca" role="listbox" aria-label="' . h(t('cerca.titol')) . '" hidden></ul>'
            . '<span class="per-a-lectors" role="status" aria-live="polite"></span></form>';
    }
    echo '<div class="usuari"><span class="idiomes">' . $idiomes . '</span><a href="' . h(url('perfil')) . '" title="' . h(t('menu.compte')) . '">👤 '
        . h($u['usuari']) . '</a><form method="post" action="' . h(url('eixir')) . '">' . csrf_camp() . '<button class="enllac">' . h(t('eixir')) . '</button></form></div></header>';
    echo '<div class="contingut" id="contingut" tabindex="-1">' . accessible(html_avisos() . $cos) . '</div><footer class="peu-app no-imprimir">Compàs ' . COMPAS_VERSIO . ' · ' . h(t('avis_resp.curt'))
        . ' <a href="' . h(url('ajuda')) . '">' . h(t('avis_resp.titol')) . '</a></footer></main></div></body></html>';
}

function pagina_simple(string $titol, string $cos): void
{
    echo capcalera_html($titol);
    echo '<body class="simple"><main class="caixa"><img class="logo" src="assets/logo.png" alt="Compàs: ' . h(t('lema')) . '">'
        . (str_contains($cos, '<h1') ? '' : '<h1 class="per-a-lectors">' . h($titol) . '</h1>')
        . accessible(html_avisos() . $cos) . '</main><p class="versio">Compàs ' . COMPAS_VERSIO . ' · © José Vicente Sanjuan · AGPL-3.0-or-later<br>' . h(t('avis_resp.curt')) . '</p></body></html>';
}
