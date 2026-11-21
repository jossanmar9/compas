<?php
// Fulls d'Excel (.xlsx) sense extensions de PHP: el zip i l'XML es fan ací mateix.
// Només el que Compàs necessita: textos, números, fórmules, estils, cel·les combinades,
// llistes desplegables, format condicional i preparació per a imprimir.
// Copyright (C) 2026 José Vicente Sanjuan
// SPDX-License-Identifier: AGPL-3.0-or-later

/** Un fitxer zip amb els fitxers donats (nom => contingut), comprimits amb deflate */
function zip_crea(array $fitxers): string
{
    $dades = '';
    $central = '';
    $n = 0;
    // Data i hora en format MS-DOS
    $t = getdate();
    $hora = ($t['hours'] << 11) | ($t['minutes'] << 5) | intdiv($t['seconds'], 2);
    $dia = (($t['year'] - 1980) << 9) | ($t['mon'] << 5) | $t['mday'];
    foreach ($fitxers as $nom => $contingut) {
        $comprimit = gzdeflate($contingut, 6);
        $crc = crc32($contingut);
        $capcalera = pack('vvvvvVVVvv', 20, 0x0800, 8, $hora, $dia, $crc, strlen($comprimit), strlen($contingut), strlen($nom), 0);
        $central .= "PK\x01\x02" . pack('vvvvvvVVVvvvvvVV', 20, 20, 0x0800, 8, $hora, $dia, $crc, strlen($comprimit), strlen($contingut),
            strlen($nom), 0, 0, 0, 0, 0, strlen($dades)) . $nom;
        $dades .= "PK\x03\x04" . $capcalera . $nom . $comprimit;
        $n++;
    }
    return $dades . $central . "PK\x05\x06" . pack('vvvvVVv', 0, 0, $n, $n, strlen($central), strlen($dades), 0);
}

/** Lletra de columna d'Excel: 1 → A, 27 → AA */
function xlsx_columna(int $n): string
{
    $s = '';
    for (; $n > 0; $n = intdiv($n - 1, 26)) {
        $s = chr(65 + ($n - 1) % 26) . $s;
    }
    return $s;
}

function xlsx_text(string $s): string
{
    // Fora els caràcters de control que l'XML no admet
    return htmlspecialchars(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $s), ENT_QUOTES | ENT_XML1, 'UTF-8');
}

/** Nom de full vàlid i únic: com a màxim 31 caràcters i sense : \ / ? * [ ] */
function xlsx_nom_full(string $nom, array $usats): string
{
    $base = mb_substr(trim(preg_replace('/[:\\\\\/?*\[\]]+/', ' ', $nom)) ?: 'Full', 0, 31);
    $nom = $base;
    for ($i = 2; in_array(mb_strtolower($nom), array_map('mb_strtolower', $usats), true); $i++) {
        $nom = mb_substr($base, 0, 31 - strlen(" ($i)")) . " ($i)";
    }
    return $nom;
}

/**
 * Un llibre d'Excel. Cada full és un array amb:
 *  - nom: nom de la pestanya
 *  - cel·les: [fila][columna] => [valor, estil] o [valor, estil, 'f'] per a una fórmula (el valor és la fórmula,
 *    i opcionalment el resultat inicial en la 4a posició)
 *  - amplades: [columna => amplada], altures: [fila => altura]
 *  - combinades: ['A1:C1', …], llistes: [['D6:W40', ['X'], títol, missatge, missatge d'entrada]]
 *  - condicional: [['D6:W40', 'X', dxf]] (pinta les cel·les iguals al text)
 *  - fixa: [columna, fila] de la primera cel·la que es desplaça; titols: '4:5' (files que es repeteixen en imprimir)
 *  - horitzontal: true per a imprimir en apaïsat; peu: text del peu de pàgina
 * Els estils són els índexs de xlsx_estils().
 */
function xlsx_llibre(array $fulls, string $titol = ''): string
{
    $fitxers = [];
    $tipusFulls = '';
    $relacions = '';
    $llistaFulls = '';
    $noms = '';
    foreach (array_values($fulls) as $i => $full) {
        $n = $i + 1;
        $fitxers["xl/worksheets/sheet$n.xml"] = xlsx_full($full);
        $tipusFulls .= '<Override PartName="/xl/worksheets/sheet' . $n . '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
        $relacions .= '<Relationship Id="rId' . $n . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet' . $n . '.xml"/>';
        $llistaFulls .= '<sheet name="' . xlsx_text($full['nom']) . '" sheetId="' . $n . '" r:id="rId' . $n . '"/>';
        if (!empty($full['titols'])) {
            [$a, $b] = explode(':', $full['titols']);
            $noms .= '<definedName name="_xlnm.Print_Titles" localSheetId="' . $i . '">\'' . xlsx_text(str_replace("'", "''", $full['nom'])) . "'!\$$a:\$$b</definedName>";
        }
    }
    $nFulls = count($fulls);
    $fitxers['xl/workbook.xml'] = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
        . '<bookViews><workbookView/></bookViews><sheets>' . $llistaFulls . '</sheets>'
        . ($noms ? '<definedNames>' . $noms . '</definedNames>' : '') . '<calcPr calcId="191029" fullCalcOnLoad="1"/></workbook>';
    $fitxers['xl/_rels/workbook.xml.rels'] = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' . $relacions
        . '<Relationship Id="rId' . ($nFulls + 1) . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>';
    $fitxers['xl/styles.xml'] = xlsx_estils();
    $fitxers['docProps/core.xml'] = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/"'
        . ' xmlns:dcterms="http://purl.org/dc/terms/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
        . '<dc:title>' . xlsx_text($titol) . '</dc:title><dc:creator>Compàs</dc:creator>'
        . '<dcterms:created xsi:type="dcterms:W3CDTF">' . gmdate('Y-m-d\TH:i:s\Z') . '</dcterms:created></cp:coreProperties>';
    $fitxers['docProps/app.xml'] = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties"><Application>Compàs</Application></Properties>';
    $fitxers['_rels/.rels'] = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
        . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
        . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>'
        . '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/></Relationships>';
    $fitxers['[Content_Types].xml'] = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
        . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
        . '<Default Extension="xml" ContentType="application/xml"/>'
        . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
        . $tipusFulls
        . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
        . '<Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>'
        . '<Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/></Types>';
    // [Content_Types].xml ha d'anar primer
    $ordre = ['[Content_Types].xml', '_rels/.rels', 'docProps/core.xml', 'docProps/app.xml', 'xl/workbook.xml', 'xl/_rels/workbook.xml.rels', 'xl/styles.xml'];
    $ordenats = [];
    foreach ($ordre as $f) {
        $ordenats[$f] = $fitxers[$f];
        unset($fitxers[$f]);
    }
    return zip_crea($ordenats + $fitxers);
}

/** Estils: 0 normal, 1 títol, 2 subtítol, 3 capçalera, 4 nom, 5 número centrat, 6 data per omplir,
 *  7 casella per a marcar, 8 etiqueta del total, 9 número del total, 10 percentatge, 11 etiqueta «Data» */
function xlsx_estils(): string
{
    $vora = '<left style="thin"><color rgb="FF9AA5B5"/></left><right style="thin"><color rgb="FF9AA5B5"/></right>'
        . '<top style="thin"><color rgb="FF9AA5B5"/></top><bottom style="thin"><color rgb="FF9AA5B5"/></bottom><diagonal/>';
    $xf = fn(int $font, int $fill, int $border, int $num = 0, string $align = '') => '<xf numFmtId="' . $num . '" fontId="' . $font . '" fillId="' . $fill
        . '" borderId="' . $border . '" xfId="0"' . ($num ? ' applyNumberFormat="1"' : '') . ($font ? ' applyFont="1"' : '') . ($fill ? ' applyFill="1"' : '')
        . ($border ? ' applyBorder="1"' : '') . ($align ? ' applyAlignment="1"><alignment ' . $align . '/></xf>' : '/>');
    $centre = 'horizontal="center" vertical="center"';
    return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
        . '<numFmts count="1"><numFmt numFmtId="164" formatCode="dd/mm"/></numFmts>'
        . '<fonts count="5">'
        . '<font><sz val="11"/><name val="Calibri"/><family val="2"/></font>'
        . '<font><b/><sz val="15"/><color rgb="FF14284B"/><name val="Calibri"/><family val="2"/></font>'
        . '<font><i/><sz val="10"/><color rgb="FF56607A"/><name val="Calibri"/><family val="2"/></font>'
        . '<font><b/><sz val="10"/><name val="Calibri"/><family val="2"/></font>'
        . '<font><b/><sz val="12"/><color rgb="FF1E7D4F"/><name val="Calibri"/><family val="2"/></font>'
        . '</fonts>'
        . '<fills count="5"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill>'
        . '<fill><patternFill patternType="solid"><fgColor rgb="FFE8EDF5"/><bgColor indexed="64"/></patternFill></fill>'
        . '<fill><patternFill patternType="solid"><fgColor rgb="FFFFF6D6"/><bgColor indexed="64"/></patternFill></fill>'
        . '<fill><patternFill patternType="solid"><fgColor rgb="FFF5F7FB"/><bgColor indexed="64"/></patternFill></fill></fills>'
        . '<borders count="2"><border><left/><right/><top/><bottom/><diagonal/></border><border>' . $vora . '</border></borders>'
        . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
        . '<cellXfs count="12">'
        . $xf(0, 0, 0)
        . $xf(1, 0, 0, 0, 'vertical="center"')
        . $xf(2, 0, 0, 0, 'vertical="center"')
        . $xf(3, 2, 1, 0, $centre . ' wrapText="1"')
        . $xf(0, 0, 1, 0, 'horizontal="left" vertical="center" indent="1"')
        . $xf(0, 0, 1, 0, $centre)
        . $xf(3, 3, 1, 164, $centre)
        . $xf(4, 0, 1, 0, $centre)
        . $xf(3, 4, 1, 0, 'horizontal="right" vertical="center" indent="1"')
        . $xf(3, 4, 1, 0, $centre)
        . $xf(0, 0, 1, 9, $centre)
        . $xf(3, 3, 1, 0, 'horizontal="right" vertical="center" indent="1"')
        . '</cellXfs>'
        . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
        . '<dxfs count="1"><dxf><font><b/><color rgb="FF1E7D4F"/></font><fill><patternFill><bgColor rgb="FFDDF3E6"/></patternFill></fill></dxf></dxfs>'
        . '</styleSheet>';
}

function xlsx_full(array $f): string
{
    $files = '';
    $maxCol = 1;
    $maxFila = 1;
    ksort($f['cel·les']);
    foreach ($f['cel·les'] as $fila => $cols) {
        ksort($cols);
        $altura = isset($f['altures'][$fila]) ? ' ht="' . $f['altures'][$fila] . '" customHeight="1"' : '';
        $files .= '<row r="' . $fila . '"' . $altura . '>';
        foreach ($cols as $col => $c) {
            $ref = xlsx_columna($col) . $fila;
            $estil = (int)($c[1] ?? 0);
            $valor = $c[0];
            if (($c[2] ?? null) === 'f') {
                $resultat = $c[3] ?? null;
                $files .= '<c r="' . $ref . '" s="' . $estil . '"' . (is_string($resultat) ? ' t="str"' : '') . '><f>' . xlsx_text(ltrim($valor, '='))
                    . '</f>' . ($resultat !== null ? '<v>' . xlsx_text((string)$resultat) . '</v>' : '') . '</c>';
            } elseif ($valor === null || $valor === '') {
                $files .= '<c r="' . $ref . '" s="' . $estil . '"/>';
            } elseif (is_int($valor) || is_float($valor)) {
                $files .= '<c r="' . $ref . '" s="' . $estil . '"><v>' . $valor . '</v></c>';
            } else {
                $files .= '<c r="' . $ref . '" s="' . $estil . '" t="inlineStr"><is><t xml:space="preserve">' . xlsx_text((string)$valor) . '</t></is></c>';
            }
            $maxCol = max($maxCol, $col);
        }
        $files .= '</row>';
        $maxFila = max($maxFila, $fila);
    }
    $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
        . '<sheetPr><pageSetUpPr fitToPage="1"/></sheetPr>'
        . '<dimension ref="A1:' . xlsx_columna($maxCol) . $maxFila . '"/>';
    if (!empty($f['fixa'])) {
        [$c, $r] = $f['fixa'];
        $xml .= '<sheetViews><sheetView workbookViewId="0" zoomScale="110"><pane xSplit="' . ($c - 1) . '" ySplit="' . ($r - 1) . '" topLeftCell="'
            . xlsx_columna($c) . $r . '" activePane="bottomRight" state="frozen"/><selection pane="bottomRight" activeCell="' . xlsx_columna($c) . $r
            . '" sqref="' . xlsx_columna($c) . $r . '"/></sheetView></sheetViews>';
    } else {
        $xml .= '<sheetViews><sheetView workbookViewId="0"/></sheetViews>';
    }
    $xml .= '<sheetFormatPr defaultRowHeight="15"/>';
    if (!empty($f['amplades'])) {
        ksort($f['amplades']);
        $xml .= '<cols>';
        foreach ($f['amplades'] as $col => $amplada) {
            $xml .= '<col min="' . $col . '" max="' . $col . '" width="' . $amplada . '" customWidth="1"/>';
        }
        $xml .= '</cols>';
    }
    $xml .= '<sheetData>' . $files . '</sheetData>';
    if (!empty($f['combinades'])) {
        $xml .= '<mergeCells count="' . count($f['combinades']) . '">';
        foreach ($f['combinades'] as $rang) {
            $xml .= '<mergeCell ref="' . $rang . '"/>';
        }
        $xml .= '</mergeCells>';
    }
    $prioritat = 1;
    foreach ($f['condicional'] ?? [] as [$rang, $text, $dxf]) {
        $primera = explode(':', $rang)[0];
        $xml .= '<conditionalFormatting sqref="' . $rang . '"><cfRule type="containsText" dxfId="' . $dxf . '" priority="' . $prioritat++
            . '" operator="containsText" text="' . xlsx_text($text) . '"><formula>NOT(ISERROR(SEARCH("' . xlsx_text($text) . '",' . $primera
            . ')))</formula></cfRule></conditionalFormatting>';
    }
    if (!empty($f['llistes'])) {
        $xml .= '<dataValidations count="' . count($f['llistes']) . '">';
        foreach ($f['llistes'] as [$rang, $valors, $titolError, $error, $entrada]) {
            $xml .= '<dataValidation type="list" allowBlank="1" showInputMessage="1" showErrorMessage="1" errorTitle="' . xlsx_text($titolError)
                . '" error="' . xlsx_text($error) . '" promptTitle="' . xlsx_text($titolError) . '" prompt="' . xlsx_text($entrada) . '" sqref="' . $rang . '">'
                . '<formula1>"' . xlsx_text(implode(',', $valors)) . '"</formula1></dataValidation>';
        }
        $xml .= '</dataValidations>';
    }
    $xml .= '<printOptions horizontalCentered="1"/><pageMargins left="0.4" right="0.4" top="0.5" bottom="0.6" header="0.3" footer="0.3"/>'
        . '<pageSetup paperSize="9" orientation="' . (!empty($f['horitzontal']) ? 'landscape' : 'portrait') . '" fitToWidth="1" fitToHeight="0"/>'
        . '<headerFooter><oddFooter>' . xlsx_text('&L' . str_replace('&', '&&', $f['peu'] ?? '') . '&R&P / &N') . '</oddFooter></headerFooter>'
        . '</worksheet>';
    return $xml;
}
