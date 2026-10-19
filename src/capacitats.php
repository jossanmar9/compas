<?php
// Capacitats i rols per defecte
// SPDX-License-Identifier: GPL-2.0-or-later

/** Totes les capacitats, agrupades per mòdul (l'etiqueta es tradueix amb "cap.<clau>") */
function capacitats(): array
{
    return [
        'persones' => ['persones.view', 'persones.edit'],
        'socis' => ['socis.view', 'socis.edit'],
        'escola' => ['escola.view', 'escola.edit', 'escola.assistencia', 'escola.notes', 'escola.totsgrups'],
        'agrupacions' => ['agrupacions.view', 'agrupacions.edit'],
        'instruments' => ['instruments.view', 'instruments.edit'],
        'sistema' => ['usuaris.manage', 'config.manage', 'auditoria.view', 'copia.manage'],
    ];
}

function totes_capacitats(): array
{
    return array_merge(...array_values(capacitats()));
}

/** Rols inicials, segons docs/rols-permisos.md. Cada societat els pot adaptar. */
function rols_per_defecte(): array
{
    $vistes = ['persones.view', 'socis.view', 'escola.view', 'agrupacions.view', 'instruments.view'];
    $gestio = ['persones.view', 'persones.edit', 'socis.view', 'socis.edit', 'escola.view', 'escola.edit',
        'escola.assistencia', 'escola.notes', 'escola.totsgrups', 'agrupacions.view', 'agrupacions.edit',
        'instruments.view', 'instruments.edit', 'copia.manage'];
    return [
        'admin' => totes_capacitats(),
        'administratiu' => $gestio,
        'president' => array_merge($vistes, ['auditoria.view']),
        'secretari' => ['persones.view', 'persones.edit', 'socis.view', 'socis.edit', 'agrupacions.view',
            'agrupacions.edit', 'escola.view', 'instruments.view'],
        'tresorer' => ['persones.view', 'socis.view', 'instruments.view'],
        'director_escola' => ['persones.view', 'escola.view', 'escola.edit', 'escola.assistencia',
            'escola.notes', 'escola.totsgrups'],
        'professor' => ['escola.assistencia', 'escola.notes'],
        'director_agrupacio' => ['persones.view', 'agrupacions.view', 'agrupacions.edit'],
        'music' => [],
        'alumne' => [],
        'tutor' => [],
    ];
}

function sembra_rols(PDO $pdo): void
{
    if ((int)$pdo->query('SELECT COUNT(*) FROM rols')->fetchColumn() > 0) {
        return;
    }
    $ins = $pdo->prepare('INSERT INTO rols(clau, nom) VALUES(?, NULL)');
    $cap = $pdo->prepare('INSERT INTO rol_capacitats(rol_id, capacitat) VALUES(?, ?)');
    foreach (rols_per_defecte() as $clau => $caps) {
        $ins->execute([$clau]);
        $id = (int)$pdo->lastInsertId();
        foreach ($caps as $c) {
            $cap->execute([$id, $c]);
        }
    }
}

/** Nom visible d'un rol: el personalitzat o el traduït */
function nom_rol(array $rol): string
{
    return $rol['nom'] ?: t('rol.' . $rol['clau']);
}
