<?php
// Dades d'exemple (opcionals) per a provar el programa. Noms genèrics.
// Copyright (C) 2026 José Vicente Sanjuan
// SPDX-License-Identifier: AGPL-3.0-or-later

function carrega_dades_exemple(): void
{
    $any = (int)date('n') >= 9 ? (int)date('Y') : (int)date('Y') - 1;
    // Dades inventades: els adults tenen DNI i telèfon d'exemple, excepte un (per a veure l'avís de dades que falten)
    $comptador = 0;
    $persona = function (string $nom, string $cognoms, ?string $naix = null, bool $complet = true) use (&$comptador) {
        $comptador++;
        $adult = es_major_edat($naix);
        q('INSERT INTO persones(nom, cognoms, data_naixement, document, telefon, adreca, codi_postal, poblacio) VALUES(?, ?, ?, ?, ?, ?, ?, ?)', [
            $nom, $cognoms, $naix,
            $adult && $complet ? sprintf('%08dX', $comptador) : null,
            $adult && $complet ? sprintf('600 000 %03d', $comptador) : null,
            (idioma() === 'es' ? 'Calle Mayor, ' : 'Carrer Major, ') . $comptador,
            '12000',
            idioma() === 'es' ? 'Pueblo de Ejemplo' : 'Poble d\'Exemple',
        ]);
        return (int)db()->lastInsertId();
    };
    $id = fn() => (int)db()->lastInsertId();
    // Textos de les dades d'exemple en l'idioma triat en instal·lar
    $es = idioma() === 'es';
    $tr = fn(string $ca, string $esp) => $es ? $esp : $ca;

    q('INSERT INTO cursos(nom, data_inici, data_fi, actual) VALUES(?, ?, ?, 1)', ["$any/" . ($any + 1), "$any-09-15", ($any + 1) . '-06-20']);
    $curs = $id();
    foreach ($es ? ['1.er trimestre', '2.º trimestre', '3.er trimestre'] : ['1r trimestre', '2n trimestre', '3r trimestre'] as $i => $p) {
        q('INSERT INTO periodes(curs_id, nom, ordre) VALUES(?, ?, ?)', [$curs, $p, $i + 1]);
    }

    $assig = [];
    foreach ([['Llenguatge musical', 'Lenguaje musical', 'llenguatge'], ['Trompeta', 'Trompeta', 'instrument'], ['Clarinet', 'Clarinete', 'instrument'],
                 ['Percussió', 'Percusión', 'instrument'], ['Conjunt instrumental', 'Conjunto instrumental', 'conjunt']] as [$n, $nes, $tp]) {
        q('INSERT INTO assignatures(nom, tipus) VALUES(?, ?)', [$tr($n, $nes), $tp]);
        $assig[$n] = $id();
    }
    $aules = [];
    foreach (['Aula 1', 'Aula 2', $tr('Sala d\'assajos', 'Sala de ensayos')] as $i => $n) {
        q('INSERT INTO aules(nom, capacitat) VALUES(?, ?)', [$n, $i === 2 ? 60 : 12]);
        $aules[] = $id();
    }

    $profs = [];
    foreach ([1, 2] as $n) {
        $p = $persona('Professora' . $n, 'Exemple', (1975 + $n * 4) . '-05-1' . $n);
        q('INSERT INTO professors(persona_id, data_alta) VALUES(?, ?)', [$p, "$any-09-01"]);
        $profs[] = $id();
    }

    $grups = [];
    foreach ([
        ['Llenguatge musical', '1r A', 0, 0, [[1, '17:00', '18:00'], [3, '17:00', '18:00']]],
        ['Trompeta', 'Individual', 1, 1, [[2, '18:00', '19:00']]],
        ['Clarinet', 'Individual', 0, 1, [[4, '18:00', '19:00']]],
        ['Conjunt instrumental', 'Únic', 1, 2, [[5, '19:00', '20:30']]],
    ] as [$a, $n, $pr, $au, $hs]) {
        $n = $es ? strtr($n, ['1r A' => '1.º A', 'Únic' => 'Único']) : $n;
        q('INSERT INTO grups(curs_id, assignatura_id, nom, professor_id, aula_id) VALUES(?,?,?,?,?)', [$curs, $assig[$a], $n, $profs[$pr], $aules[$au]]);
        $g = $id();
        $grups[$a] = $g;
        foreach ($hs as [$dia, $hi, $hf]) {
            q('INSERT INTO horaris(grup_id, dia, hora_inici, hora_fi) VALUES(?,?,?,?)', [$g, $dia, $hi, $hf]);
        }
    }

    $tutors = [];
    foreach ([1, 2, 3] as $n) {
        $tutors[] = $persona('Tutor' . $n, 'Exemple', (1978 + $n * 3) . '-0' . ($n + 2) . '-2' . $n);
    }
    $alumnesPersona = [];
    foreach (range(1, 6) as $n) {
        $p = $persona('Alumne' . $n, 'Exemple', ($any - 8 - $n) . '-0' . (($n % 9) + 1) . '-1' . $n);
        $alumnesPersona[] = $p;
        q('INSERT INTO alumnes(persona_id, data_alta) VALUES(?, ?)', [$p, "$any-09-10"]);
        $al = $id();
        q('INSERT INTO tutories(alumne_id, tutor_id, tipus_relacio, principal) VALUES(?, ?, ?, 1)', [$al, $tutors[($n - 1) % 3], ['mare', 'pare', 'tutor_legal'][($n - 1) % 3]]);
        q('INSERT INTO matricules(alumne_id, curs_id, data) VALUES(?, ?, ?)', [$al, $curs, "$any-09-10"]);
        $m = $id();
        q('INSERT INTO matricula_grups(matricula_id, grup_id) VALUES(?, ?)', [$m, $grups['Llenguatge musical']]);
        q('INSERT INTO matricula_grups(matricula_id, grup_id) VALUES(?, ?)', [$m, $grups[$n % 2 ? 'Trompeta' : 'Clarinet']]);
        if ($n > 3) {
            q('INSERT INTO matricula_grups(matricula_id, grup_id) VALUES(?, ?)', [$m, $grups['Conjunt instrumental']]);
        }
        // Drets d'imatge: la majoria autoritzen, un no i un encara no ha tornat el full signat
        $consent = [1 => 'autoritza', 2 => 'autoritza', 3 => 'no_autoritza', 4 => 'autoritza', 5 => 'autoritza', 6 => 'pendent'][$n];
        q('UPDATE persones SET consentiment_imatge = ?, consentiment_data = ? WHERE id = ?', [$consent, $consent === 'pendent' ? null : "$any-09-12", $p]);
    }

    $agr = [];
    foreach ([['Banda', 'banda'], ['Banda juvenil', 'banda_juvenil'], ['Coral', 'coral']] as [$n, $tp]) {
        q('INSERT INTO agrupacions(nom, tipus) VALUES(?, ?)', [$n, $tp]);
        $agr[$n] = $id();
    }
    $musics = [];
    // Anys d'antiguitat de cada músic (n'hi ha un que en fa 25 i un altre 50 este any, per a la llista d'insígnies)
    $anys = [25, 12, 8, 50, 3];
    foreach (range(1, 5) as $n) {
        $p = $persona('Músic' . $n, 'Exemple', [1980, 1989, 1991, 1960, 1995][$n - 1] . '-1' . ($n % 3) . '-0' . $n, $n !== 5);
        $musics[] = $p;
        $alta = ((int)date('Y') - $anys[$n - 1]) . '-0' . $n . '-15';
        q('INSERT INTO socis(persona_id, numero, tipus, data_alta) VALUES(?, ?, ?, ?)', [$p, (string)(100 + $n), 'ordinari', $alta]);
        q('INSERT INTO membres(persona_id, agrupacio_id, instrument_veu, data_alta) VALUES(?, ?, ?, ?)', [$p, $agr['Banda'], $es ? ['Trompeta', 'Clarinete', 'Saxo alto', 'Trombón', 'Percusión'][$n - 1] : ['Trompeta', 'Clarinet', 'Saxo alt', 'Trombó', 'Percussió'][$n - 1], $alta]);
        q("UPDATE persones SET consentiment_imatge = 'autoritza', consentiment_data = ? WHERE id = ?", [$alta, $p]);
    }
    q("INSERT INTO membres(persona_id, agrupacio_id, funcio, data_alta) VALUES(?, ?, 'director_titular', ?)", [$tutors[0], $agr['Banda'], "$any-01-01"]);
    q('INSERT INTO membres(persona_id, agrupacio_id, instrument_veu) VALUES(?, ?, ?)', [$tutors[1], $agr['Coral'], 'Soprano']);
    foreach (array_slice($alumnesPersona, 3) as $p) {
        q('INSERT INTO membres(persona_id, agrupacio_id, instrument_veu) VALUES(?, ?, ?)', [$p, $agr['Banda juvenil'], $tr('Clarinet', 'Clarinete')]);
    }
    foreach ($tutors as $i => $p) {
        q('INSERT INTO socis(persona_id, numero, tipus, data_alta) VALUES(?, ?, ?, ?)', [$p, (string)(200 + $i), 'protector', "$any-02-01"]);
    }

    $instr = [];
    foreach ([['INV-001', 'Trompeta', 'Yamaha', 'YTR-2330'], ['INV-002', $tr('Clarinet', 'Clarinete'), 'Buffet', 'Prodige'],
                 ['INV-003', $tr('Bombo de concert', 'Bombo de concierto'), null, null], ['INV-004', $tr('Saxo alt', 'Saxo alto'), 'Jupiter', 'JAS-500']] as [$inv, $tp, $ma, $mo]) {
        q('INSERT INTO instruments(inventari, tipus, marca, model, ubicacio) VALUES(?,?,?,?,?)', [$inv, $tp, $ma, $mo, $tr('Magatzem', 'Almacén')]);
        $instr[] = $id();
    }
    q('INSERT INTO cessions(instrument_id, persona_id, data_entrega, data_prevista, estat_entrega, fianca) VALUES(?,?,?,?,?,?)',
        [$instr[0], $alumnesPersona[0], "$any-09-20", ($any + 1) . '-06-30', $tr('Bo', 'Bueno'), 50]);
    q('INSERT INTO cessions(instrument_id, persona_id, data_entrega, data_prevista, estat_entrega) VALUES(?,?,?,?,?)',
        [$instr[1], $alumnesPersona[1], "$any-09-20", date('Y-m-d', strtotime('-7 days')), $tr('Bo', 'Bueno')]);
    q('INSERT INTO manteniments(instrument_id, data, descripcio, cost) VALUES(?,?,?,?)', [$instr[0], "$any-09-05", $tr('Neteja i greixat de pistons', 'Limpieza y engrase de pistones'), 35]);

    // Economia: tarifes, domiciliacions (IBAN d'exemple), rebuts d'este trimestre i d'este mes, i la loteria de Nadal
    foreach ([
        [$tr('Quota de soci ordinari', 'Cuota de socio ordinario'), 'quota_soci', 'ordinari', null, 'trimestral', 15],
        [$tr('Quota de soci protector', 'Cuota de socio protector'), 'quota_soci', 'protector', null, 'trimestral', 10],
        [$tr('Matrícula', 'Matrícula'), 'matricula', null, null, 'unica', 30],
        [$tr('Llenguatge musical', 'Lenguaje musical'), 'classe', null, $assig['Llenguatge musical'], 'mensual', 20],
        [$tr('Classe de trompeta', 'Clase de trompeta'), 'classe', null, $assig['Trompeta'], 'mensual', 28],
        [$tr('Classe de clarinet', 'Clase de clarinete'), 'classe', null, $assig['Clarinet'], 'mensual', 28],
        [$tr('Conjunt instrumental', 'Conjunto instrumental'), 'classe', null, $assig['Conjunt instrumental'], 'mensual', 10],
    ] as [$n, $tp, $ts, $as, $per, $imp]) {
        q('INSERT INTO tarifes(nom, tipus, tipus_soci, assignatura_id, periodicitat, import) VALUES(?,?,?,?,?,?)', [$n, $tp, $ts, $as, $per, $imp]);
    }
    desa_config('descompte_soci', '10');
    desa_config('descompte_germans', '5');
    // IBAN d'exemple vàlids (no són comptes reals)
    $iban = function (int $n): string {
        $ccc = '2100' . '0418' . '45' . sprintf('%010d', 2000000 + $n);
        $control = 98 - mod97($ccc . '142800');
        return 'ES' . sprintf('%02d', $control) . $ccc;
    };
    foreach ([$tutors[0], $tutors[1], $musics[0], $musics[1], $musics[2]] as $i => $p) {
        q('INSERT INTO mandats(persona_id, iban, data_signatura) VALUES(?, ?, ?)', [$p, $iban($i + 1), "$any-09-01"]);
        q('UPDATE mandats SET referencia = ? WHERE id = ?', [referencia_mandat($id()), $id()]);
    }
    $resum = [];
    $trimestre = 'T' . (int)ceil((int)date('n') / 3);
    crea_rebuts(calcula_quotes((int)date('Y'), $trimestre, $resum), date('Y-m-d'));
    $cursFila = fila('SELECT * FROM cursos WHERE id = ?', [$curs]);
    crea_rebuts(calcula_escola($cursFila, 'MAT', $resum), "$any-09-15");
    $mes = 'M' . date('Y-m');
    if (isset(periodes_escola($cursFila)[$mes])) {
        crea_rebuts(calcula_escola($cursFila, $mes, $resum), date('Y-m-d'));
    }
    // Les matrícules ja estan pagades; un soci ha pagat en efectiu i un rebut l'ha retornat el banc
    q("UPDATE rebuts SET estat = 'pagat', data_pagament = data_emissio WHERE tipus = 'matricula'");
    q("UPDATE rebuts SET estat = 'pagat', data_pagament = date('now','localtime') WHERE id = (SELECT MIN(id) FROM rebuts WHERE tipus = 'quota_soci' AND forma_pagament = 'efectiu')");
    q("UPDATE rebuts SET estat = 'retornat' WHERE id = (SELECT MAX(id) FROM rebuts WHERE tipus = 'quota_soci' AND forma_pagament = 'domiciliacio')");

    $nadal = (int)date('n') >= 2 ? (int)date('Y') : (int)date('Y') - 1;
    q('INSERT INTO loteries(nom, data_sorteig, numeros, preu, juga, participacions_talonari) VALUES(?, ?, ?, ?, ?, ?)',
        [$tr('Loteria de Nadal', 'Lotería de Navidad') . " $nadal", "$nadal-12-22", '12345, 67890', 5, 4, 50]);
    $lo = $id();
    foreach ([[$musics[0], 50, 0, 250], [$musics[1], 38, 12, 180], [$tutors[0], null, null, null], [$tutors[1], null, null, null]] as $i => [$p, $v, $tor, $liq]) {
        q('INSERT INTO talonaris(loteria_id, numero, persona_id, data_entrega, venudes, tornades, import_liquidat, data_liquidacio) VALUES(?,?,?,?,?,?,?,?)',
            [$lo, $i + 1, $p, "$nadal-10-15", $v, $tor, $liq, $liq !== null ? "$nadal-11-30" : null]);
    }
}
