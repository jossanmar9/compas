<?php
// Noms de compte ASCII i migració dels noms antics; els noms de persones continuen admetent accents.
// SPDX-License-Identifier: AGPL-3.0-or-later
declare(strict_types=1);

function nom_usuari_valid(string $nom): bool
{
    return preg_match('/^[a-zA-Z0-9._-]{3,40}$/D', $nom) === 1;
}

function usuari_existeix(string $nom, ?int $excepte = null): bool
{
    return (bool)valor('SELECT 1 FROM usuaris WHERE usuari = ? AND id IS NOT ?', [$nom, $excepte]);
}

function migra_noms_usuaris(PDO $pdo): void
{
    foreach ($pdo->query('SELECT id, usuari FROM usuaris ORDER BY id')->fetchAll(PDO::FETCH_ASSOC) as $u) {
        if (nom_usuari_valid($u['usuari'])) {
            continue;
        }
        $base = substr((string)preg_replace('/[^a-z0-9._-]/', '', str_replace('n~', 'n', clau_alfabetica($u['usuari']))), 0, 40);
        if (strlen($base) < 3) {
            $base = 'usuari' . $u['id'];
        }
        $nom = $base;
        $st = $pdo->prepare('SELECT 1 FROM usuaris WHERE usuari = ? AND id <> ?');
        for ($i = 2; ; $i++) {
            $st->execute([$nom, $u['id']]);
            if (!$st->fetchColumn()) {
                break;
            }
            $sufix = '-' . $i;
            $nom = substr($base, 0, 40 - strlen($sufix)) . $sufix;
        }
        $pdo->prepare('UPDATE usuaris SET usuari = ? WHERE id = ?')->execute([$nom, $u['id']]);
        $pdo->prepare('INSERT INTO auditoria(accio, entitat, element_id, detall) VALUES(?, ?, ?, ?)')
            ->execute(['migra_usuari', 'usuaris', $u['id'], $u['usuari'] . ' → ' . $nom]);
    }
}

const ENTITATS_CONSULTOR = ['persones', 'socis', 'tipus_socis', 'alumnes', 'professors', 'tutories',
    'cursos', 'periodes', 'assignatures', 'aules', 'grups', 'horaris', 'matricules', 'matricula_grups',
    'agrupacions', 'membres', 'instruments', 'cessions', 'manteniments'];

function es_consultor(): bool
{
    return pot('consulta.limitada') && !pot('persones.edit');
}

/** Comprovació central també per a adreces escrites a mà; canviar la pròpia contrasenya sí que es permet. */
function consultor_pot_ruta(string $ruta, bool $post = false): bool
{
    if ($post) {
        return in_array($ruta, ['perfil', 'eixir'], true);
    }
    if (get('csv') === '1' || get('imprimir') === '1') {
        return false;
    }
    if (in_array($ruta, ['llista', 'fitxa', 'cerca_ref'], true)) {
        return in_array((string)get('e'), ENTITATS_CONSULTOR, true);
    }
    return in_array($ruta, ['inici', 'horari', 'perfil', 'eixir', 'ajuda', 'doc_img'], true)
        || in_array($ruta, ENTITATS_CONSULTOR, true);
}

function autentica_sessio(int $id): void
{
    session_regenerate_id(true);
    $_SESSION['usuari_id'] = $id;
    // Només una empremta: servix per a tancar les altres sessions en canviar la contrasenya sense guardar-ne el resum
    $_SESSION['sessio_contrasenya'] = hash('sha256', (string)valor('SELECT contrasenya FROM usuaris WHERE id = ?', [$id]));
    $_SESSION['sessions_versio'] = (string)config('sessions_versio', '');
    usuari(true);
}
