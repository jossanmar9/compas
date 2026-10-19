<?php
// Administració: usuaris, configuració, registre d'activitat i ajuda
// SPDX-License-Identifier: GPL-2.0-or-later

/** Comprova que almenys un usuari actiu continua podent gestionar usuaris */
function queda_administrador(): bool
{
    return (bool)valor("SELECT COUNT(*) FROM usuaris u JOIN usuari_rols ur ON ur.usuari_id = u.id JOIN rol_capacitats rc ON rc.rol_id = ur.rol_id
        WHERE u.actiu = 1 AND rc.capacitat = 'usuaris.manage'");
}

/** Rol d'un usuari ('admin' o 'administratiu') */
function rol_usuari(int $id): ?string
{
    return valor('SELECT r.clau FROM usuari_rols ur JOIN rols r ON r.id = ur.rol_id WHERE ur.usuari_id = ? ORDER BY r.id LIMIT 1', [$id]);
}

function assigna_rol(int $usuariId, string $rol): void
{
    q('DELETE FROM usuari_rols WHERE usuari_id = ?', [$usuariId]);
    q('INSERT INTO usuari_rols(usuari_id, rol_id) SELECT ?, id FROM rols WHERE clau = ?', [$usuariId, in_array($rol, ROLS, true) ? $rol : 'administratiu']);
}

function pagina_usuaris(): void
{
    requereix('usuaris.manage');
    $id = get('id');
    if ($id !== '') {
        formulari_usuari($id === 'nou' ? null : (int)$id);
        return;
    }
    $files = q('SELECT * FROM usuaris ORDER BY usuari')->fetchAll();
    $html = '<div class="barra"><span class="espai"></span><a class="boto" href="' . h(url('usuaris', ['id' => 'nou'])) . '">+ ' . h(t('nou')) . '</a></div>';
    $html .= '<div class="taula-wrap"><table class="taula"><thead><tr><th>' . h(t('camp.usuari')) . '</th><th>' . h(t('camp.rol'))
        . '</th><th>' . h(t('camp.actiu')) . '</th><th>' . h(t('camp.ultim_acces')) . '</th></tr></thead><tbody>';
    foreach ($files as $f) {
        $html .= '<tr data-href="' . h(url('usuaris', ['id' => $f['id']])) . '"><td><a href="' . h(url('usuaris', ['id' => $f['id']])) . '">' . h($f['usuari']) . '</a></td><td>'
            . h(t('rol.' . (rol_usuari((int)$f['id']) ?? 'administratiu'))) . '</td><td>' . ($f['actiu'] ? '✔' : '—') . '</td><td>' . h(data_llegible($f['ultim_acces'])) . '</td></tr>';
    }
    $html .= '</tbody></table></div><p class="nota">' . h(t('usuaris.nota')) . '</p>';
    pagina(t('menu.usuaris'), $html);
}

function formulari_usuari(?int $id): void
{
    $u = $id ? fila('SELECT * FROM usuaris WHERE id = ?', [$id]) : null;
    if ($id && !$u) {
        redirigeix('usuaris');
    }
    $rol = $id ? (rol_usuari($id) ?? 'administratiu') : 'administratiu';
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
            $actiu = post('actiu') === '1' ? 1 : 0;
            $l = post('idioma');
            $l = isset(IDIOMES[$l]) ? $l : null;
            $rol = in_array(post('rol'), ROLS, true) ? post('rol') : 'administratiu';
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
                        q('UPDATE usuaris SET usuari = ?, actiu = ?, idioma = ? WHERE id = ?', [$nom, $actiu, $l, $id]);
                        if ($c1 !== '') {
                            q('UPDATE usuaris SET contrasenya = ? WHERE id = ?', [password_hash($c1, PASSWORD_DEFAULT), $id]);
                        }
                    } else {
                        q('INSERT INTO usuaris(usuari, contrasenya, actiu, idioma) VALUES(?,?,?,?)', [$nom, password_hash($c1, PASSWORD_DEFAULT), $actiu, $l]);
                        $id = (int)db()->lastInsertId();
                    }
                    assigna_rol($id, $rol);
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
            $u = ['usuari' => $nom, 'actiu' => $actiu, 'idioma' => $l];
        }
    }
    $u = $u ?? ['usuari' => '', 'actiu' => 1, 'idioma' => null];
    $html = $error ? '<div class="avis error">' . h($error) . '</div>' : '';
    $html .= '<form method="post" class="formulari">' . csrf_camp() . '<div class="camps">'
        . '<div class="camp"><label>' . h(t('camp.usuari')) . ' <span class="obl">*</span></label><input name="usuari" required value="' . h($u['usuari']) . '" autocomplete="off"></div>'
        . '<div class="camp"><label>' . h(t('camp.rol')) . '</label>' . select_simple('rol', array_combine(ROLS, array_map(fn($r) => t('rol.' . $r), ROLS)), $rol, false, '', false)
        . '<small>' . h(t('usuaris.rol_ajuda')) . '</small></div>'
        . '<div class="camp"><label>' . h(t('camp.idioma')) . '</label>' . select_simple('idioma', IDIOMES, $u['idioma'], true, t('usuaris.idioma_defecte'), false) . '</div>'
        . '<div class="camp camp-check"><label><input type="checkbox" name="actiu" value="1"' . ($u['actiu'] ? ' checked' : '') . '> ' . h(t('camp.actiu')) . '</label></div>'
        . '<div class="camp"><label>' . h($id ? t('camp.contrasenya_nova') : t('camp.contrasenya')) . ($id ? '' : ' <span class="obl">*</span>') . '</label><input type="password" name="contrasenya" autocomplete="new-password"'
        . ($id ? '' : ' required') . ' minlength="' . MIN_CONTRASENYA . '">' . ($id ? '<small>' . h(t('usuaris.contrasenya_buida')) . '</small>' : '') . '</div>'
        . '<div class="camp"><label>' . h(t('camp.contrasenya2')) . '</label><input type="password" name="contrasenya2" autocomplete="new-password"></div>'
        . '</div><div class="accions"><button>' . h(t('desa')) . '</button><a class="boto secundari" href="' . h(url('usuaris')) . '">' . h(t('cancel·la')) . '</a>';
    if ($id) {
        $html .= '<span class="espai"></span><button class="perill" name="accio" value="elimina" formnovalidate onclick="return confirm(\'' . h(t('confirma_eliminar')) . '\')">' . h(t('elimina')) . '</button>';
    }
    $html .= '</div></form>';
    pagina($id ? t('edita_x', $u['usuari']) : t('nou_x', t('usuaris.usuari')), $html);
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
        if (post('esborra_logo') === '1') {
            esborra_logo();
        } elseif (!empty($_FILES['logo']['name'])) {
            if ($err = desa_logo($_FILES['logo'])) {
                avis($err, 'error');
            }
        }
        audita('modifica', 'config');
        avis(t('desat'));
        redirigeix('configuracio');
    }
    $html = '<form method="post" class="formulari" enctype="multipart/form-data">' . csrf_camp() . '<div class="camps">';
    foreach ($camps as $c) {
        $html .= '<div class="camp"><label>' . h(t('camp.' . $c)) . '</label><input name="' . $c . '" value="' . h(config($c)) . '"' . ($c === 'societat_nom' ? ' required' : '') . '></div>';
    }
    $html .= '<div class="camp"><label>' . h(t('config.idioma_defecte')) . '</label>' . select_simple('idioma', IDIOMES, config('idioma', 'ca'), false, '', false) . '</div>';
    $logo = url_logo();
    $html .= '<div class="camp ample"><label>' . h(t('logo.titol')) . '</label>'
        . ($logo ? '<img class="logo-previ" src="' . h($logo) . '" alt=""><label class="check"><input type="checkbox" name="esborra_logo" value="1"> ' . h(t('logo.esborra')) . '</label>' : '')
        . '<input type="file" name="logo" accept="image/png,image/jpeg,image/webp,image/gif"><small>' . h(t('logo.ajuda')) . '</small></div>';
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

/* ---------- Ajuda ---------- */

function markdown_simple(string $md): string
{
    $html = '';
    $llista = null;
    $para = [];
    $codi = false;
    $taula = false;
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
        if (str_starts_with(trim($linia), '|')) {
            $cells = array_map('trim', explode('|', trim(trim($linia), '|')));
            if (preg_match('/^[\s|:-]+$/', $linia)) {
                continue; // separador de capçalera
            }
            if (!$taula) {
                $tanca();
                $html .= '<div class="taula-wrap"><table class="taula"><thead><tr><th>' . implode('</th><th>', array_map($inline, $cells)) . '</th></tr></thead><tbody>';
                $taula = true;
            } else {
                $html .= '<tr><td>' . implode('</td><td>', array_map($inline, $cells)) . '</td></tr>';
            }
            continue;
        }
        if ($taula) {
            $html .= '</tbody></table></div>';
            $taula = false;
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
    return $html . ($taula ? '</tbody></table></div>' : '');
}

function pagina_ajuda(): void
{
    $fitxer = DOCS_DIR . '/' . (idioma() === 'es' ? 'manual-usuario.md' : 'manual-usuari.md');
    $md = is_file($fitxer) ? file_get_contents($fitxer) : t('ajuda.no_manual');
    $html = '<article class="manual">' . markdown_simple(preg_replace('/^# .*\R/', '', $md)) . '</article>';
    pagina(t('menu.ajuda'), $html);
}
