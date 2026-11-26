<?php
// Cercador de dalt de tot: una persona pel nom, els cognoms, el DNI, el telèfon, el correu, el número de soci o el NIA,
// i també agrupacions, activitats, obres i material. Mentre s'escriu, proposa resultats (JSON per a app.js).
// Copyright (C) 2026 José Vicente Sanjuan
// SPDX-License-Identifier: AGPL-3.0-or-later

const CERCA_MAX = 60;

/** Pot fer servir el cercador (el consultor ja té els llistats que pot vore) */
function pot_cercar(): bool
{
    return pot('persones.view') && !es_consultor();
}

/** Les paraules de la cerca, sense accents ni majúscules; totes han d'aparéixer */
function paraules_cerca(string $q): array
{
    return array_values(array_filter(preg_split('/\s+/', normalitza_text($q)), fn($p) => $p !== ''));
}

function coincidix(array $paraules, string $text): bool
{
    $text = normalitza_text($text);
    foreach ($paraules as $p) {
        if (!str_contains($text, $p)) {
            return false;
        }
    }
    return true;
}

/**
 * Resultats agrupats: [clau del grup => ['titol' => …, 'items' => [['text', 'sub', 'url'], …]]]
 * Les persones primer, amb el que són a l'entitat (soci núm. 12, alumna, banda…).
 */
function resultats_cerca(string $q): array
{
    $paraules = paraules_cerca($q);
    if (!$paraules) {
        return [];
    }
    $grups = [];
    $digits = preg_replace('/\D/', '', $q);
    // Persones: també pel número de soci (exacte) i pel telèfon sense espais
    $perNumero = ctype_digit(trim($q)) && pot('socis.view')
        ? q('SELECT persona_id FROM socis WHERE numero = ?', [trim($q)])->fetchAll(PDO::FETCH_COLUMN) : [];
    // i l'alumnat pel NIA (exacte)
    if (pot('escola.view') && modul_actiu('escola')) {
        $perNumero = array_merge($perNumero, q('SELECT persona_id FROM alumnes WHERE nia = ?', [trim($q)])->fetchAll(PDO::FETCH_COLUMN));
    }
    $trobades = [];
    foreach (q('SELECT id, nom, cognoms, document, telefon, email, actiu FROM persones ORDER BY actiu DESC, cognoms COLLATE ALFA, nom COLLATE ALFA')->fetchAll() as $p) {
        $text = $p['nom'] . ' ' . $p['cognoms'] . ' ' . $p['document'] . ' ' . $p['email'];
        $tel = preg_replace('/\D/', '', (string)$p['telefon']);
        if (in_array($p['id'], $perNumero) || coincidix($paraules, $text) || (strlen($digits) >= 6 && str_contains($tel, $digits))) {
            $trobades[(int)$p['id']] = $p;
            if (count($trobades) >= CERCA_MAX) {
                break;
            }
        }
    }
    if ($trobades) {
        $rols = rols_persones(array_keys($trobades));
        $items = [];
        foreach ($trobades as $id => $p) {
            $items[] = [nom_persona($p), implode(' · ', array_merge($rols[$id] ?? [], $p['actiu'] ? [] : [t('cerca.inactiva')])), url('fitxa', ['e' => 'persones', 'id' => $id])];
        }
        $grups['persones'] = ['titol' => t('ent.persones'), 'items' => $items];
    }
    // Altres coses que es busquen pel nom
    foreach ([
        ['agrupacions', 'SELECT id, nom AS t, tipus AS s FROM agrupacions', fn($f) => t('opt.' . $f['s'])],
        ['activitats', 'SELECT id, nom AS t, data AS s FROM activitats', fn($f) => data_llegible($f['s'])],
        ['obres', 'SELECT id, titol AS t, numero_arxiu AS s, compositor AS c FROM obres', fn($f) => trim($f['s'] . ' · ' . $f['c'], ' ·')],
        ['instruments', 'SELECT id, inventari || \' · \' || tipus AS t, marca AS s FROM instruments', fn($f) => (string)$f['s']],
        ['entitats_federades', 'SELECT id, nom AS t, poblacio AS s FROM entitats_federades', fn($f) => (string)$f['s']],
    ] as [$ent, $sql, $sub]) {
        $e = entitat($ent);
        if (!pot($e['cap'] . '.view') || !disponible($ent)) {
            continue;
        }
        $items = [];
        foreach (q($sql)->fetchAll() as $f) {
            if (coincidix($paraules, $f['t'] . ' ' . ($f['s'] ?? '') . ' ' . ($f['c'] ?? ''))) {
                $items[] = [$f['t'], $sub($f), url('fitxa', ['e' => $ent, 'id' => $f['id']])];
                if (count($items) >= 20) {
                    break;
                }
            }
        }
        if ($items) {
            $grups[$ent] = ['titol' => t('ent.' . $ent), 'items' => $items];
        }
    }
    return $grups;
}

/** El que és cada persona a l'entitat, per a distingir dues persones amb el mateix nom */
function rols_persones(array $ids): array
{
    $rols = [];
    $in = implode(',', array_map('intval', $ids));
    $afig = function (string $sql, callable $text) use (&$rols) {
        foreach (q($sql)->fetchAll() as $f) {
            $rols[(int)$f['persona_id']][] = $text($f);
        }
    };
    if (pot('socis.view')) {
        $afig("SELECT persona_id, numero FROM socis WHERE estat = 'actiu' AND persona_id IN ($in)",
            fn($f) => $f['numero'] ? t('cerca.soci_num', t('ent1.socis'), $f['numero']) : t('ent1.socis'));
    }
    if (pot('escola.view') && modul_actiu('escola')) {
        $afig("SELECT persona_id FROM alumnes WHERE estat = 'actiu' AND persona_id IN ($in)", fn($f) => t('ent1.alumnes'));
        $afig("SELECT persona_id FROM professors WHERE estat = 'actiu' AND persona_id IN ($in)", fn($f) => t('ent1.professors'));
        $afig("SELECT DISTINCT t.tutor_id AS persona_id FROM tutories t JOIN alumnes a ON a.id = t.alumne_id WHERE a.estat = 'actiu' AND t.tutor_id IN ($in)", fn($f) => t('cerca.familia'));
    }
    if (pot('agrupacions.view') && modul_actiu('agrupacions')) {
        $afig("SELECT m.persona_id, a.nom FROM membres m JOIN agrupacions a ON a.id = m.agrupacio_id WHERE m.estat = 'actiu' AND m.persona_id IN ($in) ORDER BY a.nom", fn($f) => $f['nom']);
    }
    if (pot('societat.view') && modul_actiu('junta')) {
        $afig("SELECT persona_id, carrec FROM carrecs WHERE data_fi IS NULL AND persona_id IN ($in)", fn($f) => t('opt.' . $f['carrec']));
    }
    return $rols;
}

function pagina_cerca(): void
{
    requereix('persones.view');
    $q = trim(mb_substr((string)get('q'), 0, 100));
    $grups = resultats_cerca($q);
    if (get('json') === '1') {
        // Propostes mentre s'escriu: les primeres de cada grup
        $res = [];
        foreach ($grups as $g) {
            foreach (array_slice($g['items'], 0, 6) as [$text, $sub, $enllac]) {
                $res[] = ['text' => $text, 'sub' => $sub, 'grup' => $g['titol'], 'url' => $enllac];
            }
        }
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(array_slice($res, 0, 12), JSON_UNESCAPED_UNICODE);
        exit;
    }
    $total = array_sum(array_map(fn($g) => count($g['items']), $grups));
    // Si només hi ha una cosa, s'obri directament
    if ($total === 1) {
        header('Location: ' . reset($grups)['items'][0][2]);
        exit;
    }
    $html = '<form class="barra cerca-gran" method="get" role="search"><input type="hidden" name="r" value="cerca"><input type="search" name="q" aria-label="' . h(t('cerca.titol')) . '" value="' . h($q)
        . '" placeholder="' . h(t('cerca.placeholder')) . '" autofocus><button>' . h(t('cerca.boto')) . '</button></form>';
    if ($q === '') {
        $html .= '<p class="buit">' . h(t('cerca.explica')) . '</p>';
    } elseif (!$grups) {
        $html .= '<div class="buit"><p>' . h(t('cerca.res', $q)) . '</p>'
            . (pot('persones.edit') ? '<p><a class="boto" href="' . h(url('edita', ['e' => 'persones'])) . '">+ ' . h(t('inici.accio_persona')) . '</a></p>' : '') . '</div>';
    } else {
        $html .= '<p class="nota">' . h(t('cerca.trobats', $total, $q)) . '</p>';
        foreach ($grups as $g) {
            $html .= '<section class="fill"><header><h2>' . h($g['titol']) . ' <span class="compte">' . count($g['items']) . '</span></h2></header><ul class="resultats-cerca">';
            foreach ($g['items'] as [$text, $sub, $enllac]) {
                $html .= '<li><a href="' . h($enllac) . '"><b>' . h($text) . '</b>' . ($sub !== '' ? '<span>' . h($sub) . '</span>' : '') . '</a></li>';
            }
            $html .= '</ul></section>';
        }
        if (count($grups['persones']['items'] ?? []) >= CERCA_MAX) {
            $html .= '<p class="nota">' . h(t('cerca.massa', CERCA_MAX)) . '</p>';
        }
    }
    pagina(t('cerca.titol'), $html);
}
