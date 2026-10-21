<?php
// Plantilla de pàgina i menú
// Copyright (C) 2026 José Vicente Sanjuan
// SPDX-License-Identifier: AGPL-3.0-or-later

function menu(): array
{
    return [
        ['inici', 'menu.inici', null, '⌂'],
        ['persones', 'ent.persones', 'persones.view', '👤'],
        ['socis', 'ent.socis', 'socis.view', '★'],
        ['_escola', 'menu.escola', null, null],
        ['alumnes', 'ent.alumnes', 'escola.view', '🎓'],
        ['professors', 'ent.professors', 'escola.view', '🧑‍🏫'],
        ['matricules', 'ent.matricules', 'escola.view', '📝'],
        ['grups', 'ent.grups', 'escola.view', '👥'],
        ['horari', 'menu.horari', 'escola.view', '🕑'],
        ['notes', 'menu.notes', 'escola.notes', '✎'],
        ['butlleti', 'menu.butlletins', 'escola.view', '🖨'],
        ['cursos', 'ent.cursos', 'escola.view', '📅'],
        ['assignatures', 'ent.assignatures', 'escola.view', '♪'],
        ['aules', 'ent.aules', 'escola.view', '🚪'],
        ['_agrup', 'menu.agrupacions', null, null],
        ['agrupacions', 'ent.agrupacions', 'agrupacions.view', '🎺'],
        ['membres', 'ent.membres', 'agrupacions.view', '🎶'],
        ['_instr', 'menu.instruments', null, null],
        ['instruments', 'ent.instruments', 'instruments.view', '🎷'],
        ['cessions', 'ent.cessions', 'instruments.view', '⇄'],
        ['_economia', 'menu.economia', null, null],
        ['economia', 'menu.resum_economic', 'economia.view', '€'],
        ['rebuts', 'ent.rebuts', 'economia.view', '🧾'],
        ['genera_rebuts', 'genera.titol', 'economia.edit', '＋'],
        ['remeses', 'menu.remeses', 'economia.view', '🏦'],
        ['mandats', 'ent.mandats', 'economia.view', '✍'],
        ['tarifes', 'ent.tarifes', 'economia.view', '🏷'],
        ['loteries', 'ent.loteries', 'economia.view', '🎟'],
        ['caixa', 'menu.caixa', 'economia.view', '📒'],
        ['_societat', 'menu.societat', null, null],
        ['junta', 'menu.junta', 'societat.view', '🏛'],
        ['reunions', 'ent.reunions', 'societat.view', '📖'],
        ['documents', 'ent.documents', 'societat.view', '📁'],
        ['comunicats', 'menu.comunicats', 'societat.view', '✉'],
        ['_docs', 'menu.documents', null, null],
        ['llistats', 'menu.llistats', null, '📋'],
        ['importa', 'menu.importa', 'persones.edit', '⬆'],
        ['_admin', 'menu.administracio', null, null],
        ['usuaris', 'menu.usuaris', 'usuaris.manage', '🔑'],
        ['configuracio', 'menu.configuracio', 'config.manage', '⚙'],
        ['auditoria', 'menu.auditoria', 'auditoria.view', '📜'],
        ['copia', 'menu.copia', 'copia.manage', '💾'],
        ['perfil', 'menu.compte', null, '🔒'],
        ['ajuda', 'menu.ajuda', null, '?'],
    ];
}

function enllac_menu(string $clau): string
{
    // «remeses» té pantalla pròpia (preparar i descarregar), encara que també siga una entitat
    return isset(entitats()[$clau]) && !in_array($clau, ['remeses', 'comunicats'], true) ? url('llista', ['e' => $clau]) : url($clau);
}

function html_menu(): string
{
    $actual = get('r', 'inici');
    if (in_array($actual, ['llista', 'fitxa', 'edita'], true)) {
        $actual = get('e');
    }
    $actual = ['remesa' => 'remeses', 'loteria' => 'loteries', 'talonaris' => 'loteries', 'rebut_linies' => 'rebuts', 'economia_config' => 'economia',
        'carrecs' => 'junta', 'moviments' => 'caixa', 'rgpd' => 'documents'][$actual] ?? $actual;
    $items = [];
    $seccio = null;
    foreach (menu() as [$clau, $etq, $cap, $icona]) {
        if ($clau[0] === '_') {
            $seccio = '<li class="seccio">' . h(t($etq)) . '</li>';
            continue;
        }
        if ($cap && !pot($cap)) {
            continue;
        }
        if ($seccio) {
            $items[] = $seccio;
            $seccio = null;
        }
        $items[] = '<li><a href="' . h(enllac_menu($clau)) . '"' . ($clau === $actual ? ' class="actiu"' : '') . '><span class="ic">'
            . h($icona) . '</span>' . h(t($etq)) . '</a></li>';
    }
    return '<ul>' . implode('', $items) . '</ul>';
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
    echo '<body><div class="app">';
    $logo = url_logo();
    echo '<aside class="lateral"><a class="marca" href="?"><img src="' . h($logo ?? 'assets/icona.png') . '" alt="" class="' . ($logo ? 'logo-societat' : '') . '"><span><b>' . h($societat)
        . '</b><small>Compàs</small></span></a><button class="obre-menu" type="button" aria-label="Menú">☰</button><nav>' . html_menu() . '</nav></aside>';
    echo '<main><header class="superior"><div>' . ($subtitol ? '<small class="sub">' . h($subtitol) . '</small>' : '') . '<h1>' . h($titol) . '</h1></div>';
    echo '<div class="usuari"><span class="idiomes">' . $idiomes . '</span><a href="' . h(url('perfil')) . '" title="' . h(t('menu.compte')) . '">👤 '
        . h($u['usuari']) . '</a><form method="post" action="' . h(url('eixir')) . '">' . csrf_camp() . '<button class="enllac">' . h(t('eixir')) . '</button></form></div></header>';
    echo '<div class="contingut">' . html_avisos() . $cos . '</div></main></div></body></html>';
}

function pagina_simple(string $titol, string $cos): void
{
    echo capcalera_html($titol);
    echo '<body class="simple"><div class="caixa"><img class="logo" src="assets/logo.png" alt="Compàs: ' . h(t('lema')) . '">'
        . html_avisos() . $cos . '</div><p class="versio">Compàs ' . COMPAS_VERSIO . ' · © José Vicente Sanjuan · AGPL-3.0-or-later</p></body></html>';
}
