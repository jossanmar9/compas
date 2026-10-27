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
    $ip = (string)($_SERVER['REMOTE_ADDR'] ?? '');
    return in_array($ip, ['127.0.0.1', '::1'], true)
        || (mode_lan() && ip_privada((string)getenv('COMPAS_LAN_IP'))
            && $ip === getenv('COMPAS_LAN_IP') && $ip === ($_SERVER['SERVER_ADDR'] ?? $_SERVER['SERVER_NAME'] ?? ''));
}

function comprova_xarxa(): void
{
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
