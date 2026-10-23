<?php
// 2.0: el que necessiten la resta d'associacions: categories d'edat, activitats amb inscripcions, llicències
// i revisions mèdiques, entitats federades i càrrecs de festa de cada exercici
// Copyright (C) 2026 José Vicente Sanjuan
// SPDX-License-Identifier: AGPL-3.0-or-later

/* ---------------- Valors per a suggerir ---------------- */

/** Valors diferents que ja s'han escrit en una columna (per a suggerir-los en un camp de text) */
function valors_usats(string $taula, string $columna): array
{
    return q("SELECT DISTINCT $columna FROM $taula WHERE COALESCE($columna, '') <> '' ORDER BY $columna COLLATE ALFA LIMIT 200")->fetchAll(PDO::FETCH_COLUMN);
}

/** Càrrecs de festa per a triar: els del tipus d'entitat i els que ja s'han fet servir */
function suggeriments_carrecs_festa(): array
{
    return array_values(array_unique(array_merge(info_tipus()['carrecs'][idioma()] ?? [], valors_usats('carrecs_festa', 'carrec'))));
}

/* ---------------- Categories d'edat ---------------- */

/** Hi ha categories o el tipus d'entitat en fa servir */
function te_categories(): bool
{
    return !empty(info_tipus()['categories']) || (int)valor('SELECT COUNT(*) FROM categories') > 0;
}

/** Les categories, en ordre: [id, nom, edat_min, edat_max] */
function llista_categories(): array
{
    static $c = null;
    return $c ??= q('SELECT id, nom, edat_min, edat_max FROM categories ORDER BY COALESCE(ordre, 999999), edat_min, nom')->fetchAll();
}

/** Edat que una persona fa durant l'any en què comença l'exercici (així es compten les categories esportives i festeres) */
function edat_categoria(?string $naixement, ?int $any = null): ?int
{
    if (!$naixement || !preg_match('/^\d{4}/', $naixement)) {
        return null;
    }
    return max(0, ($any ?? any_exercici()) - (int)substr($naixement, 0, 4));
}

function categoria_edat(?string $naixement, ?int $any = null): ?string
{
    $e = edat_categoria($naixement, $any);
    if ($e === null) {
        return null;
    }
    foreach (llista_categories() as $c) {
        if ($e >= (int)$c['edat_min'] && $e <= (int)$c['edat_max']) {
            return $c['nom'];
        }
    }
    return null;
}

/* ---------------- Activitats i inscripcions ---------------- */

/** Places ocupades d'una activitat (sense comptar una inscripció, si se n'està editant una) */
function places_ocupades(int $activitat, ?int $sense = null): int
{
    return (int)valor('SELECT COALESCE(SUM(places), 0) FROM activitat_inscripcions WHERE activitat_id = ? AND id IS NOT ?', [$activitat, $sense]);
}

function valida_inscripcio(array $d, ?int $id): ?string
{
    $a = fila('SELECT * FROM activitats WHERE id = ?', [(int)$d['activitat_id']]);
    if (!$a) {
        return t('err.no_trobat');
    }
    if ($d['places'] === null || $d['places'] < 1 || $d['places'] > 999) {
        return t('err.inscripcio_places');
    }
    if ($d['import'] !== null && $d['import'] < 0) {
        return t('err.import_positiu');
    }
    if ($a['estat'] === 'anullada') {
        return t('err.activitat_anullada');
    }
    if (valor('SELECT id FROM activitat_inscripcions WHERE activitat_id = ? AND persona_id = ? AND id IS NOT ?', [$a['id'], $d['persona_id'], $id])) {
        return t('err.inscripcio_repetida');
    }
    if ($a['places'] !== null && places_ocupades((int)$a['id'], $id) + $d['places'] > (int)$a['places']) {
        return t('err.activitat_completa', (int)$a['places'] - places_ocupades((int)$a['id'], $id));
    }
    return null;
}

function avis_activitat(array $f): ?string
{
    if (in_array($f['estat'], ['anullada', 'realitzada'], true)) {
        return null;
    }
    if ($f['places'] !== null && places_ocupades((int)$f['id']) >= (int)$f['places']) {
        return t('act.avis_completa');
    }
    if ($f['estat'] === 'inscripcio_oberta' && $f['data_limit'] && $f['data_limit'] < date('Y-m-d')) {
        return t('act.avis_termini');
    }
    return null;
}

/** Resum de la fitxa: inscrits, places lliures, import i rebuts */
function seccio_activitat(array $f): string
{
    $r = fila('SELECT COUNT(*) AS n, COALESCE(SUM(places), 0) AS places, COALESCE(SUM(import), 0) AS import, COUNT(rebut_id) AS amb_rebut
        FROM activitat_inscripcions WHERE activitat_id = ?', [$f['id']]);
    $html = '<div class="targetes resum-activitat">';
    $html .= targeta((string)$r['n'], t('act.inscripcions'), url('llista', ['e' => 'activitat_inscripcions', 'f[activitat_id]' => $f['id']]));
    $html .= targeta((string)$r['places'], t('act.places_ocupades'), '');
    if ($f['places'] !== null) {
        $html .= targeta((string)max(0, (int)$f['places'] - (int)$r['places']), t('act.places_lliures'), '');
    }
    if ((float)$r['import'] > 0) {
        $html .= targeta(euros((float)$r['import']), t('act.import_total'), '');
        if (modul_actiu('economia')) {
            $html .= targeta($r['amb_rebut'] . ' / ' . $r['n'], t('act.amb_rebut'), '');
        }
    }
    return $html . '</div>';
}

function accions_fitxa_activitat(array $f): string
{
    $html = boto_document(url('activitat_llista', ['id' => $f['id']]), t('act.llista_inscrits'));
    if (modul_actiu('economia') && pot('economia.edit')
        && valor('SELECT COUNT(*) FROM activitat_inscripcions WHERE activitat_id = ? AND rebut_id IS NULL AND COALESCE(import, 0) > 0', [$f['id']])) {
        $html .= '<a class="boto secundari" href="' . h(url('genera_rebuts', ['tipus' => 'activitat', 'activitat' => $f['id']])) . '">🧾 ' . h(t('act.crea_rebuts')) . '</a>';
    }
    return $html;
}

/** Llista d'inscrits per a imprimir (per a passar llista, per a l'autobús, per al restaurant…) */
function pagina_activitat_llista(): void
{
    requereix('societat.view');
    $a = fila('SELECT t.*, ag.nom AS agrupacio FROM activitats t LEFT JOIN agrupacions ag ON ag.id = t.agrupacio_id WHERE t.id = ?', [(int)get('id')]);
    if (!$a) {
        redirigeix('llista', ['e' => 'activitats']);
    }
    $ins = q('SELECT i.*, p.nom, p.cognoms, p.telefon, p.data_naixement, r.numero AS rebut, r.estat AS estat_rebut FROM activitat_inscripcions i
        JOIN persones p ON p.id = i.persona_id LEFT JOIN rebuts r ON r.id = i.rebut_id WHERE i.activitat_id = ? ORDER BY p.cognoms COLLATE ALFA, p.nom COLLATE ALFA', [$a['id']])->fetchAll();
    $titol = t('act.llista_inscrits');
    document_inici($titol . ' · ' . $a['nom'], $a['nom'], t('llist.consell'));
    echo '<section class="full llistat">' . capcalera_societat() . '<h2>' . h($a['nom']) . '</h2>';
    $sub = array_filter([$a['tipus'] ? t('opt.' . $a['tipus']) : null, data_llegible($a['data']) . ($a['hora'] ? ' · ' . $a['hora'] : '')
        . ($a['data_fi'] && $a['data_fi'] !== $a['data'] ? ' – ' . data_llegible($a['data_fi']) : ''), $a['lloc'], $a['agrupacio']]);
    echo '<p class="subtitol">' . h(implode(' · ', $sub)) . '</p>';
    $ambImport = (bool)array_filter($ins, fn($i) => (float)$i['import'] > 0);
    echo '<table class="taula-llistat"><thead><tr><th class="num">#</th><th>' . h(t('ent1.persones')) . '</th><th>' . h(t('camp.telefon')) . '</th><th class="num">'
        . h(t('camp.places_inscripcio')) . '</th>' . ($ambImport ? '<th class="num">' . h(t('camp.import')) . '</th><th>' . h(t('ent1.rebuts')) . '</th>' : '')
        . '<th>' . h(t('camp.observacions')) . '</th><th>✓</th></tr></thead><tbody>';
    $places = 0;
    $total = 0.0;
    foreach ($ins as $n => $i) {
        $places += (int)$i['places'];
        $total += (float)$i['import'];
        echo '<tr><td class="num">' . ($n + 1) . '</td><td>' . h(nom_complet($i)) . '</td><td>' . h((string)$i['telefon']) . '</td><td class="num">' . (int)$i['places'] . '</td>'
            . ($ambImport ? '<td class="num">' . h($i['import'] !== null ? euros((float)$i['import']) : '') . '</td><td>' . h($i['rebut'] ? $i['rebut'] . ' · ' . t('opt.' . $i['estat_rebut']) : '') . '</td>' : '')
            . '<td>' . h((string)$i['observacions']) . '</td><td><span class="casella"></span></td></tr>';
    }
    if (!$ins) {
        echo '<tr><td colspan="8">' . h(t('cap_element')) . '</td></tr>';
    }
    echo '</tbody><tfoot><tr><td colspan="3">' . h(t('total')) . '</td><td class="num">' . $places . ($a['places'] ? ' / ' . (int)$a['places'] : '') . '</td>'
        . ($ambImport ? '<td class="num">' . h(euros($total)) . '</td><td></td>' : '') . '<td colspan="2"></td></tr></tfoot></table>';
    echo '<p class="peu-llistat">' . h(t('llist.generat', data_llegible(date('Y-m-d H:i:s')))) . '</p></section>';
    audita('llistat', 'activitats', (int)$a['id']);
    document_fi();
}

/** Rebuts de les inscripcions d'una activitat que encara no en tenen: un per inscripció */
function calcula_activitat(array $a, array &$resum): array
{
    $resum = ['sense_pagador' => []];
    $rebuts = [];
    $ins = q('SELECT i.*, p.nom, p.cognoms, p.data_naixement, al.id AS alumne_id, al.pagador_id FROM activitat_inscripcions i JOIN persones p ON p.id = i.persona_id
        LEFT JOIN alumnes al ON al.persona_id = p.id WHERE i.activitat_id = ? AND COALESCE(i.import, 0) > 0 ORDER BY p.cognoms, p.nom', [$a['id']])->fetchAll();
    foreach ($ins as $i) {
        // Els menors paguen per mitjà de qui paga les seues classes (pare, mare o tutor/a), si és alumne
        $pagador = (int)$i['persona_id'];
        if (!es_major_edat($i['data_naixement']) && $i['alumne_id']) {
            $pagador = pagador_alumne(['id' => $i['alumne_id'], 'pagador_id' => $i['pagador_id'], 'persona_id' => $i['persona_id'], 'data_naixement' => $i['data_naixement']]) ?? $pagador;
        }
        $p = $pagador === (int)$i['persona_id'] ? $i : fila('SELECT nom, cognoms FROM persones WHERE id = ?', [$pagador]);
        $rebuts[] = [
            'persona_id' => $pagador,
            'nom' => nom_persona($p),
            'concepte' => t('rebut.concepte_activitat', $a['nom']),
            'tipus' => 'activitat',
            'periode' => data_llegible($a['data']),
            'clau' => "activitat:{$a['id']}:{$i['id']}",
            'linies' => [[$a['nom'] . ' — ' . nom_natural($i) . ((int)$i['places'] > 1 ? ' (' . t('act.n_places', (int)$i['places']) . ')' : ''), round((float)$i['import'], 2), null]],
        ];
    }
    return completa_rebuts($rebuts);
}

/** Després de crear els rebuts, cada inscripció apunta al seu */
function enllaca_rebuts_activitat(int $activitat): void
{
    q("UPDATE activitat_inscripcions SET rebut_id = (SELECT r.id FROM rebuts r WHERE r.clau = 'activitat:' || activitat_inscripcions.activitat_id || ':' || activitat_inscripcions.id)
        WHERE activitat_id = ? AND rebut_id IS NULL", [$activitat]);
}

/** Avís de la pantalla d'inici: activitats dels pròxims 15 dies */
function avis_inici_activitats(): string
{
    $files = q("SELECT a.*, (SELECT COALESCE(SUM(places), 0) FROM activitat_inscripcions i WHERE i.activitat_id = a.id) AS ocupades FROM activitats a
        WHERE a.estat <> 'anullada' AND a.data >= date('now','localtime') AND a.data <= date('now','localtime','+15 days') ORDER BY a.data, a.hora LIMIT 10")->fetchAll();
    if (!$files) {
        return '';
    }
    $html = '<section class="fill"><header><h2>' . h(t('act.inici_titol')) . '</h2><a class="boto petit secundari" href="' . h(url('llista', ['e' => 'activitats', 'filtre' => 'properes'])) . '">'
        . h(t('avis_llista.veure')) . '</a></header><ul class="llista-simple">';
    foreach ($files as $a) {
        $html .= '<li><span class="hora">' . h(data_llegible($a['data']) . ($a['hora'] ? ' ' . $a['hora'] : '')) . '</span> <a href="' . h(url('fitxa', ['e' => 'activitats', 'id' => $a['id']])) . '">'
            . h($a['nom']) . '</a>' . ($a['lloc'] ? ' · ' . h($a['lloc']) : '') . ' <span class="etiqueta">' . h(t('act.inscrits_places', (int)$a['ocupades'] . ($a['places'] ? ' / ' . (int)$a['places'] : ''))) . '</span></li>';
    }
    return $html . '</ul></section>';
}

/* ---------------- Llicències i revisions mèdiques ---------------- */

function avis_llicencia(array $f): ?string
{
    if ($f['estat'] === 'baixa') {
        return null;
    }
    $avui = date('Y-m-d');
    $prop = date('Y-m-d', strtotime('+30 days'));
    $avisos = [];
    if ($f['data_caducitat'] && $f['data_caducitat'] < $avui) {
        $avisos[] = t('llic.caducada', data_llegible($f['data_caducitat']));
    } elseif ($f['data_caducitat'] && $f['data_caducitat'] <= $prop) {
        $avisos[] = t('llic.caduca', data_llegible($f['data_caducitat']));
    }
    if ($f['revisio_medica'] && $f['revisio_medica'] < $avui) {
        $avisos[] = t('llic.revisio_caducada', data_llegible($f['revisio_medica']));
    } elseif ($f['revisio_medica'] && $f['revisio_medica'] <= $prop) {
        $avisos[] = t('llic.revisio_caduca', data_llegible($f['revisio_medica']));
    }
    return $avisos ? implode(' · ', $avisos) : null;
}

function avis_inici_llicencies(): string
{
    $n = (int)valor("SELECT COUNT(*) FROM llicencies WHERE estat <> 'baixa' AND ((data_caducitat IS NOT NULL AND data_caducitat <= date('now','localtime','+30 days'))
        OR (revisio_medica IS NOT NULL AND revisio_medica <= date('now','localtime','+30 days')))");
    return $n ? '<div class="avis atencio">⚠ ' . h(t('llic.inici', $n)) . ' <a href="' . h(url('llista', ['e' => 'llicencies', 'filtre' => 'llic_caduquen'])) . '">' . h(t('avis_llista.veure')) . '</a></div>' : '';
}

/* ---------------- Entitats federades ---------------- */

/** Rebuts de la quota anual de les entitats federades, a nom de la persona que representa cada una */
function calcula_federades(int $any, array &$resum): array
{
    $resum = ['sense_pagador' => []];
    $rebuts = [];
    $exercici = nom_exercici($any);
    foreach (q("SELECT * FROM entitats_federades WHERE estat = 'actiu' AND COALESCE(quota, 0) > 0 ORDER BY nom COLLATE ALFA")->fetchAll() as $e) {
        if (!$e['persona_id']) {
            $resum['sense_pagador'][] = $e['nom'];
            continue;
        }
        $rebuts[] = [
            'persona_id' => (int)$e['persona_id'],
            'nom' => $e['nom'] . ' · ' . nom_persona(fila('SELECT nom, cognoms FROM persones WHERE id = ?', [$e['persona_id']])),
            'concepte' => t('rebut.concepte_federada', $exercici),
            'tipus' => 'quota_entitat',
            'periode' => $exercici,
            'clau' => "entitat:{$e['id']}:$any",
            'linies' => [[t('rebut.concepte_federada', $exercici) . ' — ' . $e['nom'] . ($e['numero'] ? ' (' . t('rebut.num_federada', $e['numero']) . ')' : ''), round((float)$e['quota'], 2), null]],
        ];
    }
    return completa_rebuts($rebuts);
}

/* ---------------- Informes (Llistats) ---------------- */

/** Persones d'alta per categoria d'edat. «qui»: socis (per defecte), alumnes, membres o totes les persones actives */
function dades_categories(): array
{
    $qui = in_array(get('qui'), ['socis', 'alumnes', 'membres', 'persones'], true) ? get('qui') : 'socis';
    $any = any_exercici();
    $sql = [
        'socis' => "SELECT p.*, s.numero FROM socis s JOIN persones p ON p.id = s.persona_id WHERE s.estat = 'actiu'",
        'alumnes' => "SELECT p.*, NULL AS numero FROM alumnes al JOIN persones p ON p.id = al.persona_id WHERE al.estat = 'actiu'",
        'membres' => "SELECT DISTINCT p.*, NULL AS numero FROM membres m JOIN persones p ON p.id = m.persona_id WHERE m.estat = 'actiu'",
        'persones' => "SELECT p.*, (SELECT s.numero FROM socis s WHERE s.persona_id = p.id AND s.estat = 'actiu' LIMIT 1) AS numero FROM persones p WHERE p.actiu = 1",
    ][$qui];
    $persones = q("$sql ORDER BY p.data_naixement DESC, p.cognoms COLLATE ALFA, p.nom COLLATE ALFA")->fetchAll();
    $cols = [t('ent1.persones'), t('camp.numero'), t('camp.data_naixement'), t('llist.edat_exercici')];
    $seccions = [];
    foreach (llista_categories() as $c) {
        $seccions[$c['nom']] = ['titol' => $c['nom'] . ' (' . t('cat.edats', (int)$c['edat_min'], (int)$c['edat_max']) . ')', 'cols' => $cols, 'num' => [1, 3], 'files' => []];
    }
    $sense = ['titol' => t('cat.sense'), 'cols' => $cols, 'num' => [1, 3], 'files' => []];
    foreach ($persones as $p) {
        $cat = categoria_edat($p['data_naixement'], $any);
        $fila = [nom_complet($p), (string)$p['numero'], data_llegible($p['data_naixement']), (string)(edat_categoria($p['data_naixement'], $any) ?? '')];
        if ($cat !== null && isset($seccions[$cat])) {
            $seccions[$cat]['files'][] = $fila;
        } else {
            $sense['files'][] = $fila;
        }
    }
    foreach ($seccions as &$s) {
        $s['titol'] .= ' · ' . count($s['files']);
    }
    unset($s);
    if ($sense['files']) {
        $sense['titol'] .= ' · ' . count($sense['files']);
        $seccions[] = $sense;
    }
    return ['titol' => t('inf.categories') . ' · ' . nom_exercici($any), 'subtitol' => t('cat.subtitol', count($persones), $any), 'seccions' => array_values($seccions), 'nota' => t('cat.nota')];
}

function dades_llicencies(): array
{
    $temporada = get('temporada', nom_exercici(any_exercici()));
    $files = q("SELECT l.*, p.nom, p.cognoms, p.data_naixement, a.nom AS agrupacio FROM llicencies l JOIN persones p ON p.id = l.persona_id
        LEFT JOIN agrupacions a ON a.id = l.agrupacio_id WHERE l.estat <> 'baixa' AND (? = '' OR l.temporada = ?)
        ORDER BY a.nom COLLATE ALFA, p.cognoms COLLATE ALFA, p.nom COLLATE ALFA", [$temporada, $temporada])->fetchAll();
    $seccions = [];
    $cols = [t('ent1.persones'), t('llist.categoria'), t('camp.federacio'), t('camp.numero_llicencia'), t('camp.data_caducitat'), t('camp.revisio_medica'), t('camp.estat_llicencia')];
    $destacats = [];
    foreach ($files as $l) {
        $clau = $l['agrupacio'] ?? t('llic.sense_agrupacio');
        $seccions[$clau] ??= ['titol' => $clau, 'cols' => $cols, 'files' => [], 'destacats' => []];
        if (avis_llicencia($l)) {
            $seccions[$clau]['destacats'][] = count($seccions[$clau]['files']);
        }
        $seccions[$clau]['files'][] = [nom_complet($l), (string)categoria_edat($l['data_naixement']), (string)$l['federacio'], (string)$l['numero'],
            data_llegible($l['data_caducitat']), data_llegible($l['revisio_medica']), t('opt.' . $l['estat'])];
    }
    return ['titol' => t('inf.llicencies') . ($temporada !== '' ? ' · ' . $temporada : ''), 'subtitol' => t('n_resultats', count($files)), 'seccions' => array_values($seccions),
        'nota' => t('llic.nota_informe')];
}

function dades_federades(): array
{
    $files = q("SELECT e.*, " . NOM_PERSONA_SQL . " AS representant, p.telefon AS tel_rep, p.email AS email_rep FROM entitats_federades e LEFT JOIN persones p ON p.id = e.persona_id
        WHERE e.estat = 'actiu' ORDER BY (COALESCE(e.numero, '') = ''), CAST(e.numero AS INTEGER), e.nom COLLATE ALFA")->fetchAll();
    return [
        'titol' => t('inf.federades'),
        'subtitol' => t('n_resultats', count($files)),
        'seccions' => [['cols' => [t('camp.numero_federada'), t('camp.nom'), t('camp.cif'), t('camp.poblacio'), t('camp.representant'), t('camp.telefon'), t('camp.email'), t('camp.quota')],
            'num' => [0, 7],
            'files' => array_map(fn($e) => [(string)$e['numero'], $e['nom'], (string)$e['cif'], (string)$e['poblacio'], (string)$e['representant'],
                (string)($e['telefon'] ?: $e['tel_rep']), (string)($e['email'] ?: $e['email_rep']), $e['quota'] !== null ? euros((float)$e['quota']) : ''], $files),
            'peu' => ['', t('total'), '', '', '', '', '', euros((float)array_sum(array_column($files, 'quota')))]]],
    ];
}

function dades_carrecs_festa(): array
{
    $files = q("SELECT c.*, p.nom, p.cognoms FROM carrecs_festa c JOIN persones p ON p.id = c.persona_id ORDER BY c.exercici DESC, c.id")->fetchAll();
    $seccions = [];
    foreach ($files as $c) {
        $seccions[$c['exercici']] ??= ['titol' => t('festa.exercici', $c['exercici']), 'cols' => [t('camp.carrec_festa'), t('ent1.persones'), t('camp.data_proclamacio')], 'files' => []];
        $seccions[$c['exercici']]['files'][] = [$c['carrec'], nom_complet($c), data_llegible($c['data'])];
    }
    return ['titol' => t('inf.carrecs_festa'), 'subtitol' => t('n_resultats', count($files)), 'seccions' => array_values($seccions)];
}
