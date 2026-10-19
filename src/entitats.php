<?php
// Definició de les entitats que es gestionen amb el formulari genèric
// SPDX-License-Identifier: GPL-2.0-or-later
//
// Cada camp: tipus (text, textarea, date, time, email, tel, number, decimal, bool, select, ref, dia),
// req (obligatori), llista (es mostra al llistat), opcions (per a select), ref (entitat relacionada).
// L'etiqueta de cada camp es tradueix amb "camp.<nom>" i cada opció amb "opt.<valor>".

const NOM_PERSONA_SQL = "COALESCE(NULLIF(p.cognoms,'') || ', ', '') || p.nom";

/** Edat a partir de la qual DNI i telèfon són obligatoris */
const MAJORIA_EDAT = 18;

/** Condició SQL: persona amb dades obligatòries que falten (alias t) */
const PERSONA_INCOMPLETA_SQL = "(COALESCE(t.cognoms, '') = '' OR t.data_naixement IS NULL
    OR (date(t.data_naixement, '+18 years') <= date('now', 'localtime') AND (COALESCE(t.document, '') = '' OR COALESCE(t.telefon, '') = '')))";

function es_major_edat(?string $naixement): bool
{
    return $naixement !== null && $naixement !== '' && $naixement <= date('Y-m-d', strtotime('-' . MAJORIA_EDAT . ' years'));
}

/** Retorna què li falta a una persona (buit si està completa) */
function dades_que_falten(array $p): array
{
    $falten = [];
    foreach (['cognoms', 'data_naixement'] as $c) {
        if (($p[$c] ?? '') === '' || $p[$c] === null) {
            $falten[] = t('camp.' . $c);
        }
    }
    if (es_major_edat($p['data_naixement'] ?? null)) {
        foreach (['document', 'telefon'] as $c) {
            if (($p[$c] ?? '') === '' || $p[$c] === null) {
                $falten[] = t('camp.' . $c);
            }
        }
    }
    return $falten;
}

function entitats(): array
{
    static $e = null;
    if ($e) {
        return $e;
    }
    $estat = ['tipus' => 'select', 'opcions' => ['actiu', 'baixa'], 'llista' => 1];
    $persona = ['tipus' => 'ref', 'ref' => 'persones', 'req' => 1, 'llista' => 1];
    $obs = ['tipus' => 'textarea'];
    $perfil = fn(string $taula) => [
        'from' => "$taula t JOIN persones p ON p.id = t.persona_id",
        'cerca' => ['p.nom', 'p.cognoms'],
        'ordre' => 'p.cognoms, p.nom',
        'etiqueta' => "SELECT t.id, " . NOM_PERSONA_SQL . " AS e FROM $taula t JOIN persones p ON p.id = t.persona_id ORDER BY e",
    ];

    $e = [
        'persones' => [
            'cap' => 'persones',
            'cerca' => ['t.nom', 't.cognoms', 't.email', 't.telefon', 't.document', 't.poblacio'],
            'ordre' => 't.cognoms, t.nom',
            'etiqueta' => "SELECT p.id, " . NOM_PERSONA_SQL . " AS e FROM persones p ORDER BY e",
            'filtres' => ['incomplets' => PERSONA_INCOMPLETA_SQL],
            'avis_fila' => fn(array $f) => ($falten = dades_que_falten($f)) ? t('persona.falten', implode(', ', $falten)) : null,
            'avis_llista' => PERSONA_INCOMPLETA_SQL,
            'camps' => [
                'nom' => ['tipus' => 'text', 'req' => 1, 'llista' => 1],
                'cognoms' => ['tipus' => 'text', 'req' => 1, 'llista' => 1],
                'data_naixement' => ['tipus' => 'date', 'req' => 1, 'llista' => 1],
                'document' => ['tipus' => 'text', 'major' => 1, 'ajuda' => t('ajuda.major_edat')],
                'telefon' => ['tipus' => 'tel', 'llista' => 1, 'major' => 1],
                'email' => ['tipus' => 'email', 'llista' => 1],
                'adreca' => ['tipus' => 'text'],
                'codi_postal' => ['tipus' => 'text'],
                'poblacio' => ['tipus' => 'text', 'llista' => 1],
                'provincia' => ['tipus' => 'text'],
                'observacions' => $obs,
                'actiu' => ['tipus' => 'bool', 'llista' => 1, 'defecte' => 1],
            ],
            'fills' => [['socis', 'persona_id'], ['alumnes', 'persona_id'], ['professors', 'persona_id'],
                ['membres', 'persona_id'], ['tutories', 'tutor_id'], ['cessions', 'persona_id']],
        ],
        'socis' => $perfil('socis') + [
            'cap' => 'socis',
            'cerca' => ['p.nom', 'p.cognoms', 't.numero'],
            'camps' => [
                'persona_id' => $persona,
                'numero' => ['tipus' => 'text', 'llista' => 1],
                'tipus' => ['tipus' => 'select', 'opcions' => ['ordinari', 'protector', 'honorari', 'juvenil'], 'llista' => 1],
                'data_alta' => ['tipus' => 'date', 'llista' => 1, 'defecte' => 'avui'],
                'data_baixa' => ['tipus' => 'date'],
                'estat' => $estat,
                'observacions' => $obs,
            ],
        ],
        'alumnes' => $perfil('alumnes') + [
            'cap' => 'escola',
            'camps' => [
                'persona_id' => $persona,
                'data_alta' => ['tipus' => 'date', 'llista' => 1, 'defecte' => 'avui'],
                'data_baixa' => ['tipus' => 'date'],
                'estat' => $estat,
                'observacions' => $obs,
            ],
            'fills' => [['tutories', 'alumne_id'], ['matricules', 'alumne_id']],
        ],
        'professors' => $perfil('professors') + [
            'cap' => 'escola',
            'camps' => [
                'persona_id' => $persona,
                'data_alta' => ['tipus' => 'date', 'llista' => 1, 'defecte' => 'avui'],
                'data_baixa' => ['tipus' => 'date'],
                'estat' => $estat,
                'observacions' => $obs,
            ],
            'fills' => [['grups', 'professor_id']],
        ],
        'tutories' => [
            'cap' => 'escola',
            'from' => 'tutories t JOIN persones p ON p.id = t.tutor_id',
            'cerca' => ['p.nom', 'p.cognoms'],
            'ordre' => 'p.cognoms, p.nom',
            'camps' => [
                'alumne_id' => ['tipus' => 'ref', 'ref' => 'alumnes', 'req' => 1, 'llista' => 1],
                'tutor_id' => ['tipus' => 'ref', 'ref' => 'persones', 'req' => 1, 'llista' => 1],
                'tipus_relacio' => ['tipus' => 'select', 'opcions' => ['mare', 'pare', 'tutor_legal', 'altre'], 'llista' => 1],
                'principal' => ['tipus' => 'bool', 'llista' => 1],
                'rep_comunicacions' => ['tipus' => 'bool', 'defecte' => 1],
                'observacions' => $obs,
            ],
        ],
        'agrupacions' => [
            'cap' => 'agrupacions',
            'cerca' => ['t.nom'],
            'ordre' => 't.nom',
            'etiqueta' => 'SELECT id, nom AS e FROM agrupacions ORDER BY nom',
            'camps' => [
                'nom' => ['tipus' => 'text', 'req' => 1, 'llista' => 1],
                'tipus' => ['tipus' => 'select', 'opcions' => ['banda', 'banda_juvenil', 'coral', 'orquestra', 'cambra', 'altra'], 'llista' => 1],
                'descripcio' => $obs,
                'estat' => $estat,
                'data_creacio' => ['tipus' => 'date'],
            ],
            'fills' => [['membres', 'agrupacio_id']],
        ],
        'membres' => [
            'cap' => 'agrupacions',
            'from' => 'membres t JOIN persones p ON p.id = t.persona_id',
            'cerca' => ['p.nom', 'p.cognoms', 't.instrument_veu'],
            'ordre' => 'p.cognoms, p.nom',
            'camps' => [
                'persona_id' => $persona,
                'agrupacio_id' => ['tipus' => 'ref', 'ref' => 'agrupacions', 'req' => 1, 'llista' => 1],
                'instrument_veu' => ['tipus' => 'text', 'llista' => 1],
                'funcio' => ['tipus' => 'select', 'opcions' => ['membre', 'director_titular', 'director_assistent', 'responsable'], 'llista' => 1],
                'data_alta' => ['tipus' => 'date', 'defecte' => 'avui'],
                'data_baixa' => ['tipus' => 'date'],
                'estat' => $estat,
                'observacions' => $obs,
            ],
        ],
        'cursos' => [
            'cap' => 'escola',
            'cerca' => ['t.nom'],
            'ordre' => 't.data_inici DESC, t.nom DESC',
            'etiqueta' => 'SELECT id, nom AS e FROM cursos ORDER BY data_inici DESC, nom DESC',
            'camps' => [
                'nom' => ['tipus' => 'text', 'req' => 1, 'llista' => 1, 'ajuda' => '2026/2027'],
                'data_inici' => ['tipus' => 'date', 'llista' => 1],
                'data_fi' => ['tipus' => 'date', 'llista' => 1],
                'estat' => ['tipus' => 'select', 'opcions' => ['actiu', 'tancat'], 'llista' => 1],
                'actual' => ['tipus' => 'bool', 'llista' => 1],
            ],
            'fills' => [['periodes', 'curs_id'], ['grups', 'curs_id'], ['matricules', 'curs_id']],
        ],
        'periodes' => [
            'cap' => 'escola',
            'ordre' => 't.curs_id, t.ordre',
            'etiqueta' => 'SELECT pe.id, pe.nom || \' (\' || c.nom || \')\' AS e FROM periodes pe JOIN cursos c ON c.id = pe.curs_id ORDER BY c.data_inici DESC, pe.ordre',
            'camps' => [
                'curs_id' => ['tipus' => 'ref', 'ref' => 'cursos', 'req' => 1, 'llista' => 1],
                'nom' => ['tipus' => 'text', 'req' => 1, 'llista' => 1, 'ajuda' => t('ajuda.periode')],
                'ordre' => ['tipus' => 'number', 'llista' => 1, 'defecte' => 1],
            ],
        ],
        'assignatures' => [
            'cap' => 'escola',
            'cerca' => ['t.nom'],
            'ordre' => 't.nom',
            'etiqueta' => 'SELECT id, nom AS e FROM assignatures ORDER BY nom',
            'camps' => [
                'nom' => ['tipus' => 'text', 'req' => 1, 'llista' => 1],
                'tipus' => ['tipus' => 'select', 'opcions' => ['llenguatge', 'instrument', 'conjunt', 'cor', 'altra'], 'llista' => 1],
                'descripcio' => $obs,
                'estat' => $estat,
            ],
            'fills' => [['grups', 'assignatura_id']],
        ],
        'aules' => [
            'cap' => 'escola',
            'cerca' => ['t.nom'],
            'ordre' => 't.nom',
            'etiqueta' => 'SELECT id, nom AS e FROM aules ORDER BY nom',
            'camps' => [
                'nom' => ['tipus' => 'text', 'req' => 1, 'llista' => 1],
                'capacitat' => ['tipus' => 'number', 'llista' => 1],
                'observacions' => $obs,
            ],
        ],
        'grups' => [
            'cap' => 'escola',
            'from' => 'grups t JOIN assignatures a ON a.id = t.assignatura_id JOIN cursos c ON c.id = t.curs_id',
            'cerca' => ['t.nom', 'a.nom'],
            'ordre' => 'c.data_inici DESC, a.nom, t.nom',
            'etiqueta' => "SELECT g.id, a.nom || ' · ' || g.nom || ' (' || c.nom || ')' AS e FROM grups g JOIN assignatures a ON a.id = g.assignatura_id JOIN cursos c ON c.id = g.curs_id ORDER BY c.data_inici DESC, a.nom, g.nom",
            'camps' => [
                'curs_id' => ['tipus' => 'ref', 'ref' => 'cursos', 'req' => 1, 'llista' => 1, 'defecte' => 'curs'],
                'assignatura_id' => ['tipus' => 'ref', 'ref' => 'assignatures', 'req' => 1, 'llista' => 1],
                'nom' => ['tipus' => 'text', 'req' => 1, 'llista' => 1, 'ajuda' => t('ajuda.grup')],
                'professor_id' => ['tipus' => 'ref', 'ref' => 'professors', 'llista' => 1],
                'aula_id' => ['tipus' => 'ref', 'ref' => 'aules', 'llista' => 1],
                'estat' => $estat,
            ],
            'fills' => [['horaris', 'grup_id'], ['matricula_grups', 'grup_id']],
        ],
        'horaris' => [
            'cap' => 'escola',
            'ordre' => 't.dia, t.hora_inici',
            'camps' => [
                'grup_id' => ['tipus' => 'ref', 'ref' => 'grups', 'req' => 1, 'llista' => 1],
                'dia' => ['tipus' => 'dia', 'req' => 1, 'llista' => 1],
                'hora_inici' => ['tipus' => 'time', 'req' => 1, 'llista' => 1],
                'hora_fi' => ['tipus' => 'time', 'req' => 1, 'llista' => 1],
            ],
        ],
        'matricules' => [
            'cap' => 'escola',
            'from' => 'matricules t JOIN alumnes al ON al.id = t.alumne_id JOIN persones p ON p.id = al.persona_id JOIN cursos c ON c.id = t.curs_id',
            'cerca' => ['p.nom', 'p.cognoms', 'c.nom'],
            'ordre' => 'c.data_inici DESC, p.cognoms, p.nom',
            'etiqueta' => "SELECT m.id, " . NOM_PERSONA_SQL . " || ' (' || c.nom || ')' AS e FROM matricules m JOIN alumnes al ON al.id = m.alumne_id JOIN persones p ON p.id = al.persona_id JOIN cursos c ON c.id = m.curs_id ORDER BY c.data_inici DESC, e",
            'camps' => [
                'alumne_id' => ['tipus' => 'ref', 'ref' => 'alumnes', 'req' => 1, 'llista' => 1],
                'curs_id' => ['tipus' => 'ref', 'ref' => 'cursos', 'req' => 1, 'llista' => 1, 'defecte' => 'curs'],
                'data' => ['tipus' => 'date', 'llista' => 1, 'defecte' => 'avui'],
                'estat' => $estat,
                'observacions' => $obs,
            ],
            'fills' => [['matricula_grups', 'matricula_id']],
        ],
        'matricula_grups' => [
            'cap' => 'escola',
            'from' => 'matricula_grups t JOIN matricules m ON m.id = t.matricula_id JOIN alumnes al ON al.id = m.alumne_id JOIN persones p ON p.id = al.persona_id',
            'ordre' => 'p.cognoms, p.nom',
            'camps' => [
                'matricula_id' => ['tipus' => 'ref', 'ref' => 'matricules', 'req' => 1, 'llista' => 1],
                'grup_id' => ['tipus' => 'ref', 'ref' => 'grups', 'req' => 1, 'llista' => 1],
            ],
            'valida' => function (array $d): ?string {
                $c1 = valor('SELECT curs_id FROM matricules WHERE id = ?', [$d['matricula_id']]);
                $c2 = valor('SELECT curs_id FROM grups WHERE id = ?', [$d['grup_id']]);
                return $c1 != $c2 ? t('err.grup_curs') : null;
            },
        ],
        'instruments' => [
            'cap' => 'instruments',
            'cerca' => ['t.inventari', 't.tipus', 't.marca', 't.model', 't.num_serie', 't.ubicacio'],
            'ordre' => 't.inventari',
            'etiqueta' => "SELECT id, inventari || ' · ' || tipus || COALESCE(' ' || marca, '') AS e FROM instruments ORDER BY inventari",
            'camps' => [
                'inventari' => ['tipus' => 'text', 'req' => 1, 'llista' => 1],
                'tipus' => ['tipus' => 'text', 'req' => 1, 'llista' => 1, 'ajuda' => t('ajuda.tipus_instrument')],
                'marca' => ['tipus' => 'text', 'llista' => 1],
                'model' => ['tipus' => 'text', 'llista' => 1],
                'num_serie' => ['tipus' => 'text'],
                'estat' => ['tipus' => 'select', 'opcions' => ['bo', 'regular', 'reparacio', 'baixa'], 'llista' => 1],
                'ubicacio' => ['tipus' => 'text', 'llista' => 1],
                'data_adquisicio' => ['tipus' => 'date'],
                'observacions' => $obs,
            ],
            'fills' => [['cessions', 'instrument_id'], ['manteniments', 'instrument_id']],
        ],
        'manteniments' => [
            'cap' => 'instruments',
            'ordre' => 't.data DESC',
            'camps' => [
                'instrument_id' => ['tipus' => 'ref', 'ref' => 'instruments', 'req' => 1, 'llista' => 1],
                'data' => ['tipus' => 'date', 'req' => 1, 'llista' => 1, 'defecte' => 'avui'],
                'descripcio' => ['tipus' => 'text', 'req' => 1, 'llista' => 1],
                'cost' => ['tipus' => 'decimal', 'llista' => 1],
                'observacions' => $obs,
            ],
        ],
        'cessions' => [
            'cap' => 'instruments',
            'from' => 'cessions t JOIN instruments i ON i.id = t.instrument_id JOIN persones p ON p.id = t.persona_id',
            'cerca' => ['i.inventari', 'i.tipus', 'p.nom', 'p.cognoms'],
            'ordre' => '(t.data_retorn IS NOT NULL), t.data_entrega DESC',
            'filtres' => ['actives' => 't.data_retorn IS NULL', 'vencudes' => "t.data_retorn IS NULL AND t.data_prevista < date('now','localtime')"],
            'camps' => [
                'instrument_id' => ['tipus' => 'ref', 'ref' => 'instruments', 'req' => 1, 'llista' => 1],
                'persona_id' => $persona,
                'data_entrega' => ['tipus' => 'date', 'req' => 1, 'llista' => 1, 'defecte' => 'avui'],
                'data_prevista' => ['tipus' => 'date', 'llista' => 1],
                'data_retorn' => ['tipus' => 'date', 'llista' => 1],
                'estat_entrega' => ['tipus' => 'text'],
                'estat_retorn' => ['tipus' => 'text'],
                'fianca' => ['tipus' => 'decimal'],
                'observacions' => $obs,
            ],
            'valida' => function (array $d, ?int $id): ?string {
                if ($d['data_retorn'] === null) {
                    $ja = valor('SELECT COUNT(*) FROM cessions WHERE instrument_id = ? AND data_retorn IS NULL AND id IS NOT ?', [$d['instrument_id'], $id]);
                    if ($ja) {
                        return t('err.instrument_cedit');
                    }
                }
                if ($d['data_retorn'] !== null && $d['data_retorn'] < $d['data_entrega']) {
                    return t('err.dates');
                }
                return null;
            },
        ],
    ];

    // Un sol curs pot ser l'actual
    $e['cursos']['despres'] = function (int $id, array $d): void {
        if (!empty($d['actual'])) {
            q('UPDATE cursos SET actual = 0 WHERE id <> ?', [$id]);
        }
    };
    $e['horaris']['valida'] = fn(array $d) => $d['hora_fi'] <= $d['hora_inici'] ? t('err.hores') : null;
    $e['persones']['valida'] = function (array $d): ?string {
        if ($d['data_naixement'] > date('Y-m-d')) {
            return t('err.naixement_futur');
        }
        if (es_major_edat($d['data_naixement'])) {
            $falten = array_filter(['document', 'telefon'], fn($c) => ($d[$c] ?? '') === '' || $d[$c] === null);
            if ($falten) {
                return t('err.major_edat', implode(', ', array_map(fn($c) => t('camp.' . $c), $falten)));
            }
        }
        return null;
    };

    foreach ($e as $nom => &$def) {
        $def['nom'] = $nom;
        $def['taula'] = $def['taula'] ?? $nom;
        $def['from'] = $def['from'] ?? $def['taula'] . ' t';
        $def['cerca'] = $def['cerca'] ?? [];
        $def['fills'] = $def['fills'] ?? [];
    }
    return $e;
}

function entitat(string $nom): array
{
    $e = entitats();
    if (!isset($e[$nom])) {
        http_response_code(404);
        exit(t('err.no_trobat'));
    }
    return $e[$nom];
}
