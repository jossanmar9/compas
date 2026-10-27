<?php
// Entrada, configuració inicial i perfil
// Copyright (C) 2026 José Vicente Sanjuan
// SPDX-License-Identifier: AGPL-3.0-or-later

const MIN_CONTRASENYA = 8;

function valida_contrasenya(string $c1, string $c2): ?string
{
    if (mb_strlen($c1) < MIN_CONTRASENYA) {
        return t('err.contrasenya_curta', MIN_CONTRASENYA);
    }
    if ($c1 !== $c2) {
        return t('err.contrasenyes');
    }
    return null;
}

function pagina_configuracio_inicial(): void
{
    $error = null;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nom = post('societat_nom');
        $tipus = isset(TIPUS_ENTITAT[post('tipus_entitat')]) ? post('tipus_entitat') : 'societat_musical';
        $usuari = post('usuari');
        $l = post('idioma');
        $error = valida_contrasenya((string)post('contrasenya'), (string)post('contrasenya2'));
        if (!$nom) {
            $error = t('err.dades_inicials');
        } elseif (!nom_usuari_valid($usuari)) {
            $error = t('err.nom_usuari');
        }
        $usuari2 = post('usuari2');
        if (!$error && $usuari2 !== '') {
            if (!nom_usuari_valid($usuari2) || strcasecmp($usuari2, $usuari) === 0) {
                $error = t('err.nom_usuari');
            } else {
                $error = valida_contrasenya((string)post('contrasenya_b'), (string)post('contrasenya_b2'));
            }
        }
        if (!$error && post('accepta_avis') !== '1') {
            $error = t('avis_resp.cal_acceptar');
        }
        if (!$error) {
            db()->beginTransaction();
            if ((int)valor('SELECT COUNT(*) FROM usuaris') !== 0) {
                db()->rollBack();
                redirigeix('inici');
            }
            desa_config('societat_nom', $nom);
            desa_config('idioma', isset(IDIOMES[$l]) ? $l : 'ca');
            // El tipus d'entitat: mòduls, mes d'inici de l'exercici i categories d'edat (tot es pot canviar després)
            aplica_tipus(db(), $tipus, true, isset(IDIOMES[$l]) ? $l : 'ca');
            sembra_tipus_socis(db(), isset(IDIOMES[$l]) ? $l : 'ca');
            sembra_tipus_obres(db(), isset(IDIOMES[$l]) ? $l : 'ca');
            sembra_subvencions(db(), isset(IDIOMES[$l]) ? $l : 'ca');
            desa_config('avis_responsabilitat', date('Y-m-d H:i') . '|' . $usuari);
            q('INSERT INTO usuaris(usuari, contrasenya, idioma) VALUES(?, ?, ?)', [$usuari, password_hash(post('contrasenya'), PASSWORD_DEFAULT), $l]);
            $id = (int)db()->lastInsertId();
            assigna_rol($id, 'admin');
            if ($usuari2 !== '') {
                q('INSERT INTO usuaris(usuari, contrasenya, idioma) VALUES(?, ?, ?)', [$usuari2, password_hash(post('contrasenya_b'), PASSWORD_DEFAULT), $l]);
                assigna_rol((int)db()->lastInsertId(), 'administratiu');
            }
            if (post('exemple') === '1') {
                carrega_dades_exemple();
            }
            $codi = genera_codi_recuperacio();
            db()->commit();
            autentica_sessio($id);
            $_SESSION['idioma'] = $l;
            audita('instal·lacio', null, null, $nom);
            avis(t('benvinguda'));
            // Abans de començar, el codi per a recuperar l'accés si s'oblida la contrasenya
            $_SESSION['codi_nou'] = $codi;
            redirigeix('codi');
        }
    }
    $html = '<h1>' . h(t('setup.titol')) . '</h1><p>' . h(t('setup.text')) . '</p>';
    if ($error) {
        $html .= '<div class="avis error">' . h($error) . '</div>';
    }
    $html .= '<form method="post" class="formulari vertical">' . csrf_camp()
        . '<div class="camp"><label>' . h(t('camp.societat_nom')) . '</label><input name="societat_nom" required value="' . h(post('societat_nom')) . '" placeholder="' . h(t('setup.exemple_nom')) . '"></div>'
        . selector_tipus_entitat(post('tipus_entitat') ?: 'societat_musical')
        . '<div class="camp"><label>' . h(t('camp.idioma')) . '</label><select name="idioma">';
    foreach (IDIOMES as $k => $n) {
        $html .= '<option value="' . $k . '"' . ($k === idioma() ? ' selected' : '') . '>' . h($n) . '</option>';
    }
    $html .= '</select></div>'
        . '<div class="camp"><label>' . h(t('setup.usuari_admin')) . '</label><input name="usuari" pattern="[A-Za-z0-9._-]{3,40}" maxlength="40" autocapitalize="none" spellcheck="false" required autocomplete="username" value="' . h(post('usuari', 'admin')) . '"></div>'
        . '<div class="camp"><label>' . h(t('camp.contrasenya')) . '</label><input type="password" name="contrasenya" required minlength="' . MIN_CONTRASENYA . '" autocomplete="new-password"></div>'
        . '<div class="camp"><label>' . h(t('camp.contrasenya2')) . '</label><input type="password" name="contrasenya2" required autocomplete="new-password"></div>'
        . '<fieldset class="camp"><legend>' . h(t('setup.administratiu')) . '</legend><small>' . h(t('setup.administratiu_ajuda')) . '</small>'
        . '<div class="camp"><label>' . h(t('camp.usuari')) . '</label><input name="usuari2" pattern="[A-Za-z0-9._-]{3,40}" maxlength="40" autocapitalize="none" spellcheck="false" autocomplete="off" value="' . h(post('usuari2')) . '"></div>'
        . '<div class="camp"><label>' . h(t('camp.contrasenya')) . '</label><input type="password" name="contrasenya_b" autocomplete="new-password"></div>'
        . '<div class="camp"><label>' . h(t('camp.contrasenya2')) . '</label><input type="password" name="contrasenya_b2" autocomplete="new-password"></div></fieldset>'
        . '<div class="camp camp-check"><label><input type="checkbox" name="exemple" value="1"> ' . h(t('setup.exemple')) . '</label><small>' . h(t('setup.exemple_ajuda')) . '</small></div>'
        . '<div class="avis-responsabilitat"><h2>⚠ ' . h(t('avis_resp.titol')) . '</h2><p>' . h(t('avis_resp.text')) . '</p>'
        . '<label class="check"><input type="checkbox" name="accepta_avis" value="1" required> <b>' . h(t('avis_resp.accepte')) . '</b></label></div>'
        . '<button>' . h(t('setup.comenca')) . '</button></form>';
    $html .= '<p class="idiomes-peu">' . enllacos_idioma() . '</p>';
    pagina_simple(t('setup.titol'), $html);
}

/** Caselles per a triar el tipus d'entitat, amb una línia que explica cada un */
function selector_tipus_entitat(string $actual): string
{
    $html = '<fieldset class="camp tipus-entitat"><legend>' . h(t('camp.tipus_entitat')) . '</legend><small>' . h(t('setup.tipus_ajuda')) . '</small><div class="opcions-tipus">';
    foreach (TIPUS_ENTITAT as $k => $tp) {
        $html .= '<label><input type="radio" name="tipus_entitat" value="' . h($k) . '"' . ($k === $actual ? ' checked' : '') . ' required> <span><b>'
            . h($tp['nom'][idioma()]) . '</b><small>' . h($tp['descripcio'][idioma()]) . '</small></span></label>';
    }
    return $html . '</div></fieldset>';
}

function enllacos_idioma(): string
{
    $parts = [];
    foreach (IDIOMES as $k => $n) {
        $parts[] = $k === idioma() ? '<strong>' . h($n) . '</strong>' : '<a href="' . h(url('idioma', ['l' => $k, 'tornar' => '/'])) . '">' . h($n) . '</a>';
    }
    return implode(' · ', $parts);
}

function pagina_entrada(): void
{
    $error = null;
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && get('r') === 'entrada') {
        // Els intents fallits es compten per a tot el programa (no per sessió: sense galeta no es podrien comptar).
        // El servidor integrat de PHP atén una petició cada vegada: no fem sleep(), bloquegem un temps.
        $intents = (int)config('entrada_fallides');
        if (mode_lan() ? entrada_bloquejada() : ($intents >= 5 && time() - (int)config('entrada_darrer') < 30)) {
            pagina_simple(t('entra'), '<div class="avis error">' . h(t('err.massa_intents')) . '</div><p><a href="?">' . h(t('torna')) . '</a></p>');
            return;
        }
        $u = nom_usuari_valid((string)post('usuari')) ? fila('SELECT * FROM usuaris WHERE usuari = ? AND actiu = 1', [post('usuari')]) : null;
        if ($u && password_verify((string)post('contrasenya'), $u['contrasenya'])) {
            if (mode_lan()) { registra_intent_entrada(true); }
            desa_config('entrada_fallides', null);
            if ($u['idioma']) {
                $_SESSION['idioma'] = $u['idioma'];
            }
            if (password_needs_rehash($u['contrasenya'], PASSWORD_DEFAULT)) {
                q('UPDATE usuaris SET contrasenya = ? WHERE id = ?', [password_hash(post('contrasenya'), PASSWORD_DEFAULT), $u['id']]);
            }
            autentica_sessio((int)$u['id']);
            q("UPDATE usuaris SET ultim_acces = datetime('now','localtime') WHERE id = ?", [$u['id']]);
            audita('entrada');
            redirigeix('inici');
        }
        if (mode_lan()) {
            registra_intent_entrada(false);
        } else {
            desa_config('entrada_fallides', (string)($intents >= 5 ? 1 : $intents + 1));
            desa_config('entrada_darrer', (string)time());
        }
        q('INSERT INTO auditoria(accio, usuari, detall) VALUES(?, ?, ?)', ['entrada_fallida', mb_substr((string)post('usuari'), 0, 60), $_SERVER['REMOTE_ADDR'] ?? '']);
        $error = t('err.entrada');
    }
    $html = '';
    if ($error) {
        $html .= '<div class="avis error">' . h($error) . '</div>';
    }
    $html .= '<form method="post" action="' . h(url('entrada')) . '" class="formulari vertical">' . csrf_camp()
        . '<div class="camp"><label>' . h(t('camp.usuari')) . '</label><input name="usuari" pattern="[A-Za-z0-9._-]{3,40}" maxlength="40" autocapitalize="none" spellcheck="false" required autofocus autocomplete="username" value="' . h(post('usuari')) . '"></div>'
        . '<div class="camp"><label>' . h(t('camp.contrasenya')) . '</label><input type="password" name="contrasenya" required autocomplete="current-password"></div>'
        . '<button>' . h(t('entra')) . '</button></form>'
        . '<p class="nota">' . h(t('entrada.oblit')) . ' <a href="' . h(url('recupera')) . '">' . h(t('entrada.oblit_admin')) . '</a></p>'
        . '<p class="idiomes-peu">' . enllacos_idioma() . '</p>';
    pagina_simple(t('entra'), $html);
}

function pagina_perfil(): void
{
    $u = usuari();
    $error = null;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!password_verify((string)post('actual'), $u['contrasenya'])) {
            $error = t('err.contrasenya_actual');
        } else {
            $error = valida_contrasenya((string)post('contrasenya'), (string)post('contrasenya2'));
        }
        if (!$error) {
            q('UPDATE usuaris SET contrasenya = ? WHERE id = ?', [password_hash(post('contrasenya'), PASSWORD_DEFAULT), $u['id']]);
            autentica_sessio((int)$u['id']);
            audita('canvi_contrasenya', 'usuaris', $u['id']);
            avis(t('contrasenya_canviada'));
            redirigeix('perfil');
        }
    }
    $rols = array_map('nom_rol', q('SELECT r.* FROM rols r JOIN usuari_rols ur ON ur.rol_id = r.id WHERE ur.usuari_id = ?', [$u['id']])->fetchAll());
    $html = '<dl class="fitxa"><dt>' . h(t('camp.usuari')) . '</dt><dd>' . h($u['usuari']) . '</dd>'
        . '<dt>' . h(t('camp.rol')) . '</dt><dd>' . h(implode(', ', $rols) ?: '—') . '</dd></dl>';
    $html .= '<section class="fill"><header><h2>' . h(t('canvia_contrasenya')) . '</h2></header>';
    if ($error) {
        $html .= '<div class="avis error">' . h($error) . '</div>';
    }
    $html .= '<form method="post" class="formulari vertical estret">' . csrf_camp()
        . '<div class="camp"><label>' . h(t('camp.contrasenya_actual')) . '</label><input type="password" name="actual" required autocomplete="current-password"></div>'
        . '<div class="camp"><label>' . h(t('camp.contrasenya_nova')) . '</label><input type="password" name="contrasenya" required minlength="' . MIN_CONTRASENYA . '" autocomplete="new-password"></div>'
        . '<div class="camp"><label>' . h(t('camp.contrasenya2')) . '</label><input type="password" name="contrasenya2" required autocomplete="new-password"></div>'
        . '<button>' . h(t('desa')) . '</button></form></section>';
    pagina(t('menu.perfil'), $html);
}
