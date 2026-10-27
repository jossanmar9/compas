<?php
// Configuració explícita de la xarxa; no es confia en capçalera X-Forwarded-*.
// SPDX-License-Identifier: AGPL-3.0-or-later
declare(strict_types=1);

function mode_lan(): bool
{
    return getenv('COMPAS_LAN') === '1';
}

function ip_privada(string $ip): bool
{
    if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        return false;
    }
    $p = array_map('intval', explode('.', $ip));
    return $p[0] === 10 || ($p[0] === 172 && $p[1] >= 16 && $p[1] <= 31) || ($p[0] === 192 && $p[1] === 168);
}

function peticio_del_servidor(): bool
{
    if (getenv('COMPAS_PROXY_TOKEN')) {
        return !empty($_SERVER['COMPAS_PROXY_VALIDAT']) && !empty($_SERVER['COMPAS_CLIENT_LOCAL']);
    }
    $ip = (string)($_SERVER['REMOTE_ADDR'] ?? '');
    return in_array($ip, ['127.0.0.1', '::1'], true)
        || (mode_lan() && ip_privada((string)getenv('COMPAS_LAN_IP'))
            && $ip === getenv('COMPAS_LAN_IP') && $ip === ($_SERVER['SERVER_ADDR'] ?? $_SERVER['SERVER_NAME'] ?? ''));
}

function comprova_xarxa(): void
{
    // El PHP compartit només rep peticions del proxy HTTPS local autenticat.
    // No s'accepten X-Forwarded-* ni metadades enviades directament pel navegador.
    $secret = (string)getenv('COMPAS_PROXY_TOKEN');
    if ($secret !== '') {
        $origen = (string)($_SERVER['REMOTE_ADDR'] ?? '');
        $client = (string)($_SERVER['HTTP_X_COMPAS_CLIENT'] ?? '');
        if (!in_array($origen, ['127.0.0.1', '::1'], true)
            || strlen($secret) < 32 || !hash_equals($secret, (string)($_SERVER['HTTP_X_COMPAS_PROXY_TOKEN'] ?? ''))
            || !(ip_privada($client) || in_array($client, ['127.0.0.1', '::1'], true))) {
            http_response_code(403);
            exit('403');
        }
        $nom = (string)getenv('COMPAS_NOM_LAN');
        if (!preg_match('/^[a-z0-9](?:[a-z0-9-]{0,60}[a-z0-9])?\.local$/D', $nom)
            || strtolower(preg_replace('/:\d+$/', '', (string)($_SERVER['HTTP_HOST'] ?? ''))) !== $nom) {
            http_response_code(421);
            exit('421');
        }
        $_SERVER['REMOTE_ADDR'] = $client;
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['COMPAS_PROXY_VALIDAT'] = true;
        $_SERVER['COMPAS_CLIENT_LOCAL'] = ($_SERVER['HTTP_X_COMPAS_LOCAL'] ?? '') === '1';
        return;
    }
    $host = strtolower(preg_replace('/:\d+$/', '', (string)($_SERVER['HTTP_HOST'] ?? '')));
    $permesos = array_merge(['127.0.0.1', 'localhost', '[::1]'], array_filter(array_map('trim', explode(',', strtolower((string)getenv('COMPAS_HOSTS'))))));
    if (mode_lan()) {
        $ip = (string)getenv('COMPAS_LAN_IP');
        if (!ip_privada($ip)) {
            http_response_code(503);
            exit('LAN: configura COMPAS_LAN_IP amb una IPv4 privada.');
        }
        $permesos[] = $ip;
    }
    if (!in_array($host, $permesos, true)) {
        http_response_code(421);
        exit('421');
    }
    if (!peticio_del_servidor() && (!mode_lan() || !ip_privada((string)($_SERVER['REMOTE_ADDR'] ?? '')))) {
        http_response_code(403);
        exit('403');
    }
}
