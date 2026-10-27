<?php
// Control local del llançador: obri l'assistent, sense executar ordres del navegador.
// SPDX-License-Identifier: AGPL-3.0-or-later

function control_xarxa_disponible(): bool
{
    return getenv('COMPAS_CONTROL_XARXA') === '1' && strlen((string)getenv('COMPAS_TOKEN')) >= 32;
}

function pagina_xarxa_local(): void
{
    requereix('xarxa.configura');
    if (!peticio_del_servidor()) {
        http_response_code(403);
        pagina(t('xarxa.titol'), '<div class="avis info">' . h(t('xarxa.servidor')) . '</div>');
        return;
    }
    if (!control_xarxa_disponible()) {
        http_response_code(409);
        pagina(t('xarxa.titol'), '<p>' . h(t('xarxa.sense_control')) . '</p>');
        return;
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $ordre = 'xarxa|' . time() . '|' . bin2hex(random_bytes(16));
        $missatge = $ordre . "\n" . hash_hmac('sha256', $ordre, (string)getenv('COMPAS_TOKEN')) . "\n";
        $tmp = tempnam(DADES_DIR, 'control-');
        try {
            if (!$tmp || file_put_contents($tmp, $missatge, LOCK_EX) !== strlen($missatge)) {
                throw new RuntimeException(t('xarxa.error'));
            }
            chmod($tmp, 0600);
            if (!rename($tmp, DADES_DIR . DIRECTORY_SEPARATOR . 'control-xarxa.txt')) {
                throw new RuntimeException(t('xarxa.error'));
            }
        } finally {
            if ($tmp && is_file($tmp)) { unlink($tmp); }
        }
        audita('xarxa_assistent');
        pagina(t('xarxa.titol'), '<div class="avis info">' . h(t('xarxa.obert')) . '</div><p><a class="boto" href="' . h(url('inici')) . '">' . h(t('menu.inici')) . '</a></p>');
        return;
    }
    pagina(t('xarxa.titol'), '<p>' . h(t('xarxa.text')) . '</p><form method="post">' . csrf_camp() . '<button>' . h(t('xarxa.obri')) . '</button></form>');
}
