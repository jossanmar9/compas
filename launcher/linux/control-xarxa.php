<?php
// Consumix una ordre local autenticada; mai executa text aportat pel client.
// SPDX-License-Identifier: AGPL-3.0-or-later
if (PHP_SAPI !== 'cli' || empty($argv[1])) { exit(1); }
$fitxer = rtrim($argv[1], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'control-xarxa.txt';
$llegint = $fitxer . '.' . bin2hex(random_bytes(8));
if (!is_file($fitxer) || !@rename($fitxer, $llegint)) { exit(1); }
$valid = false;
try {
    $linies = filesize($llegint) <= 256 ? explode("\n", trim((string)file_get_contents($llegint))) : [];
    $secret = (string)getenv('COMPAS_TOKEN');
    if (count($linies) === 2 && strlen($secret) >= 32) {
        $ordre = rtrim($linies[0], "\r");
        $valid = preg_match('/\Axarxa\|([0-9]{10})\|[a-f0-9]{32}\z/', $ordre, $m)
            && abs(time() - (int)$m[1]) <= 30
            && hash_equals(hash_hmac('sha256', $ordre, $secret), rtrim($linies[1], "\r"));
    }
} finally { unlink($llegint); }
exit($valid ? 0 : 1);
