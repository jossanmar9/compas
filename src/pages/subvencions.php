<?php
// Subvencions: què s'ha demanat, què s'ha concedit, com es justifica, què s'ha cobrat i què falta justificar
// Copyright (C) 2026 José Vicente Sanjuan
// SPDX-License-Identifier: AGPL-3.0-or-later

/** Com es justifica normalment cada organisme: [organisme_id => [requisit_id, …]] */
function requisits_per_organisme(): array
{
    $m = [];
    foreach (q('SELECT organisme_id, requisit_id FROM organisme_requisits')->fetchAll() as $r) {
        $m[(int)$r['organisme_id']][] = (int)$r['requisit_id'];
    }
    return $m;
}

/** Desa la llista de requisits d'una subvenció sense perdre els que ja estaven marcats com a fets */
function desa_requisits_subvencio(int $id, array $requisits): void
{
    $requisits = array_values(array_unique(array_map('intval', $requisits)));
    if ($requisits) {
        $marques = implode(',', array_fill(0, count($requisits), '?'));
        q("DELETE FROM subvencio_requisits WHERE subvencio_id = ? AND requisit_id NOT IN ($marques)", array_merge([$id], $requisits));
    } else {
        q('DELETE FROM subvencio_requisits WHERE subvencio_id = ?', [$id]);
    }
    foreach ($requisits as $r) {
        q('INSERT OR IGNORE INTO subvencio_requisits(subvencio_id, requisit_id) VALUES(?, ?)', [$id, $r]);
    }
}

function desa_requisits_organisme(int $id, array $requisits): void
{
    q('DELETE FROM organisme_requisits WHERE organisme_id = ?', [$id]);
    foreach (array_unique(array_map('intval', $requisits)) as $r) {
        q('INSERT INTO organisme_requisits(organisme_id, requisit_id) VALUES(?, ?)', [$id, $r]);
    }
}

/** Una despesa no es pot imputar (entre totes les subvencions) per més del que val */
function valida_despesa_subvencio(array $d, ?int $id): ?string
{
    $m = fila('SELECT * FROM moviments WHERE id = ?', [(int)$d['moviment_id']]);
    if (!$m || $m['tipus'] !== 'despesa') {
        return t('err.no_es_despesa');
    }
    if ($d['import'] !== null && $d['import'] <= 0) {
        return t('err.import_positiu');
    }
    $altres = (float)valor('SELECT COALESCE(SUM(import), 0) FROM subvencio_despeses WHERE moviment_id = ? AND id IS NOT ?', [$m['id'], $id]);
    $disponible = round((float)$m['import'] - $altres, 2);
    if ($disponible <= 0) {
        return t('err.despesa_ja_imputada', euros((float)$m['import']));
    }
    return $d['import'] !== null && $d['import'] > $disponible + 0.005 ? t('err.despesa_massa', euros($disponible)) : null;
}

/** Xifres d'una subvenció: concedit, a justificar, justificat, cobrat i requisits fets */
function xifres_subvencio(array $f): array
{
    $concedit = (float)$f['import_concedit'];
    $justificar = $f['import_justificar'] !== null ? (float)$f['import_justificar'] : $concedit;
    $justificat = (float)valor('SELECT COALESCE(SUM(import), 0) FROM subvencio_despeses WHERE subvencio_id = ?', [$f['id']]);
    $cobrat = (float)valor('SELECT COALESCE(SUM(import), 0) FROM subvencio_cobraments WHERE subvencio_id = ?', [$f['id']]);
    $req = fila('SELECT COUNT(*) AS n, COALESCE(SUM(fet), 0) AS fets FROM subvencio_requisits WHERE subvencio_id = ?', [$f['id']]);
    return [
        'concedit' => $concedit, 'justificar' => $justificar, 'justificat' => $justificat, 'falta_justificar' => max(0, round($justificar - $justificat, 2)),
        'cobrat' => $cobrat, 'falta_cobrar' => max(0, round($concedit - $cobrat, 2)), 'requisits' => (int)$req['n'], 'fets' => (int)$req['fets'],
    ];
}

/** Dies que falten fins a una data (negatiu si ja ha passat) */
function dies_fins(string $data): int
{
    return (int)floor((strtotime($data) - strtotime(date('Y-m-d'))) / 86400);
}

/** El termini que toca ara: demanar-la (prevista) o justificar-la (concedida). [tipus, data, dies] o null */
function termini_subvencio(array $f): ?array
{
    if ($f['estat'] === 'prevista' && $f['data_limit_sollicitud']) {
        return ['sollicitud', $f['data_limit_sollicitud'], dies_fins($f['data_limit_sollicitud'])];
    }
    if ($f['estat'] === 'concedida' && $f['data_limit_justificacio']) {
        return ['justificacio', $f['data_limit_justificacio'], dies_fins($f['data_limit_justificacio'])];
    }
    return null;
}

function avis_subvencio(array $f): ?string
{
    $t = termini_subvencio($f);
    if (!$t || $t[2] > 30) {
        return null;
    }
    return $t[2] < 0 ? t('subv.termini_passat_' . $t[0], data_llegible($t[1])) : t('subv.queden_' . $t[0], $t[2], data_llegible($t[1]));
}

function accions_llista_subvencions(): string
{
    return '<a class="boto secundari" href="' . h(url('llista', ['e' => 'organismes'])) . '">🏛 ' . h(t('ent.organismes')) . '</a>'
        . '<a class="boto secundari" href="' . h(url('llista', ['e' => 'requisits_justificacio'])) . '">☑ ' . h(t('ent.requisits_justificacio')) . '</a>';
}

function accions_fitxa_subvencio(array $f): string
{
    $html = '<a class="boto secundari" target="_blank" href="' . h(url('subvencio_relacio', ['id' => $f['id']])) . '">🖨 ' . h(t('subv.relacio')) . '</a>'
        . '<a class="boto secundari" href="' . h(url('subvencio_relacio', ['id' => $f['id'], 'csv' => 1])) . '">' . h(t('subv.relacio_csv')) . '</a>';
    if (pot('economia.edit')) {
        $html .= '<form method="post" action="' . h(url('subvencio_duplica', ['id' => $f['id']])) . '">' . csrf_camp()
            . '<button class="secundari" title="' . h(t('subv.duplica_ajuda')) . '">⧉ ' . h(t('subv.duplica', ((int)$f['exercici'] ?: (int)date('Y')) + 1)) . '</button></form>';
    }
    return $html;
}

/** Barra de progrés amb el text a la dreta */
function barra_progres(float $fet, float $total, string $text): string
{
    $pct = $total > 0 ? min(100, round($fet / $total * 100)) : ($fet > 0 ? 100 : 0);
    return '<div class="progres' . ($pct >= 100 ? ' complet' : '') . '"><div class="progres-barra"><span style="width:' . $pct . '%"></span></div><span class="progres-text">' . h($text) . '</span></div>';
}

/** A la fitxa: el resum (concedit, cobrat, justificat), el termini i la llista de com es justifica */
function seccio_subvencio(array $f): string
{
    $x = xifres_subvencio($f);
    $html = '<section class="fill resum-subvencio"><header><h2>' . h(t('subv.resum')) . '</h2></header><div class="targetes">'
        . targeta(euros($x['concedit']), t('camp.import_concedit'))
        . targeta(euros($x['cobrat']), $x['falta_cobrar'] > 0 ? t('subv.cobrat_falta', euros($x['falta_cobrar'])) : t('subv.cobrat'))
        . targeta(euros($x['justificat']), $x['falta_justificar'] > 0 ? t('subv.justificat_falta', euros($x['falta_justificar'])) : t('subv.justificat'))
        . '</div>';
    if ($x['justificar'] > 0) {
        $html .= barra_progres($x['justificat'], $x['justificar'], t('subv.progres_justificat', euros($x['justificat']), euros($x['justificar'])));
    }
    // El termini, si encara falta (quan falten 30 dies o menys ja ix l'avís de dalt)
    if (($t = termini_subvencio($f)) && $t[2] > 30) {
        $html .= '<div class="avis info">' . h(t('subv.queden_' . $t[0], $t[2], data_llegible($t[1]))) . '</div>';
    }
    $html .= '</section>';

    // Com es justifica: una casella per requisit, que es marca quan ja està fet
    $reqs = q('SELECT r.id, r.nom, r.descripcio, sr.fet, sr.data_fet FROM subvencio_requisits sr JOIN requisits_justificacio r ON r.id = sr.requisit_id
        WHERE sr.subvencio_id = ? ORDER BY COALESCE(r.ordre, 999999), r.nom', [$f['id']])->fetchAll();
    $html .= '<section class="fill"><header><h2>' . h(t('camp.com_es_justifica')) . ' <span class="compte">' . $x['fets'] . '/' . $x['requisits'] . '</span></h2>'
        . (pot('economia.edit') ? '<a class="boto petit secundari" href="' . h(url('edita', ['e' => 'subvencions', 'id' => $f['id']])) . '">' . h(t('subv.canvia_requisits')) . '</a>' : '') . '</header>';
    if (!$reqs) {
        return $html . '<p class="buit petit">' . h(t('subv.sense_requisits')) . '</p></section>';
    }
    if ($x['requisits'] > 0) {
        $html .= barra_progres($x['fets'], $x['requisits'], t('subv.progres_requisits', $x['fets'], $x['requisits']));
    }
    $edita = pot('economia.edit');
    $html .= $edita ? '<form method="post" action="' . h(url('subvencio_requisits', ['id' => $f['id']])) . '">' . csrf_camp() : '';
    $html .= '<ul class="llista-requisits">';
    foreach ($reqs as $r) {
        $html .= '<li' . ($r['fet'] ? ' class="fet"' : '') . '><label><input type="checkbox" name="fet[]" value="' . (int)$r['id'] . '"' . ($r['fet'] ? ' checked' : '') . ($edita ? '' : ' disabled') . '> <span>'
            . h($r['nom']) . ($r['descripcio'] ? '<small>' . h($r['descripcio']) . '</small>' : '') . '</span></label>'
            . ($r['fet'] && $r['data_fet'] ? '<span class="etiqueta">' . h(t('subv.fet_el', data_llegible($r['data_fet']))) . '</span>' : '') . '</li>';
    }
    $html .= '</ul>';
    return $html . ($edita ? '<div class="accions"><button>' . h(t('subv.desa_requisits')) . '</button></div></form>' : '') . '</section>';
}

/** Marca quins requisits ja estan fets */
function pagina_subvencio_requisits(): void
{
    requereix('economia.edit');
    $id = (int)get('id');
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && fila('SELECT id FROM subvencions WHERE id = ?', [$id])) {
        $fets = array_map('intval', (array)($_POST['fet'] ?? []));
        foreach (q('SELECT requisit_id, fet FROM subvencio_requisits WHERE subvencio_id = ?', [$id])->fetchAll() as $r) {
            $ara = in_array((int)$r['requisit_id'], $fets, true) ? 1 : 0;
            if ($ara !== (int)$r['fet']) {
                q('UPDATE subvencio_requisits SET fet = ?, data_fet = ? WHERE subvencio_id = ? AND requisit_id = ?', [$ara, $ara ? date('Y-m-d') : null, $id, $r['requisit_id']]);
            }
        }
        audita('modifica', 'subvencions', $id, 'requisits');
        avis(t('desat'));
    }
    redirigeix('fitxa', ['e' => 'subvencions', 'id' => $id]);
}

/** La mateixa subvenció per a l'any següent: dades i requisits (sense fer), sense imports concedits ni cobraments */
function pagina_subvencio_duplica(): void
{
    requereix('economia.edit');
    $f = fila('SELECT * FROM subvencions WHERE id = ?', [(int)get('id')]);
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$f) {
        redirigeix('llista', ['e' => 'subvencions']);
    }
    $any = fn(?string $d) => $d ? date('Y-m-d', strtotime($d . ' +1 year')) : null;
    db()->beginTransaction();
    q("INSERT INTO subvencions(nom, organisme_id, exercici, objecte, estat, data_limit_sollicitud, data_limit_justificacio, import_pressupost, import_sollicitat, observacions)
        VALUES(?, ?, ?, ?, 'prevista', ?, ?, ?, ?, ?)", [$f['nom'], $f['organisme_id'], ((int)$f['exercici'] ?: (int)date('Y')) + 1, $f['objecte'],
        $any($f['data_limit_sollicitud']), $any($f['data_limit_justificacio']), $f['import_pressupost'], $f['import_sollicitat'], $f['observacions']]);
    $nou = (int)db()->lastInsertId();
    q('INSERT INTO subvencio_requisits(subvencio_id, requisit_id) SELECT ?, requisit_id FROM subvencio_requisits WHERE subvencio_id = ?', [$nou, $f['id']]);
    audita('crea', 'subvencions', $nou, 'duplica ' . $f['id']);
    db()->commit();
    avis(t('subv.duplicada'));
    redirigeix('edita', ['e' => 'subvencions', 'id' => $nou]);
}

/** Despeses imputades a una subvenció, amb les dades de la factura */
function despeses_subvencio(int $id): array
{
    return q('SELECT d.import AS imputat, d.observacions AS obs, m.* FROM subvencio_despeses d JOIN moviments m ON m.id = d.moviment_id
        WHERE d.subvencio_id = ? ORDER BY m.data, m.id', [$id])->fetchAll();
}

/** Relació de despeses per a la justificació (compte justificatiu): per a imprimir o en CSV */
function pagina_subvencio_relacio(): void
{
    requereix('economia.view');
    $f = fila('SELECT s.*, o.nom AS organisme FROM subvencions s LEFT JOIN organismes o ON o.id = s.organisme_id WHERE s.id = ?', [(int)get('id')]);
    if (!$f) {
        redirigeix('llista', ['e' => 'subvencions']);
    }
    $despeses = despeses_subvencio((int)$f['id']);
    $total = array_sum(array_column($despeses, 'imputat'));
    $totalFactures = array_sum(array_column($despeses, 'import'));
    $cols = [t('subv.col_num'), t('camp.data'), t('camp.proveidor'), t('camp.nif_proveidor'), t('subv.col_factura'), t('camp.concepte'), t('subv.col_import_factura'), t('camp.import_imputat')];

    if (get('csv') === '1') {
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="justificacio-' . preg_replace('/[^a-z0-9]+/', '-', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $f['nom']))) . '-' . (int)$f['exercici'] . '.csv"');
        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, $cols, ';', '"', '');
        foreach ($despeses as $i => $d) {
            fputcsv($out, [$i + 1, data_llegible($d['data']), $d['proveidor'], $d['nif'], $d['justificant'], $d['concepte'],
                number_format((float)$d['import'], 2, ',', ''), number_format((float)$d['imputat'], 2, ',', '')], ';', '"', '');
        }
        fputcsv($out, ['', '', '', '', '', t('total'), number_format($totalFactures, 2, ',', ''), number_format($total, 2, ',', '')], ';', '"', '');
        audita('exporta', 'subvencions', (int)$f['id'], 'relacio');
        exit;
    }

    [$signant, $carrec, $president] = signants_certificat();
    $titol = t('subv.relacio_titol');
    document_inici($titol . ' · ' . $f['nom'], $titol, t('cert.consell'));
    echo '<section class="full document">' . capcalera_societat() . '<h2 class="centrat">' . h($titol) . '</h2>';
    $dades = [
        t('camp.organisme_id') => $f['organisme'], t('camp.convocatoria') => $f['nom'], t('camp.exercici') => $f['exercici'], t('camp.expedient') => $f['expedient'],
        t('camp.objecte') => $f['objecte'], t('camp.import_concedit') => $f['import_concedit'] !== null ? euros((float)$f['import_concedit']) : null,
        t('camp.import_justificar') => $f['import_justificar'] !== null ? euros((float)$f['import_justificar']) : null,
    ];
    echo '<table class="formulari-paper">';
    foreach ($dades as $k => $v) {
        if ($v !== null && $v !== '') {
            echo '<tr><th>' . h($k) . '</th><td>' . h((string)$v) . '</td></tr>';
        }
    }
    echo '</table><table class="taula-llistat"><thead><tr>';
    foreach ($cols as $i => $c) {
        echo '<th' . ($i >= 6 ? ' class="num"' : '') . '>' . h($c) . '</th>';
    }
    echo '</tr></thead><tbody>';
    foreach ($despeses as $i => $d) {
        echo '<tr><td>' . ($i + 1) . '</td><td>' . h(data_llegible($d['data'])) . '</td><td>' . h((string)$d['proveidor']) . '</td><td>' . h((string)$d['nif']) . '</td><td>'
            . h((string)$d['justificant']) . '</td><td>' . h($d['concepte']) . '</td><td class="num">' . h(euros((float)$d['import'])) . '</td><td class="num">' . h(euros((float)$d['imputat'])) . '</td></tr>';
    }
    if (!$despeses) {
        echo '<tr><td colspan="8">' . h(t('subv.sense_despeses')) . '</td></tr>';
    }
    echo '</tbody><tfoot><tr><td colspan="6">' . h(t('total')) . '</td><td class="num">' . h(euros($totalFactures)) . '</td><td class="num">' . h(euros($total)) . '</td></tr></tfoot></table>';
    $poblacio = config('societat_poblacio');
    echo '<p class="justificat">' . h(t('subv.relacio_peu', count($despeses), euros($total))) . '</p>';
    echo '<p class="justificat">' . h(($poblacio ? $poblacio . ', ' : '') . data_llarga()) . '</p>';
    echo signatures_document($signant, $carrec, $president) . '</section>';
    audita('subvencio_relacio', 'subvencions', (int)$f['id']);
    document_fi();
}

/** Avís de la pantalla d'inici: subvencions que s'han de demanar o justificar en els pròxims 30 dies (o que ja han passat) */
function avis_inici_subvencions(): string
{
    $files = q("SELECT s.*, o.nom AS organisme FROM subvencions s LEFT JOIN organismes o ON o.id = s.organisme_id
        WHERE (s.estat = 'prevista' AND s.data_limit_sollicitud IS NOT NULL AND s.data_limit_sollicitud <= date('now','localtime','+30 days') AND s.data_limit_sollicitud >= date('now','localtime','-60 days'))
           OR (s.estat = 'concedida' AND s.data_limit_justificacio IS NOT NULL AND s.data_limit_justificacio <= date('now','localtime','+30 days'))
        ORDER BY COALESCE(CASE s.estat WHEN 'prevista' THEN s.data_limit_sollicitud ELSE s.data_limit_justificacio END, '')")->fetchAll();
    if (!$files) {
        return '';
    }
    $html = '<section class="fill"><header><h2>⚠ ' . h(t('subv.inici_titol')) . '</h2></header><ul class="llista-simple">';
    foreach ($files as $f) {
        $t = termini_subvencio($f);
        $falta = $t[0] === 'justificacio' ? xifres_subvencio($f)['falta_justificar'] : 0;
        $html .= '<li><a href="' . h(url('fitxa', ['e' => 'subvencions', 'id' => $f['id']])) . '">' . h($f['nom'] . ($f['exercici'] ? ' (' . $f['exercici'] . ')' : '')) . '</a>'
            . ($f['organisme'] ? ' — ' . h($f['organisme']) : '') . ' <span class="etiqueta' . ($t[2] < 0 ? ' perill' : '') . '">'
            . h($t[2] < 0 ? t('subv.termini_passat_' . $t[0], data_llegible($t[1])) : t('subv.queden_' . $t[0], $t[2], data_llegible($t[1])))
            . '</span>' . ($falta > 0 ? ' <span class="etiqueta">' . h(t('subv.falta_justificar', euros($falta))) . '</span>' : '') . '</li>';
    }
    return $html . '</ul></section>';
}
