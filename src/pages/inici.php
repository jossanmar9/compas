<?php
// Inici, portal personal ("La meua fitxa") i horari general
// SPDX-License-Identifier: GPL-2.0-or-later

function targeta(string $xifra, string $text, ?string $enllac = null): string
{
    $in = '<b>' . h($xifra) . '</b><span>' . h($text) . '</span>';
    return $enllac ? '<a class="targeta" href="' . h($enllac) . '">' . $in . '</a>' : '<div class="targeta">' . $in . '</div>';
}

function pagina_inici(): void
{
    $curs = curs_actual();
    $html = '<div class="targetes">';
    if (pot('persones.view')) {
        $html .= targeta((string)valor('SELECT COUNT(*) FROM persones WHERE actiu = 1'), t('ent.persones'), url('llista', ['e' => 'persones']));
    }
    if (pot('socis.view')) {
        $html .= targeta((string)valor("SELECT COUNT(*) FROM socis WHERE estat = 'actiu'"), t('inici.socis_actius'), url('llista', ['e' => 'socis']));
    }
    if (pot('escola.view') && $curs) {
        $html .= targeta((string)valor("SELECT COUNT(*) FROM matricules WHERE curs_id = ? AND estat = 'actiu'", [$curs['id']]), t('inici.matriculats', $curs['nom']),
            url('llista', ['e' => 'matricules', 'f[curs_id]' => $curs['id']]));
        $html .= targeta((string)valor("SELECT COUNT(*) FROM grups WHERE curs_id = ? AND estat = 'actiu'", [$curs['id']]), t('ent.grups'),
            url('llista', ['e' => 'grups', 'f[curs_id]' => $curs['id']]));
    }
    if (pot('agrupacions.view')) {
        $html .= targeta((string)valor("SELECT COUNT(*) FROM membres WHERE estat = 'actiu'"), t('inici.musics'), url('llista', ['e' => 'membres']));
    }
    if (pot('instruments.view')) {
        $html .= targeta((string)valor('SELECT COUNT(*) FROM cessions WHERE data_retorn IS NULL'), t('inici.cedits'), url('llista', ['e' => 'cessions', 'filtre' => 'actives']));
    }
    $html .= '</div>';

    if (pot('escola.view') && !$curs) {
        $html .= '<div class="avis info">' . h(t('inici.sense_curs')) . ' <a href="' . h(url('edita', ['e' => 'cursos'])) . '">' . h(t('inici.crea_curs')) . '</a></div>';
    }

    if (pot('instruments.view')) {
        $vencudes = q("SELECT c.id, c.data_prevista, i.inventari, i.tipus, " . NOM_PERSONA_SQL . " AS persona FROM cessions c JOIN instruments i ON i.id = c.instrument_id
            JOIN persones p ON p.id = c.persona_id WHERE c.data_retorn IS NULL AND c.data_prevista < date('now','localtime') ORDER BY c.data_prevista")->fetchAll();
        if ($vencudes) {
            $html .= '<section class="fill"><header><h2>⚠ ' . h(t('inici.vencudes')) . '</h2></header><ul class="llista-simple">';
            foreach ($vencudes as $v) {
                $html .= '<li><a href="' . h(url('fitxa', ['e' => 'cessions', 'id' => $v['id']])) . '">' . h($v['inventari'] . ' · ' . $v['tipus']) . '</a> — '
                    . h($v['persona']) . ' <span class="etiqueta">' . h(t('inici.prevista', data_llegible($v['data_prevista']))) . '</span></li>';
            }
            $html .= '</ul></section>';
        }
    }

    // Classes de hui
    if ($curs && (pot('escola.view') || el_meu_professor())) {
        $params = [$curs['id'], (int)date('N')];
        $filtre = '';
        if (!pot('escola.view')) {
            $filtre = ' AND g.professor_id = ?';
            $params[] = el_meu_professor()['id'];
        }
        $classes = q("SELECT g.id, a.nom AS assignatura, g.nom, h.hora_inici, h.hora_fi, au.nom AS aula FROM horaris h JOIN grups g ON g.id = h.grup_id
            JOIN assignatures a ON a.id = g.assignatura_id LEFT JOIN aules au ON au.id = g.aula_id
            WHERE g.curs_id = ? AND h.dia = ? AND g.estat = 'actiu' $filtre ORDER BY h.hora_inici", $params)->fetchAll();
        $html .= '<section class="fill"><header><h2>' . h(t('inici.classes_hui', t('dia.' . date('N')))) . '</h2></header>';
        if (!$classes) {
            $html .= '<p class="buit petit">' . h(t('inici.sense_classes')) . '</p>';
        } else {
            $html .= '<ul class="llista-simple">';
            foreach ($classes as $c) {
                $html .= '<li><span class="hora">' . h($c['hora_inici'] . '–' . $c['hora_fi']) . '</span> ' . h($c['assignatura'] . ' · ' . $c['nom'])
                    . ($c['aula'] ? ' <span class="etiqueta">' . h($c['aula']) . '</span>' : '');
                if (pot('escola.assistencia')) {
                    $html .= ' <a class="boto petit secundari" href="' . h(url('assistencia', ['grup' => $c['id'], 'data' => date('Y-m-d')])) . '">' . h(t('menu.assistencia')) . '</a>';
                }
                $html .= '</li>';
            }
            $html .= '</ul>';
        }
        $html .= '</section>';
    }

    if (usuari()['persona_id']) {
        $html .= '<p><a class="boto secundari" href="' . h(url('portal')) . '">' . h(t('menu.portal')) . ' →</a></p>';
    }
    pagina(t('menu.inici'), $html, config('societat_nom'));
}

/** Resum acadèmic d'un alumne per a un curs: grups, horari, assistència i notes */
function resum_alumne(int $alumneId, array $curs): string
{
    $m = fila('SELECT * FROM matricules WHERE alumne_id = ? AND curs_id = ?', [$alumneId, $curs['id']]);
    if (!$m) {
        return '<p class="buit petit">' . h(t('portal.sense_matricula', $curs['nom'])) . '</p>';
    }
    $grups = q("SELECT g.id, a.nom AS assignatura, g.nom, " . NOM_PERSONA_SQL . " AS professor,
        (SELECT group_concat(h.dia || '|' || h.hora_inici || '|' || h.hora_fi, ';') FROM horaris h WHERE h.grup_id = g.id) AS horari
        FROM matricula_grups mg JOIN grups g ON g.id = mg.grup_id JOIN assignatures a ON a.id = g.assignatura_id
        LEFT JOIN professors pr ON pr.id = g.professor_id LEFT JOIN persones p ON p.id = pr.persona_id
        WHERE mg.matricula_id = ? ORDER BY a.nom", [$m['id']])->fetchAll();
    $periodes = q('SELECT * FROM periodes WHERE curs_id = ? ORDER BY ordre', [$curs['id']])->fetchAll();

    $html = '<div class="taula-wrap"><table class="taula"><thead><tr><th>' . h(t('camp.assignatura_id')) . '</th><th>' . h(t('camp.professor_id')) . '</th><th>'
        . h(t('menu.horari')) . '</th><th>' . h(t('portal.faltes')) . '</th>';
    foreach ($periodes as $pe) {
        $html .= '<th>' . h($pe['nom']) . '</th>';
    }
    $html .= '</tr></thead><tbody>';
    foreach ($grups as $g) {
        $hores = [];
        foreach (array_filter(explode(';', (string)$g['horari'])) as $hh) {
            [$d, $hi, $hf] = explode('|', $hh);
            $hores[] = t('dia_curt.' . $d) . ' ' . $hi . '–' . $hf;
        }
        $as = q('SELECT estat, COUNT(*) AS n FROM assistencia WHERE grup_id = ? AND alumne_id = ? GROUP BY estat', [$g['id'], $alumneId])->fetchAll();
        $as = array_column($as, 'n', 'estat');
        $faltes = ($as['F'] ?? 0) . ' / ' . ($as['FJ'] ?? 0) . ' / ' . ($as['R'] ?? 0);
        $html .= '<tr><td>' . h($g['assignatura'] . ' · ' . $g['nom']) . '</td><td>' . h($g['professor']) . '</td><td>' . h(implode(', ', $hores)) . '</td><td>' . h($faltes) . '</td>';
        foreach ($periodes as $pe) {
            $n = fila('SELECT nota, observacions FROM qualificacions WHERE grup_id = ? AND alumne_id = ? AND periode_id = ?', [$g['id'], $alumneId, $pe['id']]);
            $html .= '<td' . ($n && $n['observacions'] ? ' title="' . h($n['observacions']) . '"' : '') . '>' . h($n['nota'] ?? '') . ($n && $n['observacions'] ? ' 💬' : '') . '</td>';
        }
        $html .= '</tr>';
    }
    return $html . '</tbody></table></div><p class="peu-taula">' . h(t('portal.llegenda_faltes')) . '</p>';
}

function pagina_portal(): void
{
    $u = usuari();
    if (!$u['persona_id']) {
        redirigeix('inici');
    }
    $p = fila('SELECT * FROM persones WHERE id = ?', [$u['persona_id']]);
    $curs = curs_actual();
    $html = '<dl class="fitxa">';
    foreach (['telefon', 'email', 'adreca', 'poblacio'] as $c) {
        if ($p[$c]) {
            $html .= '<dt>' . h(t('camp.' . $c)) . '</dt><dd>' . h($p[$c]) . '</dd>';
        }
    }
    $soci = fila('SELECT * FROM socis WHERE persona_id = ?', [$p['id']]);
    if ($soci) {
        $html .= '<dt>' . h(t('ent1.socis')) . '</dt><dd>' . h(t('portal.soci', $soci['numero'] ?? '—', t('opt.' . $soci['estat']))) . '</dd>';
    }
    $html .= '</dl><p class="nota">' . h(t('portal.dades_errors')) . '</p>';

    if ($curs) {
        $alumne = fila('SELECT * FROM alumnes WHERE persona_id = ?', [$p['id']]);
        if ($alumne) {
            $html .= '<section class="fill"><header><h2>' . h(t('portal.escola', $curs['nom'])) . '</h2></header>' . resum_alumne((int)$alumne['id'], $curs) . '</section>';
        }
        $tutelats = q("SELECT al.id, " . NOM_PERSONA_SQL . " AS nom FROM tutories tu JOIN alumnes al ON al.id = tu.alumne_id JOIN persones p ON p.id = al.persona_id
            WHERE tu.tutor_id = ? AND tu.consulta_academica = 1 ORDER BY nom", [$p['id']])->fetchAll();
        foreach ($tutelats as $tt) {
            $html .= '<section class="fill"><header><h2>' . h($tt['nom']) . ' <small>(' . h($curs['nom']) . ')</small></h2></header>' . resum_alumne((int)$tt['id'], $curs) . '</section>';
        }
        $prof = el_meu_professor();
        if ($prof) {
            $grups = q("SELECT g.id, a.nom || ' · ' || g.nom AS nom, (SELECT COUNT(*) FROM matricula_grups mg WHERE mg.grup_id = g.id) AS n FROM grups g
                JOIN assignatures a ON a.id = g.assignatura_id WHERE g.professor_id = ? AND g.curs_id = ? ORDER BY nom", [$prof['id'], $curs['id']])->fetchAll();
            $html .= '<section class="fill"><header><h2>' . h(t('portal.els_meus_grups')) . '</h2></header><ul class="llista-simple">';
            foreach ($grups as $g) {
                $html .= '<li>' . h($g['nom']) . ' <span class="etiqueta">' . h(t('portal.n_alumnes', $g['n'])) . '</span>';
                if (pot('escola.assistencia')) {
                    $html .= ' <a class="boto petit secundari" href="' . h(url('assistencia', ['grup' => $g['id']])) . '">' . h(t('menu.assistencia')) . '</a>';
                }
                if (pot('escola.notes')) {
                    $html .= ' <a class="boto petit secundari" href="' . h(url('notes', ['grup' => $g['id']])) . '">' . h(t('menu.notes')) . '</a>';
                }
                $html .= '</li>';
            }
            $html .= ($grups ? '' : '<li class="buit petit">' . h(t('cap_element')) . '</li>') . '</ul></section>';
        }
    }

    $membres = q("SELECT a.nom, m.instrument_veu, m.funcio FROM membres m JOIN agrupacions a ON a.id = m.agrupacio_id WHERE m.persona_id = ? AND m.estat = 'actiu' ORDER BY a.nom", [$p['id']])->fetchAll();
    if ($membres) {
        $html .= '<section class="fill"><header><h2>' . h(t('ent.agrupacions')) . '</h2></header><ul class="llista-simple">';
        foreach ($membres as $m) {
            $html .= '<li>' . h($m['nom']) . ($m['instrument_veu'] ? ' — ' . h($m['instrument_veu']) : '') . ($m['funcio'] !== 'membre' ? ' <span class="etiqueta">' . h(t('opt.' . $m['funcio'])) . '</span>' : '') . '</li>';
        }
        $html .= '</ul></section>';
    }
    $cessions = q('SELECT c.*, i.inventari, i.tipus FROM cessions c JOIN instruments i ON i.id = c.instrument_id WHERE c.persona_id = ? AND c.data_retorn IS NULL', [$p['id']])->fetchAll();
    if ($cessions) {
        $html .= '<section class="fill"><header><h2>' . h(t('portal.instruments_cedits')) . '</h2></header><ul class="llista-simple">';
        foreach ($cessions as $c) {
            $html .= '<li>' . h($c['inventari'] . ' · ' . $c['tipus']) . ($c['data_prevista'] ? ' <span class="etiqueta">' . h(t('inici.prevista', data_llegible($c['data_prevista']))) . '</span>' : '') . '</li>';
        }
        $html .= '</ul></section>';
    }
    pagina(nom_persona($p), $html, t('menu.portal'));
}

function pagina_horari(): void
{
    requereix('escola.view');
    $cursos = opcions_ref('cursos');
    $cursId = (int)get('curs', (string)(curs_actual()['id'] ?? 0));
    $prof = (int)get('professor', '0');
    $aula = (int)get('aula', '0');

    $html = '<form class="barra" method="get"><input type="hidden" name="r" value="horari">' . select_simple('curs', $cursos, $cursId, false)
        . select_simple('professor', opcions_ref('professors'), $prof, true, t('filtre.tot_professorat'))
        . select_simple('aula', opcions_ref('aules'), $aula, true, t('filtre.totes_aules')) . '<button>' . h(t('filtrar')) . '</button></form>';

    $params = [$cursId];
    $where = "g.curs_id = ? AND g.estat = 'actiu'";
    if ($prof) {
        $where .= ' AND g.professor_id = ?';
        $params[] = $prof;
    }
    if ($aula) {
        $where .= ' AND g.aula_id = ?';
        $params[] = $aula;
    }
    $files = q("SELECT h.*, g.id AS grup_id, a.nom AS assignatura, g.nom AS grup, au.nom AS aula, " . NOM_PERSONA_SQL . " AS professor
        FROM horaris h JOIN grups g ON g.id = h.grup_id JOIN assignatures a ON a.id = g.assignatura_id
        LEFT JOIN aules au ON au.id = g.aula_id LEFT JOIN professors pr ON pr.id = g.professor_id LEFT JOIN persones p ON p.id = pr.persona_id
        WHERE $where ORDER BY h.dia, h.hora_inici", $params)->fetchAll();
    $dies = [];
    foreach ($files as $f) {
        $dies[$f['dia']][] = $f;
    }
    $ultim = max(5, $dies ? max(array_keys($dies)) : 5);
    $html .= '<div class="horari">';
    for ($d = 1; $d <= $ultim; $d++) {
        $html .= '<div class="dia"><h3>' . h(t('dia.' . $d)) . '</h3>';
        foreach ($dies[$d] ?? [] as $f) {
            $html .= '<a class="sessio" href="' . h(url('fitxa', ['e' => 'grups', 'id' => $f['grup_id']])) . '"><b>' . h($f['hora_inici'] . '–' . $f['hora_fi']) . '</b>'
                . h($f['assignatura'] . ' · ' . $f['grup']) . '<small>' . h(trim(($f['professor'] ?? '') . ($f['aula'] ? ' · ' . $f['aula'] : ''), ' ·')) . '</small></a>';
        }
        $html .= '</div>';
    }
    $html .= '</div>';
    if (!$files) {
        $html .= '<p class="buit petit">' . h(t('horari.buit')) . '</p>';
    }
    pagina(t('menu.horari'), $html);
}

function select_simple(string $nom, array $ops, $actual, bool $buit = true, string $textBuit = '—', bool $auto = true): string
{
    $s = '<select name="' . h($nom) . '"' . ($auto ? ' onchange="this.form.submit()"' : '') . '>';
    if ($buit) {
        $s .= '<option value="">' . h($textBuit) . '</option>';
    }
    foreach ($ops as $k => $v) {
        $s .= '<option value="' . h($k) . '"' . ((string)$k === (string)$actual ? ' selected' : '') . '>' . h($v) . '</option>';
    }
    return $s . '</select>';
}
