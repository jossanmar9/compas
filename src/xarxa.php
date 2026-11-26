<?php
// Compàs només funciona en este ordinador: no s'hi pot entrar des de la xarxa ni des d'Internet.
// SPDX-License-Identifier: AGPL-3.0-or-later
declare(strict_types=1);

/** La petició ve d'este mateix ordinador */
function peticio_local(): bool
{
    return in_array((string)($_SERVER['REMOTE_ADDR'] ?? ''), ['127.0.0.1', '::1'], true);
}

function comprova_xarxa(): void
{
    // Només es responen les peticions adreçades a este ordinador. Així una pàgina web maliciosa no pot fer-se passar
    // per Compàs canviant el seu DNS cap a 127.0.0.1 (DNS rebinding). Altres noms: variable COMPAS_HOSTS, separats per comes.
    $host = strtolower(preg_replace('/:\d+$/', '', (string)($_SERVER['HTTP_HOST'] ?? '')));
    $permesos = array_merge(['127.0.0.1', 'localhost', '[::1]'], array_filter(array_map('trim', explode(',', strtolower((string)getenv('COMPAS_HOSTS'))))));
    if (!in_array($host, $permesos, true)) {
        http_response_code(421);
        exit('421');
    }
    if (!peticio_local()) {
        http_response_code(403);
        exit('403');
    }
    comprova_app();
}

/**
 * A Android, qualsevol altra app del mòbil podria connectar-se a 127.0.0.1. Per això l'app de Compàs posa
 * al seu WebView una galeta amb un secret que canvia cada vegada que arranca (COMPAS_APP_TOKEN), i sense
 * ella no es respon res.
 */
function comprova_app(): void
{
    $token = (string)getenv('COMPAS_APP_TOKEN');
    if ($token !== '' && !hash_equals($token, (string)($_COOKIE['compas_app'] ?? ''))) {
        http_response_code(403);
        exit('403');
    }
}
