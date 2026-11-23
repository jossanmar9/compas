<?php
// Alta d'un alumne en una sola pantalla: les seues dades, la mare, el pare o el tutor/a legal (si és menor)
// i els grups del curs. Fa el mateix que els quatre passos de sempre (persona, alumne, tutor i matrícula) d'una vegada.
// Copyright (C) 2026 José Vicente Sanjuan
// SPDX-License-Identifier: AGPL-3.0-or-later

/** Els camps de persona del formulari, amb un prefix («a_» per a l'alumne, «t_» per al tutor) */
function camps_persona_alta(string $prefix, bool $complet): array
{
    $c = [
        'nom' => ['tipus' => 'text', 'req' => 1],
        'cognoms' => ['tipus' => 'text', 'req' => 1],
        'data_naixement' => ['tipus' => 'date', 'req' => 1],
        'document' => ['tipus' => 'text', 'major' => 1],
        'telefon' => ['tipus' => 'tel', 'major' => 1],
        'email' => ['tipus' => 'email'],
    ];
    if ($complet) {
        $c += ['adreca' => ['tipus' => 'text'], 'codi_postal' => ['tipus' => 'text'], 'poblacio' => ['tipus' => 'text'],
            'consentiment_imatge' => ['tipus' => 'select', 'opcions' => ['pendent', 'autoritza', 'no_autoritza'], 'ajuda' => t('ajuda.consentiment')]];
    }
    $res = [];
    foreach ($c as $k => $d) {
        $res[$prefix . $k] = $d + ['etiqueta' => 'camp.' . $k, 'columna' => $k];
    }
    return $res;
}

/** Les dades d'una persona del formulari, ja normalitzades, i els errors */
function llig_persona_alta(array $camps, array &$errors, string $qui): array
{
    $p = [];
    foreach ($camps as $nom => $d) {
        $p[$d['columna']] = normalitza($d, $_POST[$nom] ?? '');
        if (!empty($d['req']) && ($p[$d['columna']] === null || $p[$d['columna']] === '')) {
            $errors[] = t('err.obligatori', $qui . ': ' . t($d['etiqueta']));
        }
    }
    if ($p['data_naixement'] && es_major_edat($p['data_naixement'])) {
        foreach (['document', 'telefon'] as $c) {
            if (!$p[$c]) {
                $errors[] = t('err.obligatori', $qui . ': ' . t('camp.' . $c));
            }
        }
    }
    return $p;
}

function insereix_persona_alta(array $p): int
{
    $p = array_filter($p, fn($v) => $v !== null && $v !== '');
    q('INSERT INTO persones(' . implode(', ', array_keys($p)) . ') VALUES(' . implode(', ', array_fill(0, count($p), '?')) . ')', array_values($p));
    $id = (int)db()->lastInsertId();
    audita('crea', 'persones', $id);
    return $id;
}

/** «de soci», «d'alumne», «d'esportista» (en castellà, sempre «de») */
function de_nom(string $nom): string
{
    return idioma() !== 'es' && preg_match('/^h?[aeiouàèéíïòóúü]/iu', $nom) ? 'd\'' . $nom : 'de ' . $nom;
}

/** A la fitxa d'una persona: el que se'n pot fer d'un clic (fer-la sòcia, matricular-la, apuntar-la a un grup o a una activitat) */
function accions_persona(array $p): string
{
    $id = (int)$p['id'];
    $tornar = url('fitxa', ['e' => 'persones', 'id' => $id]);
    $b = [];
    $boto = fn(string $icona, string $text, string $enllac) => '<a class="boto secundari" href="' . h($enllac) . '"><span aria-hidden="true">' . $icona . '</span> ' . h($text) . '</a>';
    if (pot('socis.edit') && !valor("SELECT id FROM socis WHERE persona_id = ?", [$id])) {
        $b[] = $boto('★', t('persona.fes_soci', de_nom(mb_strtolower(t('ent1.socis')))), url('edita', ['e' => 'socis', 'f[persona_id]' => $id, 'tornar' => $tornar]));
    }
    if (pot('escola.edit') && disponible('alumnes')) {
        $al = valor('SELECT id FROM alumnes WHERE persona_id = ?', [$id]);
        $b[] = $al ? $boto('🎓', t('persona.matricula', mb_strtolower(t('ent1.matricules'))), url('edita', ['e' => 'matricules', 'f[alumne_id]' => $al, 'tornar' => $tornar]))
            : $boto('🎓', t('persona.alta_alumne', de_nom(mb_strtolower(t('ent1.alumnes')))), url('alta_alumne', ['persona' => $id]));
    }
    if (pot('agrupacions.edit') && disponible('membres')) {
        $b[] = $boto('👥', t('persona.apunta_grup', mb_strtolower(t('ent1.membres'))), url('edita', ['e' => 'membres', 'f[persona_id]' => $id, 'tornar' => $tornar]));
    }
    if (pot('societat.edit') && disponible('activitat_inscripcions')) {
        $b[] = $boto('🗓', t('persona.inscriu'), url('edita', ['e' => 'activitat_inscripcions', 'f[persona_id]' => $id, 'tornar' => $tornar]));
    }
    return $b ? '<div class="accions accions-persona"><span class="etiq">' . h(t('persona.que_fer')) . '</span>' . implode('', $b) . '</div>' : '';
}

function pagina_alta_alumne(): void
{
    requereix('escola.edit');
    if (!disponible('alumnes')) {
        redirigeix('inici');
    }
    $curs = curs_actual();
    $existent = (int)get('persona') ?: (int)post('a_persona');
    $persona = $existent ? fila('SELECT * FROM persones WHERE id = ?', [$existent]) : null;
    if ($persona && ($al = valor('SELECT id FROM alumnes WHERE persona_id = ?', [$persona['id']]))) {
        // Ja és alumne: el que falta és la matrícula
        avis(t('altaal.ja_alumne', nom_persona($persona)), 'info');
        redirigeix('fitxa', ['e' => 'alumnes', 'id' => $al]);
    }
    $campsA = camps_persona_alta('a_', true);
    $campsT = camps_persona_alta('t_', false);
    $errors = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $a = $persona ?: llig_persona_alta($campsA, $errors, t('altaal.alumne'));
        $menor = $a['data_naixement'] && !es_major_edat($a['data_naixement']);
        $modeT = post('t_mode');
        $t = null;
        $tutorId = null;
        if ($modeT === 'existent') {
            $tutorId = (int)post('t_persona') ?: null;
            if (!$tutorId || !valor('SELECT id FROM persones WHERE id = ?', [$tutorId])) {
                $errors[] = t('altaal.tria_tutor');
            }
        } elseif ($modeT === 'nova') {
            $t = llig_persona_alta($campsT, $errors, t('altaal.tutor'));
            if ($t['data_naixement'] && !es_major_edat($t['data_naixement'])) {
                $errors[] = t('altaal.tutor_major');
            }
        } elseif ($menor) {
            $errors[] = t('altaal.cal_tutor');
        }
        // La mateixa persona no s'ha de crear dues vegades
        if (!$persona && !$errors) {
            $dup = valor('SELECT id FROM persones WHERE lower(nom) = lower(?) AND lower(cognoms) = lower(?) AND data_naixement = ?', [$a['nom'], $a['cognoms'], $a['data_naixement']]);
            if ($dup) {
                $errors[] = t('altaal.duplicada', nom_persona($a));
                $dupId = (int)$dup;
            }
        }
        $grups = array_values(array_unique(array_filter(array_map('intval', (array)($_POST['grups'] ?? [])))));
        if (!$errors) {
            db()->beginTransaction();
            try {
                $pid = $persona ? (int)$persona['id'] : insereix_persona_alta($a);
                if ($t) {
                    // El tutor nou viu a la mateixa casa, si no es diu una altra cosa
                    if (post('t_mateixa_casa') === '1') {
                        $casa = $persona ?: $a;
                        $t += ['adreca' => $casa['adreca'] ?? null, 'codi_postal' => $casa['codi_postal'] ?? null, 'poblacio' => $casa['poblacio'] ?? null];
                    }
                    $tutorId = insereix_persona_alta($t);
                }
                q('INSERT INTO alumnes(persona_id, data_alta) VALUES(?, ?)', [$pid, post('data_alta') ?: date('Y-m-d')]);
                $alumne = (int)db()->lastInsertId();
                audita('crea', 'alumnes', $alumne);
                if ($tutorId) {
                    q('INSERT INTO tutories(alumne_id, tutor_id, tipus_relacio, principal) VALUES(?, ?, ?, 1)', [$alumne, $tutorId, post('t_relacio') ?: 'mare']);
                    audita('crea', 'tutories', (int)db()->lastInsertId());
                }
                if ($curs && $grups) {
                    q('INSERT INTO matricules(alumne_id, curs_id, data) VALUES(?, ?, ?)', [$alumne, $curs['id'], date('Y-m-d')]);
                    $m = (int)db()->lastInsertId();
                    audita('crea', 'matricules', $m);
                    foreach ($grups as $g) {
                        if (valor('SELECT id FROM grups WHERE id = ? AND curs_id = ?', [$g, $curs['id']])) {
                            q('INSERT INTO matricula_grups(matricula_id, grup_id) VALUES(?, ?)', [$m, $g]);
                        }
                    }
                }
                db()->commit();
            } catch (PDOException $ex) {
                db()->rollBack();
                throw $ex;
            }
            avis(t('altaal.fet', $persona ? nom_persona($persona) : nom_persona($a)));
            redirigeix('fitxa', ['e' => 'alumnes', 'id' => $alumne]);
        }
    }

    // Formulari
    $v = fn(string $c, $def = null) => $_POST[$c] ?? $def;
    $html = '';
    foreach ($errors as $e) {
        $html .= '<div class="avis error">' . h($e) . '</div>';
    }
    if (isset($dupId)) {
        $html .= '<p class="accions"><a class="boto secundari" href="' . h(url('alta_alumne', ['persona' => $dupId])) . '">' . h(t('altaal.usa_existent')) . '</a></p>';
    }
    $html .= '<p class="intro">' . h(t('altaal.intro')) . '</p><form method="post" class="formulari alta" action="' . h(url('alta_alumne')) . '">' . csrf_camp();
    // 1. L'alumne
    $html .= '<fieldset class="bloc-alta" data-persona="a"><legend><span class="pas">1</span> ' . h(t('altaal.alumne')) . '</legend>';
    if ($persona) {
        $html .= '<input type="hidden" name="a_persona" value="' . (int)$persona['id'] . '"><input type="hidden" data-naixement value="' . h($persona['data_naixement']) . '">'
            . '<p class="persona-triada"><b>' . h(nom_persona($persona)) . '</b> ' . h($persona['data_naixement'] ? data_llegible($persona['data_naixement']) : '') . '</p>';
    } else {
        $html .= '<div class="camps">';
        foreach ($campsA as $nom => $d) {
            $html .= camp_formulari($nom, $d, $v($nom, $d['columna'] === 'consentiment_imatge' ? 'pendent' : null));
        }
        $html .= '</div>';
    }
    $html .= '</fieldset>';
    // 2. La família
    $modeT = $v('t_mode', 'nova');
    $html .= '<fieldset class="bloc-alta" data-tutor><legend><span class="pas">2</span> ' . h(t('altaal.tutor')) . '</legend><small class="si-major" hidden>' . h(t('altaal.major_opcional')) . '</small>'
        . '<div class="opcions-mode" role="radiogroup">';
    foreach (['nova' => t('altaal.tutor_nou'), 'existent' => t('altaal.tutor_existent'), 'cap' => t('altaal.tutor_cap')] as $k => $text) {
        $html .= '<label class="' . ($k === 'cap' ? 'si-major' : '') . '"' . ($k === 'cap' ? ' hidden' : '') . '><input type="radio" name="t_mode" value="' . $k . '"' . ($modeT === $k ? ' checked' : '') . '> ' . h($text) . '</label>';
    }
    $html .= '</div><div class="camps"><div class="camp"><label for="c_t_relacio">' . h(t('camp.tipus_relacio')) . '</label>' . str_replace('<select ', '<select id="c_t_relacio" ', select_simple('t_relacio',
        ['mare' => t('opt.mare'), 'pare' => t('opt.pare'), 'tutor_legal' => t('opt.tutor_legal'), 'altre' => t('opt.altre')], $v('t_relacio', 'mare'), false, '', false)) . '</div></div>';
    $html .= '<div class="mode-tutor" data-mode="existent"><div class="camps">'
        . camp_formulari('t_persona', ['tipus' => 'ref', 'ref' => 'persones', 'etiqueta' => 'altaal.tria_persona', 'req' => 1], $v('t_persona')) . '</div></div>';
    $html .= '<div class="mode-tutor" data-mode="nova" data-persona="t"><div class="camps">';
    foreach ($campsT as $nom => $d) {
        $html .= camp_formulari($nom, $d, $v($nom));
    }
    $html .= '<div class="camp camp-check"><label><input type="hidden" name="t_mateixa_casa" value="0"><input type="checkbox" name="t_mateixa_casa" value="1"'
        . ($v('t_mateixa_casa', '1') === '1' ? ' checked' : '') . '> ' . h(t('altaal.mateixa_casa')) . '</label></div></div></div></fieldset>';
    // 3. Els grups del curs
    $html .= '<fieldset class="bloc-alta"><legend><span class="pas">3</span> ' . h($curs ? t('altaal.grups', $curs['nom']) : t('altaal.grups_sense_curs')) . '</legend>';
    if ($curs) {
        $grups = q("SELECT g.id, a.nom AS assignatura, g.nom, (SELECT group_concat(h.dia || '|' || h.hora_inici, ';') FROM horaris h WHERE h.grup_id = g.id) AS horari,
            (SELECT COUNT(*) FROM matricula_grups mg WHERE mg.grup_id = g.id) AS n
            FROM grups g JOIN assignatures a ON a.id = g.assignatura_id WHERE g.curs_id = ? AND g.estat = 'actiu' ORDER BY a.nom COLLATE ALFA, g.nom COLLATE ALFA", [$curs['id']])->fetchAll();
        $marcats = array_map('intval', (array)($_POST['grups'] ?? []));
        $perAssig = [];
        foreach ($grups as $g) {
            $hores = [];
            foreach (array_filter(explode(';', (string)$g['horari'])) as $x) {
                [$d, $hi] = explode('|', $x);
                $hores[] = t('dia_curt.' . $d) . ' ' . $hi;
            }
            $perAssig[$g['assignatura']][] = '<label><input type="checkbox" name="grups[]" value="' . (int)$g['id'] . '"' . (in_array((int)$g['id'], $marcats, true) ? ' checked' : '') . '> '
                . h($g['nom']) . ' <small>' . h(implode(', ', $hores) . ' · ' . t('altaal.n_alumnes', (int)$g['n'])) . '</small></label>';
        }
        if ($perAssig) {
            $html .= '<small>' . h(t('altaal.grups_ajuda')) . '</small><div class="grups-alta">';
            foreach ($perAssig as $assig => $items) {
                $html .= '<div><b>' . h($assig) . '</b>' . implode('', $items) . '</div>';
            }
            $html .= '</div>';
        } else {
            $html .= '<p class="buit petit">' . h(t('altaal.sense_grups')) . '</p>';
        }
    } else {
        $html .= '<p class="buit petit">' . h(t('inici.sense_curs')) . '</p>';
    }
    $html .= '<div class="camps"><div class="camp"><label for="c_data_alta">' . h(t('camp.data_alta')) . '</label><input type="date" id="c_data_alta" name="data_alta" value="'
        . h($v('data_alta', date('Y-m-d'))) . '"></div></div></fieldset>';
    $html .= '<div class="accions"><button>' . h(t('altaal.desa')) . '</button><a class="boto secundari" href="' . h($persona ? url('fitxa', ['e' => 'persones', 'id' => $persona['id']]) : url('llista', ['e' => 'alumnes'])) . '">'
        . h(t('cancel·la')) . '</a></div></form>';
    pagina(t('altaal.titol', de_nom(mb_strtolower(t('ent1.alumnes')))), $html);
}
