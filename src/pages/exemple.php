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
    $tipusSoci = fn(string $codi) => valor('SELECT id FROM tipus_socis WHERE codi = ?', [$codi]);
    $musics = [];
    // Anys d'antiguitat de cada músic (n'hi ha un que en fa 25 i un altre 50 este any, per a la llista d'insígnies)
    $anys = [25, 12, 8, 50, 3];
    foreach (range(1, 5) as $n) {
        $p = $persona('Músic' . $n, 'Exemple', [1980, 1989, 1991, 1960, 1995][$n - 1] . '-1' . ($n % 3) . '-0' . $n, $n !== 5);
        $musics[] = $p;
        $alta = ((int)date('Y') - $anys[$n - 1]) . '-0' . $n . '-15';
        q('INSERT INTO socis(persona_id, numero, tipus, data_alta) VALUES(?, ?, ?, ?)', [$p, (string)(100 + $n), $tipusSoci('ordinari'), $alta]);
        q('INSERT INTO membres(persona_id, agrupacio_id, instrument_veu, data_alta) VALUES(?, ?, ?, ?)', [$p, $agr['Banda'], $es ? ['Trompeta', 'Clarinete', 'Saxo alto', 'Trombón', 'Percusión'][$n - 1] : ['Trompeta', 'Clarinet', 'Saxo alt', 'Trombó', 'Percussió'][$n - 1], $alta]);
        q("UPDATE persones SET consentiment_imatge = 'autoritza', consentiment_data = ? WHERE id = ?", [$alta, $p]);
    }
    q("INSERT INTO membres(persona_id, agrupacio_id, funcio, data_alta) VALUES(?, ?, 'director_titular', ?)", [$tutors[0], $agr['Banda'], "$any-01-01"]);
    q('INSERT INTO membres(persona_id, agrupacio_id, instrument_veu) VALUES(?, ?, ?)', [$tutors[1], $agr['Coral'], 'Soprano']);
    foreach (array_slice($alumnesPersona, 3) as $p) {
        q('INSERT INTO membres(persona_id, agrupacio_id, instrument_veu) VALUES(?, ?, ?)', [$p, $agr['Banda juvenil'], $tr('Clarinet', 'Clarinete')]);
    }
    foreach ($tutors as $i => $p) {
        q('INSERT INTO socis(persona_id, numero, tipus, data_alta) VALUES(?, ?, ?, ?)', [$p, (string)(200 + $i), $tipusSoci('protector'), "$any-02-01"]);
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
        [$tr('Quota de soci ordinari', 'Cuota de socio ordinario'), 'quota_soci', $tipusSoci('ordinari'), null, 'trimestral', 15],
        [$tr('Quota de soci protector', 'Cuota de socio protector'), 'quota_soci', $tipusSoci('protector'), null, 'trimestral', 10],
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

    // Societat: junta actual i l'anterior, reunions, documents, certificat del professorat, caixa i un comunicat
    $elegida = ((int)date('Y') - 2) . '-06-15';
    $anterior = ((int)date('Y') - 6) . '-06-10';
    foreach ([[$musics[0], 'presidencia'], [$tutors[1], 'vicepresidencia'], [$tutors[0], 'secretaria'], [$musics[1], 'tresoreria'], [$musics[2], 'vocal']] as [$p, $c]) {
        q('INSERT INTO carrecs(persona_id, carrec, descripcio, data_inici) VALUES(?, ?, ?, ?)', [$p, $c, $c === 'vocal' ? $tr('Vocal d\'escola', 'Vocal de escuela') : null, $elegida]);
    }
    foreach ([[$musics[3], 'presidencia'], [$musics[0], 'secretaria'], [$musics[1], 'tresoreria']] as [$p, $c]) {
        q('INSERT INTO carrecs(persona_id, carrec, data_inici, data_fi) VALUES(?, ?, ?, ?)', [$p, $c, $anterior, $elegida]);
    }
    desa_config('junta_anys_mandat', '4');
    // Correus d'exemple per a les llistes de comunicats (un adult sense correu, per a veure-ho)
    q("UPDATE persones SET email = replace(lower(nom), 'ú', 'u') || '@exemple.org' WHERE data_naixement <= date('now','localtime','-18 years') AND nom <> 'Músic5'");
    $lloc = $tr('Local social', 'Local social');
    q('INSERT INTO reunions(tipus, data, hora, hora_segona, lloc, data_convocatoria, ordre_del_dia, assistents, desenvolupament, acords, hora_fi, estat) VALUES(?,?,?,?,?,?,?,?,?,?,?,?)', [
        'assemblea_ordinaria', ((int)date('Y')) . '-03-20', '19:30', '20:00', $lloc, ((int)date('Y')) . '-03-05',
        $tr("Lectura i aprovació, si escau, de l'acta anterior\nEstat de comptes de l'exercici anterior\nPressupost de l'exercici\nPrecs i preguntes", "Lectura y aprobación, si procede, del acta anterior\nEstado de cuentas del ejercicio anterior\nPresupuesto del ejercicio\nRuegos y preguntas"),
        $tr('42 socis i sòcies presents i 8 representats.', '42 socios y socias presentes y 8 representados.'),
        $tr("La tresoreria presenta els comptes, que s'aproven per unanimitat. Es presenta el pressupost i s'obri un torn de preguntes.", 'La tesorería presenta las cuentas, que se aprueban por unanimidad. Se presenta el presupuesto y se abre un turno de preguntas.'),
        $tr("Aprovar l'acta de l'assemblea anterior.\nAprovar els comptes de l'exercici anterior.\nAprovar el pressupost de l'exercici, amb una quota de soci de 60 € l'any.", "Aprobar el acta de la asamblea anterior.\nAprobar las cuentas del ejercicio anterior.\nAprobar el presupuesto del ejercicio, con una cuota de socio de 60 € al año."),
        '21:15', 'celebrada',
    ]);
    q('INSERT INTO reunions(tipus, data, hora, lloc, data_convocatoria, ordre_del_dia, estat) VALUES(?,?,?,?,?,?,?)', [
        'junta', date('Y-m-d', strtotime('+10 days')), '20:30', $lloc, date('Y-m-d'),
        $tr("Preparació del concert de Santa Cecília\nLoteria de Nadal\nPrecs i preguntes", "Preparación del concierto de Santa Cecilia\nLotería de Navidad\nRuegos y preguntas"), 'convocada',
    ]);
    $doc = function (string $titol, string $tipus, ?string $data, ?string $caduca, ?int $persona = null, ?string $text = null) {
        q('INSERT INTO documents(titol, tipus, persona_id, data_document, data_caducitat) VALUES(?, ?, ?, ?, ?)', [$titol, $tipus, $persona, $data, $caduca]);
        $id = (int)db()->lastInsertId();
        if ($text !== null) {
            q('INSERT INTO document_fitxers(document_id, contingut) VALUES(?, ?)', [$id, $text]);
            q("UPDATE documents SET fitxer_nom = ?, fitxer_tipus = 'text/plain', fitxer_mida = ? WHERE id = ?", [mb_strtolower(preg_replace('/\W+/u', '-', $titol)) . '.txt', strlen($text), $id]);
        }
    };
    $doc($tr('Estatuts de la societat', 'Estatutos de la sociedad'), 'estatuts', '2010-05-20', null, null, $tr("Estatuts d'exemple.\n\nArticle 1. Denominació…", "Estatutos de ejemplo.\n\nArtículo 1. Denominación…"));
    $doc($tr('Reglament de règim intern', 'Reglamento de régimen interno'), 'reglament', '2018-03-15', null);
    $doc($tr('Assegurança de responsabilitat civil', 'Seguro de responsabilidad civil'), 'asseguranca', date('Y-m-d', strtotime('-11 months')), date('Y-m-d', strtotime('+30 days')));
    $doc($tr('Contracte de la professora de llenguatge', 'Contrato de la profesora de lenguaje'), 'contracte', "$any-09-01", ($any + 1) . '-06-30', (int)valor('SELECT persona_id FROM professors WHERE id = ?', [$profs[0]]));
    q('UPDATE professors SET certificat_delictes = ? WHERE id = ?', ["$any-08-20", $profs[0]]);
    foreach ([
        [date('Y-m-d', strtotime('-40 days')), 'despesa', 'banc', 'federacio', $tr('Quota anual de la Federació', 'Cuota anual de la Federación'), 180],
        [date('Y-m-d', strtotime('-30 days')), 'despesa', 'banc', 'assegurances', $tr('Assegurança de responsabilitat civil', 'Seguro de responsabilidad civil'), 320],
        [date('Y-m-d', strtotime('-12 days')), 'despesa', 'caixa', 'material', $tr('Partitures per al concert de Santa Cecília', 'Partituras para el concierto de Santa Cecilia'), 85.5],
        [date('Y-m-d', strtotime('-5 days')), 'ingres', 'banc', 'subvencions', $tr("Subvenció de l'ajuntament", 'Subvención del ayuntamiento'), 1500],
    ] as [$d, $tp, $cmp, $cat, $c, $imp]) {
        q('INSERT INTO moviments(data, tipus, compte, categoria, concepte, import) VALUES(?,?,?,?,?,?)', [$d, $tp, $cmp, $cat, $c, $imp]);
    }
    // Subvencions: una de la Diputació concedida i a mig justificar, una de la Generalitat per demanar i el conveni de l'ajuntament de l'any passat
    q("UPDATE moviments SET proveidor = ?, nif = ?, justificant = ? WHERE categoria = 'material'", [$tr('Música i Partitures SL', 'Música y Partituras SL'), 'B12000001', 'F-2026-0187']);
    q('INSERT INTO moviments(data, tipus, compte, categoria, concepte, import, justificant, proveidor, nif) VALUES(?,?,?,?,?,?,?,?,?)', [date('Y-m-d', strtotime('-20 days')), 'despesa', 'banc',
        'instruments', $tr('Reparació d\'instruments de l\'escola', 'Reparación de instrumentos de la escuela'), 1450, 'F-311', $tr('Taller de Vent La Plana', 'Taller de Viento La Plana'), 'B12000002']);
    $reparacio = $id();
    $partitures = (int)valor("SELECT id FROM moviments WHERE categoria = 'material' LIMIT 1");
    $org = fn(int $ordre) => (int)valor('SELECT id FROM organismes WHERE ordre = ?', [$ordre]);
    $subvencio = function (array $c, int $fets) use ($id): int {
        q('INSERT INTO subvencions(' . implode(', ', array_keys($c)) . ') VALUES(' . implode(', ', array_fill(0, count($c), '?')) . ')', array_values($c));
        $sid = $id();
        q('INSERT INTO subvencio_requisits(subvencio_id, requisit_id) SELECT ?, requisit_id FROM organisme_requisits WHERE organisme_id = ?', [$sid, $c['organisme_id']]);
        foreach (array_slice(q('SELECT requisit_id FROM subvencio_requisits WHERE subvencio_id = ? ORDER BY requisit_id', [$sid])->fetchAll(PDO::FETCH_COLUMN), 0, $fets) as $r) {
            q("UPDATE subvencio_requisits SET fet = 1, data_fet = date('now','localtime','-5 days') WHERE subvencio_id = ? AND requisit_id = ?", [$sid, $r]);
        }
        return $sid;
    };
    $dip = $subvencio(['nom' => $tr('Ajudes a societats musicals', 'Ayudas a sociedades musicales'), 'organisme_id' => $org(20), 'exercici' => (int)date('Y'), 'expedient' => 'SUBV/' . date('Y') . '/0412',
        'objecte' => $tr('Escola de música i material', 'Escuela de música y material'), 'estat' => 'concedida', 'data_sollicitud' => date('Y-m-d', strtotime('-150 days')),
        'data_resolucio' => date('Y-m-d', strtotime('-45 days')), 'data_limit_justificacio' => date('Y-m-d', strtotime('+25 days')),
        'import_pressupost' => 6000, 'import_sollicitat' => 3000, 'import_concedit' => 2400, 'import_justificar' => 3000], 3);
    q('INSERT INTO subvencio_cobraments(subvencio_id, data, import, compte, concepte) VALUES(?, ?, ?, ?, ?)', [$dip, date('Y-m-d', strtotime('-20 days')), 1200, 'banc', $tr('Bestreta del 50 %', 'Anticipo del 50 %')]);
    q('INSERT INTO subvencio_despeses(subvencio_id, moviment_id, import) VALUES(?, ?, ?), (?, ?, ?)', [$dip, $reparacio, 1450, $dip, $partitures, 85.5]);
    $subvencio(['nom' => $tr('Ajudes de l\'Institut Valencià de Cultura', 'Ayudas del Instituto Valenciano de Cultura'), 'organisme_id' => $org(10), 'exercici' => (int)date('Y') + 1,
        'objecte' => $tr('Concerts de la banda', 'Conciertos de la banda'), 'estat' => 'prevista', 'data_limit_sollicitud' => date('Y-m-d', strtotime('+12 days')), 'import_pressupost' => 9000, 'import_sollicitat' => 4500], 0);
    $passat = $subvencio(['nom' => $tr('Conveni de l\'escola de música', 'Convenio de la escuela de música'), 'organisme_id' => $org(30), 'exercici' => (int)date('Y') - 1,
        'objecte' => $tr('Escola de música', 'Escuela de música'), 'estat' => 'tancada', 'data_limit_justificacio' => ((int)date('Y') - 1) . '-12-15', 'data_justificacio' => ((int)date('Y') - 1) . '-12-10',
        'import_concedit' => 1500], 4);
    q('INSERT INTO subvencio_cobraments(subvencio_id, data, import, compte, concepte) VALUES(?, ?, ?, ?, ?)', [$passat, ((int)date('Y') - 1) . '-12-28', 1500, 'banc', $tr('Pagament', 'Pago')]);
    desa_config('caixa_saldo_' . date('Y') . '_banc', '2350');
    desa_config('caixa_saldo_' . date('Y') . '_caixa', '180');
    q('INSERT INTO comunicats(data, destinataris, n_destinataris, adreces, assumpte, cos) VALUES(?,?,?,?,?,?)', [date('Y-m-d', strtotime('-3 days')), $tr('Socis i sòcies', 'Socios y socias'), 8, null,
        $tr('Convocatòria del concert de Santa Cecília', 'Convocatoria del concierto de Santa Cecilia'), null]);

    // Repertori: obres conegudes, amb números d'arxiu i ubicacions inventades
    $tipusObra = fn(string $nom) => valor('SELECT id FROM tipus_obres WHERE nom = ?', [$nom]);
    $armari = fn(string $a, int $p, int $c) => $tr("Armari $a · Prestatgeria $p · Carpeta $c", "Armario $a · Estantería $p · Carpeta $c");
    $pp = $tr('Partitura + particel·les', 'Partitura + particellas');
    foreach ([
        ['Paquito el chocolatero', $tr('Pasdoble', 'Pasodoble'), 'Gustavo Pascual Falcó', null, ['Banda', 'Banda juvenil'], 'ARX-0001', $armari('A', 1, 1), $pp, 'complet', 1, null],
        ['Amparito Roca', $tr('Pasdoble', 'Pasodoble'), 'Jaime Texidor', null, ['Banda'], 'ARX-0002', $armari('A', 1, 2), $pp, 'complet', 1, null],
        ['El fallero', $tr('Pasdoble', 'Pasodoble'), 'José Serrano', null, ['Banda'], 'ARX-0003', $armari('A', 1, 3), $pp, 'incomplet', 0,
            $tr('Falta la particel·la del 2n clarinet.', 'Falta la particella del 2º clarinete.')],
        ['Pepita Greus', $tr('Pasdoble', 'Pasodoble'), 'Pascual Pérez Choví', null, ['Banda'], 'ARX-0004', $armari('A', 1, 4), $pp, 'complet', 0, null],
        ['Chimo', $tr('Marxa mora', 'Marcha mora'), 'José María Ferrero', null, ['Banda'], 'ARX-0005', $armari('A', 2, 5), $pp, 'complet', 1, null],
        ['Ragón Falez', $tr('Marxa mora', 'Marcha mora'), 'Gaspar Ángel Tortosa', null, ['Banda'], 'ARX-0006', $armari('A', 2, 6), $pp, 'en_revisio', 0, null],
        ['Amarguras', $tr('Marxa de processó', 'Marcha de procesión'), 'Manuel Font de Anta', null, ['Banda'], 'ARX-0007', $armari('A', 3, 7), $pp, 'complet', 1, null],
        [$tr('Himne de l\'Exposició', 'Himno de la Exposición'), $tr('Himne', 'Himno'), 'José Serrano', null, ['Banda', 'Coral'], 'ARX-0008', $armari('A', 3, 8),
            $tr('Partitura, particel·les i veus del cor', 'Partitura, particellas y voces del coro'), 'complet', 1, null],
        ["Nit d'albaes", $tr('Poema simfònic', 'Poema sinfónico'), 'Salvador Giner', null, ['Banda'], 'ARX-0009', $armari('B', 1, 9), $pp, 'complet', 0, null],
        [$tr('Poeta i aldeà', 'Poeta y aldeano'), 'Obertura', 'Franz von Suppé', 'Henry Fillmore', ['Banda'], 'ARX-0010', $armari('B', 1, 10), $pp, 'complet', 0, null],
        ['La boda de Luis Alonso', $tr('Selecció de sarsuela', 'Selección de zarzuela'), 'Gerónimo Giménez', null, ['Banda'], 'ARX-0011', $armari('B', 2, 11), $pp, 'complet', 1, null],
        [$tr('Pirates del Carib', 'Piratas del Caribe'), 'Banda sonora', 'Klaus Badelt', 'Ted Ricketts', ['Banda', 'Banda juvenil'], 'ARX-0012', $armari('B', 2, 12), $pp, 'complet', 1, null],
        [$tr('Marxa del centenari', 'Marcha del centenario'), $tr('Pasdoble', 'Pasodoble'), $tr('Anònim', 'Anónimo'), null, [], 'ARX-0013', $armari('B', 3, 13),
            $tr('Només la partitura', 'Solo la partitura'), 'retirat', 0, $tr('Material molt deteriorat.', 'Material muy deteriorado.')],
    ] as [$titol, $tp, $comp, $arr, $agrs, $num, $ubi, $mat, $estat, $dig, $obs]) {
        q('INSERT INTO obres(titol, tipus_id, compositor, arranjador, numero_arxiu, ubicacio, material, estat, digitalitzada, observacions) VALUES(?,?,?,?,?,?,?,?,?,?)',
            [$titol, $tipusObra($tp), $comp, $arr, $num, $ubi, $mat, $estat, $dig, $obs]);
        $obra = $id();
        foreach ($agrs as $ag) {
            q('INSERT INTO obra_agrupacions(obra_id, agrupacio_id) VALUES(?, ?)', [$obra, $agr[$ag]]);
        }
    }

    // Uniformes: polos i dessuadores per talla i motxilles sense talla; compres, vendes i un préstec
    $unif = [];
    foreach ([[$tr('Polo de la banda', 'Polo de la banda'), 'polo', 'S, M, L, XL, XXL', 18], [$tr('Dessuadora', 'Sudadera'), 'dessuadora', '8, 10, 12, 14, S, M, L, XL', 25],
                 [$tr('Motxilla de l\'escola', 'Mochila de la escuela'), 'motxilla', null, 12]] as [$n, $tp, $talles, $preu]) {
        q('INSERT INTO uniformes(nom, tipus, talles, preu) VALUES(?, ?, ?, ?)', [$n, $tp, $talles, $preu]);
        $unif[] = $id();
    }
    foreach ([[0, 'S', 5, 9.5], [0, 'M', 10, 9.5], [0, 'L', 10, 9.5], [0, 'XL', 6, 9.5], [0, 'XXL', 2, 9.5],
                 [1, '10', 4, 14], [1, '12', 4, 14], [1, 'M', 6, 14], [1, 'L', 6, 14], [2, null, 15, 6]] as [$u, $talla, $n, $preu]) {
        q('INSERT INTO uniforme_compres(uniforme_id, data, talla, quantitat, preu_unitat, proveidor) VALUES(?, ?, ?, ?, ?, ?)',
            [$unif[$u], "$any-09-10", $talla, $n, $preu, $tr('Brodats La Plana', 'Bordados La Plana')]);
    }
    foreach ($musics as $i => $p) {
        q("INSERT INTO uniforme_lliuraments(uniforme_id, persona_id, talla, quantitat, data, tipus, import) VALUES(?, ?, ?, 1, ?, 'venda', 18)",
            [$unif[0], $p, ['M', 'L', 'L', 'XL', 'XXL'][$i], date('Y-m-d', strtotime('-' . (20 + $i) . ' days'))]);
    }
    foreach (array_slice($alumnesPersona, 0, 3) as $i => $p) {
        q("INSERT INTO uniforme_lliuraments(uniforme_id, persona_id, talla, quantitat, data, tipus, import) VALUES(?, ?, ?, 1, ?, 'venda', 12)",
            [$unif[2], $p, null, date('Y-m-d', strtotime('-15 days'))]);
    }
    q("INSERT INTO uniforme_lliuraments(uniforme_id, persona_id, talla, quantitat, data, tipus) VALUES(?, ?, '12', 1, ?, 'prestec')",
        [$unif[1], $alumnesPersona[0], date('Y-m-d', strtotime('-10 days'))]);
}
