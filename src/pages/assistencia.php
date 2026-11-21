<?php
// Fulls d'assistència en Excel: un full per agrupació (banda, coral, equip…) amb els membres,
// una fila dalt per a escriure la data de cada assaig i caselles per a marcar amb X qui ha vingut.
// Copyright (C) 2026 José Vicente Sanjuan
// SPDX-License-Identifier: AGPL-3.0-or-later

const ASSISTENCIA_COLUMNES = 20;   // assajos per full (es pot canviar amb ?columnes=)
const ASSISTENCIA_FILES_BUIDES = 3; // files en blanc per a qui s'incorpore després

/** Membres d'alta d'una agrupació, per instrument o veu i per cognoms */
function membres_assistencia(int $agrupacio): array
{
    return q("SELECT p.nom, p.cognoms, t.instrument_veu, t.funcio FROM membres t JOIN persones p ON p.id = t.persona_id
        WHERE t.agrupacio_id = ? AND t.estat = 'actiu' AND (t.data_baixa IS NULL OR t.data_baixa > date('now','localtime'))
        ORDER BY (COALESCE(t.instrument_veu, '') = ''), t.instrument_veu COLLATE ALFA, p.cognoms COLLATE ALFA, p.nom COLLATE ALFA", [$agrupacio])->fetchAll();
}

/** El full d'una agrupació, en el format de xlsx_llibre() */
function full_assistencia(array $agrupacio, array $membres, int $columnes, array $usats): array
{
    // Columnes: A núm., B nom, C instrument o veu, D… una per assaig, i al final el total i el percentatge
    $primera = 4;
    $ultima = $primera + $columnes - 1;
    $colTotal = $ultima + 1;
    $colPct = $ultima + 2;
    $lletra = fn(int $c) => xlsx_columna($c);
    $rangDates = '$' . $lletra($primera) . '$4:$' . $lletra($ultima) . '$4';
    $filaIni = 6;
    $files = array_merge($membres, array_fill(0, ASSISTENCIA_FILES_BUIDES, null));
    $filaFi = $filaIni + count($files) - 1;
    $filaTotal = $filaFi + 1;

    $c = [];
    $c[1][1] = [t('assist.titol') . ' · ' . $agrupacio['nom'], 1];
    $c[2][1] = [implode(' · ', array_filter([config('societat_nom'), t('assist.exercici', nom_exercici(any_exercici())),
        t('assist.instruccions')])), 2];
    // Fila 4: les dates que s'escriuen a mà; fila 5: capçaleres
    $c[4][1] = [t('assist.data'), 11];
    $c[4][2] = ['', 11];
    $c[4][3] = ['', 11];
    $c[5][1] = [t('assist.num'), 3];
    $c[5][2] = [t('ent1.membres'), 3];
    $c[5][3] = [t('camp.instrument_veu'), 3];
    for ($col = $primera; $col <= $ultima; $col++) {
        $c[4][$col] = ['', 6];
        $c[5][$col] = [$col - $primera + 1, 3];
    }
    $c[4][$colTotal] = [t('assist.total'), 3];
    $c[4][$colPct] = ['%', 3];
    $c[5][$colTotal] = ['', 3];
    $c[5][$colPct] = ['', 3];

    foreach ($files as $i => $m) {
        $fila = $filaIni + $i;
        $c[$fila][1] = [$m ? $i + 1 : '', 5];
        $c[$fila][2] = [$m ? nom_persona($m) : '', 4];
        $c[$fila][3] = [$m ? (string)($m['instrument_veu'] ?: ($m['funcio'] !== 'membre' ? t('opt.' . $m['funcio']) : '')) : '', 4];
        for ($col = $primera; $col <= $ultima; $col++) {
            $c[$fila][$col] = ['', 7];
        }
        $rang = $lletra($primera) . $fila . ':' . $lletra($ultima) . $fila;
        // Les files en blanc no mostren res fins que s'hi escriu un nom
        $c[$fila][$colTotal] = ["IF(\$B$fila=\"\",\"\",COUNTIF($rang,\"X\"))", 9, 'f', $m ? 0 : ''];
        $c[$fila][$colPct] = ["IF(OR(\$B$fila=\"\",COUNTA($rangDates)=0),\"\"," . $lletra($colTotal) . "$fila/COUNTA($rangDates))", 10, 'f', ''];
    }
    // Última fila: quants n'han vingut a cada assaig
    $c[$filaTotal][1] = [t('assist.assistents'), 8];
    $c[$filaTotal][2] = ['', 8];
    $c[$filaTotal][3] = ['', 8];
    for ($col = $primera; $col <= $ultima; $col++) {
        // Només es compta l'assaig quan té data
        $l = $lletra($col);
        $c[$filaTotal][$col] = ["IF($l\$4=\"\",\"\",COUNTIF($l$filaIni:$l$filaFi,\"X\"))", 9, 'f', ''];
    }
    $c[$filaTotal][$colTotal] = ['', 8];
    $c[$filaTotal][$colPct] = ['', 8];

    $amplades = [1 => 5, 2 => 30, 3 => 16, $colTotal => 7, $colPct => 7];
    for ($col = $primera; $col <= $ultima; $col++) {
        $amplades[$col] = 6.5;
    }
    $altures = [1 => 24, 4 => 22, 5 => 20];
    for ($fila = $filaIni; $fila <= $filaTotal; $fila++) {
        $altures[$fila] = 19;
    }
    $caselles = $lletra($primera) . $filaIni . ':' . $lletra($ultima) . $filaFi;
    return [
        'nom' => xlsx_nom_full($agrupacio['nom'], $usats),
        'cel·les' => $c,
        'amplades' => $amplades,
        'altures' => $altures,
        'combinades' => ['A1:' . $lletra($colPct) . '1', 'A2:' . $lletra($colPct) . '2', 'A4:C4', 'A' . $filaTotal . ':C' . $filaTotal,
            $lletra($colTotal) . '4:' . $lletra($colTotal) . '5', $lletra($colPct) . '4:' . $lletra($colPct) . '5'],
        'llistes' => [[$caselles, ['X'], t('assist.titol'), t('assist.error'), t('assist.entrada')]],
        'condicional' => [[$caselles, 'X', 0]],
        'fixa' => [$primera, $filaIni],
        'titols' => '4:5',
        'horitzontal' => true,
        'peu' => (config('societat_nom') ?: 'Compàs') . ' · ' . $agrupacio['nom'],
    ];
}

/** Descarrega els fulls d'assistència: d'una agrupació (?agrupacio=) o de totes les que estan d'alta */
function pagina_assistencia(): void
{
    requereix('agrupacions.view');
    requereix('dades.exporta');
    $columnes = (int)get('columnes', (string)ASSISTENCIA_COLUMNES);
    $columnes = $columnes >= 4 && $columnes <= 40 ? $columnes : ASSISTENCIA_COLUMNES;
    $id = (int)get('agrupacio');
    $agrupacions = $id
        ? q('SELECT * FROM agrupacions WHERE id = ?', [$id])->fetchAll()
        : q("SELECT * FROM agrupacions WHERE estat = 'actiu' ORDER BY nom COLLATE ALFA")->fetchAll();
    if (!$agrupacions) {
        avis(t('assist.sense'), 'error');
        redirigeix('llista', ['e' => 'agrupacions']);
    }
    $fulls = [];
    foreach ($agrupacions as $a) {
        $fulls[] = full_assistencia($a, membres_assistencia((int)$a['id']), $columnes, array_column($fulls, 'nom'));
    }
    $nom = $id ? $agrupacions[0]['nom'] : t('ent.agrupacions');
    $fitxer = 'assistencia-' . (trim(preg_replace('/[^a-z0-9]+/', '-', strtolower((string)iconv('UTF-8', 'ASCII//TRANSLIT', $nom))), '-') ?: 'agrupacions') . '.xlsx';
    $xlsx = xlsx_llibre($fulls, t('assist.titol') . ' · ' . $nom);
    audita('assistencia', 'agrupacions', $id ?: null, $nom);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $fitxer . '"');
    header('Content-Length: ' . strlen($xlsx));
    echo $xlsx;
    exit;
}

/** Botó per a descarregar el full (a la fitxa d'una agrupació o al llistat, per a totes) */
function boto_assistencia(?int $agrupacio = null): string
{
    if (!pot('dades.exporta')) {
        return '';
    }
    return '<a class="boto secundari" href="' . h(url('assistencia', $agrupacio ? ['agrupacio' => $agrupacio] : [])) . '">📊 '
        . h(t($agrupacio ? 'assist.boto' : 'assist.boto_totes')) . '</a>';
}
