<?php
// Compàs a Android: el mateix programa, que l'app arranca dins del mòbil (variable COMPAS_ANDROID=1).
// Copyright (C) 2026 José Vicente Sanjuan
// SPDX-License-Identifier: AGPL-3.0-or-later
//
// Diferències amb l'ordinador:
//  - El WebView d'Android no pot baixar la resposta d'un formulari (POST): perd el cos i només sap l'adreça.
//    Per això, quan una pàgina envia un fitxer (CSV, còpia, XML, PDF…), es guarda un moment a dades/baixades
//    i es redirigix a ?r=baixada&f=…, que l'app torna a demanar i desa a la carpeta Baixades del mòbil.
//  - Les còpies externes van, si no s'ha dit una altra cosa, a Documents/Compàs del mòbil (fora de l'app:
//    no es perden si es desinstal·la i es poden passar a l'ordinador).
//  - Recuperar l'accés sense codi: en lloc de crear un fitxer a la carpeta de dades (que al mòbil no es veu),
//    es crea des de la mateixa app. Només l'app pot parlar amb el servidor (vegeu comprova_app a xarxa.php),
//    així que és el mateix que tindre l'ordinador davant.
declare(strict_types=1);

const BAIXADES_MINUTS = 10;

function es_android(): bool
{
    return getenv('COMPAS_ANDROID') === '1';
}

function carpeta_baixades(): string
{
    $d = DADES_DIR . DIRECTORY_SEPARATOR . 'baixades';
    if (!is_dir($d)) {
        mkdir($d, 0770, true);
    }
    return $d;
}

/** Arreplega l'eixida de cada pàgina per a convertir els fitxers en baixades */
function android_captura_baixades(): void
{
    if (es_android()) {
        ob_start('android_baixada');
    }
}

/** Gestor de l'eixida: si la resposta és un fitxer, el guarda i redirigix a ?r=baixada */
function android_baixada(string $cos, int $fase): string
{
    if (!($fase & PHP_OUTPUT_HANDLER_FINAL) || !empty($GLOBALS['android_sense_captura']) || headers_sent()) {
        return $cos;
    }
    $disposicio = $tipus = '';
    foreach (headers_list() as $h) {
        [$nom, $valor] = array_map('trim', explode(':', $h, 2) + [1 => '']);
        if (strcasecmp($nom, 'Content-Disposition') === 0) {
            $disposicio = $valor;
        } elseif (strcasecmp($nom, 'Content-Type') === 0) {
            $tipus = $valor;
        }
    }
    // Les pàgines i les imatges es mostren com sempre
    if ($disposicio === '' || preg_match('#^(text/html|image/)#i', $tipus)) {
        return $cos;
    }
    try {
        $id = bin2hex(random_bytes(16));
        $d = carpeta_baixades();
        neteja_baixades($d);
        file_put_contents($d . DIRECTORY_SEPARATOR . $id, $cos);
        file_put_contents($d . DIRECTORY_SEPARATOR . $id . '.json', json_encode([
            'disposicio' => preg_replace('/^inline/i', 'attachment', $disposicio),
            'tipus' => $tipus ?: 'application/octet-stream',
            'usuari' => (int)($_SESSION['usuari_id'] ?? 0),
        ]));
    } catch (Throwable $e) {
        error_log('Baixada a Android: ' . $e->getMessage());
        return $cos;
    }
    header_remove('Content-Disposition');
    header_remove('Content-Length');
    header('Content-Type: text/html; charset=UTF-8');
    header('Location: ?r=baixada&f=' . $id, true, 303);
    return '';
}

function neteja_baixades(string $d): void
{
    foreach (glob($d . DIRECTORY_SEPARATOR . '*') ?: [] as $f) {
        if (is_file($f) && filemtime($f) < time() - BAIXADES_MINUTS * 60) {
            @unlink($f);
        }
    }
}

/** Torna un fitxer guardat per android_baixada (l'app el desa a Baixades) */
function serveix_baixada(): never
{
    $GLOBALS['android_sense_captura'] = true;
    $id = (string)get('f');
    $f = carpeta_baixades() . DIRECTORY_SEPARATOR . $id;
    $meta = preg_match('/^[0-9a-f]{32}$/', $id) && is_file($f) && is_file($f . '.json') ? json_decode((string)file_get_contents($f . '.json'), true) : null;
    if (!is_array($meta) || (int)$meta['usuari'] !== (int)($_SESSION['usuari_id'] ?? 0)) {
        http_response_code(404);
        exit('404');
    }
    header('Content-Type: ' . $meta['tipus']);
    header('Content-Disposition: ' . $meta['disposicio']);
    header('Content-Length: ' . filesize($f));
    readfile($f);
    exit;
}

/** Carpeta de les còpies externes al mòbil (Documents/Compàs), la primera vegada que s'obri a Android */
function android_carpeta_externa_inicial(): void
{
    if (!es_android() || config('copia_externa_android') !== null) {
        return;
    }
    $e = rtrim((string)getenv('COMPAS_CARPETA_EXTERNA'), '/');
    if ($e !== '' && config('copia_externa') === null) {
        if (!is_dir($e)) {
            @mkdir($e, 0770, true);
        }
        if (is_dir($e) && is_writable($e)) {
            desa_config('copia_externa', $e);
        }
    }
    desa_config('copia_externa_android', '1');
}
