<?php
// Escola: registre d'assistència i qualificacions
// SPDX-License-Identifier: GPL-2.0-or-later

const ESTATS_ASSISTENCIA = ['P', 'F', 'FJ', 'R'];

/** Grups on l'usuari pot passar llista o posar notes */
function grups_permesos(): array
{
    $curs = curs_actual();
    if (!$curs) {
        return [];
    }
    $sql = "SELECT g.id, a.nom || ' · ' || g.nom AS e FROM grups g JOIN assignatures a ON a.id = g.assignatura_id WHERE g.curs_id = ? AND g.estat = 'actiu'";
    $params = [$curs['id']];
    if (!pot('escola.totsgrups')) {
        $prof = el_meu_professor();
        if (!$prof) {
            return [];
        }
        $sql .= ' AND g.professor_id = ?';
        $params[] = $prof['id'];
    }
    return array_column(q($sql . ' ORDER BY e', $params)->fetchAll(), 'e', 'id');
}

function pot_grup(int $grupId): bool
{
    if (pot('escola.totsgrups')) {
        return (bool)valor('SELECT 1 FROM grups WHERE id = ?', [$grupId]);
    }
    $prof = el_meu_professor();
    return $prof && (bool)valor('SELECT 1 FROM grups WHERE id = ? AND professor_id = ?', [$grupId, $prof['id']]);
}

function alumnes_grup(int $grupId): array
{
    return q("SELECT al.id, " . NOM_PERSONA_SQL . " AS nom FROM matricula_grups mg JOIN matricules m ON m.id = mg.matricula_id
        JOIN alumnes al ON al.id = m.alumne_id JOIN persones p ON p.id = al.persona_id
        WHERE mg.grup_id = ? AND m.estat = 'actiu' ORDER BY p.cognoms, p.nom", [$grupId])->fetchAll();
}

function selector_grup(string $ruta, array $grups, int $actual, array $extra = []): string
{
    $html = '<form class="barra" method="get"><input type="hidden" name="r" value="' . h($ruta) . '">'
        . select_simple('grup', $grups, $actual, true, t('tria_grup'));
    foreach ($extra as $k => $v) {
        $html .= $v;
    }
    return $html . '<button>' . h(t('filtrar')) . '</button></form>';
}

function sense_grups(): string
{
    if (!curs_actual()) {
        return '<div class="buit">' . h(t('inici.sense_curs')) . '</div>';
    }
    return '<div class="buit">' . h(pot('escola.totsgrups') ? t('escola.sense_grups') : t('escola.sense_grups_prof')) . '</div>';
}

function pagina_assistencia(): void
{
    requereix('escola.assistencia');
    $grups = grups_permesos();
    $grup = (int)get('grup', '0');
    $data = (string)get('data', date('Y-m-d'));
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) {
        $data = date('Y-m-d');
    }
    if ($grup && !pot_grup($grup)) {
        requereix('__cap__');
    }
    if (!$grups && !$grup) {
        pagina(t('menu.assistencia'), sense_grups());
        return;
    }
    if (!isset($grups[$grup]) && $grup) {
        $grups[$grup] = opcions_ref('grups')[$grup] ?? '#' . $grup;
    }

    if ($grup && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $estats = (array)($_POST['estat'] ?? []);
        $obs = (array)($_POST['obs'] ?? []);
        db()->beginTransaction();
        foreach (alumnes_grup($grup) as $al) {
            $e = $estats[$al['id']] ?? 'P';
            if (!in_array($e, ESTATS_ASSISTENCIA, true)) {
                $e = 'P';
            }
            $o = trim((string)($obs[$al['id']] ?? '')) ?: null;
            q('INSERT INTO assistencia(grup_id, alumne_id, data, estat, observacions) VALUES(?,?,?,?,?)
               ON CONFLICT(grup_id, alumne_id, data) DO UPDATE SET estat = excluded.estat, observacions = excluded.observacions', [$grup, $al['id'], $data, $e, $o]);
        }
        db()->commit();
        audita('assistencia', 'grups', $grup, $data);
        avis(t('desat'));
        redirigeix('assistencia', ['grup' => $grup, 'data' => $data]);
    }

    $html = selector_grup('assistencia', $grups, $grup, ['<input type="date" name="data" value="' . h($data) . '" onchange="this.form.submit()">']);
    if (!$grup) {
        pagina(t('menu.assistencia'), $html . '<p class="buit petit">' . h(t('tria_grup')) . '</p>');
        return;
    }
    $alumnes = alumnes_grup($grup);
    $fet = q('SELECT alumne_id, estat, observacions FROM assistencia WHERE grup_id = ? AND data = ?', [$grup, $data])->fetchAll();
    $fet = array_column($fet, null, 'alumne_id');
    $dia = (int)date('N', strtotime($data));
    if (!valor('SELECT 1 FROM horaris WHERE grup_id = ? AND dia = ?', [$grup, $dia])) {
        $html .= '<div class="avis info">' . h(t('assist.no_toca', t('dia.' . $dia))) . '</div>';
    }
    if (!$alumnes) {
        $html .= '<div class="buit">' . h(t('escola.grup_buit')) . '</div>';
    } else {
        $html .= '<form method="post" class="assistencia">' . csrf_camp();
        $html .= '<p class="accions"><button type="button" class="secundari" data-tots="P">' . h(t('assist.tots_presents')) . '</button>'
            . ($fet ? ' <span class="etiqueta ok">' . h(t('assist.ja_registrada')) . '</span>' : ' <span class="etiqueta">' . h(t('assist.pendent')) . '</span>') . '</p>';
        $html .= '<div class="taula-wrap"><table class="taula"><thead><tr><th>' . h(t('ent1.alumnes')) . '</th>';
        foreach (ESTATS_ASSISTENCIA as $e) {
            $html .= '<th class="centre" title="' . h(t('assist.' . $e)) . '">' . h($e) . '</th>';
        }
        $html .= '<th>' . h(t('camp.observacions')) . '</th></tr></thead><tbody>';
        foreach ($alumnes as $al) {
            $actual = $fet[$al['id']]['estat'] ?? 'P';
            $html .= '<tr><td>' . h($al['nom']) . '</td>';
            foreach (ESTATS_ASSISTENCIA as $e) {
                $html .= '<td class="centre"><input type="radio" name="estat[' . (int)$al['id'] . ']" value="' . $e . '" aria-label="' . h(t('assist.' . $e)) . '"' . ($actual === $e ? ' checked' : '') . '></td>';
            }
            $html .= '<td><input name="obs[' . (int)$al['id'] . ']" value="' . h($fet[$al['id']]['observacions'] ?? '') . '"></td></tr>';
        }
        $html .= '</tbody></table></div><p class="peu-taula">' . h(t('assist.llegenda')) . '</p><div class="accions"><button>' . h(t('desa')) . '</button></div></form>';
    }

    // Resum del grup
    $dates = q('SELECT data, SUM(estat = \'F\') AS f, SUM(estat = \'FJ\') AS fj, SUM(estat = \'R\') AS r FROM assistencia WHERE grup_id = ? GROUP BY data ORDER BY data DESC LIMIT 30', [$grup])->fetchAll();
    if ($dates) {
        $html .= '<section class="fill"><header><h2>' . h(t('assist.historial')) . '</h2></header><ul class="llista-simple columnes">';
        foreach ($dates as $d) {
            $html .= '<li><a href="' . h(url('assistencia', ['grup' => $grup, 'data' => $d['data']])) . '">' . h(data_llegible($d['data'])) . '</a> <span class="etiqueta">F '
                . (int)$d['f'] . ' · FJ ' . (int)$d['fj'] . ' · R ' . (int)$d['r'] . '</span></li>';
        }
        $html .= '</ul></section>';
    }
    pagina(t('menu.assistencia'), $html, $grups[$grup] ?? '');
}

function pagina_notes(): void
{
    requereix('escola.notes');
    $grups = grups_permesos();
    $grup = (int)get('grup', '0');
    if ($grup && !pot_grup($grup)) {
        requereix('__cap__');
    }
    if (!$grups && !$grup) {
        pagina(t('menu.notes'), sense_grups());
        return;
    }
    if (!isset($grups[$grup]) && $grup) {
        $grups[$grup] = opcions_ref('grups')[$grup] ?? '#' . $grup;
    }
    $periodes = [];
    $periode = (int)get('periode', '0');
    if ($grup) {
        $cursId = valor('SELECT curs_id FROM grups WHERE id = ?', [$grup]);
        $periodes = array_column(q('SELECT id, nom FROM periodes WHERE curs_id = ? ORDER BY ordre', [$cursId])->fetchAll(), 'nom', 'id');
        if (!isset($periodes[$periode])) {
            $periode = (int)array_key_first($periodes);
        }
    }

    if ($grup && $periode && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $notes = (array)($_POST['nota'] ?? []);
        $obs = (array)($_POST['obs'] ?? []);
        $prof = el_meu_professor()['id'] ?? null;
        db()->beginTransaction();
        foreach (alumnes_grup($grup) as $al) {
            $n = mb_substr(trim((string)($notes[$al['id']] ?? '')), 0, 20);
            $o = trim((string)($obs[$al['id']] ?? ''));
            if ($n === '' && $o === '') {
                q('DELETE FROM qualificacions WHERE grup_id = ? AND alumne_id = ? AND periode_id = ?', [$grup, $al['id'], $periode]);
                continue;
            }
            q("INSERT INTO qualificacions(grup_id, alumne_id, periode_id, professor_id, nota, observacions, modificat) VALUES(?,?,?,?,?,?, datetime('now','localtime'))
               ON CONFLICT(grup_id, alumne_id, periode_id) DO UPDATE SET nota = excluded.nota, observacions = excluded.observacions,
               professor_id = COALESCE(excluded.professor_id, professor_id), modificat = excluded.modificat",
                [$grup, $al['id'], $periode, $prof, $n ?: null, $o ?: null]);
        }
        db()->commit();
        audita('qualificacions', 'grups', $grup, $periodes[$periode] ?? '');
        avis(t('desat'));
        redirigeix('notes', ['grup' => $grup, 'periode' => $periode]);
    }

    $html = selector_grup('notes', $grups, $grup, $periodes ? [select_simple('periode', $periodes, $periode, false)] : []);
    if (!$grup) {
        pagina(t('menu.notes'), $html . '<p class="buit petit">' . h(t('tria_grup')) . '</p>');
        return;
    }
    if (!$periodes) {
        $html .= '<div class="avis info">' . h(t('notes.sense_periodes')) . (pot('escola.edit') ? ' <a href="' . h(url('edita', ['e' => 'periodes', 'f[curs_id]' => $cursId])) . '">' . h(t('notes.crea_periode')) . '</a>' : '') . '</div>';
        pagina(t('menu.notes'), $html, $grups[$grup]);
        return;
    }
    $alumnes = alumnes_grup($grup);
    $fet = array_column(q('SELECT alumne_id, nota, observacions FROM qualificacions WHERE grup_id = ? AND periode_id = ?', [$grup, $periode])->fetchAll(), null, 'alumne_id');
    if (!$alumnes) {
        $html .= '<div class="buit">' . h(t('escola.grup_buit')) . '</div>';
    } else {
        $html .= '<form method="post">' . csrf_camp() . '<div class="taula-wrap"><table class="taula"><thead><tr><th>' . h(t('ent1.alumnes')) . '</th><th>' . h(t('camp.nota'))
            . '</th><th>' . h(t('notes.observacions')) . '</th></tr></thead><tbody>';
        foreach ($alumnes as $al) {
            $html .= '<tr><td>' . h($al['nom']) . '</td><td><input class="nota" name="nota[' . (int)$al['id'] . ']" maxlength="20" value="' . h($fet[$al['id']]['nota'] ?? '') . '"></td>'
                . '<td><textarea rows="1" name="obs[' . (int)$al['id'] . ']">' . h($fet[$al['id']]['observacions'] ?? '') . '</textarea></td></tr>';
        }
        $html .= '</tbody></table></div><p class="peu-taula">' . h(t('notes.ajuda')) . '</p><div class="accions"><button>' . h(t('desa')) . '</button></div></form>';
    }
    pagina(t('menu.notes'), $html, $grups[$grup] . ' · ' . $periodes[$periode]);
}
