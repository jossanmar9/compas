<?php
// Dades d'exemple per als tipus d'entitat que no són una societat musical. Noms genèrics i inventats.
// Copyright (C) 2026 José Vicente Sanjuan
// SPDX-License-Identifier: AGPL-3.0-or-later

function carrega_exemple_associacio(string $tipus): void
{
    $es = idioma() === 'es';
    $tr = fn(string $ca, string $esp) => $es ? $esp : $ca;
    $id = fn() => (int)db()->lastInsertId();
    $any = any_exercici();
    $inici = sprintf('%d-%02d-01', $any, mes_inici_exercici());
    $exercici = nom_exercici($any);
    $info = info_tipus($tipus);

    $n = 0;
    $persona = function (string $nom, string $cognoms, string $naix, bool $complet = true) use (&$n, $tr): int {
        $n++;
        $adult = es_major_edat($naix);
        q('INSERT INTO persones(nom, cognoms, data_naixement, document, telefon, email, adreca, codi_postal, poblacio, consentiment_imatge, consentiment_data) VALUES(?,?,?,?,?,?,?,?,?,?,?)', [
            $nom, $cognoms, $naix, $adult && $complet ? sprintf('%08dX', $n) : null, $adult && $complet ? sprintf('600 000 %03d', $n) : null,
            $adult && $complet ? strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $nom)) . $n . '@exemple.org' : null,
            $tr('Carrer Major, ', 'Calle Mayor, ') . $n, '12000', $tr('Poble d\'Exemple', 'Pueblo de Ejemplo'), $n % 7 ? 'autoritza' : 'pendent', $n % 7 ? date('Y-m-d', strtotime('-60 days')) : null,
        ]);
        return (int)db()->lastInsertId();
    };
    $tipusSoci = fn(string $codi) => valor('SELECT id FROM tipus_socis WHERE codi = ?', [$codi]);
    $edat = fn(int $anys, int $i = 0) => sprintf('%d-%02d-%02d', (int)date('Y') - $anys, ($i % 12) + 1, ($i % 27) + 1);

    // Adults i menors, tots socis (o famílies associades, fallers, comparsistes…)
    $adults = $menors = [];
    $noms = ['Anna', 'Jordi', 'Marta', 'Pau', 'Laura', 'Vicent', 'Clara', 'Toni', 'Sara', 'Miquel', 'Elena', 'Raül'];
    foreach ($noms as $i => $nom) {
        $adults[] = $persona($nom, $tr('Exemple', 'Ejemplo') . ' ' . chr(65 + $i), $edat(25 + $i * 4, $i), $i !== 11);
    }
    foreach (['Nil', 'Aina', 'Joan', 'Martina', 'Biel', 'Lluna', 'Hugo', 'Noa'] as $i => $nom) {
        $menors[] = $persona($nom, $tr('Exemple', 'Ejemplo') . ' ' . chr(65 + $i % 6), $edat(6 + $i, $i + 3));
    }
    $num = 0;
    foreach ($adults as $i => $p) {
        q('INSERT INTO socis(persona_id, numero, tipus, data_alta) VALUES(?, ?, ?, ?)', [$p, (string)++$num, $tipusSoci($i === 10 ? 'honorari' : 'ordinari'), ((int)date('Y') - [25, 12, 8, 3, 1][$i % 5]) . '-03-01']);
    }
    if ($tipus !== 'ampa' && $tipus !== 'federacio') {
        foreach ($menors as $p) {
            q('INSERT INTO socis(persona_id, numero, tipus, data_alta) VALUES(?, ?, ?, ?)', [$p, (string)++$num, $tipusSoci('juvenil'), $inici]);
        }
    }

    // Quotes i domiciliacions
    q("INSERT INTO tarifes(nom, tipus, tipus_soci, periodicitat, import) VALUES(?, 'quota_soci', ?, 'anual', ?)", [$tr('Quota ordinària', 'Cuota ordinaria'), $tipusSoci('ordinari'),
        in_array($tipus, ['falla', 'gaiata', 'foguera', 'comparsa'], true) ? 360 : 40]);
    q("INSERT INTO tarifes(nom, tipus, tipus_soci, periodicitat, import) VALUES(?, 'quota_soci', ?, 'anual', ?)", [$tr('Quota infantil', 'Cuota infantil'), $tipusSoci('juvenil'),
        in_array($tipus, ['falla', 'gaiata', 'foguera', 'comparsa'], true) ? 120 : 20]);
    $iban = function (int $k): string {
        $ccc = '2100' . '0418' . '45' . sprintf('%010d', 3000000 + $k);
        return 'ES' . sprintf('%02d', 98 - mod97($ccc . '142800')) . $ccc;
    };
    foreach (array_slice($adults, 0, 6) as $k => $p) {
        q('INSERT INTO mandats(persona_id, iban, data_signatura) VALUES(?, ?, ?)', [$p, $iban($k + 1), $inici]);
        q('UPDATE mandats SET referencia = ? WHERE id = ?', [referencia_mandat($id()), $id()]);
    }
    $resum = [];
    crea_rebuts(calcula_quotes($any, 'A', $resum), date('Y-m-d'));
    q("UPDATE rebuts SET estat = 'pagat', data_pagament = data_emissio WHERE id IN (SELECT id FROM rebuts WHERE forma_pagament = 'efectiu' ORDER BY id LIMIT 2)");

    // Junta
    $elegida = ((int)date('Y') - 1) . '-06-15';
    foreach ([[0, 'presidencia'], [1, 'vicepresidencia'], [2, 'secretaria'], [3, 'tresoreria'], [4, 'vocal']] as [$k, $c]) {
        q('INSERT INTO carrecs(persona_id, carrec, data_inici) VALUES(?, ?, ?)', [$adults[$k], $c, $elegida]);
    }
    desa_config('junta_anys_mandat', '4');
    q('INSERT INTO reunions(tipus, data, hora, lloc, data_convocatoria, ordre_del_dia, estat) VALUES(?,?,?,?,?,?,?)', ['junta', date('Y-m-d', strtotime('+9 days')), '20:30',
        $tr('Local social', 'Local social'), date('Y-m-d'), $tr("Activitats del trimestre\nEstat de comptes\nPrecs i preguntes", "Actividades del trimestre\nEstado de cuentas\nRuegos y preguntas"), 'convocada']);
    q('INSERT INTO documents(titol, tipus, data_document, data_caducitat) VALUES(?, ?, ?, ?)', [$tr('Estatuts de l\'entitat', 'Estatutos de la entidad'), 'estatuts', '2012-05-20', null]);
    q('INSERT INTO documents(titol, tipus, data_document, data_caducitat) VALUES(?, ?, ?, ?)', [$tr('Assegurança de responsabilitat civil', 'Seguro de responsabilidad civil'), 'asseguranca',
        date('Y-m-d', strtotime('-11 months')), date('Y-m-d', strtotime('+30 days'))]);
    foreach ([[-35, 'despesa', 'banc', 'assegurances', $tr('Assegurança de responsabilitat civil', 'Seguro de responsabilidad civil'), 290],
                 [-15, 'despesa', 'caixa', 'material', $tr('Material per a les activitats', 'Material para las actividades'), 64.3],
                 [-6, 'ingres', 'banc', 'subvencions', $tr('Subvenció de l\'ajuntament', 'Subvención del ayuntamiento'), 1200]] as [$d, $tp, $cmp, $cat, $c, $imp]) {
        q('INSERT INTO moviments(data, tipus, compte, categoria, concepte, import) VALUES(?,?,?,?,?,?)', [date('Y-m-d', strtotime("$d days")), $tp, $cmp, $cat, $c, $imp]);
    }
    q("INSERT INTO subvencions(nom, organisme_id, exercici, estat, data_limit_justificacio, import_concedit, objecte) VALUES(?, (SELECT id FROM organismes WHERE ordre = 30), ?, 'concedida', ?, 1200, ?)",
        [$tr('Conveni amb l\'ajuntament', 'Convenio con el ayuntamiento'), (int)date('Y'), date('Y-m-d', strtotime('+40 days')), $tr('Activitats de l\'any', 'Actividades del año')]);
    desa_config('caixa_saldo_' . date('Y') . '_banc', '1800');

    // Grups (equips, seccions, esquadres, comissions…)
    $grups = [
        'club_esportiu' => [['Sènior A', 'equip'], ['Juvenil', 'equip'], ['Aleví', 'equip']],
        'colla' => [[$tr('Dolçaina i tabal', 'Dulzaina y tabal'), 'seccio'], [$tr('Gegants i cabets', 'Gigantes y cabezudos'), 'seccio']],
        'associacio_cultural' => [[$tr('Grup de teatre', 'Grupo de teatro'), 'grup'], [$tr('Grup de dansa', 'Grupo de danza'), 'grup']],
        'ampa' => [[$tr('Comissió de festes', 'Comisión de fiestas'), 'comissio'], [$tr('Comissió de menjador', 'Comisión de comedor'), 'comissio']],
        'comparsa' => [[$tr('Esquadra de negres', 'Escuadra de negros'), 'esquadra'], [$tr('Esquadra especial', 'Escuadra especial'), 'esquadra']],
    ][$tipus] ?? [];
    $agr = [];
    foreach ($grups as [$nom, $tp]) {
        q('INSERT INTO agrupacions(nom, tipus, data_creacio) VALUES(?, ?, ?)', [$nom, $tp, '2015-09-01']);
        $agr[] = $id();
    }
    foreach ($agr as $g => $a) {
        $qui = $tipus === 'club_esportiu' && $g > 0 ? array_slice($menors, ($g - 1) * 4, 4) : array_slice($adults, $g * 4, 5);
        foreach ($qui as $k => $p) {
            $funcio = $tipus === 'club_esportiu' ? ['Porter/a', 'Defensa', 'Migcampista', 'Davanter/a', 'Davanter/a'][$k] : ($tipus === 'colla' ? [$tr('Dolçaina', 'Dulzaina'), 'Tabal'][$k % 2] : null);
            q('INSERT INTO membres(persona_id, agrupacio_id, instrument_veu, data_alta) VALUES(?, ?, ?, ?)', [$p, $a, $funcio, $inici]);
        }
        q("INSERT INTO membres(persona_id, agrupacio_id, funcio, data_alta) VALUES(?, ?, 'director_titular', ?)", [$adults[($g + 6) % 12], $a, $inici]);
    }

    // Escola esportiva, extraescolars o tallers
    if (in_array('escola', $info['moduls'], true)) {
        q('INSERT INTO cursos(nom, data_inici, data_fi, actual) VALUES(?, ?, ?, 1)', [$exercici, sprintf('%d-%02d-15', $any, max(1, mes_inici_exercici())), ($any + 1) . '-06-20']);
        $curs = $id();
        $activitats = [
            'club_esportiu' => [$tr('Futbol', 'Fútbol'), $tr('Bàsquet', 'Baloncesto')],
            'ampa' => [$tr('Anglés', 'Inglés'), $tr('Robòtica', 'Robótica'), $tr('Escacs', 'Ajedrez')],
            'associacio_cultural' => [$tr('Teatre', 'Teatro'), $tr('Dansa', 'Danza'), $tr('Fotografia', 'Fotografía')],
        ][$tipus] ?? [$tr('Taller', 'Taller')];
        q('INSERT INTO aules(nom, capacitat) VALUES(?, ?)', [$tipus === 'club_esportiu' ? $tr('Camp municipal', 'Campo municipal') : $tr('Sala 1', 'Sala 1'), 30]);
        $aula = $id();
        $monitor = $adults[7];
        q('INSERT INTO professors(persona_id, data_alta, certificat_delictes) VALUES(?, ?, ?)', [$monitor, $inici, date('Y-m-d', strtotime('-100 days'))]);
        $prof = $id();
        q('INSERT INTO professors(persona_id, data_alta) VALUES(?, ?)', [$adults[8], $inici]);
        $grupsEscola = [];
        foreach ($activitats as $k => $a) {
            q("INSERT INTO assignatures(nom, tipus) VALUES(?, 'altra')", [$a]);
            $as = $id();
            q('INSERT INTO grups(curs_id, assignatura_id, nom, professor_id, aula_id) VALUES(?,?,?,?,?)', [$curs, $as, $tr('Grup únic', 'Grupo único'), $prof, $aula]);
            $grupsEscola[] = $id();
            q('INSERT INTO horaris(grup_id, dia, hora_inici, hora_fi) VALUES(?,?,?,?)', [$id(), $k % 5 + 1, '17:30', '18:30']);
            q("INSERT INTO tarifes(nom, tipus, assignatura_id, periodicitat, import) VALUES(?, 'classe', ?, 'mensual', ?)", [$a, $as, 18 + $k * 4]);
        }
        foreach ($menors as $k => $p) {
            q('INSERT INTO alumnes(persona_id, data_alta) VALUES(?, ?)', [$p, $inici]);
            $al = $id();
            q("INSERT INTO tutories(alumne_id, tutor_id, tipus_relacio, principal) VALUES(?, ?, ?, 1)", [$al, $adults[$k % 6], $k % 2 ? 'pare' : 'mare']);
            q('INSERT INTO matricules(alumne_id, curs_id, data) VALUES(?, ?, ?)', [$al, $curs, $inici]);
            q('INSERT INTO matricula_grups(matricula_id, grup_id) VALUES(?, ?)', [$id(), $grupsEscola[$k % count($grupsEscola)]]);
        }
    }

    // Llicències federatives i revisions mèdiques (una caduca prompte, una altra ja ha caducat)
    if (in_array('llicencies', $info['moduls'], true)) {
        $fed = $tr('Federació de Futbol de la Comunitat Valenciana', 'Federación de Fútbol de la Comunidad Valenciana');
        foreach (q('SELECT persona_id, agrupacio_id FROM membres WHERE instrument_veu IS NOT NULL ORDER BY id')->fetchAll() as $k => $m) {
            q('INSERT INTO llicencies(persona_id, agrupacio_id, federacio, numero, temporada, data_alta, data_caducitat, revisio_medica, import, estat) VALUES(?,?,?,?,?,?,?,?,?,?)', [
                $m['persona_id'], $m['agrupacio_id'], $fed, sprintf('LIC-%05d', 120 + $k), $exercici, $inici, ($any + 1) . '-06-30',
                $k === 1 ? date('Y-m-d', strtotime('+12 days')) : ($k === 2 ? date('Y-m-d', strtotime('-5 days')) : ($any + 1) . '-08-31'), 45, $k === 3 ? 'tramit' : 'actiu',
            ]);
        }
    }

    // Material i roba
    if (in_array('uniformes', $info['moduls'], true)) {
        $article = ['club_esportiu' => [$tr('Equipació de joc', 'Equipación de juego'), 'samarreta', 'S, M, L, XL', 35],
            'comparsa' => [$tr('Vestit de l\'esquadra', 'Traje de la escuadra'), 'altre_uniforme', 'S, M, L, XL', 280]][$tipus]
            ?? [$tr('Samarreta de l\'entitat', 'Camiseta de la entidad'), 'samarreta', 'S, M, L, XL', 12];
        q('INSERT INTO uniformes(nom, tipus, talles, preu) VALUES(?, ?, ?, ?)', [$article[0], $article[1], $article[2], $article[3]]);
        $u = $id();
        foreach (['S' => 6, 'M' => 10, 'L' => 10, 'XL' => 4] as $talla => $q) {
            q('INSERT INTO uniforme_compres(uniforme_id, data, talla, quantitat, preu_unitat) VALUES(?, ?, ?, ?, ?)', [$u, $inici, $talla, $q, round($article[3] * 0.6, 2)]);
        }
        foreach (array_slice($adults, 0, 4) as $k => $p) {
            q("INSERT INTO uniforme_lliuraments(uniforme_id, persona_id, talla, quantitat, data, tipus, import) VALUES(?, ?, ?, 1, ?, ?, ?)",
                [$u, $p, ['M', 'L', 'S', 'XL'][$k], date('Y-m-d', strtotime('-' . (10 + $k) . ' days')), $tipus === 'comparsa' ? 'prestec' : 'venda', $tipus === 'comparsa' ? null : $article[3]]);
        }
    }

    // Loteria
    if (in_array('loteria', $info['moduls'], true)) {
        $nadal = (int)date('n') >= 2 ? (int)date('Y') : (int)date('Y') - 1;
        q('INSERT INTO loteries(nom, data_sorteig, numeros, preu, juga, participacions_talonari) VALUES(?, ?, ?, ?, ?, ?)',
            [$tr('Loteria de Nadal', 'Lotería de Navidad') . " $nadal", "$nadal-12-22", '24680', 5, 4, 50]);
        $lo = $id();
        foreach (array_slice($adults, 0, 4) as $k => $p) {
            q('INSERT INTO talonaris(loteria_id, numero, persona_id, data_entrega, venudes) VALUES(?, ?, ?, ?, ?)', [$lo, $k + 1, $p, "$nadal-10-15", $k < 2 ? 50 - $k * 9 : null]);
        }
    }

    // Càrrecs de festa d'enguany i de l'exercici passat
    if (!empty($info['carrecs'])) {
        $carrecs = $info['carrecs'][$es ? 'es' : 'ca'];
        foreach ([[$carrecs[0], $adults[4]], [$carrecs[1], $menors[3]], [$carrecs[2], $adults[0]]] as [$c, $p]) {
            q('INSERT INTO carrecs_festa(persona_id, carrec, exercici, data) VALUES(?, ?, ?, ?)', [$p, $c, $exercici, date('Y-m-d', strtotime('-40 days'))]);
        }
        q('INSERT INTO carrecs_festa(persona_id, carrec, exercici, data) VALUES(?, ?, ?, ?)', [$adults[6], $carrecs[0], nom_exercici($any - 1), date('Y-m-d', strtotime('-400 days'))]);
    }

    // Entitats federades (federacions)
    if ($tipus === 'federacio') {
        foreach ([[$tr('Associació Cultural El Pla', 'Asociación Cultural El Pla'), 'Castelló', 0], [$tr('Colla La Murta', 'Colla La Murta'), 'Vila-real', 1],
                     [$tr('Club Esportiu La Vall', 'Club Deportivo La Vall'), 'Onda', 2], [$tr('Penya El Raval', 'Peña El Raval'), 'Borriana', null]] as $k => [$nom, $pob, $rep]) {
            q("INSERT INTO entitats_federades(nom, numero, tipus, cif, poblacio, persona_id, data_alta, quota, estat) VALUES(?, ?, ?, ?, ?, ?, ?, ?, 'actiu')",
                [$nom, (string)($k + 1), $tr('Associació', 'Asociación'), sprintf('G120000%02d', $k + 1), $pob, $rep !== null ? $adults[$rep] : null, '2018-01-15', 150]);
        }
        $r = [];
        crea_rebuts(calcula_federades($any, $r), date('Y-m-d'));
    }

    exemple_activitats($adults, $tipus === 'club_esportiu');
    q('INSERT INTO comunicats(data, destinataris, n_destinataris, assumpte) VALUES(?, ?, ?, ?)', [date('Y-m-d', strtotime('-3 days')), t('ent.socis'), count($adults),
        $tr('Inscripcions al sopar de germanor', 'Inscripciones a la cena de hermandad')]);
}

/** Activitats d'exemple: una de pròxima amb inscripcions i preu (dos ja tenen rebut), una feta i una gratuïta */
function exemple_activitats(array $adults, bool $club = false): void
{
    $tr = fn(string $ca, string $esp) => idioma() === 'es' ? $esp : $ca;
    $id = fn() => (int)db()->lastInsertId();
    $act = [
        [$tr('Sopar de germanor', 'Cena de hermandad'), 'apat', '+12 days', '21:30', $tr('Local social', 'Local social'), 18, 80],
        [$tr('Eixida a la muntanya', 'Salida a la montaña'), 'eixida', '-25 days', '08:00', $tr('Plaça Major', 'Plaza Mayor'), null, null],
        [$club ? $tr('Torneig de Nadal', 'Torneo de Navidad') : $tr('Xarrada oberta', 'Charla abierta'), $club ? 'competicio' : 'reunio_oberta',
            '+30 days', '18:00', $tr('Centre cívic', 'Centro cívico'), null, null],
    ];
    foreach ($act as $k => [$nom, $tp, $quan, $hora, $lloc, $preu, $places]) {
        $data = date('Y-m-d', strtotime($quan));
        q('INSERT INTO activitats(nom, tipus, data, hora, lloc, preu, places, data_limit, estat) VALUES(?,?,?,?,?,?,?,?,?)',
            [$nom, $tp, $data, $hora, $lloc, $preu, $places, $preu ? date('Y-m-d', strtotime($quan . ' -3 days')) : null, $data < date('Y-m-d') ? 'realitzada' : 'inscripcio_oberta']);
        $a = $id();
        foreach (array_slice($adults, $k, 6 - $k) as $j => $p) {
            $pl = $j === 0 ? 2 : 1;
            q('INSERT INTO activitat_inscripcions(activitat_id, persona_id, places, import, data) VALUES(?, ?, ?, ?, ?)', [$a, $p, $pl, $preu ? $preu * $pl : null, date('Y-m-d', strtotime('-2 days'))]);
        }
        if ($k === 0) {
            $r = [];
            crea_rebuts(array_slice(calcula_activitat(fila('SELECT * FROM activitats WHERE id = ?', [$a]), $r), 0, 2), date('Y-m-d'));
            enllaca_rebuts_activitat($a);
        }
    }
}
