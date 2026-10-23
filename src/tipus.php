<?php
// Tipus d'entitat: cada un amb els seus mòduls per defecte, el seu vocabulari, el mes en què comença l'exercici,
// les categories d'edat i els càrrecs de festa per a començar. Tot es pot canviar després.
// Copyright (C) 2026 José Vicente Sanjuan
// SPDX-License-Identifier: AGPL-3.0-or-later

const TOTS_MODULS = ['escola', 'agrupacions', 'repertori', 'instruments', 'uniformes', 'economia', 'loteria', 'caixa', 'junta', 'documents', 'comunicats',
    'subvencions', 'activitats', 'llicencies', 'entitats', 'carrecs_festa'];

const TIPUS_ENTITAT = [
    'societat_musical' => [
        'nom' => ['ca' => 'Societat musical', 'es' => 'Sociedad musical'],
        'descripcio' => ['ca' => 'Banda, escola de música, agrupacions i repertori', 'es' => 'Banda, escuela de música, agrupaciones y repertorio'],
        'moduls' => ['escola', 'agrupacions', 'repertori', 'instruments', 'uniformes', 'economia', 'loteria', 'caixa', 'junta', 'documents', 'comunicats', 'subvencions', 'activitats'],
        'vocabulari' => [], 'mes_exercici' => 1, 'notes' => true,
    ],
    'club_esportiu' => [
        'nom' => ['ca' => 'Club esportiu', 'es' => 'Club deportivo'],
        'descripcio' => ['ca' => 'Futbol, bàsquet, tenis…: equips, escola esportiva i llicències', 'es' => 'Fútbol, baloncesto, tenis…: equipos, escuela deportiva y licencias'],
        'moduls' => ['escola', 'agrupacions', 'instruments', 'uniformes', 'economia', 'loteria', 'caixa', 'junta', 'documents', 'comunicats', 'subvencions', 'activitats', 'llicencies'],
        'vocabulari' => ['escola' => 'esportiva', 'grups' => 'equips', 'material' => 'esportiu', 'roba' => 'equipacions'],
        'mes_exercici' => 9, 'notes' => false,
        'nom_exemple' => ['ca' => 'Club …', 'es' => 'Club …'],
        'categories' => [['Prebenjamí', 'Prebenjamín', 6, 7], ['Benjamí', 'Benjamín', 8, 9], ['Aleví', 'Alevín', 10, 11], ['Infantil', 'Infantil', 12, 13],
            ['Cadet', 'Cadete', 14, 15], ['Juvenil', 'Juvenil', 16, 18], ['Sènior', 'Sénior', 19, 34], ['Veterà', 'Veterano', 35, 120]],
    ],
    'colla' => [
        'nom' => ['ca' => 'Colla', 'es' => 'Colla'],
        'descripcio' => ['ca' => 'Gegants, dolçaina i tabal, muixeranga, castells…', 'es' => 'Gigantes, dulzaina y tabal, muixeranga, castells…'],
        'moduls' => ['agrupacions', 'instruments', 'uniformes', 'economia', 'loteria', 'caixa', 'junta', 'documents', 'comunicats', 'subvencions', 'activitats'],
        'vocabulari' => ['escola' => 'colla', 'grups' => 'seccions', 'socis' => 'membres', 'material' => 'material', 'roba' => 'roba'],
        'mes_exercici' => 1, 'notes' => true,
        'nom_exemple' => ['ca' => 'Colla …', 'es' => 'Colla …'],
    ],
    'associacio_cultural' => [
        'nom' => ['ca' => 'Associació cultural', 'es' => 'Asociación cultural'],
        'descripcio' => ['ca' => 'Tallers, grups de teatre, dansa o cor, activitats', 'es' => 'Talleres, grupos de teatro, danza o coro, actividades'],
        'moduls' => ['escola', 'agrupacions', 'instruments', 'economia', 'caixa', 'junta', 'documents', 'comunicats', 'subvencions', 'activitats'],
        'vocabulari' => ['escola' => 'tallers', 'grups' => 'grups', 'material' => 'material', 'roba' => 'roba'],
        'mes_exercici' => 1, 'notes' => false,
        'nom_exemple' => ['ca' => 'Associació Cultural …', 'es' => 'Asociación Cultural …'],
    ],
    'ampa' => [
        'nom' => ['ca' => 'AMPA / AFA', 'es' => 'AMPA / AFA'],
        'descripcio' => ['ca' => 'Famílies associades, activitats extraescolars i comissions', 'es' => 'Familias asociadas, actividades extraescolares y comisiones'],
        'moduls' => ['escola', 'uniformes', 'economia', 'caixa', 'junta', 'documents', 'comunicats', 'subvencions', 'activitats'],
        'vocabulari' => ['escola' => 'extraescolars', 'grups' => 'comissions', 'socis' => 'families', 'material' => 'material', 'roba' => 'roba'],
        'mes_exercici' => 9, 'notes' => false,
        'nom_exemple' => ['ca' => 'AMPA …', 'es' => 'AMPA …'],
    ],
    'falla' => [
        'nom' => ['ca' => 'Falla', 'es' => 'Falla'],
        'descripcio' => ['ca' => 'Cens de fallers i falleres, càrrecs, quotes i loteria', 'es' => 'Censo de falleros y falleras, cargos, cuotas y lotería'],
        'moduls' => ['uniformes', 'economia', 'loteria', 'caixa', 'junta', 'documents', 'comunicats', 'subvencions', 'activitats', 'carrecs_festa'],
        'vocabulari' => ['escola' => 'tallers', 'grups' => 'seccions', 'socis' => 'fallers', 'material' => 'material', 'roba' => 'roba'],
        'mes_exercici' => 4, 'notes' => false,
        'nom_exemple' => ['ca' => 'Falla …', 'es' => 'Falla …'],
        'categories' => [['Infantil', 'Infantil', 0, 13], ['Major', 'Mayor', 14, 120]],
        'carrecs' => ['ca' => ['Fallera Major', 'Fallera Major Infantil', 'President/a', 'President/a infantil', 'Dama de la Fallera Major', 'Dama infantil', 'Faller/a d\'honor'],
            'es' => ['Fallera Mayor', 'Fallera Mayor Infantil', 'Presidente/a', 'Presidente/a infantil', 'Dama de la Fallera Mayor', 'Dama infantil', 'Fallero/a de honor']],
    ],
    'gaiata' => [
        'nom' => ['ca' => 'Gaiata', 'es' => 'Gaiata'],
        'descripcio' => ['ca' => 'Comissió de sector: membres, madrines, quotes i loteria', 'es' => 'Comisión de sector: miembros, madrinas, cuotas y lotería'],
        'moduls' => ['uniformes', 'economia', 'loteria', 'caixa', 'junta', 'documents', 'comunicats', 'subvencions', 'activitats', 'carrecs_festa'],
        'vocabulari' => ['escola' => 'tallers', 'grups' => 'seccions', 'socis' => 'comissio', 'material' => 'material', 'roba' => 'roba'],
        'mes_exercici' => 4, 'notes' => false,
        'nom_exemple' => ['ca' => 'Gaiata …', 'es' => 'Gaiata …'],
        'categories' => [['Infantil', 'Infantil', 0, 13], ['Major', 'Mayor', 14, 120]],
        'carrecs' => ['ca' => ['Madrina', 'Madrina infantil', 'Dama', 'Dama infantil', 'President/a', 'President/a infantil'],
            'es' => ['Madrina', 'Madrina infantil', 'Dama', 'Dama infantil', 'Presidente/a', 'Presidente/a infantil']],
    ],
    'foguera' => [
        'nom' => ['ca' => 'Foguera', 'es' => 'Foguera'],
        'descripcio' => ['ca' => 'Foguerers i foguereres, belleses i dames, quotes i loteria', 'es' => 'Foguerers y foguereras, bellezas y damas, cuotas y lotería'],
        'moduls' => ['uniformes', 'economia', 'loteria', 'caixa', 'junta', 'documents', 'comunicats', 'subvencions', 'activitats', 'carrecs_festa'],
        'vocabulari' => ['escola' => 'tallers', 'grups' => 'seccions', 'socis' => 'foguerers', 'material' => 'material', 'roba' => 'roba'],
        'mes_exercici' => 7, 'notes' => false,
        'nom_exemple' => ['ca' => 'Foguera …', 'es' => 'Foguera …'],
        'categories' => [['Infantil', 'Infantil', 0, 13], ['Major', 'Mayor', 14, 120]],
        'carrecs' => ['ca' => ['Bellesa', 'Bellesa infantil', 'Dama d\'honor', 'Dama d\'honor infantil', 'President/a', 'President/a infantil'],
            'es' => ['Belleza', 'Belleza infantil', 'Dama de honor', 'Dama de honor infantil', 'Presidente/a', 'Presidente/a infantil']],
    ],
    'comparsa' => [
        'nom' => ['ca' => 'Comparsa', 'es' => 'Comparsa'],
        'descripcio' => ['ca' => 'Moros i cristians: comparsistes, esquadres, vestits i càrrecs', 'es' => 'Moros y cristianos: comparsistas, escuadras, trajes y cargos'],
        'moduls' => ['agrupacions', 'uniformes', 'economia', 'loteria', 'caixa', 'junta', 'documents', 'comunicats', 'subvencions', 'activitats', 'carrecs_festa'],
        'vocabulari' => ['escola' => 'tallers', 'grups' => 'esquadres', 'socis' => 'comparsistes', 'material' => 'material', 'roba' => 'vestits'],
        'mes_exercici' => 1, 'notes' => false,
        'nom_exemple' => ['ca' => 'Comparsa …', 'es' => 'Comparsa …'],
        'categories' => [['Infantil', 'Infantil', 0, 13], ['Juvenil', 'Juvenil', 14, 17], ['Major', 'Mayor', 18, 120]],
        'carrecs' => ['ca' => ['Capità/Capitana', 'Alferes', 'Abanderat/da', 'Rodella', 'Cap de festa'],
            'es' => ['Capitán/Capitana', 'Alférez', 'Abanderado/a', 'Rodela', 'Jefe/a de fiesta']],
    ],
    'penya' => [
        'nom' => ['ca' => 'Penya', 'es' => 'Peña'],
        'descripcio' => ['ca' => 'Penya festera o esportiva: socis, quotes, loteria i eixides', 'es' => 'Peña festera o deportiva: socios, cuotas, lotería y salidas'],
        'moduls' => ['uniformes', 'economia', 'loteria', 'caixa', 'junta', 'documents', 'comunicats', 'activitats'],
        'vocabulari' => ['escola' => 'tallers', 'grups' => 'grups', 'material' => 'material', 'roba' => 'roba'],
        'mes_exercici' => 1, 'notes' => false,
        'nom_exemple' => ['ca' => 'Penya …', 'es' => 'Peña …'],
    ],
    'federacio' => [
        'nom' => ['ca' => 'Federació', 'es' => 'Federación'],
        'descripcio' => ['ca' => 'Entitats federades amb les seues quotes, assemblees i comunicats', 'es' => 'Entidades federadas con sus cuotas, asambleas y comunicados'],
        'moduls' => ['economia', 'caixa', 'junta', 'documents', 'comunicats', 'subvencions', 'activitats', 'entitats'],
        'vocabulari' => ['escola' => 'tallers', 'grups' => 'grups', 'material' => 'material', 'roba' => 'roba'],
        'mes_exercici' => 1, 'notes' => false,
        'nom_exemple' => ['ca' => 'Federació …', 'es' => 'Federación …'],
    ],
];

/** El tipus d'esta entitat (les instal·lacions anteriors a la 2.0 són societats musicals) */
function tipus_entitat(): string
{
    static $cache = null;
    if ($cache === null) {
        try {
            $t = (string)config('tipus_entitat');
        } catch (Throwable $e) {
            $t = '';
        }
        if (!isset(TIPUS_ENTITAT[$t])) {
            return 'societat_musical'; // sense cache: encara pot ser la configuració inicial
        }
        $cache = $t;
    }
    return $cache;
}

function info_tipus(?string $tipus = null): array
{
    return TIPUS_ENTITAT[$tipus ?? tipus_entitat()];
}

/** Nom del tipus en l'idioma de la pantalla */
function nom_tipus(?string $tipus = null): string
{
    return info_tipus($tipus)['nom'][idioma()] ?? '';
}

/** Mòduls que el tipus no fa servir normalment (es desactiven en triar-lo) */
function moduls_desactivats_tipus(string $tipus): array
{
    return array_values(array_diff(TOTS_MODULS, TIPUS_ENTITAT[$tipus]['moduls']));
}

/** L'escola posa notes i fa butlletins (societat musical, colla); en la resta, només inscripcions */
function te_notes(): bool
{
    return !empty(info_tipus()['notes']);
}

/** Mes (1-12) en què comença l'exercici o la temporada */
function mes_inici_exercici(): int
{
    $m = (int)config('mes_exercici');
    return $m >= 1 && $m <= 12 ? $m : (int)(info_tipus()['mes_exercici'] ?? 1);
}

/** Any en què comença l'exercici que conté una data (per defecte, hui) */
function any_exercici(?string $data = null): int
{
    $t = strtotime($data ?: date('Y-m-d'));
    $any = (int)date('Y', $t);
    return (int)date('n', $t) >= mes_inici_exercici() ? $any : $any - 1;
}

/** Nom de l'exercici que comença un any: «2026» o «2026-2027» si no comença al gener */
function nom_exercici(int $any): string
{
    return mes_inici_exercici() === 1 ? (string)$any : $any . '-' . ($any + 1);
}

/** Configura els mòduls, les categories i el mes d'inici d'un tipus nou (en la configuració inicial o en canviar-lo) */
function aplica_tipus(PDO $pdo, string $tipus, bool $moduls, string $idioma): void
{
    $desa = $pdo->prepare('INSERT INTO config(clau, valor) VALUES(?, ?) ON CONFLICT(clau) DO UPDATE SET valor = excluded.valor');
    $desa->execute(['tipus_entitat', $tipus]);
    if ($moduls) {
        $desa->execute(['moduls_desactivats', implode(',', moduls_desactivats_tipus($tipus)) ?: null]);
        $desa->execute(['mes_exercici', (string)(TIPUS_ENTITAT[$tipus]['mes_exercici'] ?? 1)]);
    }
    // Les categories d'edat del tipus, si encara no n'hi ha cap
    if ((int)$pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn() === 0) {
        $ins = $pdo->prepare('INSERT INTO categories(nom, edat_min, edat_max, ordre) VALUES(?, ?, ?, ?)');
        foreach (TIPUS_ENTITAT[$tipus]['categories'] ?? [] as $i => [$ca, $es, $min, $max]) {
            $ins->execute([$idioma === 'es' ? $es : $ca, $min, $max, ($i + 1) * 10]);
        }
    }
}
