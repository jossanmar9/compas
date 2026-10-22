<?php
// Omple una instal·lació nova de Compàs amb una societat musical gran i inventada (per a provar i per a les captures de la web):
// 278 alumnes, 5 agrupacions (banda 78, coral 27, banda jove 34, orquestra 43, tabal i dolçaina 23), 341 socis, 653 obres,
// dos cursos amb notes i horaris, uns 5.000 rebuts, remeses, instruments, uniformes, loteria, junta, documents i caixa.
//
// Ús: primer, configura una instal·lació nova des del programa (sense dades d'exemple); després
//     COMPAS_DADES=carpeta-de-dades php eines/societat-gran.php
// Les dades estan ancorades al curs 2026/2027 i són sempre les mateixes (llavor fixa). En l'idioma de la instal·lació.
// Copyright (C) 2026 José Vicente Sanjuan
// SPDX-License-Identifier: AGPL-3.0-or-later
declare(strict_types=1);

require dirname(__DIR__) . '/src/bootstrap.php';
require SRC_DIR . '/entitats.php';
require SRC_DIR . '/crud.php';
foreach (glob(SRC_DIR . '/pages/*.php') as $f) {
    require_once $f;
}
if ((int)valor('SELECT COUNT(*) FROM persones') > 0) {
    fwrite(STDERR, "La base de dades ja té persones: cal una instal·lació nova.\n");
    exit(1);
}
session_id('societat-gran');
@session_start();
$_SESSION['usuari_id'] = (int)valor('SELECT MIN(id) FROM usuaris');
mt_srand(2026);

$ES = config('idioma') === 'es';
$tr = fn(string $ca, string $es) => $ES ? $es : $ca;
$AVUI = '2026-10-22';
$rnd = fn(int $a, int $b) => mt_rand($a, $b);
$tria = fn(array $a) => $a[array_rand($a)];
$prob = fn(float $p) => mt_rand() / mt_getrandmax() < $p;
$id = fn() => (int)db()->lastInsertId();
$data = fn(string $ini, string $fi) => date('Y-m-d', mt_rand(strtotime($ini), strtotime($fi)));
$suma = fn(string $d, int $dies) => date('Y-m-d', strtotime("$d +$dies days"));

// ---------- Noms (inventats) ----------
$nomsH = ['Pau', 'Vicent', 'Joan', 'Josep', 'Pere', 'Jordi', 'Àlex', 'Marc', 'Hèctor', 'Andreu', 'Toni', 'Xavier', 'Salvador', 'Rafael', 'Enric', 'Lluís', 'Miquel',
    'Ferran', 'Biel', 'Nil', 'Arnau', 'Pol', 'Iker', 'Hugo', 'Leo', 'Martí', 'Adrià', 'Òscar', 'Carles', 'David', 'Sergi', 'Raül', 'Gerard', 'Ximo', 'Batiste',
    'Quique', 'Manel', 'Àngel', 'Ricard', 'Roger', 'Eloi', 'Aleix', 'Bernat', 'Guillem', 'Jaume', 'Nacho', 'Daniel', 'Mateu', 'Lucas', 'Izan', 'Bruno'];
$nomsD = ['Maria', 'Laia', 'Júlia', 'Marta', 'Anna', 'Clara', 'Paula', 'Carla', 'Neus', 'Amparo', 'Empar', 'Rosa', 'Teresa', 'Vicenta', 'Mireia', 'Aitana', 'Noa',
    'Lucía', 'Martina', 'Sara', 'Irene', 'Elena', 'Alba', 'Aina', 'Nerea', 'Carmen', 'Pilar', 'Isabel', 'Sílvia', 'Núria', 'Montse', 'Eva', 'Lorena', 'Inma',
    'Cristina', 'Raquel', 'Patrícia', 'Ainhoa', 'Valèria', 'Èlia', 'Maialen', 'Berta', 'Gemma', 'Lidia', 'Andrea', 'Sofia', 'Vega', 'Ona', 'Abril', 'Mar', 'Lola'];
$cognomsL = ['Garcia', 'Martínez', 'Ferrer', 'Sanchis', 'Soler', 'Navarro', 'Peris', 'Vidal', 'Climent', 'Monfort', 'Ribes', 'Pastor', 'Bosch', 'Llorens', 'Gil',
    'Domènech', 'Moliner', 'Tormo', 'Castelló', 'Planells', 'Esteve', 'Mompó', 'Bellver', 'Gimeno', 'Roig', 'Segarra', 'Albiol', 'Arnau', 'Cabedo', 'Beltran',
    'Vicent', 'Marqués', 'Cervera', 'Puig', 'Salvador', 'Folch', 'Fabregat', 'Agost', 'Pitarch', 'Carda', 'Nebot', 'Bort', 'Museros', 'Alcaide', 'Huguet',
    'Badenes', 'Escrig', 'Font', 'Grau', 'Ivars', 'Juan', 'López', 'Mas', 'Orts', 'Pérez', 'Querol', 'Ramos', 'Sorribes', 'Torres', 'Usó', 'Vilar', 'Sales',
    'Mezquita', 'Porcar', 'Ripollés', 'Safont', 'Tena', 'Aparici', 'Bou', 'Castell', 'Fuster', 'Guinot', 'Lluch', 'Martí', 'Ortells', 'Ros'];
$carrers = $ES
    ? ['Calle Mayor', 'Calle de San Vicente', 'Plaza de la Música', 'Avenida del País Valenciano', 'Calle del Mar', 'Calle de los Músicos', 'Calle Nueva',
        'Calle de la Iglesia', 'Calle del Sol', 'Avenida de la Constitución', 'Calle de las Eras', 'Calle del Arrabal', 'Calle de San José', 'Calle de la Fuente']
    : ['Carrer Major', 'Carrer de Sant Vicent', 'Plaça de la Música', 'Avinguda del País Valencià', 'Carrer de la Mar', 'Carrer dels Músics', 'Carrer Nou',
        'Carrer de l\'Església', 'Carrer del Sol', 'Avinguda de la Constitució', 'Carrer de les Eres', 'Carrer del Raval', 'Carrer de Sant Josep', 'Carrer de la Font'];
$pobles = [['Vilanova de la Música', '12999', 0.78], ['Benicompàs', '12998', 0.12], ['L\'Alqueria del Pentagrama', '12997', 0.10]];

$dniN = 40000000;
$persones = []; // id => [nom, cognoms, naixement, sexe, llar]
$persona = function (string $sexe, string $naix, ?string $cognoms = null, ?array $llar = null) use (&$dniN, &$persones, $nomsH, $nomsD, $cognomsL, $carrers, $pobles, $rnd, $tria, $prob, $AVUI, $tr) {
    $nom = $tria($sexe === 'H' ? $nomsH : $nomsD);
    $cognoms ??= $tria($cognomsL) . ' ' . $tria($cognomsL);
    $adult = $naix <= date('Y-m-d', strtotime("$AVUI -18 years"));
    $dniN += $rnd(1, 900000);
    $dni = $dniN . 'TRWAGMYFPDXBNJZSQVHLCKE'[$dniN % 23];
    if (!$llar) {
        $x = mt_rand() / mt_getrandmax();
        $p = $x < $pobles[0][2] ? $pobles[0] : ($x < $pobles[0][2] + $pobles[1][2] ? $pobles[1] : $pobles[2]);
        $llar = [$tria($carrers) . ', ' . $rnd(1, 120) . ($prob(.3) ? ', ' . $rnd(1, 6) . 'a' : ''), $p[1], $p[0]];
    }
    $sense = $adult && $prob(.03); // alguns adults sense DNI o telèfon (avisos de dades que falten)
    $email = $adult || $prob(.15) ? strtolower(strtr(iconv('UTF-8', 'ASCII//TRANSLIT', $nom . '.' . explode(' ', $cognoms)[0]), [' ' => '', "'" => ''])) . $rnd(1, 99) . '@exemple.org' : null;
    $cons = $prob(.82) ? 'autoritza' : ($prob(.5) ? 'no_autoritza' : 'pendent');
    q('INSERT INTO persones(nom, cognoms, data_naixement, document, telefon, email, adreca, codi_postal, poblacio, provincia, consentiment_imatge, consentiment_data, creat)
       VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)', [
        $nom, $cognoms, $naix, $adult && !$sense ? $dni : ($prob(.4) ? $dni : null),
        $adult && !$sense ? '6' . $rnd(10, 99) . ' ' . $rnd(100, 999) . ' ' . $rnd(100, 999) : null,
        $email, $llar[0], $llar[1], $llar[2], $tr('Castelló', 'Castellón'), $cons, $cons === 'pendent' ? null : date('Y-m-d', strtotime('-' . $rnd(10, 900) . ' days', strtotime($AVUI))),
        date('Y-m-d H:i:s', strtotime('-' . $rnd(30, 1500) . ' days', strtotime($AVUI))),
    ]);
    $pid = (int)db()->lastInsertId();
    $persones[$pid] = [$nom, $cognoms, $naix, $sexe, $llar];
    return $pid;
};
$sexe = fn() => mt_rand(0, 1) ? 'H' : 'D';
$naixEdat = fn(int $min, int $max) => date('Y-m-d', strtotime($AVUI . ' -' . $rnd($min, $max) . ' years -' . $rnd(0, 364) . ' days'));

db()->beginTransaction();

// ---------- Configuració ----------
$nomSocietat = $tr('Unió Musical La Clau', 'Unión Musical La Clave');
foreach ([
    'societat_nom' => $nomSocietat, 'societat_cif' => 'G12999999', 'societat_adreca' => $tr('Plaça de la Música, 1', 'Plaza de la Música, 1'),
    'societat_poblacio' => '12999 Vilanova de la Música', 'societat_telefon' => '964 000 000', 'societat_email' => 'secretaria@exemple.org',
    'sepa_nom' => $nomSocietat, 'sepa_nif' => 'G12999999', 'sepa_sufix' => '000',
    'descompte_soci' => '10', 'descompte_germans' => '5', 'junta_anys_mandat' => '4',
    'rebut_peu' => $tr('Gràcies per fer possible la música al poble.', 'Gracias por hacer posible la música en el pueblo.'),
] as $c => $v) {
    desa_config($c, $v);
}
$ibanN = 0;
$iban = function () use (&$ibanN) {
    $ibanN++;
    $ccc = ['2100', '0049', '3058', '2038', '0182'][$ibanN % 5] . sprintf('%04d', 1000 + $ibanN % 900) . sprintf('%02d', $ibanN % 97) . sprintf('%010d', 3000000 + $ibanN * 7);
    return 'ES' . sprintf('%02d', 98 - mod97($ccc . '142800')) . $ccc;
};
desa_config('sepa_iban', $iban());

// ---------- Escola: cursos, períodes, assignatures, aules ----------
$cursos = [];
foreach ([['2025/2026', '2025-09-15', '2026-06-19', 'tancat', 0], ['2026/2027', '2026-09-14', '2027-06-18', 'actiu', 1]] as [$n, $di, $df, $e, $act]) {
    q('INSERT INTO cursos(nom, data_inici, data_fi, estat, actual) VALUES(?,?,?,?,?)', [$n, $di, $df, $e, $act]);
    $c = $id();
    $per = [];
    foreach ($ES ? ['1.er trimestre', '2.º trimestre', '3.er trimestre'] : ['1r trimestre', '2n trimestre', '3r trimestre'] as $i => $p) {
        q('INSERT INTO periodes(curs_id, nom, ordre) VALUES(?,?,?)', [$c, $p, $i + 1]);
        $per[] = $id();
    }
    $cursos[$n] = ['id' => $c, 'periodes' => $per, 'inici' => $di];
}
// Clau interna (en valencià) => [nom en l'idioma de la instal·lació, família]
$instrumentsEscola = ['Flauta' => 'vent', 'Oboè' => 'vent', 'Fagot' => 'vent', 'Clarinet' => 'vent', 'Saxòfon' => 'vent', 'Trompa' => 'metall', 'Trompeta' => 'metall',
    'Trombó' => 'metall', 'Bombardí' => 'metall', 'Tuba' => 'metall', 'Percussió' => 'perc', 'Violí' => 'corda', 'Viola' => 'corda', 'Violoncel' => 'corda',
    'Contrabaix' => 'corda', 'Piano' => 'altre', 'Guitarra' => 'altre', 'Dolçaina' => 'trad', 'Tabal' => 'trad'];
$NOM = [ // traducció dels noms d'assignatures, instruments i faristols
    'Sensibilització musical' => 'Sensibilización musical', 'Iniciació musical' => 'Iniciación musical', 'Llenguatge musical' => 'Lenguaje musical',
    'Llenguatge musical per a adults' => 'Lenguaje musical para adultos', 'Cor infantil' => 'Coro infantil', 'Conjunt instrumental' => 'Conjunto instrumental',
    'Orquestra de corda infantil' => 'Orquesta de cuerda infantil', 'Oboè' => 'Oboe', 'Clarinet' => 'Clarinete', 'Saxòfon' => 'Saxofón', 'Trombó' => 'Trombón',
    'Bombardí' => 'Bombardino', 'Percussió' => 'Percusión', 'Violí' => 'Violín', 'Violoncel' => 'Violonchelo', 'Contrabaix' => 'Contrabajo', 'Dolçaina' => 'Dulzaina',
    'Requint' => 'Requinto', 'Saxo alt' => 'Saxo alto', 'Saxo baríton' => 'Saxo barítono', 'Violí I' => 'Violín I', 'Violí II' => 'Violín II', 'Contralt' => 'Contralto', 'Baix' => 'Bajo',
];
$nomL = fn(string $k) => $ES ? ($NOM[$k] ?? $k) : $k;
$assig = [];
foreach ([['Sensibilització musical', 'llenguatge'], ['Iniciació musical', 'llenguatge'], ['Llenguatge musical', 'llenguatge'], ['Llenguatge musical per a adults', 'llenguatge'],
             ['Cor infantil', 'cor'], ['Conjunt instrumental', 'conjunt'], ['Orquestra de corda infantil', 'conjunt']] as [$n, $tp]) {
    q('INSERT INTO assignatures(nom, tipus) VALUES(?,?)', [$nomL($n), $tp]);
    $assig[$n] = $id();
}
foreach (array_keys($instrumentsEscola) as $n) {
    q("INSERT INTO assignatures(nom, tipus) VALUES(?, 'instrument')", [$nomL($n)]);
    $assig[$n] = $id();
}
$aules = [];
$nomAula = ['Aula 5 (piano)' => 'Aula 5 (piano)', 'Aula 9 (percussió)' => 'Aula 9 (percusión)', 'Aula 10 (corda)' => 'Aula 10 (cuerda)', 'Sala d\'assajos' => 'Sala de ensayos', 'Auditori' => 'Auditorio'];
foreach ([['Aula 1', 15], ['Aula 2', 15], ['Aula 3', 12], ['Aula 4', 12], ['Aula 5 (piano)', 4], ['Aula 6', 6], ['Aula 7', 6], ['Aula 8', 6], ['Aula 9 (percussió)', 8],
             ['Aula 10 (corda)', 10], ['Sala d\'assajos', 90], ['Auditori', 250]] as [$n, $cap]) {
    q('INSERT INTO aules(nom, capacitat) VALUES(?,?)', [$ES ? ($nomAula[$n] ?? $n) : $n, $cap]);
    $aules[$n] = $id();
}

// ---------- Professorat ----------
$especialitats = [
    ['Llenguatge musical', 'D'], ['Llenguatge musical', 'H'], ['Llenguatge musical', 'D'], ['Flauta', 'D'], ['Oboè/Fagot', 'H'], ['Clarinet', 'H'], ['Clarinet', 'D'],
    ['Saxòfon', 'H'], ['Trompa', 'D'], ['Trompeta', 'H'], ['Trombó/Bombardí/Tuba', 'H'], ['Percussió', 'H'], ['Percussió', 'D'], ['Violí', 'D'], ['Violí/Viola', 'H'],
    ['Violoncel/Contrabaix', 'D'], ['Piano', 'D'], ['Guitarra', 'H'], ['Dolçaina/Tabal', 'H'], ['Direcció/Conjunts', 'H'], ['Cor', 'D'],
];
$profs = [];
$profPersona = [];
foreach ($especialitats as $i => [$esp, $sx]) {
    $p = $persona($sx, $naixEdat(26, 58));
    q('INSERT INTO professors(persona_id, data_alta, certificat_delictes, observacions) VALUES(?,?,?,?)',
        [$p, $data('2008-09-01', '2025-09-01'), $i === 7 ? null : ($i === 12 ? '2020-09-02' : $data('2024-06-01', '2026-09-10')),
            $tr('Especialitat: ', 'Especialidad: ') . implode('/', array_map(fn($e) => $nomL(['Direcció' => $tr('Direcció', 'Dirección'), 'Conjunts' => $tr('Conjunts', 'Conjuntos'), 'Cor' => $tr('Cor', 'Coro')][$e] ?? $e), explode('/', $esp)))]);
    $pr = $id();
    foreach (explode('/', $esp) as $e) {
        $profs[$e][] = $pr;
    }
    $profPersona[$pr] = $p;
}
$profDe = function (string $assignatura) use ($profs) {
    $map = ['Sensibilització musical' => 'Llenguatge musical', 'Iniciació musical' => 'Llenguatge musical', 'Llenguatge musical per a adults' => 'Llenguatge musical',
        'Cor infantil' => 'Cor', 'Conjunt instrumental' => 'Conjunts', 'Orquestra de corda infantil' => 'Violí'];
    return $profs[$map[$assignatura] ?? $assignatura] ?? $profs['Conjunts'];
};

// ---------- Alumnat (278) amb famílies ----------
$plans = array_merge(array_fill(0, 22, [4, 5]), array_fill(0, 28, [6, 7]), array_fill(0, 120, [8, 12]), array_fill(0, 78, [13, 17]), array_fill(0, 30, [18, 62]));
shuffle($plans);
$alumnes = [];
$families = [];
$repartInstr = ['Flauta' => 26, 'Oboè' => 6, 'Fagot' => 3, 'Clarinet' => 38, 'Saxòfon' => 20, 'Trompa' => 10, 'Trompeta' => 24, 'Trombó' => 12, 'Bombardí' => 6,
    'Tuba' => 5, 'Percussió' => 26, 'Violí' => 26, 'Viola' => 8, 'Violoncel' => 10, 'Contrabaix' => 4, 'Piano' => 14, 'Guitarra' => 10, 'Dolçaina' => 8, 'Tabal' => 4];
$bossaInstr = [];
foreach ($repartInstr as $ins => $n) {
    $bossaInstr = array_merge($bossaInstr, array_fill(0, $n, $ins));
}
shuffle($bossaInstr);
$i = 0;
while ($i < count($plans)) {
    if ($plans[$i][0] >= 18) {
        $grupFam = [[$persona($sexe(), $naixEdat($plans[$i][0], $plans[$i][1])), $plans[$i]]];
        $tutors = [];
        $i++;
    } else {
        $nfills = $prob(.62) ? 1 : ($prob(.8) ? 2 : 3);
        $cog1 = $tria($cognomsL);
        $cog2 = $tria($cognomsL);
        $mare = $persona('D', $naixEdat(32, 52), $cog2 . ' ' . $tria($cognomsL));
        $llar = $persones[$mare][4];
        $tutors = [[$mare, 'mare']];
        if ($prob(.78)) {
            $tutors[] = [$persona('H', $naixEdat(33, 55), $cog1 . ' ' . $tria($cognomsL), $llar), 'pare'];
        }
        if ($prob(.2)) {
            $tutors = array_reverse($tutors);
        }
        $grupFam = [];
        for ($k = 0; $k < $nfills && $i < count($plans) && $plans[$i][0] < 18; $k++, $i++) {
            $grupFam[] = [$persona($sexe(), $naixEdat($plans[$i][0], $plans[$i][1]), $cog1 . ' ' . $cog2, $llar), $plans[$i]];
        }
        $families[] = $tutors;
    }
    foreach ($grupFam as [$p, $pla]) {
        $edat = (int)((strtotime($AVUI) - strtotime($persones[$p][2])) / (365.25 * 86400));
        $antic = $edat >= 8 ? $prob(.82) : ($edat >= 6 ? $prob(.5) : false);
        $alta = $antic ? $data('2019-09-01', '2025-09-15') : $data('2026-09-01', '2026-09-20');
        $instr = $edat >= 7 && $bossaInstr ? array_pop($bossaInstr) : null;
        q('INSERT INTO alumnes(persona_id, data_alta, descompte_pct, observacions) VALUES(?,?,?,?)',
            [$p, $alta, $prob(.02) ? 50 : null, $prob(.04) ? $tr('Beca de l\'ajuntament', 'Beca del ayuntamiento') : null]);
        $al = $id();
        foreach ($tutors as $k => [$t, $rel]) {
            q('INSERT INTO tutories(alumne_id, tutor_id, tipus_relacio, principal, rep_comunicacions) VALUES(?,?,?,?,?)', [$al, $t, $rel, $k === 0 ? 1 : 0, $k === 0 || $prob(.6) ? 1 : 0]);
        }
        $alumnes[$al] = ['persona' => $p, 'edat' => $edat, 'instr' => $instr, 'alta' => $alta];
    }
}
for ($k = 0; $k < 14; $k++) {
    $p = $persona($sexe(), $naixEdat(12, 20));
    q("INSERT INTO alumnes(persona_id, data_alta, data_baixa, estat) VALUES(?,?,?, 'baixa')", [$p, $data('2016-09-01', '2022-09-01'), $data('2023-06-01', '2025-06-30')]);
}

// ---------- Grups i horaris (un joc per curs), sense que cap aula ni cap professor estiga en dos llocs alhora ----------
$ocupAula = [];
$ocupProf = [];
$slots = ['16:00', '17:00', '18:00', '19:00', '20:00'];
$fiSlot = fn(string $h, int $min = 60) => date('H:i', strtotime("2000-01-01 $h +$min minutes"));
$posaHorari = function (int $curs, int $grup, int $prof, array $aulesPossibles, array $dies, int $sessions, int $min = 60, ?array $franges = null) use (&$ocupAula, &$ocupProf, $slots, $fiSlot) {
    foreach ($franges ?? $slots as $s) {
        foreach ($aulesPossibles as $au) {
            $lliures = array_values(array_filter($dies, fn($d) => !isset($ocupAula[$curs][$au][$d][$s]) && !isset($ocupProf[$curs][$prof][$d][$s])));
            if (count($lliures) >= $sessions) {
                foreach (array_slice($lliures, 0, $sessions) as $d) {
                    $ocupAula[$curs][$au][$d][$s] = 1;
                    $ocupProf[$curs][$prof][$d][$s] = 1;
                    q('INSERT INTO horaris(grup_id, dia, hora_inici, hora_fi) VALUES(?,?,?,?)', [$grup, $d, $s, $fiSlot($s, $min)]);
                }
                return $au;
            }
        }
    }
    return null;
};
$aulesInstr = ['vent' => ['Aula 6', 'Aula 7', 'Aula 8'], 'metall' => ['Aula 7', 'Aula 8', 'Aula 6'], 'perc' => ['Aula 9 (percussió)'], 'corda' => ['Aula 10 (corda)', 'Aula 6'],
    'altre' => ['Aula 5 (piano)', 'Aula 8'], 'trad' => ['Aula 9 (percussió)', 'Aula 7']];
$tornProf = [];
foreach ($cursos as $nomCurs => $c) {
    $cid = $c['id'];
    $dif = 2026 - (int)substr($nomCurs, 0, 4);
    $mat = [];
    foreach ($alumnes as $al => $a) {
        if ($a['alta'] <= date('Y-m-d', strtotime($c['inici'] . ' +10 days'))) {
            $mat[$al] = $a['edat'] - $dif;
        }
    }
    $grupsCurs = [];
    $nouGrup = function (string $assignatura, string $nom, array $aulesP, array $dies, int $sessions, int $min = 60, ?array $franges = null)
        use ($cid, $profDe, $posaHorari, $aules, $assig, &$grupsCurs, &$tornProf) {
        $ps = $profDe($assignatura);
        $tornProf[$cid][$assignatura] = (($tornProf[$cid][$assignatura] ?? -1) + 1) % count($ps);
        $prof = $ps[$tornProf[$cid][$assignatura]];
        q('INSERT INTO grups(curs_id, assignatura_id, nom, professor_id) VALUES(?,?,?,?)', [$cid, $assig[$assignatura], $nom, $prof]);
        $g = (int)db()->lastInsertId();
        if ($au = $posaHorari($cid, $g, $prof, array_map(fn($x) => $aules[$x], $aulesP), $dies, $sessions, $min, $franges)) {
            q('UPDATE grups SET aula_id = ? WHERE id = ?', [$au, $g]);
        }
        $grupsCurs[$g] = $assignatura;
        return $g;
    };
    // Llenguatge per nivells (per edat), grups d'uns 16
    $nivells = [];
    $ordinal = $ES ? ['', '1.º', '2.º', '3.º', '4.º'] : ['', '1r', '2n', '3r', '4t'];
    foreach ($mat as $al => $edat) {
        if ($edat < 4 || ($edat >= 14 && $edat < 18)) {
            continue; // de 14 a 17 anys ja han acabat el llenguatge
        }
        if ($edat >= 18) {
            $nivells['Llenguatge musical per a adults|' . $tr('Adults', 'Adultos')][] = $al;
        } elseif ($edat <= 5) {
            $nivells['Sensibilització musical|' . $edat . $tr(' anys', ' años')][] = $al;
        } elseif ($edat <= 7) {
            $nivells['Iniciació musical|' . $ordinal[$edat - 5]][] = $al;
        } elseif ($edat <= 11) {
            $nivells['Llenguatge musical|' . $ordinal[$edat - 7]][] = $al;
        } else {
            $nivells['Llenguatge musical|' . $tr('Grau mitjà ', 'Grado medio ') . ($edat - 11)][] = $al;
        }
    }
    ksort($nivells);
    $matGrups = [];
    foreach ($nivells as $clau => $llista) {
        [$asg, $niv] = explode('|', $clau);
        $parts = (int)ceil(count($llista) / 16);
        foreach (array_chunk($llista, (int)ceil(count($llista) / $parts)) as $k => $tros) {
            $nom = $niv . ($parts > 1 ? ' ' . chr(65 + $k) : '');
            $aulaL = ['Aula 1', 'Aula 2', 'Aula 3', 'Aula 4'];
            $fr = $asg === 'Llenguatge musical per a adults' ? ['20:00', '19:00'] : null;
            $g = $asg === 'Sensibilització musical' ? $nouGrup($asg, $nom, $aulaL, [2, 4], 1) : $nouGrup($asg, $nom, $aulaL, $k % 2 ? [2, 4, 1, 3] : [1, 3, 2, 4], 2, 60, $fr);
            foreach ($tros as $al) {
                $matGrups[$al][] = $g;
            }
        }
    }
    // Instrument: grups d'uns 5 alumnes del mateix instrument, repartits per la vesprada
    $perInstr = [];
    foreach ($mat as $al => $edat) {
        if ($alumnes[$al]['instr'] && $edat >= 7) {
            $perInstr[$alumnes[$al]['instr']][] = $al;
        }
    }
    foreach ($perInstr as $ins => $llista) {
        usort($llista, fn($a, $b) => $mat[$a] <=> $mat[$b]);
        foreach (array_chunk($llista, 5) as $k => $tros) {
            $fr = $slots;
            shuffle($fr);
            $dies = [1, 2, 3, 4, 5];
            shuffle($dies);
            $g = $nouGrup($ins, $tr('Grup ', 'Grupo ') . ($k + 1), $aulesInstr[$instrumentsEscola[$ins]], $dies, 1, 60, $fr);
            foreach ($tros as $al) {
                $matGrups[$al][] = $g;
            }
        }
    }
    $unic = $tr('Únic', 'Único');
    $gCor = $nouGrup('Cor infantil', $unic, ['Sala d\'assajos', 'Aula 1'], [5], 1, 60, ['17:00', '16:00']);
    $gConj = $nouGrup('Conjunt instrumental', $unic, ['Sala d\'assajos'], [5], 1, 90, ['18:00']);
    $gOrq = $nouGrup('Orquestra de corda infantil', $unic, ['Aula 10 (corda)', 'Sala d\'assajos'], [3], 1, 90);
    foreach ($mat as $al => $edat) {
        $ins = $alumnes[$al]['instr'];
        if ($edat >= 6 && $edat <= 11 && $prob(.45)) {
            $matGrups[$al][] = $gCor;
        }
        if ($ins && $edat >= 10 && $edat <= 14 && in_array($instrumentsEscola[$ins], ['vent', 'metall', 'perc'], true)) {
            $matGrups[$al][] = $gConj;
        }
        if ($ins && $edat >= 8 && $edat <= 14 && $instrumentsEscola[$ins] === 'corda') {
            $matGrups[$al][] = $gOrq;
        }
    }
    foreach ($mat as $al => $edat) {
        if (empty($matGrups[$al])) {
            continue;
        }
        $dataM = $alumnes[$al]['alta'] > $c['inici'] ? $alumnes[$al]['alta'] : date('Y-m-d', strtotime($c['inici'] . ' -' . $rnd(5, 60) . ' days'));
        q('INSERT INTO matricules(alumne_id, curs_id, data) VALUES(?,?,?)', [$al, $cid, $dataM]);
        $m = $id();
        foreach (array_unique($matGrups[$al]) as $g) {
            q('INSERT INTO matricula_grups(matricula_id, grup_id) VALUES(?,?)', [$m, $g]);
        }
    }
    // Notes del curs passat (tres trimestres)
    if ($dif === 1) {
        $base = [];
        foreach ($mat as $al => $_) {
            $base[$al] = $rnd(5, 9);
        }
        $observacions = $ES
            ? ['Muy buena actitud en clase.', 'Tiene que estudiar más en casa.', 'Ha mejorado mucho este trimestre.', 'Falta a menudo a clase.', 'Buena afinación; tiene que cuidar el ritmo.', 'Excelente progreso.']
            : ['Molt bona actitud a classe.', 'Ha d\'estudiar més a casa.', 'Ha millorat molt este trimestre.', 'Falta sovint a classe.', 'Bona afinació; ha de cuidar el ritme.', 'Excel·lent progrés.'];
        foreach ($grupsCurs as $g => $asg) {
            $prof = (int)valor('SELECT professor_id FROM grups WHERE id = ?', [$g]);
            $als = q('SELECT m.alumne_id FROM matricula_grups mg JOIN matricules m ON m.id = mg.matricula_id WHERE mg.grup_id = ?', [$g])->fetchAll(PDO::FETCH_COLUMN);
            foreach ($c['periodes'] as $k => $per) {
                foreach ($als as $al) {
                    q('INSERT INTO qualificacions(grup_id, alumne_id, periode_id, professor_id, nota, observacions, modificat) VALUES(?,?,?,?,?,?,?)', [
                        $g, $al, $per, $prof, (string)max(3, min(10, $base[$al] + $rnd(-2, 2) + ($k === 2 ? 1 : 0))), $prob(.12) ? $tria($observacions) : null,
                        ['2025-12-18', '2026-03-27', '2026-06-17'][$k] . ' 19:30:00',
                    ]);
                }
            }
        }
    }
}
desa_config('butlleti_peu', $tr('Les classes es reprenen el 14 de setembre. Bon estiu!', 'Las clases se reanudan el 14 de septiembre. ¡Feliz verano!'));

// ---------- Agrupacions i membres ----------
$agr = [];
$nomsAgr = ['Banda Simfònica' => 'Banda Sinfónica', 'Coral Polifònica' => 'Coral Polifónica', 'Banda Jove' => 'Banda Joven', 'Orquestra' => 'Orquesta',
    'Colla de Tabal i Dolçaina' => 'Colla de Tabal y Dulzaina'];
foreach ([['Banda Simfònica', 'banda', '1902-05-12'], ['Coral Polifònica', 'coral', '1985-10-01'], ['Banda Jove', 'banda_juvenil', '1998-09-15'],
             ['Orquestra', 'orquestra', '2004-01-20'], ['Colla de Tabal i Dolçaina', 'altra', '1994-03-19']] as [$n, $tp, $d]) {
    q('INSERT INTO agrupacions(nom, tipus, data_creacio) VALUES(?,?,?)', [$ES ? $nomsAgr[$n] : $n, $tp, $d]);
    $agr[$n] = $id();
}
$alumnesPer = [];
foreach ($alumnes as $al => $a) {
    if ($a['instr']) {
        $alumnesPer[$a['instr']][] = $al;
    }
}
$membre = function (int $p, string $ag, ?string $instr, ?string $alta = null, string $funcio = 'membre') use ($agr, $data, $nomL) {
    q('INSERT INTO membres(persona_id, agrupacio_id, instrument_veu, funcio, data_alta) VALUES(?,?,?,?,?)',
        [$p, $agr[$ag], $instr === null ? null : $nomL($instr), $funcio, $alta ?? $data('1975-06-01', '2026-09-30')]);
};
$escolaDe = fn(string $f) => match (true) {
    str_starts_with($f, 'Saxo') => 'Saxòfon', $f === 'Requint' => 'Clarinet', str_starts_with($f, 'Violí') => 'Violí', default => $f,
};
$plantilles = [
    'Banda Simfònica' => ['Flauta' => 8, 'Oboè' => 2, 'Fagot' => 1, 'Requint' => 1, 'Clarinet' => 18, 'Saxo alt' => 5, 'Saxo tenor' => 3, 'Saxo baríton' => 1, 'Trompa' => 5,
        'Trompeta' => 9, 'Trombó' => 6, 'Bombardí' => 4, 'Tuba' => 4, 'Contrabaix' => 1, 'Percussió' => 10],
    'Banda Jove' => ['Flauta' => 5, 'Oboè' => 1, 'Clarinet' => 9, 'Saxo alt' => 4, 'Saxo tenor' => 1, 'Trompa' => 2, 'Trompeta' => 4, 'Trombó' => 2, 'Bombardí' => 1, 'Tuba' => 1, 'Percussió' => 4],
    'Orquestra' => ['Violí I' => 10, 'Violí II' => 9, 'Viola' => 6, 'Violoncel' => 6, 'Contrabaix' => 3, 'Flauta' => 2, 'Oboè' => 2, 'Clarinet' => 2, 'Fagot' => 1, 'Trompa' => 2],
    'Coral Polifònica' => ['Soprano' => 8, 'Contralt' => 8, 'Tenor' => 5, 'Baix' => 6],
    'Colla de Tabal i Dolçaina' => ['Dolçaina' => 15, 'Tabal' => 8],
];
$edatsAg = ['Banda Simfònica' => [15, 99], 'Banda Jove' => [11, 17], 'Orquestra' => [12, 99], 'Colla de Tabal i Dolçaina' => [10, 99]];
$adultsNous = [];
$musicsAdults = [];
$usats = [];
foreach ($plantilles as $ag => $pl) {
    foreach ($pl as $faristol => $n) {
        for ($k = 0; $k < $n; $k++) {
            $p = null;
            if (isset($edatsAg[$ag])) {
                [$mn, $mx] = $edatsAg[$ag];
                foreach ($alumnesPer[$escolaDe($faristol)] ?? [] as $al) {
                    $a = $alumnes[$al];
                    if ($a['edat'] >= $mn && $a['edat'] <= $mx && !isset($usats[$ag][$a['persona']]) && $prob($ag === 'Banda Simfònica' ? .35 : .9)) {
                        $p = $a['persona'];
                        break;
                    }
                }
            }
            if (!$p && $ag !== 'Banda Jove') {
                $cand = array_values(array_filter(array_keys($adultsNous), fn($x) => $adultsNous[$x] === $escolaDe($faristol) && !isset($usats[$ag][$x])));
                if ($ag === 'Coral Polifònica' && $prob(.5)) {
                    $p = $tria($families)[0][0]; // una mare o un pare de l'escola
                    if (($faristol === 'Soprano' || $faristol === 'Contralt') !== ($persones[$p][3] === 'D') || isset($usats[$ag][$p])) {
                        $p = null;
                    }
                } elseif ($cand && $ag === 'Orquestra' && $prob(.5)) {
                    $p = $tria($cand);
                }
                if (!$p) {
                    $sx = in_array($faristol, ['Soprano', 'Contralt'], true) ? 'D' : (in_array($faristol, ['Tenor', 'Baix'], true) ? 'H' : $sexe());
                    $p = $persona($sx, $naixEdat(18, 72));
                    $adultsNous[$p] = $escolaDe($faristol);
                }
                $musicsAdults[$p] = true;
            }
            if (!$p) {
                $p = $persona($sexe(), $naixEdat(12, 17));
            }
            $usats[$ag][$p] = true;
            $alta = $ag === 'Banda Simfònica' && isset($adultsNous[$p]) ? $data(date('Y-m-d', strtotime($persones[$p][2] . ' +14 years')), '2025-09-01') : null;
            $membre($p, $ag, $faristol, $alta);
        }
    }
}
foreach (['Banda Simfònica' => 'Conjunts', 'Banda Jove' => 'Clarinet', 'Orquestra' => 'Violí', 'Coral Polifònica' => 'Cor', 'Colla de Tabal i Dolçaina' => 'Dolçaina'] as $ag => $esp) {
    $membre($profPersona[$profs[$esp][0]], $ag, null, $data('2012-09-01', '2022-09-01'), 'director_titular');
}
$membre($profPersona[$profs['Saxòfon'][0]], 'Banda Simfònica', null, '2023-01-10', 'director_assistent');
for ($k = 0; $k < 9; $k++) {
    q("INSERT INTO membres(persona_id, agrupacio_id, instrument_veu, data_alta, data_baixa, estat) VALUES(?,?,?,?,?, 'baixa')",
        [$persona($sexe(), $naixEdat(25, 80)), $agr['Banda Simfònica'], $nomL($tria(['Clarinet', 'Trompeta', 'Flauta', 'Percussió', 'Trombó'])), $data('1970-01-01', '2010-01-01'), $data('2015-01-01', '2026-06-30')]);
}

// ---------- Socis (341 actius) ----------
$tipusSoci = [];
foreach (q('SELECT id, codi FROM tipus_socis')->fetchAll() as $t) {
    $tipusSoci[$t['codi']] = (int)$t['id'];
}
$socis = [];
$fesSoci = function (int $p, string $codi, string $alta) use (&$socis, $tipusSoci) {
    if (isset($socis[$p]) || count($socis) >= 341) {
        return;
    }
    q('INSERT INTO socis(persona_id, tipus, data_alta) VALUES(?,?,?)', [$p, $tipusSoci[$codi], $alta]);
    $socis[$p] = true;
};
foreach (array_keys($musicsAdults) as $p) {
    if ($prob(.92)) {
        $fesSoci($p, 'ordinari', date('Y-m-d', max(strtotime($persones[$p][2] . ' +16 years'), strtotime($data('1985-01-01', '2026-06-01')))));
    }
}
foreach ($alumnes as $a) {
    if ($a['edat'] >= 14 && $a['edat'] < 18 && $prob(.35)) {
        $fesSoci($a['persona'], 'juvenil', $data('2022-01-01', '2026-09-30'));
    }
}
foreach ($families as $fam) {
    if ($prob(.55)) {
        $fesSoci($fam[0][0], $prob(.85) ? 'ordinari' : 'protector', $data('2005-01-01', '2026-09-30'));
    }
}
foreach ($profPersona as $p) {
    if ($prob(.3)) {
        $fesSoci($p, 'ordinari', $data('2010-01-01', '2025-01-01'));
    }
}
while (count($socis) < 341) {
    $x = mt_rand(1, 100);
    $fesSoci($persona($sexe(), $naixEdat(30, 92)), $x <= 70 ? 'ordinari' : ($x <= 95 ? 'protector' : 'honorari'), $data('1960-01-01', '2026-09-30'));
}
$k = 0;
foreach (q('SELECT id FROM socis ORDER BY data_alta, id')->fetchAll(PDO::FETCH_COLUMN) as $s) {
    q('UPDATE socis SET numero = ? WHERE id = ?', [(string)(++$k), $s]);
}
$motiusBaixa = $ES ? ['Defunción', 'Cambio de residencia', 'Lo ha pedido', null] : ['Defunció', 'Canvi de residència', 'Ho ha demanat', null];
for ($k = 0; $k < 25; $k++) {
    q("INSERT INTO socis(persona_id, numero, tipus, data_alta, data_baixa, estat, observacions) VALUES(?,?,?,?,?, 'baixa', ?)",
        [$persona($sexe(), $naixEdat(30, 90)), (string)(900 + $k), $tipusSoci['ordinari'], $data('1980-01-01', '2015-01-01'), $data('2018-01-01', '2026-07-31'), $tria($motiusBaixa)]);
}

// ---------- Instruments, cessions i manteniments ----------
$tipusInv = ['Flauta' => [8, 'Yamaha', 'YFL-212'], 'Clarinet' => [16, 'Buffet', 'E11'], 'Saxo alt' => [8, 'Yamaha', 'YAS-280'], 'Saxo tenor' => [4, 'Jupiter', 'JTS-700'],
    'Saxo baríton' => [2, 'Yamaha', 'YBS-480'], 'Oboè' => [3, 'Rigoutat', 'Expression'], 'Fagot' => [2, 'Moosmann', '100'], 'Trompeta' => [10, 'Bach', 'TR-300'],
    'Trompa' => [6, 'Hans Hoyer', '6801'], 'Trombó' => [6, 'Yamaha', 'YSL-354'], 'Bombardí' => [4, 'Besson', 'BE-162'], 'Tuba' => [4, 'Miraphone', '186'],
    'Bombo de concert' => [2, 'Adams', null], 'Timbals (joc de 4)' => [1, 'Adams', 'Revolution'], 'Xilòfon' => [1, 'Majestic', null], 'Caixa' => [6, 'Pearl', 'Philharmonic'],
    'Plats' => [3, 'Zildjian', null], 'Marimba' => [1, 'Adams', 'Alpha'], 'Violí 4/4' => [6, 'Stentor', 'Student II'], 'Violí 3/4' => [5, 'Stentor', 'Student I'],
    'Violí 1/2' => [4, 'Stentor', 'Student I'], 'Viola' => [4, 'Gliga', null], 'Violoncel' => [4, 'Stentor', 'Conservatoire'], 'Contrabaix' => [2, 'Gliga', null],
    'Dolçaina' => [8, null, null], 'Tabal' => [5, null, null], 'Piano vertical' => [2, 'Yamaha', 'U1'], 'Piano de cua' => [1, 'Kawai', 'GX-2']];
$nomInv = ['Bombo de concert' => 'Bombo de concierto', 'Timbals (joc de 4)' => 'Timbales (juego de 4)', 'Xilòfon' => 'Xilófono', 'Caixa' => 'Caja', 'Plats' => 'Platos',
    'Violí 4/4' => 'Violín 4/4', 'Violí 3/4' => 'Violín 3/4', 'Violí 1/2' => 'Violín 1/2', 'Piano de cua' => 'Piano de cola'];
$magatzem = $tr('Magatzem', 'Almacén');
$instrs = [];
$n = 0;
foreach ($tipusInv as $tp => [$q, $marca, $model]) {
    for ($k = 0; $k < $q; $k++) {
        $n++;
        $estat = $prob(.08) ? 'regular' : ($prob(.04) ? 'reparacio' : 'bo');
        $ubi = $tp === 'Piano de cua' ? $tr('Auditori', 'Auditorio') : ($tp === 'Piano vertical' ? 'Aula 5 (piano)' : $tria([$magatzem, $magatzem, $tr('Sala d\'assajos', 'Sala de ensayos'), $tr('Aula 9 (percussió)', 'Aula 9 (percusión)')]));
        q('INSERT INTO instruments(inventari, tipus, marca, model, num_serie, estat, ubicacio, data_adquisicio, observacions) VALUES(?,?,?,?,?,?,?,?,?)', [
            sprintf('INV-%03d', $n), $ES ? ($nomInv[$tp] ?? $nomL($tp)) : $tp, $marca, $model, $marca ? strtoupper(substr($marca, 0, 2)) . $rnd(100000, 999999) : null, $estat, $ubi,
            $data('1995-01-01', '2026-06-01'), $estat === 'reparacio' ? $tr('Al taller', 'En el taller') : null,
        ]);
        $instrs[] = [$id(), $tp];
    }
}
q("INSERT INTO instruments(inventari, tipus, marca, estat, ubicacio, data_adquisicio, observacions) VALUES('INV-900', ?, 'Selmer', 'baixa', ?, '1988-03-01', ?)",
    [$nomL('Clarinet'), $magatzem, $tr('Irreparable', 'Irreparable')]);
$escolaInstr = fn(string $tp) => match (true) {
    str_starts_with($tp, 'Saxo') => 'Saxòfon', str_starts_with($tp, 'Violí') => 'Violí', default => $tp,
};
$bo = $tr('Bo', 'Bueno');
$cedits = [];
foreach ($instrs as [$ins, $tp]) {
    $esc = $escolaInstr($tp);
    if (empty($alumnesPer[$esc]) || !$prob(.7)) {
        continue;
    }
    $cands = array_values(array_filter($alumnesPer[$esc], fn($al) => !isset($cedits[$al])));
    if (!$cands) {
        continue;
    }
    $al = $tria($cands);
    $cedits[$al] = true;
    $p = $alumnes[$al]['persona'];
    if ($prob(.25)) {
        $e = $data('2022-09-15', '2024-10-01');
        q('INSERT INTO cessions(instrument_id, persona_id, data_entrega, data_prevista, data_retorn, estat_entrega, estat_retorn, fianca) VALUES(?,?,?,?,?,?,?,?)',
            [$ins, $p, $e, $suma($e, 280), $suma($e, $rnd(250, 600)), $bo, $tria([$bo, $bo, 'Regular']), 50]);
        continue;
    }
    q('INSERT INTO cessions(instrument_id, persona_id, data_entrega, data_prevista, estat_entrega, fianca, observacions) VALUES(?,?,?,?,?,?,?)', [
        $ins, $p, $data('2025-09-10', '2026-10-15'), $prob(.12) ? $data('2026-06-30', '2026-10-15') : '2027-06-30', $bo, $tria([50, 50, 30, null]),
        $prob(.1) ? $tr('Inclou estoig i faristol', 'Incluye estuche y atril') : null,
    ]);
}
$tasques = $ES
    ? ['Revisión general', 'Cambio de zapatillas', 'Limpieza y engrase de pistones', 'Cambio de cuerdas', 'Ajuste de mecanismo', 'Afinación', 'Cambio de parche', 'Reparación de soldadura', 'Cambio de corchos']
    : ['Revisió general', 'Canvi de zapatilles', 'Neteja i greixat de pistons', 'Canvi de cordes', 'Ajust de mecanisme', 'Afinació', 'Canvi de pell', 'Reparació de soldadura', 'Canvi de suros'];
for ($k = 0; $k < 45; $k++) {
    q('INSERT INTO manteniments(instrument_id, data, descripcio, cost) VALUES(?,?,?,?)', [$tria($instrs)[0], $data('2023-01-01', '2026-10-15'), $tria($tasques), $rnd(15, 380)]);
}

// ---------- Economia ----------
foreach ([
    [$tr('Quota de soci ordinari', 'Cuota de socio ordinario'), 'quota_soci', 'ordinari', null, 'trimestral', 15],
    [$tr('Quota de soci protector', 'Cuota de socio protector'), 'quota_soci', 'protector', null, 'trimestral', 9],
    [$tr('Quota de soci juvenil', 'Cuota de socio juvenil'), 'quota_soci', 'juvenil', null, 'trimestral', 6],
    [$tr('Matrícula', 'Matrícula'), 'matricula', null, null, 'unica', 35],
    [$nomL('Sensibilització musical'), 'classe', null, 'Sensibilització musical', 'mensual', 22], [$nomL('Iniciació musical'), 'classe', null, 'Iniciació musical', 'mensual', 26],
    [$nomL('Llenguatge musical'), 'classe', null, 'Llenguatge musical', 'mensual', 28], [$nomL('Llenguatge musical per a adults'), 'classe', null, 'Llenguatge musical per a adults', 'mensual', 28],
    [$nomL('Cor infantil'), 'classe', null, 'Cor infantil', 'mensual', 8], [$nomL('Conjunt instrumental'), 'classe', null, 'Conjunt instrumental', 'mensual', 10],
    [$nomL('Orquestra de corda infantil'), 'classe', null, 'Orquestra de corda infantil', 'mensual', 10],
] as [$nom, $tp, $ts, $as, $per, $imp]) {
    q('INSERT INTO tarifes(nom, tipus, tipus_soci, assignatura_id, periodicitat, import) VALUES(?,?,?,?,?,?)', [$nom, $tp, $ts ? $tipusSoci[$ts] : null, $as ? $assig[$as] : null, $per, $imp]);
}
foreach (array_keys($instrumentsEscola) as $ins) {
    q("INSERT INTO tarifes(nom, tipus, assignatura_id, periodicitat, import) VALUES(?, 'classe', ?, 'mensual', ?)",
        [$tr('Classe de ', 'Clase de ') . mb_strtolower($nomL($ins)), $assig[$ins], $ins === 'Piano' ? 48 : 42]);
}
$pagadors = [];
foreach ($families as $fam) {
    $pagadors[$fam[0][0]] = true;
}
foreach ($alumnes as $a) {
    if ($a['edat'] >= 18) {
        $pagadors[$a['persona']] = true;
    }
}
foreach (array_keys($socis) as $p) {
    $pagadors[$p] = true;
}
foreach (array_keys($pagadors) as $p) {
    if ($prob(.7)) {
        q('INSERT INTO mandats(persona_id, iban, data_signatura) VALUES(?,?,?)', [$p, $iban(), $data('2012-01-01', '2026-09-20')]);
        $m = $id();
        q('UPDATE mandats SET referencia = ? WHERE id = ?', [referencia_mandat($m), $m]);
    }
}
db()->commit();

// Rebuts: quotes de 2025 i 2026 i escola dels dos cursos fins a octubre (amb el càlcul del programa: descomptes, germans, domiciliació…)
$res = [];
foreach ([2025, 2026] as $any) {
    foreach (['T1', 'T2', 'T3', 'T4'] as $t) {
        crea_rebuts(calcula_quotes($any, $t, $res), sprintf('%d-%02d-05', $any, ((int)$t[1] - 1) * 3 + 1));
    }
}
foreach ($cursos as $c) {
    $cf = fila('SELECT * FROM cursos WHERE id = ?', [$c['id']]);
    crea_rebuts(calcula_escola($cf, 'MAT', $res), date('Y-m-d', strtotime($c['inici'] . ' -5 days')));
    foreach (array_keys(periodes_escola($cf)) as $codi) {
        if ($codi[0] === 'M' && $codi !== 'MAT' && substr($codi, 1) <= '2026-10') {
            crea_rebuts(calcula_escola($cf, $codi, $res), substr($codi, 1) . '-03');
        }
    }
}
db()->beginTransaction();
q("UPDATE rebuts SET estat = 'pagat', data_pagament = date(data_emissio, '+' || (abs(random()) % 20 + 2) || ' days') WHERE data_emissio < '2026-10-01'");
// Llavor fixa també per als que es retornen o queden pendents
foreach (array_slice(q("SELECT id FROM rebuts WHERE data_emissio < '2026-10-01' AND forma_pagament = 'domiciliacio' ORDER BY id")->fetchAll(PDO::FETCH_COLUMN), 0) as $k => $r) {
    if ($k % 157 === 40) {
        q("UPDATE rebuts SET estat = 'retornat', data_pagament = NULL, observacions = ? WHERE id = ?", [$tr('Retornat pel banc', 'Devuelto por el banco'), $r]);
    }
}
foreach (q("SELECT id FROM rebuts WHERE data_emissio BETWEEN '2026-04-01' AND '2026-09-30' AND forma_pagament = 'efectiu' ORDER BY id")->fetchAll(PDO::FETCH_COLUMN) as $k => $r) {
    if ($k % 13 === 5) {
        q("UPDATE rebuts SET estat = 'per_cobrar', data_pagament = NULL WHERE id = ?", [$r]);
    }
}
q("UPDATE rebuts SET forma_pagament = 'transferencia' WHERE forma_pagament = 'efectiu' AND id % 9 = 0");
foreach (q("SELECT DISTINCT substr(data_emissio, 1, 7) FROM rebuts WHERE forma_pagament = 'domiciliacio' ORDER BY 1")->fetchAll(PDO::FETCH_COLUMN) as $mes) {
    $preparada = $mes === '2026-10';
    q('INSERT INTO remeses(referencia, data_creacio, data_cobrament, estat) VALUES(?,?,?,?)', ['tmp-' . $mes, $mes . '-06 10:00:00', $preparada ? '2026-10-30' : $mes . '-10', $preparada ? 'preparada' : 'cobrada']);
    $rid = $id();
    q('UPDATE remeses SET referencia = ? WHERE id = ?', [sprintf('REM-%s06-%03d', str_replace('-', '', $mes), $rid), $rid]);
    q("UPDATE rebuts SET remesa_id = ? WHERE forma_pagament = 'domiciliacio' AND substr(data_emissio, 1, 7) = ?", [$rid, $mes]);
}
q("UPDATE mandats SET ultim_us = (SELECT MAX(r.data_emissio) FROM rebuts r WHERE r.mandat_id = mandats.id AND r.data_emissio < '2026-10-01')");
$conceptes = $ES ? ['Donativo', 'Alquiler de traje de concierto', 'Libro del centenario', 'Cena de Santa Cecilia'] : ['Donatiu', 'Lloguer de vestit de concert', 'Llibre del centenari', 'Sopar de Santa Cecília'];
for ($k = 0; $k < 12; $k++) {
    $d = $data('2025-11-01', '2026-10-20');
    q("INSERT INTO rebuts(numero, persona_id, concepte, tipus, data_emissio, import, forma_pagament, estat, data_pagament) VALUES(?,?,?, 'altres', ?, ?, 'efectiu', 'pagat', ?)",
        [numero_rebut_nou($d), $tria(array_keys($socis)), $tria($conceptes), $d, $tria([10, 20, 25, 30, 50, 100]), $d]);
}

// ---------- Loteria de Nadal ----------
foreach ([[2025, 'tancat', '04512, 61207'], [2026, 'actiu', '18326, 70954']] as [$any, $est, $nums]) {
    q('INSERT INTO loteries(nom, data_sorteig, numeros, preu, juga, participacions_talonari, estat, peu) VALUES(?,?,?,?,?,?,?,?)', [
        $tr('Loteria de Nadal ', 'Lotería de Navidad ') . $any, "$any-12-22", $nums, 5, 4, 50, $est,
        $tr('La societat juga 4 € de cada participació. Caduca als tres mesos del sorteig.', 'La sociedad juega 4 € de cada participación. Caduca a los tres meses del sorteo.'),
    ]);
    $lo = $id();
    $musics = q("SELECT DISTINCT persona_id FROM membres WHERE estat = 'actiu' AND funcio = 'membre' ORDER BY persona_id")->fetchAll(PDO::FETCH_COLUMN);
    shuffle($musics);
    $musics = array_slice($musics, 0, 70);
    foreach ($musics as $k => $p) {
        $entrega = "$any-10-" . sprintf('%02d', $rnd(3, 18));
        if ($any === 2025 || $prob(.15)) {
            $v = $rnd(30, 50);
            q('INSERT INTO talonaris(loteria_id, numero, persona_id, data_entrega, venudes, tornades, import_liquidat, data_liquidacio) VALUES(?,?,?,?,?,?,?,?)',
                [$lo, $k + 1, $p, $entrega, $v, 50 - $v, $v * 5, $any === 2025 ? "$any-12-" . sprintf('%02d', $rnd(1, 19)) : '2026-10-20']);
        } else {
            q('INSERT INTO talonaris(loteria_id, numero, persona_id, data_entrega) VALUES(?,?,?,?)', [$lo, $k + 1, $p, $entrega]);
        }
    }
    for ($k = count($musics) + 1; $k <= count($musics) + 10; $k++) {
        q('INSERT INTO talonaris(loteria_id, numero) VALUES(?,?)', [$lo, $k]);
    }
}

// ---------- Junta, reunions, documents i comunicats ----------
$juntaAdults = array_values(array_filter(array_keys($socis), fn($p) => $persones[$p][2] <= '1990-01-01'));
shuffle($juntaAdults);
$vocal = fn(string $ca, string $es) => $tr('Vocal ', 'Vocal ') . $tr($ca, $es);
foreach ([['presidencia', null], ['vicepresidencia', null], ['secretaria', null], ['tresoreria', null], ['vocal', $vocal('d\'escola', 'de escuela')],
             ['vocal', $vocal('de la banda', 'de la banda')], ['vocal', $vocal('de material i arxiu', 'de material y archivo')], ['vocal', $vocal('de festes', 'de fiestas')],
             ['vocal', $vocal('de la coral', 'de la coral')]] as $k => [$c, $desc]) {
    q('INSERT INTO carrecs(persona_id, carrec, descripcio, data_inici) VALUES(?,?,?,?)', [$juntaAdults[$k], $c, $desc, '2024-06-14']);
}
foreach ([['presidencia', null], ['secretaria', null], ['tresoreria', null], ['vocal', $vocal('d\'escola', 'de escuela')]] as $k => [$c, $desc]) {
    q('INSERT INTO carrecs(persona_id, carrec, descripcio, data_inici, data_fi) VALUES(?,?,?,?,?)', [$juntaAdults[10 + $k], $c, $desc, '2020-06-12', '2024-06-14']);
}
$nomP = fn(int $p) => $persones[$p][0] . ' ' . $persones[$p][1];
desa_config('certificat_signant', $nomP($juntaAdults[2]));
desa_config('certificat_president', $nomP($juntaAdults[0]));
$acta = $tr("Lectura i aprovació, si escau, de l'acta anterior\n", "Lectura y aprobación, si procede, del acta anterior\n");
$precs = $tr("Precs i preguntes", "Ruegos y preguntas");
$ordres = $ES
    ? ["Concierto de Santa Cecilia\nLotería de Navidad\n", "Matrícula del curso próximo\nContratación del profesorado\n", "Preparación de las fiestas patronales\nCompra de instrumentos\n", "Certamen provincial de bandas\nUniformes nuevos\n"]
    : ["Concert de Santa Cecília\nLoteria de Nadal\n", "Matrícula del curs vinent\nContractació del professorat\n", "Preparació de les festes patronals\nCompra d'instruments\n", "Certamen provincial de bandes\nUniformes nous\n"];
$acords = $ES
    ? ['Aprobar el presupuesto del concierto.', 'Encargar 40 polos nuevos.', 'Comprar una tuba y dos clarinetes.', 'Convocar la asamblea general.']
    : ['Aprovar el pressupost del concert.', 'Encarregar 40 polos nous.', 'Comprar una tuba i dos clarinets.', 'Convocar l\'assemblea general.'];
$local = $tr('Local social', 'Local social');
foreach (['2025-01-22', '2025-03-19', '2025-05-21', '2025-07-09', '2025-09-17', '2025-11-12', '2026-01-21', '2026-03-18', '2026-05-20', '2026-07-08', '2026-09-16'] as $k => $d) {
    q("INSERT INTO reunions(tipus, data, hora, lloc, data_convocatoria, ordre_del_dia, assistents, desenvolupament, acords, hora_fi, estat) VALUES('junta',?,?,?,?,?,?,?,?,?, 'celebrada')", [
        $d, '20:30', $local, $suma($d, -7), $acta . $ordres[$k % 4] . $precs, $tr('Assistixen ', 'Asisten ') . $rnd(6, 9) . $tr(' membres de la junta.', ' miembros de la junta.'),
        $tr('Es tracten els punts de l\'ordre del dia. La presidència informa de les activitats previstes i s\'obri un torn de paraula.',
            'Se tratan los puntos del orden del día. La presidencia informa de las actividades previstas y se abre un turno de palabra.'),
        $tr("Aprovar l'acta anterior.\n", "Aprobar el acta anterior.\n") . $acords[$k % 4], '22:15',
    ]);
}
foreach (['2025-03-28', '2026-03-27'] as $d) {
    q("INSERT INTO reunions(tipus, data, hora, hora_segona, lloc, data_convocatoria, ordre_del_dia, assistents, desenvolupament, acords, hora_fi, estat) VALUES('assemblea_ordinaria',?,?,?,?,?,?,?,?,?,?, 'celebrada')", [
        $d, '19:30', '20:00', $tr('Auditori', 'Auditorio'), $suma($d, -15),
        $acta . $tr("Memòria d'activitats\nEstat de comptes de l'exercici anterior\nPressupost de l'exercici\n", "Memoria de actividades\nEstado de cuentas del ejercicio anterior\nPresupuesto del ejercicio\n") . $precs,
        $rnd(55, 90) . $tr(' socis i sòcies presents i ', ' socios y socias presentes y ') . $rnd(5, 20) . $tr(' representats.', ' representados.'),
        $tr('La tresoreria presenta els comptes i el pressupost.', 'La tesorería presenta las cuentas y el presupuesto.'),
        $tr("Aprovar l'acta anterior.\nAprovar els comptes de l'exercici anterior.\nAprovar el pressupost de l'exercici.", "Aprobar el acta anterior.\nAprobar las cuentas del ejercicio anterior.\nAprobar el presupuesto del ejercicio."), '21:40',
    ]);
}
q("INSERT INTO reunions(tipus, data, hora, lloc, data_convocatoria, ordre_del_dia, estat) VALUES('junta', '2026-10-28', '20:30', ?, '2026-10-21', ?, 'convocada')", [$local, $acta . $ordres[0] . $precs]);
foreach ([
    [$tr('Estatuts de la societat', 'Estatutos de la sociedad'), 'estatuts', '2012-04-20', null], [$tr('Reglament de règim intern', 'Reglamento de régimen interno'), 'reglament', '2019-03-15', null],
    [$tr('Inscripció al Registre d\'Associacions', 'Inscripción en el Registro de Asociaciones'), 'registre', '1983-06-10', null],
    [$tr('Assegurança de responsabilitat civil', 'Seguro de responsabilidad civil'), 'asseguranca', '2025-11-15', '2026-11-15'],
    [$tr('Assegurança d\'instruments', 'Seguro de instrumentos'), 'asseguranca', '2026-01-10', '2027-01-10'],
    [$tr('Conveni amb l\'ajuntament', 'Convenio con el ayuntamiento'), 'conveni', '2026-01-15', '2026-12-31'],
    [$tr('Subvenció de la Diputació 2026', 'Subvención de la Diputación 2026'), 'subvencio', '2026-05-04', null], [$tr('Llicència SGAE', 'Licencia SGAE'), 'llicencia', '2026-01-01', '2026-12-31'],
    [$tr('Model 182 (donatius) 2025', 'Modelo 182 (donativos) 2025'), 'fiscal', '2026-01-28', null],
    [$tr('Certificat de la Federació de Societats Musicals', 'Certificado de la Federación de Sociedades Musicales'), 'altre_document', '2026-02-11', null],
] as [$t, $tp, $d, $cad]) {
    q('INSERT INTO documents(titol, tipus, data_document, data_caducitat) VALUES(?,?,?,?)', [$t, $tp, $d, $cad]);
    $doc = $id();
    $text = "$t\n\n" . $tr('Document de prova.', 'Documento de prueba.');
    q('INSERT INTO document_fitxers(document_id, contingut) VALUES(?,?)', [$doc, $text]);
    q("UPDATE documents SET fitxer_nom = ?, fitxer_tipus = 'text/plain', fitxer_mida = ? WHERE id = ?", [strtolower(preg_replace('/\W+/', '-', iconv('UTF-8', 'ASCII//TRANSLIT', $t))) . '.txt', strlen($text), $doc]);
}
foreach ($profPersona as $p) {
    q("INSERT INTO documents(titol, tipus, persona_id, data_document, data_caducitat) VALUES(?, 'contracte', ?, '2026-09-01', '2027-06-30')", [$tr('Contracte de ', 'Contrato de ') . $nomP($p), $p]);
}
foreach ($ES
    ? [['2025-11-10', 'Socios y socias', 341, 'Concierto de Santa Cecilia'], ['2025-12-01', 'Músicos de la Banda Sinfónica', 78, 'Ensayo extra para el concierto de Navidad'],
        ['2026-01-08', 'Familias de la escuela', 170, 'Inicio del 2.º trimestre'], ['2026-03-10', 'Socios y socias', 341, 'Convocatoria de la asamblea general'],
        ['2026-04-02', 'Músicos de la Banda Joven', 34, 'Viaje al certamen'], ['2026-06-01', 'Familias de la escuela', 170, 'Matrícula del curso 2026/2027'],
        ['2026-06-20', 'Coralistas', 27, 'Cena de final de temporada'], ['2026-09-01', 'Familias de la escuela', 170, 'Horarios del curso 2026/2027'],
        ['2026-10-05', 'Colla de Tabal y Dulzaina', 23, 'Pasacalles de las fiestas'], ['2026-10-19', 'Músicos de la Orquesta', 43, 'Calendario de ensayos']]
    : [['2025-11-10', 'Socis i sòcies', 341, 'Concert de Santa Cecília'], ['2025-12-01', 'Músics de la Banda Simfònica', 78, 'Assaig extra per al concert de Nadal'],
        ['2026-01-08', 'Famílies de l\'escola', 170, 'Inici del 2n trimestre'], ['2026-03-10', 'Socis i sòcies', 341, 'Convocatòria de l\'assemblea general'],
        ['2026-04-02', 'Músics de la Banda Jove', 34, 'Viatge al certamen'], ['2026-06-01', 'Famílies de l\'escola', 170, 'Matrícula del curs 2026/2027'],
        ['2026-06-20', 'Coralistes', 27, 'Sopar de final de temporada'], ['2026-09-01', 'Famílies de l\'escola', 170, 'Horaris del curs 2026/2027'],
        ['2026-10-05', 'Colla de Tabal i Dolçaina', 23, 'Cercavila de les festes'], ['2026-10-19', 'Músics de l\'Orquestra', 43, 'Calendari d\'assajos']] as [$d, $dest, $nd, $as]) {
    q('INSERT INTO comunicats(data, destinataris, n_destinataris, assumpte, usuari) VALUES(?,?,?,?,?)', [$d, $dest, $nd, $as, 'admin']);
}

// ---------- Llibre de caixa ----------
foreach ([2025, 2026] as $any) {
    $movs = [];
    for ($m = 1; $m <= ($any === 2026 ? 10 : 12); $m++) {
        if ($m < 7 || $m > 8) {
            $movs[] = [sprintf('%d-%02d-28', $any, $m), 'despesa', 'banc', 'personal', $tr('Nòmines del professorat', 'Nóminas del profesorado'), 9800];
        }
        $movs[] = [sprintf('%d-%02d-05', $any, $m), 'despesa', 'banc', 'subministraments', $tr('Llum i aigua del local', 'Luz y agua del local'), $rnd(180, 420)];
        $movs[] = [sprintf('%d-%02d-12', $any, $m), 'despesa', 'banc', 'comissions', $tr('Comissions bancàries', 'Comisiones bancarias'), $rnd(8, 35)];
        if ($prob(.6)) {
            $movs[] = [sprintf('%d-%02d-%02d', $any, $m, $rnd(1, 27)), 'despesa', 'caixa', 'material',
                $tria($ES ? ['Cañas y boquillas', 'Partituras', 'Material de oficina', 'Atriles'] : ['Canyes i boquilles', 'Partitures', 'Material d\'oficina', 'Faristols']), $rnd(25, 260)];
        }
        if ($prob(.45)) {
            $movs[] = [sprintf('%d-%02d-%02d', $any, $m, $rnd(1, 27)), 'ingres', 'banc', 'actuacions',
                $tria($ES ? ['Procesión de las fiestas', 'Concierto en Benicompàs', 'Pasacalles de moros y cristianos', 'Actuación en una boda'] : ['Processó de les festes', 'Concert a Benicompàs', 'Cercavila de moros i cristians', 'Actuació en una boda']), $rnd(600, 3200)];
        }
    }
    $movs[] = ["$any-02-15", 'despesa', 'banc', 'federacio', $tr('Quota anual de la Federació', 'Cuota anual de la Federación'), 640];
    $movs[] = ["$any-03-01", 'despesa', 'banc', 'assegurances', $tr('Assegurança de responsabilitat civil', 'Seguro de responsabilidad civil'), 410];
    // Les subvencions entren per l'apartat de Subvencions (cobraments), no com a moviments
    $movs[] = ["$any-04-20", 'despesa', 'banc', 'desplacaments', $tr('Autobusos del certamen', 'Autobuses del certamen'), 1350];
    $movs[] = ["$any-05-10", 'despesa', 'banc', 'instruments', $tr('Compra d\'instruments', 'Compra de instrumentos'), $rnd(2500, 6000)];
    $movs[] = ["$any-11-25", 'ingres', 'caixa', 'donatius', $tr('Donatius del concert de Santa Cecília', 'Donativos del concierto de Santa Cecilia'), 480];
    foreach ($movs as $mv) {
        if ($mv[0] <= $AVUI) {
            q('INSERT INTO moviments(data, tipus, compte, categoria, concepte, import) VALUES(?,?,?,?,?,?)', $mv);
        }
    }
}
// Proveïdors i factures de les despeses (per a poder justificar subvencions)
$proveidors = [
    'personal' => [$tr('Nòmina', 'Nómina'), null], 'subministraments' => [$tr('Companyia Elèctrica del Mediterrani', 'Compañía Eléctrica del Mediterráneo'), 'A46000001'],
    'material' => [$tr('Música i Partitures SL', 'Música y Partituras SL'), 'B12000001'], 'federacio' => [$tr('Federació de Societats Musicals', 'Federación de Sociedades Musicales'), 'G46000002'],
    'assegurances' => [$tr('Assegurances La Plana', 'Seguros La Plana'), 'A12000003'], 'desplacaments' => [$tr('Autocars del Maestrat', 'Autocares del Maestrazgo'), 'B12000004'],
    'instruments' => [$tr('Taller de Vent La Plana', 'Taller de Viento La Plana'), 'B12000002'],
];
foreach (q("SELECT id, categoria FROM moviments WHERE tipus = 'despesa' ORDER BY id")->fetchAll() as $k => $m) {
    if (isset($proveidors[$m['categoria']]) && $m['categoria'] !== 'comissions') {
        q('UPDATE moviments SET proveidor = ?, nif = ?, justificant = ? WHERE id = ?', [$proveidors[$m['categoria']][0], $proveidors[$m['categoria']][1],
            $m['categoria'] === 'personal' ? null : sprintf('F-%04d', 100 + $k), $m['id']]);
    }
}

// ---------- Subvencions ----------
$org = fn(int $ordre) => (int)valor('SELECT id FROM organismes WHERE ordre = ?', [$ordre]);
$despesesDe = fn(string $categoria, string $ini, string $fi) => q("SELECT id, import FROM moviments WHERE tipus = 'despesa' AND categoria = ? AND data BETWEEN ? AND ? ORDER BY data",
    [$categoria, $ini, $fi])->fetchAll();
$subvencio = function (array $c, int $fets, array $cobraments, array $despeses) use ($id): int {
    q('INSERT INTO subvencions(' . implode(', ', array_keys($c)) . ') VALUES(' . implode(', ', array_fill(0, count($c), '?')) . ')', array_values($c));
    $sid = $id();
    q('INSERT INTO subvencio_requisits(subvencio_id, requisit_id) SELECT ?, requisit_id FROM organisme_requisits WHERE organisme_id = ?', [$sid, $c['organisme_id']]);
    foreach (array_slice(q('SELECT requisit_id FROM subvencio_requisits WHERE subvencio_id = ? ORDER BY requisit_id', [$sid])->fetchAll(PDO::FETCH_COLUMN), 0, $fets) as $r) {
        q('UPDATE subvencio_requisits SET fet = 1, data_fet = ? WHERE subvencio_id = ? AND requisit_id = ?', [$c['data_justificacio'] ?? '2026-10-15', $sid, $r]);
    }
    foreach ($cobraments as [$data, $import, $concepte]) {
        q("INSERT INTO subvencio_cobraments(subvencio_id, data, import, compte, concepte) VALUES(?, ?, ?, 'banc', ?)", [$sid, $data, $import, $concepte]);
    }
    foreach ($despeses as [$moviment, $import]) {
        q('INSERT INTO subvencio_despeses(subvencio_id, moviment_id, import) VALUES(?, ?, ?)', [$sid, $moviment, $import]);
    }
    return $sid;
};
$bestreta = $tr('Bestreta', 'Anticipo');
$final = $tr('Pagament final', 'Pago final');
foreach ([2025, 2026] as $any) {
    $tancat = $any === 2025;
    // Conveni amb l'ajuntament: nòmines del professorat
    $nomines = array_map(fn($m) => [$m['id'], 1800.0], array_slice($despesesDe('personal', "$any-01-01", "$any-12-31"), 0, 12));
    $subvencio(['nom' => $tr('Conveni de l\'escola de música', 'Convenio de la escuela de música'), 'organisme_id' => $org(30), 'exercici' => $any, 'expedient' => "CONV-$any-07",
        'objecte' => $tr('Escola de música', 'Escuela de música'), 'estat' => $tancat ? 'tancada' : 'concedida', 'data_resolucio' => "$any-02-10",
        'data_limit_justificacio' => "$any-12-15", 'data_justificacio' => $tancat ? "$any-12-05" : null, 'import_concedit' => 18000, 'import_justificar' => 18000],
        $tancat ? 4 : 2, [["$any-06-30", 9000, $bestreta]] + ($tancat ? [1 => ["$any-12-28", 9000, $final]] : []), $tancat ? $nomines : array_slice($nomines, 0, 8));
    // Diputació: compra d'instruments i desplaçaments
    $instruments = $despesesDe('instruments', "$any-01-01", "$any-12-31");
    $autobusos = $despesesDe('desplacaments', "$any-01-01", "$any-12-31");
    $subvencio(['nom' => $tr('Ajudes a societats musicals', 'Ayudas a sociedades musicales'), 'organisme_id' => $org(20), 'exercici' => $any, 'expedient' => "SUBV/$any/0412",
        'objecte' => $tr('Instruments i certamen', 'Instrumentos y certamen'), 'estat' => $tancat ? 'tancada' : 'concedida', 'data_sollicitud' => "$any-02-20", 'data_resolucio' => "$any-06-02",
        'data_limit_justificacio' => $tancat ? "$any-11-30" : '2026-11-16', 'data_justificacio' => $tancat ? "$any-11-20" : null,
        'import_pressupost' => 9000, 'import_sollicitat' => 6000, 'import_concedit' => 4500, 'import_justificar' => 6000],
        $tancat ? 8 : 5, [["$any-07-15", 2250, $bestreta]] + ($tancat ? [1 => ["$any-12-20", 2250, $final]] : []),
        array_merge(array_map(fn($m) => [$m['id'], (float)$m['import']], $instruments), array_map(fn($m) => [$m['id'], (float)$m['import']], $autobusos)));
    // Federació: memòria i certificat
    $subvencio(['nom' => $tr('Ajuda a la formació musical', 'Ayuda a la formación musical'), 'organisme_id' => $org(40), 'exercici' => $any, 'objecte' => $tr('Escola de música', 'Escuela de música'),
        'estat' => $tancat ? 'tancada' : 'justificada', 'data_limit_justificacio' => "$any-10-31", 'data_justificacio' => "$any-10-20", 'import_concedit' => 1200],
        2, $tancat ? [["$any-12-10", 1200, $final]] : [], []);
}
// Generalitat: concedida, per a justificar; i la de l'any vinent, per demanar
$subvencio(['nom' => $tr('Ajudes de l\'Institut Valencià de Cultura', 'Ayudas del Instituto Valenciano de Cultura'), 'organisme_id' => $org(10), 'exercici' => 2026, 'expedient' => 'IVC-SM-2026-1187',
    'objecte' => $tr('Temporada de concerts', 'Temporada de conciertos'), 'estat' => 'concedida', 'data_sollicitud' => '2026-03-14', 'data_resolucio' => '2026-07-21',
    'data_limit_justificacio' => '2026-12-01', 'import_pressupost' => 14000, 'import_sollicitat' => 7000, 'import_concedit' => 5200, 'import_justificar' => 10400],
    3, [], array_map(fn($m) => [$m['id'], (float)$m['import']], array_merge($despesesDe('material', '2026-01-01', '2026-12-31'), $despesesDe('subministraments', '2026-01-01', '2026-06-30'))));
$subvencio(['nom' => $tr('Ajudes de l\'Institut Valencià de Cultura', 'Ayudas del Instituto Valenciano de Cultura'), 'organisme_id' => $org(10), 'exercici' => 2027,
    'objecte' => $tr('Temporada de concerts', 'Temporada de conciertos'), 'estat' => 'prevista', 'data_limit_sollicitud' => '2026-11-05', 'import_pressupost' => 15000, 'import_sollicitat' => 7500], 0, [], []);

desa_config('caixa_saldo_2025_banc', '14250');
desa_config('caixa_saldo_2025_caixa', '640');
desa_config('caixa_saldo_2026_banc', '17980.35');
desa_config('caixa_saldo_2026_caixa', '512.20');

// ---------- Uniformes ----------
$unif = [];
foreach ([[$tr('Polo de la banda', 'Polo de la banda'), 'polo', 'XS, S, M, L, XL, XXL', 18], [$tr('Camisa de concert', 'Camisa de concierto'), 'camisa', 'S, M, L, XL, XXL', 26],
             [$tr('Jaqueta de concert', 'Chaqueta de concierto'), 'jaqueta', 'S, M, L, XL, XXL', 85], [$tr('Dessuadora de l\'escola', 'Sudadera de la escuela'), 'dessuadora', '6, 8, 10, 12, 14, S, M, L', 22],
             [$tr('Samarreta de l\'escola', 'Camiseta de la escuela'), 'samarreta', '6, 8, 10, 12, 14, S, M, L', 9], [$tr('Samarreta de la colla', 'Camiseta de la colla'), 'samarreta', 'S, M, L, XL', 12],
             [$tr('Motxilla de l\'escola', 'Mochila de la escuela'), 'motxilla', null, 14], [$tr('Gorra de festes', 'Gorra de fiestas'), 'gorra', null, 6]] as $k => [$n, $tp, $t, $preu]) {
    q('INSERT INTO uniformes(nom, tipus, talles, preu) VALUES(?,?,?,?)', [$n, $tp, $t, $preu]);
    $unif[$k] = [$id(), $t ? array_map('trim', explode(',', $t)) : [null], $preu];
}
foreach ($unif as [$u, $talles, $preu]) {
    foreach (['2025-09-05', '2026-09-03'] as $d) {
        foreach ($talles as $t) {
            q('INSERT INTO uniforme_compres(uniforme_id, data, talla, quantitat, preu_unitat, proveidor) VALUES(?,?,?,?,?,?)',
                [$u, $d, $t, $t === null ? $rnd(30, 60) : $rnd(4, 18), round($preu * .55, 2), $tria([$tr('Brodats La Plana', 'Bordados La Plana'), $tr('Tèxtil Maestrat', 'Textil Maestrazgo')])]);
        }
    }
}
foreach (q("SELECT DISTINCT persona_id FROM membres WHERE agrupacio_id IN (?, ?) AND estat = 'actiu' AND funcio = 'membre' ORDER BY persona_id", [$agr['Banda Simfònica'], $agr['Orquestra']])->fetchAll(PDO::FETCH_COLUMN) as $p) {
    if ($prob(.6)) {
        q("INSERT INTO uniforme_lliuraments(uniforme_id, persona_id, talla, data, tipus, import) VALUES(?,?,?,?, 'venda', 18)", [$unif[0][0], $p, $tria(['S', 'M', 'M', 'L', 'L', 'XL']), $data('2025-09-10', '2026-10-15')]);
    }
    if ($prob(.25)) {
        q("INSERT INTO uniforme_lliuraments(uniforme_id, persona_id, talla, data, tipus) VALUES(?,?,?,?, 'prestec')", [$unif[2][0], $p, $tria(['M', 'L', 'XL']), $data('2025-11-01', '2026-10-15')]);
    }
}
foreach ($alumnes as $a) {
    if ($a['edat'] < 15 && $prob(.4)) {
        $t = $a['edat'] <= 6 ? '6' : ($a['edat'] <= 8 ? '8' : ($a['edat'] <= 10 ? '10' : ($a['edat'] <= 12 ? '12' : '14')));
        q("INSERT INTO uniforme_lliuraments(uniforme_id, persona_id, talla, data, tipus, import) VALUES(?,?,?,?, 'venda', 9)", [$unif[4][0], $a['persona'], $t, $data('2025-09-15', '2026-10-15')]);
    }
    if ($a['edat'] < 15 && $prob(.2)) {
        q("INSERT INTO uniforme_lliuraments(uniforme_id, persona_id, data, tipus, import) VALUES(?,?,?, 'venda', 14)", [$unif[6][0], $a['persona'], $data('2025-09-15', '2026-10-15')]);
    }
}
foreach (q("SELECT persona_id FROM membres WHERE agrupacio_id = ? AND estat = 'actiu' AND funcio = 'membre'", [$agr['Colla de Tabal i Dolçaina']])->fetchAll(PDO::FETCH_COLUMN) as $p) {
    q("INSERT INTO uniforme_lliuraments(uniforme_id, persona_id, talla, data, tipus) VALUES(?,?,?,?, 'gratuit')", [$unif[5][0], $p, $tria(['S', 'M', 'L', 'XL']), '2026-04-10']);
}
db()->commit();

// ---------- Repertori (653 obres) ----------
db()->beginTransaction();
// Tipus d'obra: els que crea el programa (en l'idioma de la instal·lació) i quatre més
$tipusObra = [];
$ordreTipus = ['Pasdoble', 'Marxa mora', 'Marxa cristiana', 'Marxa de processó', 'Himne', 'Obertura', 'Poema simfònic', 'Suite', 'Fantasia', 'Selecció de sarsuela', 'Banda sonora', 'Música lleugera', 'Altres'];
foreach (q('SELECT id FROM tipus_obres ORDER BY ordre, id')->fetchAll(PDO::FETCH_COLUMN) as $k => $tid) {
    if (isset($ordreTipus[$k])) {
        $tipusObra[$ordreTipus[$k]] = (int)$tid;
    }
}
foreach ([['Música tradicional', 'Música tradicional'], ['Obra coral', 'Obra coral'], ['Música clàssica', 'Música clásica'], ['Música de cambra', 'Música de cámara']] as $k => [$ca, $es]) {
    q('INSERT INTO tipus_obres(nom, ordre) VALUES(?, ?)', [$tr($ca, $es), 200 + $k * 10]);
    $tipusObra[$ca] = $id();
}
$reals = [ // obres conegudes: [títol, tipus, compositor, arranjador, agrupacions]
    ['Paquito el chocolatero', 'Pasdoble', 'Gustavo Pascual Falcó', null, 'B,BJ,D'], ['Amparito Roca', 'Pasdoble', 'Jaime Texidor', null, 'B,BJ'],
    ['El fallero', 'Pasdoble', 'José Serrano', null, 'B,BJ'], ['Pepita Greus', 'Pasdoble', 'Pascual Pérez Choví', null, 'B'], ['Valencia', 'Pasdoble', 'José Padilla', null, 'B'],
    ['Suspiros de España', 'Pasdoble', 'Antonio Álvarez Alonso', null, 'B'], ['España cañí', 'Pasdoble', 'Pascual Marquina', null, 'B,BJ'], ['Gallito', 'Pasdoble', 'Santiago Lope', null, 'B'],
    ['El gato montés', 'Pasdoble', 'Manuel Penella', null, 'B'], ['Puenteareas', 'Pasdoble', 'Reveriano Soutullo', null, 'B'], ['Les Corts de València', 'Pasdoble', 'Vicente Pérez', null, 'B'],
    ['Chimo', 'Marxa mora', 'José María Ferrero', null, 'B'], ['Ragón Falez', 'Marxa mora', 'Gaspar Ángel Tortosa', null, 'B'], ['L\'entrà del moro', 'Marxa mora', 'Juan Pérez Ribes', null, 'B'],
    ['Abencerrajes', 'Marxa mora', 'José Carbonell', null, 'B'], ['Xavieret', 'Marxa mora', 'Rafael Mullor', null, 'B'], ['Als creuats', 'Marxa cristiana', 'Josep Pérez Vilaplana', null, 'B'],
    ['Aleluya', 'Marxa cristiana', 'Ramón García i Soler', null, 'B'], ['Amarguras', 'Marxa de processó', 'Manuel Font de Anta', null, 'B'],
    ['Mater mea', 'Marxa de processó', 'Ricardo Dorado', null, 'B'], ['Nazareno', 'Marxa de processó', 'Pascual Pérez Choví', null, 'B'],
    ['Himne de l\'Exposició', 'Himne', 'José Serrano', null, 'B,C,BJ'], ['Himne de la Comunitat Valenciana', 'Himne', 'José Serrano', null, 'B,C'],
    ['Nit d\'albaes', 'Poema simfònic', 'Salvador Giner', null, 'B'], ['Es chopà hasta la Moma', 'Poema simfònic', 'Salvador Giner', null, 'B'],
    ['Lo Cant del Valencià', 'Poema simfònic', 'Josep Melià', null, 'B,C'], ['La Lola', 'Selecció de sarsuela', 'Gerónimo Giménez', null, 'B'],
    ['La boda de Luis Alonso', 'Selecció de sarsuela', 'Gerónimo Giménez', null, 'B'], ['La Revoltosa', 'Selecció de sarsuela', 'Ruperto Chapí', null, 'B'],
    ['La verbena de la Paloma', 'Selecció de sarsuela', 'Tomás Bretón', null, 'B,O'], ['El tambor de granaderos', 'Obertura', 'Ruperto Chapí', null, 'B'],
    [$tr('Poeta i aldeà', 'Poeta y aldeano'), 'Obertura', 'Franz von Suppé', 'Henry Fillmore', 'B'], [$tr('Guillem Tell', 'Guillermo Tell'), 'Obertura', 'Gioachino Rossini', 'Vincent Hindsley', 'B'],
    [$tr('El barber de Sevilla', 'El barbero de Sevilla'), 'Obertura', 'Gioachino Rossini', null, 'O'], [$tr('La flauta màgica', 'La flauta mágica'), 'Obertura', 'Wolfgang Amadeus Mozart', null, 'O'],
    [$tr('Pirates del Carib', 'Piratas del Caribe'), 'Banda sonora', 'Klaus Badelt', 'Ted Ricketts', 'B,BJ'], [$tr('El senyor dels anells', 'El señor de los anillos'), 'Banda sonora', 'Howard Shore', 'Johan de Meij', 'B'],
    ['Star Wars', 'Banda sonora', 'John Williams', 'Donald Hunsberger', 'B,BJ,O'], ['Jurassic Park', 'Banda sonora', 'John Williams', 'Paul Lavender', 'BJ'],
    ['Harry Potter', 'Banda sonora', 'John Williams', 'Jerry Brubaker', 'BJ,O'], [$tr('El rei Lleó', 'El rey León'), 'Banda sonora', 'Elton John', 'John Higgins', 'BJ'],
    [$tr('Simfonia núm. 1 «El senyor dels anells»', 'Sinfonía n.º 1 «El señor de los anillos»'), 'Suite', 'Johan de Meij', null, 'B'], ['The Seventh Night of July', 'Suite', 'Itaru Sakai', null, 'B'],
    ['Arabesque', 'Fantasia', 'Samuel R. Hazo', null, 'B'], [$tr('Danzón núm. 2', 'Danzón n.º 2'), 'Música clàssica', 'Arturo Márquez', 'Oliver Nickel', 'B,O'],
    [$tr('Simfonia núm. 40', 'Sinfonía n.º 40'), 'Música clàssica', 'Wolfgang Amadeus Mozart', null, 'O'], ['Eine kleine Nachtmusik', 'Música clàssica', 'Wolfgang Amadeus Mozart', null, 'O'],
    [$tr('Les quatre estacions: la primavera', 'Las cuatro estaciones: la primavera'), 'Música clàssica', 'Antonio Vivaldi', null, 'O'], [$tr('Cànon en re', 'Canon en re'), 'Música clàssica', 'Johann Pachelbel', null, 'O'],
    [$tr('Simfonia del Nou Món (4t moviment)', 'Sinfonía del Nuevo Mundo (4.º movimiento)'), 'Música clàssica', 'Antonín Dvořák', null, 'O,B'],
    [$tr('Dansa hongaresa núm. 5', 'Danza húngara n.º 5'), 'Música clàssica', 'Johannes Brahms', null, 'O,B'], [$tr('Peer Gynt: el matí', 'Peer Gynt: la mañana'), 'Música clàssica', 'Edvard Grieg', null, 'O'],
    [$tr('Carmen: suite núm. 1', 'Carmen: suite n.º 1'), 'Música clàssica', 'Georges Bizet', null, 'O,B'], [$tr('Marxa Radetzky', 'Marcha Radetzky'), 'Música lleugera', 'Johann Strauss pare', null, 'B,O,BJ'],
    [$tr('El Danubi blau', 'El Danubio azul'), 'Música lleugera', 'Johann Strauss fill', null, 'O'], [$tr('Al·leluia (El Messies)', 'Aleluya (El Mesías)'), 'Obra coral', 'Georg Friedrich Händel', null, 'C,O'],
    ['Signore delle cime', 'Obra coral', 'Bepi De Marzi', null, 'C'], ['Ave verum corpus', 'Obra coral', 'Wolfgang Amadeus Mozart', null, 'C'],
    ['El cant dels ocells', 'Obra coral', $tr('Popular catalana', 'Popular catalana'), 'Pau Casals', 'C,O'], ['Va, pensiero', 'Obra coral', 'Giuseppe Verdi', null, 'C,B'],
    ['Muixeranga d\'Algemesí', 'Música tradicional', 'Popular', null, 'D,B'], ['Dansà de Russafa', 'Música tradicional', 'Popular', null, 'D'],
    ['Jota de Paterna', 'Música tradicional', 'Popular', null, 'D'], ['Les danses de Titaguas', 'Música tradicional', 'Popular', null, 'D'],
];
$sigles = ['B' => 'Banda Simfònica', 'BJ' => 'Banda Jove', 'O' => 'Orquestra', 'C' => 'Coral Polifònica', 'D' => 'Colla de Tabal i Dolçaina'];
// Obres inventades (amb títols i compositors inventats) per a arribar a 653
$llocs = ['Vilanova', 'Benicompàs', 'la Plana', 'la Serra', 'la Marina', 'l\'Horta', 'Penyagolosa', 'el Maestrat', 'la Vall', 'el Raval', 'la Mar', 'Sant Roc', 'la Font'];
$motius = ['Festa', 'Alegria', 'Record', 'Homenatge', 'Nit', 'Albada', 'Somni', 'Llum', 'Retorn', 'Camí', 'Cant', 'Matinada', 'Primavera', 'Tardor', 'Rondalla', 'Fanfara'];
$persMus = ['Tio Pep', 'Mestre Vicent', 'la Tia Rosa', 'Batistet', 'Amparín', 'el Músic Vell', 'Pepeta', 'Ximo', 'el Clarinet', 'la Banda', 'Lluïset', 'Tonica'];
$compositors = [];
for ($k = 0; $k < 70; $k++) {
    $compositors[] = $tria(array_merge($nomsH, $nomsH, $nomsD)) . ' ' . $tria($cognomsL) . ' ' . $tria($cognomsL);
}
$plantillaObra = [
    'Pasdoble' => [fn() => $tria($persMus), fn() => $tria($motius) . ' a ' . $tria($llocs), fn() => 'Festers de ' . $tria($llocs)],
    'Marxa mora' => [fn() => $tria(['Al-', 'Ben ', 'Abu ']) . $tria(['Azraq', 'Hakim', 'Sahir', 'Qasim', 'Yamal', 'Rashid', 'Faruq', 'Nadir', 'Zahir']), fn() => 'Kàbila ' . $tria(['dels Benimerins', 'Almoràvit', 'de la Mitja Lluna', 'del Desert'])],
    'Marxa cristiana' => [fn() => $tria(['Els Almogàvers', 'Templaris', 'Cavallers de ', 'Maseros de ']) . (mt_rand(0, 1) ? $tria($llocs) : ''), fn() => 'Capità ' . $tria($persMus)],
    'Marxa de processó' => [fn() => trim($tria(['Getsemaní', 'Soledat', 'Pietat', 'Dolors', 'Calvari', 'Misericòrdia', 'Esperança', 'Consol']) . ' ' . $tria(['', 'de ' . $tria($llocs)]))],
    'Fantasia' => [fn() => $tria($motius) . ' ' . $tria(['mediterrani', 'valencià', 'de festa', 'llunyà', 'a la muntanya'])],
    'Música lleugera' => [fn() => $tria(['Swing', 'Blues', 'Mambo', 'Fox', 'Bolero', 'Cha-cha-cha']) . ' de ' . $tria($llocs), fn() => $tria(['Selecció de ', 'Popurri de ']) . $tria(['cançons de l\'estiu', 'boleros', 'música dels 80', 'temes de cinema'])],
    'Música tradicional' => [fn() => $tria(['Dansà', 'Jota', 'Bolero', 'Fandango', 'Cercavila', 'Xaranga', 'Ball de bastons', 'Albaes']) . ' de ' . $tria($llocs)],
    'Obra coral' => [fn() => $tria(['Cançó', 'Nadala', 'Himne', 'Motet', 'Pregària', 'Cant']) . ' ' . $tria(['de ' . $tria($llocs), 'del mariner', 'de bressol', 'a la mare', 'de la sega', 'de Nadal'])],
    'Música clàssica' => [fn() => $tria(['Minuet', 'Serenata', 'Divertimento', 'Allegro', 'Adagio', 'Andante', 'Rondó']) . ' en ' . $tria(['do', 're', 'mi bemoll', 'fa', 'sol', 'la', 'si bemoll']) . ' ' . $tria(['major', 'menor'])],
    'Suite' => [fn() => 'Suite ' . $tria(['de ' . $tria($llocs), 'mediterrània', 'popular', 'festiva', 'dels oficis'])],
    'Poema simfònic' => [fn() => $tria($motius) . ' de ' . $tria($llocs)],
];
$distrib = ['Pasdoble' => [150, 'B'], 'Marxa mora' => [45, 'B'], 'Marxa cristiana' => [30, 'B'], 'Marxa de processó' => [40, 'B'], 'Fantasia' => [25, 'B'], 'Música lleugera' => [55, 'B'],
    'Música tradicional' => [65, 'D'], 'Obra coral' => [80, 'C'], 'Música clàssica' => [70, 'O'], 'Suite' => [15, 'B'], 'Poema simfònic' => [15, 'B']];
$falten = 653 - count($reals);
$totDist = array_sum(array_column($distrib, 0));
$vist = [];
$generades = [];
foreach ($distrib as $tp => [$q, $ag]) {
    for ($k = 0; $k < (int)round($q * $falten / $totDist); $k++) {
        for ($intent = 0; $intent < 20; $intent++) {
            $t = trim(($tria($plantillaObra[$tp]))()) . ($intent > 4 ? ' ' . ['II', 'III', $tr('núm. 2', 'n.º 2'), $tr('núm. 3', 'n.º 3')][$intent % 4] : '');
            if (!isset($vist[$t])) {
                break;
            }
        }
        $vist[$t] = true;
        $ags = $ag . ($ag === 'B' && $prob(.22) ? ',BJ' : '') . ($ag === 'D' && $prob(.15) ? ',B' : '') . ($ag === 'C' && $prob(.1) ? ',O' : '');
        $generades[] = [$t, $tp, $tria($compositors), $prob(.15) ? $tria($compositors) : null, $ags];
    }
}
$obres = array_slice(array_merge($reals, $generades), 0, 653);
while (count($obres) < 653) {
    $obres[] = [$tria($persMus) . ' ' . count($obres), 'Pasdoble', $tria($compositors), null, 'B'];
}
$materials = $ES
    ? ['Partitura + particellas', 'Partitura + particellas', 'Partitura + particellas', 'Solo particellas', 'Partitura de director', 'Particellas fotocopiadas']
    : ['Partitura + particel·les', 'Partitura + particel·les', 'Partitura + particel·les', 'Només particel·les', 'Partitura de director', 'Particel·les fotocopiades'];
$falta = $ES ? ['2.º clarinete', '3.ª trompeta', 'tuba', 'requinto', 'fagot', 'caja', 'viola', 'contralto'] : ['2n clarinet', '3a trompeta', 'tuba', 'requint', 'fagot', 'caixa', 'viola', 'contralt'];
$num = ['B' => 0, 'BJ' => 0, 'O' => 0, 'C' => 0, 'D' => 0];
$armaris = ['B' => 'A', 'BJ' => 'B', 'O' => 'C', 'C' => 'D', 'D' => 'E'];
foreach ($obres as [$t, $tp, $comp, $arr, $ags]) {
    $s = array_unique(explode(',', $ags));
    $n = ++$num[$s[0]];
    $estat = $prob(.86) ? 'complet' : ($prob(.5) ? 'incomplet' : ($prob(.6) ? 'en_revisio' : 'retirat'));
    q('INSERT INTO obres(titol, tipus_id, compositor, arranjador, numero_arxiu, ubicacio, material, estat, digitalitzada, observacions) VALUES(?,?,?,?,?,?,?,?,?,?)', [
        $t, $tipusObra[$tp] ?? $tipusObra['Altres'], $comp, $arr, sprintf('%s-%04d', $s[0], $n),
        sprintf($tr('Armari %s · Prestatgeria %d · Carpeta %d', 'Armario %s · Estantería %d · Carpeta %d'), $armaris[$s[0]], intdiv($n - 1, 40) + 1, $n),
        $s[0] === 'C' ? $tr('Partitura vocal', 'Partitura vocal') : $tria($materials), $estat, $prob(.4) ? 1 : 0,
        $estat === 'incomplet' ? $tr('Falta la particel·la de ', 'Falta la particella de ') . $tria($falta) . '.' : ($estat === 'retirat' ? $tr('Material molt deteriorat.', 'Material muy deteriorado.') : null),
    ]);
    $o = $id();
    foreach ($s as $sig) {
        q('INSERT INTO obra_agrupacions(obra_id, agrupacio_id) VALUES(?,?)', [$o, $agr[$sigles[$sig]]]);
    }
}
db()->commit();

printf("%s: %d persones, %d socis actius, %d alumnes actius, %d obres, %d rebuts.\n", $nomSocietat, valor('SELECT COUNT(*) FROM persones'),
    valor("SELECT COUNT(*) FROM socis WHERE estat = 'actiu'"), valor("SELECT COUNT(*) FROM alumnes WHERE estat = 'actiu'"), valor('SELECT COUNT(*) FROM obres'), valor('SELECT COUNT(*) FROM rebuts'));
