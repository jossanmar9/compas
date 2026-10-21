<?php
// Definició de les entitats que es gestionen amb el formulari genèric
// Copyright (C) 2026 José Vicente Sanjuan
// SPDX-License-Identifier: AGPL-3.0-or-later
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

/** Botó que obri un document per a imprimir en una pestanya nova */
function boto_document(string $url, string $text): string
{
    return '<a class="boto secundari" target="_blank" href="' . h($url) . '">🖨 ' . h($text) . '</a>';
}

function entitats(): array
{
    static $e = null;
    if ($e) {
        return $e;
    }
    $estat = ['tipus' => 'select', 'opcions' => ['actiu', 'baixa'], 'llista' => 1];
    // L'IBAN a la fitxa de persona i de soci, per a qui pot fer domiciliacions
    $ambIban = function_exists('pot') && pot('economia.edit');
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
            'filtres' => [
                'incomplets' => PERSONA_INCOMPLETA_SQL,
                'imatge_pendent' => "t.consentiment_imatge = 'pendent' AND t.id IN (SELECT persona_id FROM alumnes WHERE estat = 'actiu')",
                'imatge_no' => "t.consentiment_imatge = 'no_autoritza'",
            ],
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
                'consentiment_imatge' => ['tipus' => 'select', 'opcions' => ['pendent', 'autoritza', 'no_autoritza'], 'ajuda' => t('ajuda.consentiment')],
                'consentiment_data' => ['tipus' => 'date'],
                'actiu' => ['tipus' => 'bool', 'llista' => 1, 'defecte' => 1],
            ] + ($ambIban ? ['iban' => camp_iban(fn(array $f) => $f['id'] ?? null) + ['nomes_major' => 1]] : []),
            'accions_fitxa' => fn(array $f) => boto_document(url('consentiment', ['persona' => $f['id']]), t('consent.full')),
            'fills' => [['socis', 'persona_id'], ['alumnes', 'persona_id'], ['professors', 'persona_id'],
                ['membres', 'persona_id'], ['tutories', 'tutor_id'], ['cessions', 'persona_id'],
                ['mandats', 'persona_id'], ['rebuts', 'persona_id']],
        ],
        'socis' => $perfil('socis') + [
            'cap' => 'socis',
            'accions_fitxa' => fn(array $f) => boto_document(url('certificat', ['tipus' => 'soci', 'id' => $f['id']]), t('cert.boto')),
            'cerca' => ['p.nom', 'p.cognoms', 't.numero'],
            'camps' => [
                'persona_id' => $persona,
                'numero' => ['tipus' => 'text', 'llista' => 1],
                'tipus' => ['tipus' => 'select', 'opcions' => ['ordinari', 'protector', 'honorari', 'juvenil'], 'llista' => 1],
                'data_alta' => ['tipus' => 'date', 'llista' => 1, 'defecte' => 'avui'],
                'data_baixa' => ['tipus' => 'date'],
                'estat' => $estat,
                'observacions' => $obs,
            ] + ($ambIban ? ['iban' => camp_iban(fn(array $f) => $f['persona_id'] ?? null)] : []),
            'valida' => fn(array $d) => valida_iban_persona($d['iban'] ?? null, (int)$d['persona_id']),
        ],
        'alumnes' => $perfil('alumnes') + [
            'cap' => 'escola',
            'accions_fitxa' => fn(array $f) => boto_document(url('consentiment', ['persona' => $f['persona_id']]), t('consent.full')),
            'camps' => [
                'persona_id' => $persona,
                'data_alta' => ['tipus' => 'date', 'llista' => 1, 'defecte' => 'avui'],
                'data_baixa' => ['tipus' => 'date'],
                'estat' => $estat,
                'pagador_id' => ['tipus' => 'ref', 'ref' => 'persones', 'ajuda' => t('ajuda.pagador')],
                'descompte_pct' => ['tipus' => 'decimal', 'pct' => 1, 'ajuda' => t('ajuda.descompte_pct')],
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
            'accions_fitxa' => fn(array $f) => boto_document(url('certificat', ['tipus' => 'membre', 'id' => $f['id']]), t('cert.boto')),
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
            'accions_fitxa' => fn(array $f) => pot('escola.edit') ? '<a class="boto secundari" href="' . h(url('passa_curs', ['curs' => $f['id']])) . '">⏭ ' . h(t('passa.boto')) . '</a>' : '',
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
            'accions_fitxa' => fn(array $f) => boto_document(url('certificat', ['tipus' => 'matricula', 'id' => $f['id']]), t('cert.boto')),
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
        // ---------- Economia ----------
        'tarifes' => [
            'cap' => 'economia',
            'cerca' => ['t.nom'],
            'ordre' => 't.tipus, t.nom',
            'etiqueta' => 'SELECT id, nom AS e FROM tarifes ORDER BY nom',
            'camps' => [
                'nom' => ['tipus' => 'text', 'req' => 1, 'llista' => 1, 'ajuda' => t('ajuda.tarifa_nom')],
                'tipus' => ['tipus' => 'select', 'opcions' => ['quota_soci', 'matricula', 'classe', 'altres'], 'llista' => 1],
                'tipus_soci' => ['tipus' => 'select', 'opcions' => ['ordinari', 'protector', 'honorari', 'juvenil'], 'buit' => 1, 'llista' => 1, 'ajuda' => t('ajuda.tarifa_tipus_soci')],
                'assignatura_id' => ['tipus' => 'ref', 'ref' => 'assignatures', 'llista' => 1, 'ajuda' => t('ajuda.tarifa_assignatura')],
                'periodicitat' => ['tipus' => 'select', 'opcions' => ['anual', 'semestral', 'trimestral', 'mensual', 'unica'], 'llista' => 1, 'ajuda' => t('ajuda.tarifa_periodicitat')],
                'import' => ['tipus' => 'decimal', 'req' => 1, 'llista' => 1, 'euros' => 1],
                'estat' => $estat,
                'observacions' => $obs,
            ],
            'valida' => function (array $d): ?string {
                if ($d['import'] === null || $d['import'] < 0) {
                    return t('err.import');
                }
                $permeses = ['quota_soci' => ['anual', 'semestral', 'trimestral', 'mensual'], 'matricula' => ['unica'], 'classe' => ['anual', 'trimestral', 'mensual']];
                if (isset($permeses[$d['tipus']]) && !in_array($d['periodicitat'], $permeses[$d['tipus']], true)) {
                    return t('err.tarifa_periodicitat', t('opt.' . $d['tipus']), implode(', ', array_map(fn($p) => t('opt.' . $p), $permeses[$d['tipus']])));
                }
                return null;
            },
        ],
        'mandats' => [
            'cap' => 'economia',
            'from' => 'mandats t JOIN persones p ON p.id = t.persona_id',
            'cerca' => ['p.nom', 'p.cognoms', 't.titular', 't.referencia', 't.iban'],
            'ordre' => 'p.cognoms, p.nom',
            'etiqueta' => "SELECT m.id, " . NOM_PERSONA_SQL . " || ' · ' || m.referencia AS e FROM mandats m JOIN persones p ON p.id = m.persona_id ORDER BY e",
            'accions_fitxa' => fn(array $f) => boto_document(url('mandat_doc', ['id' => $f['id']]), t('mandat.full')),
            'camps' => [
                'persona_id' => $persona + ['etiqueta' => 'camp.titular_persona', 'ajuda' => t('ajuda.mandat_persona')],
                'titular' => ['tipus' => 'text', 'ajuda' => t('ajuda.mandat_titular')],
                'iban' => ['tipus' => 'iban', 'req' => 1, 'llista' => 1],
                'bic' => ['tipus' => 'text', 'ajuda' => t('ajuda.bic')],
                'referencia' => ['tipus' => 'text', 'llista' => 1, 'ajuda' => t('ajuda.mandat_referencia')],
                'data_signatura' => ['tipus' => 'date', 'req' => 1, 'llista' => 1, 'defecte' => 'avui'],
                'estat' => $estat,
                'observacions' => $obs,
            ],
            'valida' => function (array $d, ?int $id): ?string {
                if (!iban_valid($d['iban'])) {
                    return t('err.iban');
                }
                if ($d['bic'] !== null && !bic_valid($d['bic'])) {
                    return t('err.bic');
                }
                if ($d['estat'] === 'actiu' && valor("SELECT COUNT(*) FROM mandats WHERE persona_id = ? AND estat = 'actiu' AND id IS NOT ?", [$d['persona_id'], $id])) {
                    return t('err.mandat_actiu');
                }
                return null;
            },
            'despres' => function (int $id, array $d): void {
                if ($d['bic'] !== null) {
                    q('UPDATE mandats SET bic = upper(bic) WHERE id = ?', [$id]);
                }
                if ($d['referencia'] === null) {
                    q('UPDATE mandats SET referencia = ? WHERE id = ?', [referencia_mandat($id), $id]);
                }
            },
            'fills' => [['rebuts', 'mandat_id']],
        ],
        'rebuts' => [
            'cap' => 'economia',
            'from' => 'rebuts t JOIN persones p ON p.id = t.persona_id',
            'cerca' => ['p.nom', 'p.cognoms', 't.numero', 't.concepte', 't.periode'],
            'ordre' => 't.data_emissio DESC, t.numero DESC',
            'etiqueta' => "SELECT r.id, r.numero || ' · ' || " . NOM_PERSONA_SQL . " AS e FROM rebuts r JOIN persones p ON p.id = r.persona_id ORDER BY r.id DESC",
            'suma' => 'import',
            'filtres' => [
                'per_cobrar' => "t.estat IN ('per_cobrar', 'retornat')",
                'retornats' => "t.estat = 'retornat'",
                'efectiu' => "t.estat IN ('per_cobrar', 'retornat') AND t.forma_pagament <> 'domiciliacio'",
                'domiciliar' => "t.estat IN ('per_cobrar', 'retornat') AND t.forma_pagament = 'domiciliacio' AND t.remesa_id IS NULL",
                'any' => "substr(t.data_emissio, 1, 4) = strftime('%Y', 'now', 'localtime')",
            ],
            'avis_fila' => fn(array $f) => $f['estat'] === 'retornat' ? t('rebut.avis_retornat') : null,
            'accions_fitxa' => 'accions_rebut',
            'camps' => [
                'numero' => ['tipus' => 'text', 'llista' => 1, 'etiqueta' => 'camp.numero_rebut', 'ajuda' => t('ajuda.rebut_numero')],
                'persona_id' => $persona + ['etiqueta' => 'camp.pagador', 'ajuda' => t('ajuda.rebut_persona')],
                'concepte' => ['tipus' => 'text', 'req' => 1, 'llista' => 1],
                'tipus' => ['tipus' => 'select', 'opcions' => ['quota_soci', 'matricula', 'classe', 'loteria', 'altres']],
                'periode' => ['tipus' => 'text', 'ajuda' => t('ajuda.rebut_periode')],
                'data_emissio' => ['tipus' => 'date', 'req' => 1, 'llista' => 1, 'defecte' => 'avui'],
                'import' => ['tipus' => 'decimal', 'llista' => 1, 'euros' => 1, 'ajuda' => t('ajuda.rebut_import')],
                'forma_pagament' => ['tipus' => 'select', 'opcions' => ['domiciliacio', 'efectiu', 'transferencia'], 'llista' => 1],
                'estat' => ['tipus' => 'select', 'opcions' => ['per_cobrar', 'pagat', 'retornat', 'anullat'], 'llista' => 1],
                'data_pagament' => ['tipus' => 'date'],
                'mandat_id' => ['tipus' => 'ref', 'ref' => 'mandats', 'ajuda' => t('ajuda.rebut_mandat')],
                'remesa_id' => ['tipus' => 'ref', 'ref' => 'remeses', 'ajuda' => t('ajuda.rebut_remesa')],
                'observacions' => $obs,
            ],
            'valida' => function (array $d, ?int $id): ?string {
                if ($d['import'] !== null && $d['import'] < 0) {
                    return t('err.import');
                }
                if ($d['estat'] === 'pagat' && $d['data_pagament'] === null) {
                    return t('err.data_pagament');
                }
                return null;
            },
            'despres' => function (int $id, array $d): void {
                if ($d['numero'] === null) {
                    q('UPDATE rebuts SET numero = ? WHERE id = ?', [numero_rebut_nou($d['data_emissio']), $id]);
                }
                recalcula_rebut($id);
            },
            'elimina_permes' => fn(int $id) => valor("SELECT COUNT(*) FROM rebuts r JOIN remeses re ON re.id = r.remesa_id WHERE r.id = ?", [$id]) ? t('err.rebut_en_remesa') : null,
            'fills' => [['rebut_linies', 'rebut_id']],
        ],
        'rebut_linies' => [
            'cap' => 'economia',
            'ordre' => 't.ordre, t.id',
            'pare' => ['rebuts', 'rebut_id'],
            'camps' => [
                'rebut_id' => ['tipus' => 'ref', 'ref' => 'rebuts', 'req' => 1],
                'concepte' => ['tipus' => 'text', 'req' => 1, 'llista' => 1],
                'import' => ['tipus' => 'decimal', 'req' => 1, 'llista' => 1, 'euros' => 1, 'ajuda' => t('ajuda.linia_import')],
                'alumne_id' => ['tipus' => 'ref', 'ref' => 'alumnes', 'llista' => 1],
                'ordre' => ['tipus' => 'number', 'defecte' => 0],
            ],
            'despres' => fn(int $id, array $d) => recalcula_rebut((int)$d['rebut_id']),
            'despres_elimina' => fn(array $f) => recalcula_rebut((int)$f['rebut_id']),
        ],
        'remeses' => [
            'cap' => 'economia',
            'nomes_lectura' => 1,
            'cerca' => ['t.referencia'],
            'ordre' => 't.data_cobrament DESC, t.id DESC',
            'etiqueta' => "SELECT id, referencia || ' (' || strftime('%d/%m/%Y', data_cobrament) || ')' AS e FROM remeses ORDER BY id DESC",
            'accions_fitxa' => fn(array $f) => '<a class="boto" href="' . h(url('remesa', ['id' => $f['id']])) . '">🏦 ' . h(t('remesa.obri')) . '</a>',
            'camps' => [
                'referencia' => ['tipus' => 'text', 'llista' => 1],
                'data_creacio' => ['tipus' => 'date', 'llista' => 1],
                'data_cobrament' => ['tipus' => 'date', 'llista' => 1],
                'estat' => ['tipus' => 'select', 'opcions' => ['preparada', 'cobrada'], 'llista' => 1],
                'observacions' => $obs,
            ],
            'fills' => [['rebuts', 'remesa_id']],
        ],
        'loteries' => [
            'cap' => 'economia',
            'cerca' => ['t.nom', 't.numeros'],
            'ordre' => 't.data_sorteig DESC, t.id DESC',
            'etiqueta' => 'SELECT id, nom AS e FROM loteries ORDER BY data_sorteig DESC, id DESC',
            'accions_fitxa' => fn(array $f) => '<a class="boto" href="' . h(url('loteria', ['id' => $f['id']])) . '">🎟 ' . h(t('loteria.resum')) . '</a>',
            'camps' => [
                'nom' => ['tipus' => 'text', 'req' => 1, 'llista' => 1, 'ajuda' => t('ajuda.loteria_nom')],
                'data_sorteig' => ['tipus' => 'date', 'llista' => 1],
                'numeros' => ['tipus' => 'text', 'req' => 1, 'llista' => 1, 'ajuda' => t('ajuda.loteria_numeros')],
                'preu' => ['tipus' => 'decimal', 'req' => 1, 'llista' => 1, 'euros' => 1, 'ajuda' => t('ajuda.loteria_preu')],
                'juga' => ['tipus' => 'decimal', 'req' => 1, 'euros' => 1, 'ajuda' => t('ajuda.loteria_juga')],
                'participacions_talonari' => ['tipus' => 'number', 'req' => 1, 'defecte' => 50],
                'estat' => ['tipus' => 'select', 'opcions' => ['actiu', 'tancat'], 'llista' => 1],
                'peu' => ['tipus' => 'textarea', 'ajuda' => t('ajuda.loteria_peu')],
                'observacions' => $obs,
            ],
            'valida' => function (array $d): ?string {
                if ($d['preu'] <= 0 || $d['juga'] <= 0 || $d['juga'] > $d['preu']) {
                    return t('err.loteria_preu');
                }
                if ($d['participacions_talonari'] < 1 || $d['participacions_talonari'] > 500) {
                    return t('err.loteria_talonari');
                }
                return null;
            },
            'fills' => [['talonaris', 'loteria_id']],
        ],
        'talonaris' => [
            'cap' => 'economia',
            'from' => 'talonaris t LEFT JOIN persones p ON p.id = t.persona_id',
            'cerca' => ['p.nom', 'p.cognoms'],
            'ordre' => 't.loteria_id DESC, t.numero',
            'pare' => ['loteries', 'loteria_id'],
            'etiqueta' => "SELECT ta.id, lo.nom || ' · ' || ta.numero AS e FROM talonaris ta JOIN loteries lo ON lo.id = ta.loteria_id ORDER BY lo.id DESC, ta.numero",
            'avis_fila' => 'avis_talonari',
            'accions_fitxa' => fn(array $f) => boto_document(url('participacions', ['talonari' => $f['id']]), t('loteria.imprimeix')),
            'camps' => [
                'loteria_id' => ['tipus' => 'ref', 'ref' => 'loteries', 'req' => 1, 'llista' => 1],
                'numero' => ['tipus' => 'number', 'req' => 1, 'llista' => 1, 'etiqueta' => 'camp.numero_talonari', 'ajuda' => t('ajuda.talonari_numero')],
                'persona_id' => ['tipus' => 'ref', 'ref' => 'persones', 'llista' => 1, 'etiqueta' => 'loteria.col_persona'],
                'data_entrega' => ['tipus' => 'date', 'defecte' => 'avui'],
                'venudes' => ['tipus' => 'number', 'llista' => 1],
                'tornades' => ['tipus' => 'number', 'llista' => 1],
                'import_liquidat' => ['tipus' => 'decimal', 'llista' => 1, 'euros' => 1],
                'data_liquidacio' => ['tipus' => 'date'],
                'observacions' => $obs,
            ],
            'valida' => function (array $d): ?string {
                $mida = (int)valor('SELECT participacions_talonari FROM loteries WHERE id = ?', [$d['loteria_id']]);
                if (($d['venudes'] ?? 0) < 0 || ($d['tornades'] ?? 0) < 0 || ($d['venudes'] ?? 0) + ($d['tornades'] ?? 0) > $mida) {
                    return t('err.talonari_quantitats', $mida);
                }
                if ($d['numero'] < 1) {
                    return t('err.talonari_numero');
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
    // Si es marca el consentiment d'imatge sense data, es posa la d'avui
    $e['persones']['despres'] = function (int $id, array $d): void {
        q("UPDATE persones SET consentiment_data = date('now','localtime') WHERE id = ? AND consentiment_imatge <> 'pendent' AND consentiment_data IS NULL", [$id]);
    };
    $e['horaris']['valida'] = fn(array $d) => $d['hora_fi'] <= $d['hora_inici'] ? t('err.hores') : null;
    $e['persones']['valida'] = function (array $d): ?string {
        if ($d['data_naixement'] > date('Y-m-d')) {
            return t('err.naixement_futur');
        }
        if ($msg = valida_iban_persona($d['iban'] ?? null, null, $d['data_naixement'])) {
            return $msg;
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
