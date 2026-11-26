<?php
// Importació de socis i alumnat amb IBAN des d'un full (sense servidor: crida les funcions directament)
// Copyright (C) 2026 José Vicente Sanjuan
// SPDX-License-Identifier: AGPL-3.0-or-later
require dirname(__DIR__, 2) . '/src/bootstrap.php';
require SRC_DIR . '/entitats.php'; require SRC_DIR . '/crud.php';
foreach (glob(SRC_DIR . '/pages/*.php') as $f) require $f;
inicia_sessio(); autentica_sessio(1);
$ok = function ($c, $m) { echo ($c ? 'ok: ' : 'FALLA: ') . $m . "\n"; };
// Assignació automàtica de columnes
$ok(endevina_camp('IBAN', 'socis') === 'iban', 'IBAN → iban');
$ok(endevina_camp('Número de cuenta', 'socis') === 'iban', 'Número de cuenta → iban (no número de soci)');
$ok(endevina_camp('Nº socio', 'socis') === 'numero', 'Nº socio continua sent el número');
$ok(endevina_camp('Fecha firma', 'socis') === 'data_mandat', 'Fecha firma → data_mandat');
// Socis
$files = [['Nom', 'Cognoms', 'Naixement', 'DNI', 'Telèfon', 'IBAN', 'Signatura'],
    ['Soci', 'Banc1', '1970-01-01', '11111111H', '600', 'ES91 2100 0418 4502 0005 1332', '2020-05-01'],
    ['Soci', 'Banc2', '1971-01-01', '22222222J', '600', '21000418450200051332', ''],
    ['Soci', 'Jove', '2015-01-01', '', '', 'ES9121000418450200051332', ''],
    ['Soci', 'Dolent', '1972-01-01', '33333333P', '600', 'ES12 3456', ''],
    ['Josep', 'Martínez Pastor', '', '', '', 'DE89370400440532013000', '']];
$imp = ['tipus' => 'socis', 'nom_fitxer' => 'prova.csv', 'capcalera' => true, 'files' => $files,
    'assignacio' => ['nom', 'cognoms', 'data_naixement', 'document', 'telefon', 'iban', 'data_mandat']];
$a = analitza_importacio($imp);
foreach ($a as $r) echo '   ', $r['n'], ' ', $r['estat'], ' ', implode(' | ', $r['avisos']), "\n";
$mateixa = []; foreach ($a as $i => $r) $mateixa[$i] = $r['persona_id'] ? 1 : 0;
$fet = executa_importacio($imp, $a, $mateixa);
print_r($fet);
$m = fn($nom) => fila("SELECT m.* FROM mandats m JOIN persones p ON p.id = m.persona_id WHERE p.cognoms = ? AND m.estat = 'actiu'", [$nom]);
$ok(($m('Banc1')['data_signatura'] ?? '') === '2020-05-01', 'soci amb IBAN i data de signatura');
$ok(($m('Banc2')['iban'] ?? '') === 'ES9121000418450200051332', 'soci amb CCC convertit');
$ok($m('Jove') === null, 'menor sense domiciliació');
$ok($m('Dolent') === null, 'IBAN incorrecte no s importa');
$ok($fet['domiciliacions_diferents'] === 1, 'Josep ja tenia domiciliació diferent: no es canvia');
// Alumnes amb el tutor
$files = [['Alumne', 'Naix', 'Tutor', 'DNI tutor', 'IBAN'], ['Nen Prova', '2016-03-03', 'Mare Prova', '44444444A', 'ES9121000418450200051332']];
$imp = ['tipus' => 'alumnes', 'nom_fitxer' => 'a.csv', 'capcalera' => true, 'files' => $files, 'assignacio' => ['nom_complet', 'data_naixement', 'tutor_nom_complet', 'tutor_document', 'iban']];
$a = analitza_importacio($imp); echo '   ', implode(' | ', $a[0]['avisos']), "\n";
$fet = executa_importacio($imp, $a, [0]);
$ok(($m('Prova')['persona_id'] ?? 0) == valor("SELECT id FROM persones WHERE nom = 'Mare'"), 'alumne: domiciliació per al tutor/a');

// 2.8 · Números de soci: cada soci importat sense número en rep un, i cap no es repeteix
$nums = array_column(q("SELECT s.numero FROM socis s JOIN persones p ON p.id = s.persona_id WHERE p.nom = 'Soci'")->fetchAll(), 'numero');
$ok(count($nums) === 4 && count(array_unique($nums)) === 4 && !in_array(null, $nums, true), 'socis importats sense número: en reben un cadascun ' . implode(',', $nums));
$ok((int)valor('SELECT COUNT(*) FROM (SELECT numero FROM socis WHERE numero IS NOT NULL GROUP BY numero HAVING COUNT(*) > 1)') === 0, 'cap número de soci repetit');
// Els números dels socis de baixa i dels esborrats no es tornen a donar
$seg = (int)seguent_numero_soci();
q("UPDATE socis SET estat = 'baixa', data_baixa = '2026-01-01' WHERE numero = ?", [(string)($seg - 1)]);
$ok((int)seguent_numero_soci() === $seg, 'el número d un soci de baixa no queda lliure');
q('DELETE FROM socis WHERE numero = ?', [(string)($seg - 1)]);
$ok((int)seguent_numero_soci() === $seg, 'ni el d un soci esborrat');
// Un número del full que ja té algú: se li dona el següent lliure, després dels que en porten
$ja = (string)valor("SELECT numero FROM socis WHERE numero IS NOT NULL ORDER BY id LIMIT 1");
$files = [['Nom', 'Cognoms', 'Número'], ['Numerat', 'Repetit', $ja], ['Numerat', 'Lliure', (string)($seg + 5)], ['Numerat', 'Sense', '']];
$imp = ['tipus' => 'socis', 'nom_fitxer' => 'n.csv', 'capcalera' => true, 'files' => $files, 'assignacio' => ['nom', 'cognoms', 'numero']];
$a = analitza_importacio($imp);
$ok(str_contains(implode(' ', $a[0]['avisos']), $ja), 'avisa del número repetit');
executa_importacio($imp, $a, [0, 0, 0]);
$num = fn($c) => valor("SELECT s.numero FROM socis s JOIN persones p ON p.id = s.persona_id WHERE p.cognoms = ?", [$c]);
$ok($num('Lliure') === (string)($seg + 5), 'el número lliure del full es respecta');
$ok($num('Repetit') !== $ja && (int)$num('Repetit') > $seg + 5 && (int)$num('Sense') > $seg + 5 && $num('Repetit') !== $num('Sense'), 'el repetit i el que no en porta reben els següents lliures');

// 2.8 · Fitxers de LibreOffice (.ods) i NIA
$files = llig_full(dirname(__DIR__) . '/fitxers/alumnes.ods', 'alumnes.ods');
$ok(count($files) === 4 && $files[1][1] === 'Ferrer  Vidal' && $files[1][2] === '2016-03-04' && $files[1][4] === '600123456' && $files[2][1] === 'Sorribes & Fills', '.ods: dates, números, & i files buides');
$ok(endevina_camp('NIA', 'alumnes') === 'nia' && endevina_camp('NIA', 'socis') === null, 'columna NIA reconeguda per a l alumnat');
$imp = ['tipus' => 'alumnes', 'nom_fitxer' => 'alumnes.ods', 'capcalera' => true, 'files' => $files, 'assignacio' => array_map(fn($c) => endevina_camp($c, 'alumnes'), $files[0])];
$a = analitza_importacio($imp);
executa_importacio($imp, $a, array_fill(0, count($a), 0));
$nia = fn($nom) => valor("SELECT a.nia FROM alumnes a JOIN persones p ON p.id = a.persona_id WHERE p.nom = ?", [$nom]);
$ok($nia('Pau') === '10999001' && $nia('Àngela') === '10999002' && $nia('Noa') === null, '.ods importat amb el NIA');
// Tornar a importar completa el NIA dels que no en tenien, i no el posa si ja és d'un altre
q("UPDATE alumnes SET nia = NULL WHERE persona_id = (SELECT id FROM persones WHERE nom = 'Àngela')");
$files[3][3] = '10999001';
$a = analitza_importacio(['files' => $files] + $imp);
$mateixa = []; foreach ($a as $i => $r) $mateixa[$i] = $r['persona_id'] ? 1 : 0;
$ok(str_contains(implode(' ', $a[2]['avisos']), '10999001'), 'avisa del NIA que ja té un altre alumne');
executa_importacio(['files' => $files] + $imp, $a, $mateixa);
$ok($nia('Àngela') === '10999002', 'completa el NIA d un alumne que ja hi era');
$ok($nia('Noa') === null, 'no posa un NIA que ja és d un altre');
