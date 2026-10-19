<?php
// Administració: usuaris, rols, configuració, auditoria, còpies i ajuda
// SPDX-License-Identifier: GPL-2.0-or-later

/** Comprova que almenys un usuari actiu continua podent gestionar usuaris */
function queda_administrador(): bool
{
    return (bool)valor("SELECT COUNT(*) FROM usuaris u JOIN usuari_rols ur ON ur.usuari_id = u.id JOIN rol_capacitats rc ON rc.rol_id = ur.rol_id
        WHERE u.actiu = 1 AND rc.capacitat = 'usuaris.manage'");
}

function pagina_usuaris(): void
{
    requereix('usuaris.manage');
    $rols = q('SELECT * FROM rols ORDER BY id')->fetchAll();
    $id = get('id');
    if ($id !== '') {
        formulari_usuari($id === 'nou' ? null : (int)$id, $rols);
        return;
    }
    $files = q("SELECT u.*, " . NOM_PERSONA_SQL . " AS persona, (SELECT group_concat(rol_id) FROM usuari_rols WHERE usuari_id = u.id) AS rols
        FROM usuaris u LEFT JOIN persones p ON p.id = u.persona_id ORDER BY u.usuari")->fetchAll();
    $noms = array_column(array_map(fn($r) => ['id' => $r['id'], 'n' => nom_rol($r)], $rols), 'n', 'id');
    $html = '<div class="barra"><span class="espai"></span><a class="boto" href="' . h(url('usuaris', ['id' => 'nou'])) . '">+ ' . h(t('nou')) . '</a></div>';
    $html .= '<div class="taula-wrap"><table class="taula"><thead><tr><th>' . h(t('camp.usuari')) . '</th><th>' . h(t('camp.persona_id')) . '</th><th>' . h(t('menu.rols'))
        . '</th><th>' . h(t('camp.actiu')) . '</th><th>' . h(t('camp.ultim_acces')) . '</th></tr></thead><tbody>';
    foreach ($files as $f) {
        $r = array_map(fn($x) => $noms[$x] ?? '', array_filter(explode(',', (string)$f['rols'])));
        $html .= '<tr><td><a href="' . h(url('usuaris', ['id' => $f['id']])) . '">' . h($f['usuari']) . '</a></td><td>' . h($f['persona']) . '</td><td>' . h(implode(', ', $r))
            . '</td><td>' . ($f['actiu'] ? '✔' : '—') . '</td><td>' . h(data_llegible($f['ultim_acces'])) . '</td></tr>';
    }
    $html .= '</tbody></table></div><p class="nota">' . h(t('usuaris.nota')) . '</p>';
    pagina(t('menu.usuaris'), $html);
}

function formulari_usuari(?int $id, array $rols): void
{
    $u = $id ? fila('SELECT * FROM usuaris WHERE id = ?', [$id]) : null;
    if ($id && !$u) {
        redirigeix('usuaris');
    }
    $assignats = $id ? array_column(q('SELECT rol_id FROM usuari_rols WHERE usuari_id = ?', [$id])->fetchAll(), 'rol_id') : [];
    $error = null;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (post('accio') === 'elimina' && $id) {
            if ($id === (int)usuari()['id']) {
                $error = t('err.eliminar_propi');
            } else {
                db()->beginTransaction();
                q('DELETE FROM usuaris WHERE id = ?', [$id]);
                if (!queda_administrador()) {
                    db()->rollBack();
                    $error = t('err.ultim_admin');
                } else {
                    db()->commit();
                    audita('elimina', 'usuaris', $id, $u['usuari']);
                    avis(t('eliminat'));
                    redirigeix('usuaris');
                }
            }
        } else {
            $nom = post('usuari');
            $persona = (int)post('persona_id') ?: null;
            $actiu = post('actiu') === '1' ? 1 : 0;
            $l = post('idioma');
            $l = isset(IDIOMES[$l]) ? $l : null;
            $sel = array_map('intval', (array)($_POST['rols'] ?? []));
            $c1 = (string)post('contrasenya');
            if (!preg_match('/^[\p{L}\p{N}._-]{3,40}$/u', $nom)) {
                $error = t('err.nom_usuari');
            } elseif (!$id || $c1 !== '') {
                $error = valida_contrasenya($c1, (string)post('contrasenya2'));
            }
            if (!$error) {
                try {
                    db()->beginTransaction();
                    if ($id) {
                        q('UPDATE usuaris SET usuari = ?, persona_id = ?, actiu = ?, idioma = ? WHERE id = ?', [$nom, $persona, $actiu, $l, $id]);
                        if ($c1 !== '') {
                            q('UPDATE usuaris SET contrasenya = ? WHERE id = ?', [password_hash($c1, PASSWORD_DEFAULT), $id]);
                        }
                    } else {
                        q('INSERT INTO usuaris(usuari, contrasenya, persona_id, actiu, idioma) VALUES(?,?,?,?,?)', [$nom, password_hash($c1, PASSWORD_DEFAULT), $persona, $actiu, $l]);
                        $id = (int)db()->lastInsertId();
                    }
                    q('DELETE FROM usuari_rols WHERE usuari_id = ?', [$id]);
                    foreach ($sel as $r) {
                        q('INSERT OR IGNORE INTO usuari_rols(usuari_id, rol_id) SELECT ?, id FROM rols WHERE id = ?', [$id, $r]);
                    }
                    if (!queda_administrador()) {
                        throw new RuntimeException(t('err.ultim_admin'));
                    }
                    db()->commit();
                    audita($u ? 'modifica' : 'crea', 'usuaris', $id, $nom . ($c1 !== '' && $u ? ' (contrasenya)' : ''));
                    avis(t('desat'));
                    redirigeix('usuaris');
                } catch (PDOException $ex) {
                    db()->rollBack();
                    $error = error_bd($ex);
                } catch (RuntimeException $ex) {
                    db()->rollBack();
                    $error = $ex->getMessage();
                }
                if (!$u) {
                    $id = null;
                }
            }
            $u = ['usuari' => $nom, 'persona_id' => $persona, 'actiu' => $actiu, 'idioma' => $l];
            $assignats = $sel;
        }
    }
    $u = $u ?? ['usuari' => '', 'persona_id' => null, 'actiu' => 1, 'idioma' => null];
    $html = $error ? '<div class="avis error">' . h($error) . '</div>' : '';
    $html .= '<form method="post" class="formulari">' . csrf_camp() . '<div class="camps">'
        . '<div class="camp"><label>' . h(t('camp.usuari')) . ' <span class="obl">*</span></label><input name="usuari" required value="' . h($u['usuari']) . '" autocomplete="off"></div>'
        . '<div class="camp"><label>' . h(t('camp.persona_id')) . '</label>' . str_replace('<select', '<select data-cerca="1"', select_simple('persona_id', opcions_ref('persones'), $u['persona_id'], true, '—', false))
        . '<small>' . h(t('usuaris.persona_ajuda')) . '</small></div>'
        . '<div class="camp"><label>' . h(t('camp.idioma')) . '</label>' . select_simple('idioma', IDIOMES, $u['idioma'], true, t('usuaris.idioma_defecte'), false) . '</div>'
        . '<div class="camp camp-check"><label><input type="checkbox" name="actiu" value="1"' . ($u['actiu'] ? ' checked' : '') . '> ' . h(t('camp.actiu')) . '</label></div>'
        . '<div class="camp"><label>' . h($id ? t('camp.contrasenya_nova') : t('camp.contrasenya')) . ($id ? '' : ' <span class="obl">*</span>') . '</label><input type="password" name="contrasenya" autocomplete="new-password"'
        . ($id ? '' : ' required') . ' minlength="' . MIN_CONTRASENYA . '">' . ($id ? '<small>' . h(t('usuaris.contrasenya_buida')) . '</small>' : '') . '</div>'
        . '<div class="camp"><label>' . h(t('camp.contrasenya2')) . '</label><input type="password" name="contrasenya2" autocomplete="new-password"></div>'
        . '<fieldset class="camp ample"><legend>' . h(t('menu.rols')) . '</legend><div class="opcions">';
    foreach ($rols as $r) {
        $html .= '<label><input type="checkbox" name="rols[]" value="' . (int)$r['id'] . '"' . (in_array($r['id'], $assignats) ? ' checked' : '') . '> ' . h(nom_rol($r)) . '</label>';
    }
    $html .= '</div></fieldset></div><div class="accions"><button>' . h(t('desa')) . '</button><a class="boto secundari" href="' . h(url('usuaris')) . '">' . h(t('cancel·la')) . '</a>';
    if ($id) {
        $html .= '<span class="espai"></span><button class="perill" name="accio" value="elimina" formnovalidate onclick="return confirm(\'' . h(t('confirma_eliminar')) . '\')">' . h(t('elimina')) . '</button>';
    }
    $html .= '</div></form>';
    pagina($id ? t('edita_x', $u['usuari']) : t('nou_x', t('usuaris.usuari')), $html);
}

function pagina_rols(): void
{
    requereix('usuaris.manage');
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $marcades = (array)($_POST['cap'] ?? []);
        $noms = (array)($_POST['nom'] ?? []);
        $totes = totes_capacitats();
        db()->beginTransaction();
        $nou = trim((string)post('nou_rol'));
        if ($nou !== '') {
            $clau = 'personal_' . substr(bin2hex(random_bytes(4)), 0, 8);
            q('INSERT INTO rols(clau, nom) VALUES(?, ?)', [$clau, mb_substr($nou, 0, 60)]);
        }
        foreach (q('SELECT * FROM rols')->fetchAll() as $r) {
            $nom = trim((string)($noms[$r['id']] ?? ''));
            q('UPDATE rols SET nom = ? WHERE id = ?', [$nom === '' || $nom === t('rol.' . $r['clau']) ? null : mb_substr($nom, 0, 60), $r['id']]);
            if ($r['clau'] === 'admin') {
                continue; // l'administrador del sistema sempre ho pot tot
            }
            q('DELETE FROM rol_capacitats WHERE rol_id = ?', [$r['id']]);
            foreach ((array)($marcades[$r['id']] ?? []) as $c) {
                if (in_array($c, $totes, true)) {
                    q('INSERT INTO rol_capacitats(rol_id, capacitat) VALUES(?, ?)', [$r['id'], $c]);
                }
            }
        }
        if (!queda_administrador()) {
            db()->rollBack();
            avis(t('err.ultim_admin'), 'error');
        } else {
            db()->commit();
            audita('modifica', 'rols');
            avis(t('desat'));
        }
        redirigeix('rols');
    }
    $rols = q('SELECT * FROM rols ORDER BY id')->fetchAll();
    $tenen = [];
    foreach (q('SELECT * FROM rol_capacitats')->fetchAll() as $rc) {
        $tenen[$rc['rol_id']][$rc['capacitat']] = true;
    }
    $html = '<p class="nota">' . h(t('rols.ajuda')) . '</p><form method="post">' . csrf_camp() . '<div class="taula-wrap"><table class="taula matriu"><thead><tr><th></th>';
    foreach ($rols as $r) {
        $html .= '<th><input class="nom-rol" name="nom[' . (int)$r['id'] . ']" value="' . h(nom_rol($r)) . '" title="' . h(t('rols.renomena')) . '"></th>';
    }
    $html .= '</tr></thead><tbody>';
    foreach (capacitats() as $modul => $caps) {
        $html .= '<tr class="grup"><th colspan="' . (count($rols) + 1) . '">' . h(t('modul.' . $modul)) . '</th></tr>';
        foreach ($caps as $c) {
            $html .= '<tr><td>' . h(t('cap.' . $c)) . '</td>';
            foreach ($rols as $r) {
                $admin = $r['clau'] === 'admin';
                $html .= '<td class="centre"><input type="checkbox" name="cap[' . (int)$r['id'] . '][]" value="' . h($c) . '"'
                    . (!empty($tenen[$r['id']][$c]) ? ' checked' : '') . ($admin ? ' disabled' : '') . ' aria-label="' . h(nom_rol($r) . ': ' . t('cap.' . $c)) . '"></td>';
            }
            $html .= '</tr>';
        }
    }
    $html .= '</tbody></table></div><div class="accions"><input name="nou_rol" placeholder="' . h(t('rols.nou')) . '"><button>' . h(t('desa')) . '</button></div></form>';
    $html .= '<p class="nota">' . h(t('rols.portal')) . '</p>';
    pagina(t('menu.rols'), $html);
}

function pagina_configuracio(): void
{
    requereix('config.manage');
    $camps = ['societat_nom', 'societat_cif', 'societat_adreca', 'societat_telefon', 'societat_email', 'societat_web'];
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        foreach ($camps as $c) {
            desa_config($c, mb_substr((string)post($c), 0, 200) ?: null);
        }
        $l = post('idioma');
        desa_config('idioma', isset(IDIOMES[$l]) ? $l : 'ca');
        audita('modifica', 'config');
        avis(t('desat'));
        redirigeix('configuracio');
    }
    $html = '<form method="post" class="formulari">' . csrf_camp() . '<div class="camps">';
    foreach ($camps as $c) {
        $html .= '<div class="camp"><label>' . h(t('camp.' . $c)) . '</label><input name="' . $c . '" value="' . h(config($c)) . '"' . ($c === 'societat_nom' ? ' required' : '') . '></div>';
    }
    $html .= '<div class="camp"><label>' . h(t('config.idioma_defecte')) . '</label>' . select_simple('idioma', IDIOMES, config('idioma', 'ca'), false, '', false) . '</div>';
    $html .= '</div><div class="accions"><button>' . h(t('desa')) . '</button></div></form>';
    $html .= '<section class="fill"><header><h2>' . h(t('config.sistema')) . '</h2></header><dl class="fitxa">'
        . '<dt>' . h(t('config.versio')) . '</dt><dd>Compàs ' . COMPAS_VERSIO . '</dd>'
        . '<dt>PHP</dt><dd>' . h(PHP_VERSION) . ' · SQLite ' . h(valor('SELECT sqlite_version()')) . '</dd>'
        . '<dt>' . h(t('config.carpeta_dades')) . '</dt><dd><code>' . h(DADES_DIR) . '</code></dd>'
        . '<dt>' . h(t('config.mida_bd')) . '</dt><dd>' . h(number_format(filesize(DADES_DIR . '/compas.db') / 1024, 0, ',', '.')) . ' KB</dd></dl></section>';
    pagina(t('menu.configuracio'), $html);
}

function pagina_auditoria(): void
{
    requereix('auditoria.view');
    $params = [];
    $where = '1=1';
    if (($qq = get('q')) !== '') {
        $where .= ' AND (usuari LIKE ? OR accio LIKE ? OR entitat LIKE ? OR detall LIKE ?)';
        array_push($params, "%$qq%", "%$qq%", "%$qq%", "%$qq%");
    }
    $files = q("SELECT * FROM auditoria WHERE $where ORDER BY id DESC LIMIT 300", $params)->fetchAll();
    $html = '<form class="barra" method="get"><input type="hidden" name="r" value="auditoria"><input type="search" name="q" value="' . h(get('q')) . '" placeholder="' . h(t('cercar')) . '"><button>' . h(t('filtrar')) . '</button></form>';
    $html .= '<div class="taula-wrap"><table class="taula compacta"><thead><tr><th>' . h(t('camp.data')) . '</th><th>' . h(t('camp.usuari')) . '</th><th>' . h(t('audit.accio'))
        . '</th><th>' . h(t('audit.element')) . '</th><th>' . h(t('audit.detall')) . '</th></tr></thead><tbody>';
    foreach ($files as $f) {
        $element = '';
        if ($f['entitat']) {
            $element = isset(entitats()[$f['entitat']]) ? t('ent1.' . $f['entitat']) : $f['entitat'];
            if ($f['element_id'] && isset(entitats()[$f['entitat']]) && $f['accio'] !== 'elimina') {
                $element = '<a href="' . h(url('fitxa', ['e' => $f['entitat'], 'id' => $f['element_id']])) . '">' . h($element . ' #' . $f['element_id']) . '</a>';
            } else {
                $element = h($element . ($f['element_id'] ? ' #' . $f['element_id'] : ''));
            }
        }
        $html .= '<tr><td>' . h(data_llegible($f['data'])) . '</td><td>' . h($f['usuari']) . '</td><td>' . h(t('audit.' . $f['accio'])) . '</td><td>' . $element . '</td><td>' . h($f['detall']) . '</td></tr>';
    }
    $html .= '</tbody></table></div><p class="peu-taula">' . h(t('audit.limit')) . '</p>';
    pagina(t('menu.auditoria'), $html);
}

/* ---------- Còpies de seguretat ---------- */

function carpeta_copies(): string
{
    $d = DADES_DIR . DIRECTORY_SEPARATOR . 'copies';
    if (!is_dir($d)) {
        mkdir($d, 0770, true);
    }
    return $d;
}

function fes_copia(string $desti): void
{
    if (is_file($desti)) {
        unlink($desti);
    }
    q('VACUUM INTO ?', [$desti]);
}

/** Còpia automàtica diària; es guarden les 14 últimes */
function copia_automatica(): void
{
    $avui = date('Y-m-d');
    if (config('ultima_copia') === $avui) {
        return;
    }
    desa_config('ultima_copia', $avui);
    fes_copia(carpeta_copies() . DIRECTORY_SEPARATOR . 'compas-' . date('Ymd') . '.db');
    $copies = glob(carpeta_copies() . DIRECTORY_SEPARATOR . 'compas-*.db');
    sort($copies);
    foreach (array_slice($copies, 0, max(0, count($copies) - 14)) as $vella) {
        unlink($vella);
    }
}

function es_bd_compas(string $fitxer): bool
{
    if (filesize($fitxer) < 100 || file_get_contents($fitxer, false, null, 0, 16) !== "SQLite format 3\0") {
        return false;
    }
    try {
        $p = new PDO('sqlite:' . $fitxer, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $ok = (int)$p->query("SELECT COUNT(*) FROM sqlite_master WHERE type = 'table' AND name IN ('usuaris','persones','rols')")->fetchColumn() === 3
            && (int)$p->query('SELECT COUNT(*) FROM usuaris')->fetchColumn() > 0;
        $p = null;
        return $ok;
    } catch (Throwable $e) {
        return false;
    }
}

function pagina_copia(): void
{
    requereix('copia.manage');
    $accio = post('accio');
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $accio === 'baixa') {
        $tmp = DADES_DIR . DIRECTORY_SEPARATOR . 'baixada-' . bin2hex(random_bytes(4)) . '.db';
        fes_copia($tmp);
        audita('copia_baixada');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="compas-' . date('Ymd-His') . '.db"');
        header('Content-Length: ' . filesize($tmp));
        readfile($tmp);
        unlink($tmp);
        exit;
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $accio === 'restaura') {
        $f = $_FILES['fitxer'] ?? null;
        if (!$f || $f['error'] !== UPLOAD_ERR_OK || !es_bd_compas($f['tmp_name'])) {
            avis(t('copia.no_valida'), 'error');
        } elseif (post('confirma') !== '1') {
            avis(t('copia.cal_confirmar'), 'error');
        } else {
            move_uploaded_file($f['tmp_name'], DADES_DIR . DIRECTORY_SEPARATOR . 'restaura.db');
            audita('copia_restaurada', null, null, $f['name']);
            avis(t('copia.restaurada'));
        }
        redirigeix('copia');
    }
    $html = '<p>' . h(t('copia.text')) . '</p><form method="post">' . csrf_camp() . '<input type="hidden" name="accio" value="baixa"><button>' . h(t('copia.baixa')) . '</button></form>';
    $copies = glob(carpeta_copies() . DIRECTORY_SEPARATOR . '*.db');
    rsort($copies);
    $html .= '<section class="fill"><header><h2>' . h(t('copia.automatiques')) . '</h2></header><p class="nota">' . h(t('copia.automatiques_text')) . '</p><code>'
        . h(carpeta_copies()) . '</code><ul class="llista-simple columnes">';
    foreach ($copies as $c) {
        $html .= '<li>' . h(basename($c)) . ' <span class="etiqueta">' . h(number_format(filesize($c) / 1024, 0, ',', '.')) . ' KB</span></li>';
    }
    $html .= '</ul></section>';
    $html .= '<section class="fill perill-zona"><header><h2>' . h(t('copia.restaurar')) . '</h2></header><p>' . h(t('copia.restaurar_text')) . '</p>'
        . '<form method="post" enctype="multipart/form-data" class="formulari vertical estret">' . csrf_camp() . '<input type="hidden" name="accio" value="restaura">'
        . '<input type="file" name="fitxer" accept=".db" required><label class="check"><input type="checkbox" name="confirma" value="1" required> ' . h(t('copia.entenc')) . '</label>'
        . '<button class="perill">' . h(t('copia.restaurar')) . '</button></form></section>';
    pagina(t('menu.copia'), $html);
}

/* ---------- Ajuda ---------- */

function markdown_simple(string $md): string
{
    $html = '';
    $llista = null;
    $para = [];
    $codi = false;
    $tanca = function () use (&$html, &$llista, &$para) {
        if ($para) {
            $html .= '<p>' . implode(' ', $para) . '</p>';
            $para = [];
        }
        if ($llista) {
            $html .= "</$llista>";
            $llista = null;
        }
    };
    $inline = function (string $s): string {
        $s = h($s);
        $s = preg_replace('/`([^`]+)`/', '<code>$1</code>', $s);
        $s = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $s);
        $s = preg_replace('/(?<![\w*])\*([^*]+)\*(?!\w)/', '<em>$1</em>', $s);
        return preg_replace('/\[([^\]]+)\]\((https?:[^)\s]+|#[\w-]+)\)/', '<a href="$2">$1</a>', $s);
    };
    foreach (preg_split('/\R/', $md) as $linia) {
        if (str_starts_with($linia, '```')) {
            $tanca();
            $html .= $codi ? '</pre>' : '<pre>';
            $codi = !$codi;
            continue;
        }
        if ($codi) {
            $html .= h($linia) . "\n";
            continue;
        }
        if (trim($linia) === '') {
            $tanca();
        } elseif (preg_match('/^(#{1,4})\s+(.*)$/', $linia, $m)) {
            $tanca();
            $n = strlen($m[1]) + 1;
            $id = trim(preg_replace('/[^\p{L}\p{N}]+/u', '-', mb_strtolower($m[2])), '-');
            $html .= "<h$n id=\"" . h($id) . "\">" . $inline($m[2]) . "</h$n>";
        } elseif (preg_match('/^\s*([-*]|\d+\.)\s+(.*)$/', $linia, $m)) {
            if ($para) {
                $html .= '<p>' . implode(' ', $para) . '</p>';
                $para = [];
            }
            $tipus = ctype_digit($m[1][0]) ? 'ol' : 'ul';
            if ($llista !== $tipus) {
                if ($llista) {
                    $html .= "</$llista>";
                }
                $html .= "<$tipus>";
                $llista = $tipus;
            }
            $html .= '<li>' . $inline($m[2]) . '</li>';
        } elseif (str_starts_with($linia, '> ')) {
            $tanca();
            $html .= '<blockquote>' . $inline(substr($linia, 2)) . '</blockquote>';
        } else {
            if ($llista) {
                $html .= "</$llista>";
                $llista = null;
            }
            $para[] = $inline(trim($linia));
        }
    }
    $tanca();
    return $html;
}

function pagina_ajuda(): void
{
    $fitxer = DOCS_DIR . '/' . (idioma() === 'es' ? 'manual-usuario.md' : 'manual-usuari.md');
    $md = is_file($fitxer) ? file_get_contents($fitxer) : t('ajuda.no_manual');
    $html = '<article class="manual">' . markdown_simple(preg_replace('/^# .*\R/', '', $md)) . '</article>';
    pagina(t('menu.ajuda'), $html);
}
