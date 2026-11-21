<?php
// Omple una instal·lació nova de Compàs amb una entitat gran i inventada del tipus que s'ha triat en configurar-la
// (per a provar el programa amb molt de volum i per a les captures de la web). Les dades són sempre les mateixes.
//
// Ús: primer, configura una instal·lació nova des del programa (sense dades d'exemple); després
//     COMPAS_DADES=carpeta-de-dades php eines/entitat-gran.php [data]
// Amb una data (AAAA-MM-DD), les dades es fan com si hui fora eixe dia (les captures de la web fan servir el 22-10-2026).
// També posa el nom, el CIF i l'adreça de l'entitat inventada. Des del programa es pot fer el mateix en la configuració
// inicial, amb «Dades d'exemple: entitat gran».
// Copyright (C) 2026 José Vicente Sanjuan
// SPDX-License-Identifier: AGPL-3.0-or-later
declare(strict_types=1);

require dirname(__DIR__) . '/src/bootstrap.php';
require SRC_DIR . '/entitats.php';
require SRC_DIR . '/crud.php';
foreach (glob(SRC_DIR . '/pages/*.php') as $f) {
    require_once $f;
}
$avui = $argv[1] ?? null;
if ($avui !== null && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $avui)) {
    fwrite(STDERR, "La data ha de ser AAAA-MM-DD.\n");
    exit(1);
}
session_id('entitat-gran');
@session_start();
$_SESSION['usuari_id'] = (int)valor('SELECT MIN(id) FROM usuaris');
try {
    carrega_exemple_gran($avui, true);
} catch (RuntimeException $e) {
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);
}
printf("%s (%s): %d persones, %d socis actius, %d alumnes actius, %d membres de grups, %d rebuts.\n", config('societat_nom'), nom_tipus(),
    valor('SELECT COUNT(*) FROM persones'), valor("SELECT COUNT(*) FROM socis WHERE estat = 'actiu'"), valor("SELECT COUNT(*) FROM alumnes WHERE estat = 'actiu'"),
    valor("SELECT COUNT(*) FROM membres WHERE estat = 'actiu'"), valor('SELECT COUNT(*) FROM rebuts'));
