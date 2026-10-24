<?php
// Compàs: Gestió lliure per a associacions
// Copyright (C) 2026 José Vicente Sanjuan
// SPDX-License-Identifier: AGPL-3.0-or-later

declare(strict_types=1);

const COMPAS_VERSIO = '2.0.1';
const SRC_DIR = __DIR__;

define('ARREL_DIR', dirname(__DIR__));
define('DADES_DIR', rtrim(getenv('COMPAS_DADES') ?: ARREL_DIR . DIRECTORY_SEPARATOR . 'data', '/\\'));
define('DOCS_DIR', ARREL_DIR . DIRECTORY_SEPARATOR . 'docs');

date_default_timezone_set(getenv('COMPAS_ZONA') ?: 'Europe/Madrid');
mb_internal_encoding('UTF-8');

require SRC_DIR . '/i18n.php';
require SRC_DIR . '/tipus.php';
require SRC_DIR . '/lang/vocabulari.php';
require SRC_DIR . '/capacitats.php';
require SRC_DIR . '/markdown.php';
require SRC_DIR . '/xifrat.php';
require SRC_DIR . '/sepa.php';

/* ---------------- Base de dades ---------------- */

/** Text en minúscules i sense accents, per a ordenar alfabèticament */
function clau_alfabetica(string $s): string
{
    static $cache = [];
    if (isset($cache[$s])) {
        return $cache[$s];
    }
    if (count($cache) > 20000) {
        $cache = [];
    }
    return $cache[$s] = strtr(mb_strtolower(trim($s)), ['à' => 'a', 'á' => 'a', 'è' => 'e', 'é' => 'e', 'ì' => 'i', 'í' => 'i', 'ï' => 'i', 'ò' => 'o', 'ó' => 'o',
        'ù' => 'u', 'ú' => 'u', 'ü' => 'u', 'ç' => 'c', 'ñ' => 'n~', '·' => '', 'ŀ' => 'l', "'" => '', '’' => '']);
}

function db(): PDO
{
    static $pdo = null;
    if ($pdo) {
        return $pdo;
    }
    if (!is_dir(DADES_DIR)) {
        mkdir(DADES_DIR, 0770, true);
    }
    $fitxer = DADES_DIR . DIRECTORY_SEPARATOR . 'compas.db';
    // Restauració pendent: es fa ací, abans d'obrir la base de dades.
    // Abans de deixar-la preparada ja s'ha fet una còpia «abans-restaurar» de les dades actuals.
    $restaura = DADES_DIR . DIRECTORY_SEPARATOR . 'restaura.db';
    if (is_file($restaura)) {
        foreach (['', '-wal', '-shm'] as $s) {
            if (is_file($fitxer . $s)) {
                unlink($fitxer . $s);
            }
        }
        rename($restaura, $fitxer);
    }
    $pdo = new PDO('sqlite:' . $fitxer, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec('PRAGMA foreign_keys = ON');
    $pdo->exec('PRAGMA journal_mode = WAL');
    $pdo->exec('PRAGMA busy_timeout = 5000');
    // Ordre alfabètic «com les persones»: sense distingir majúscules ni accents (Àlex va amb la A) i amb els números
    // en ordre natural (2 abans que 10, INV-9 abans que INV-10). S'usa amb «COLLATE ALFA» en ordenar els llistats.
    $alfa = fn($a, $b) => strnatcmp(clau_alfabetica((string)$a), clau_alfabetica((string)$b)) ?: strcmp((string)$a, (string)$b);
    method_exists($pdo, 'createCollation') ? $pdo->createCollation('ALFA', $alfa) : $pdo->sqliteCreateCollation('ALFA', $alfa);
    // L'esquema i les migracions només es repassen quan canvia la versió de Compàs o l'esquema:
    // la base de dades guarda en «user_version» amb quina combinació s'ha preparat.
    $esquema = (string)file_get_contents(SRC_DIR . '/schema.sql');
    $versio = crc32(COMPAS_VERSIO . "\n" . $esquema) & 0x7fffffff;
    if ((int)$pdo->query('PRAGMA user_version')->fetchColumn() !== $versio) {
        actualitza_esquema($pdo, $esquema, $versio);
    }
    // Després de restaurar es manté la configuració de còpies que hi havia
    $conservada = DADES_DIR . DIRECTORY_SEPARATOR . 'restaura-config.json';
    if (is_file($conservada)) {
        foreach ((array)json_decode((string)file_get_contents($conservada), true) as $clau => $valor) {
            $pdo->prepare('INSERT INTO config(clau, valor) VALUES(?, ?) ON CONFLICT(clau) DO UPDATE SET valor = excluded.valor')->execute([$clau, $valor]);
        }
        unlink($conservada);
    }
    return $pdo;
}

/**
 * Prepara la base de dades per a esta versió: taules i índexs, columnes noves, dades inicials i rols.
 * Si ja hi havia dades, abans en fa una còpia («abans d'actualitzar»). Tot va en una sola transacció:
 * si alguna cosa falla, la base de dades es queda com estava i es mostra l'error.
 */
function actualitza_esquema(PDO $pdo, string $esquema, int $versio): void
{
    $installat = (bool)$pdo->query("SELECT 1 FROM sqlite_master WHERE type = 'table' AND name = 'usuaris'")->fetchColumn()
        && (int)$pdo->query('SELECT COUNT(*) FROM usuaris')->fetchColumn() > 0;
    if ($installat) {
        $copies = DADES_DIR . DIRECTORY_SEPARATOR . 'copies';
        if (!is_dir($copies)) {
            mkdir($copies, 0770, true);
        }
        // Dos actualitzacions en el mateix segon no han de xocar (VACUUM INTO no sobreescriu)
        $desti = $copies . DIRECTORY_SEPARATOR . 'compas-' . date('Ymd-His') . '-abans-actualitzar.db';
        for ($i = 2; file_exists($desti); $i++) {
            $desti = $copies . DIRECTORY_SEPARATOR . 'compas-' . date('Ymd-His') . "-$i-abans-actualitzar.db";
        }
        $pdo->prepare('VACUUM INTO ?')->execute([$desti]);
    }
    $pdo->beginTransaction();
    try {
        $pdo->exec($esquema);
        migra_esquema($pdo);
        sembra_rols($pdo);
        $pdo->exec('PRAGMA user_version = ' . $versio);
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw new RuntimeException("No s'ha pogut actualitzar la base de dades (" . $e->getMessage() . '). Les dades no s\'han tocat'
            . ($installat ? " i hi ha una còpia «abans d'actualitzar» a la carpeta de còpies." : '.'), 0, $e);
    }
}

/**
 * Afig a les bases de dades d'una versió anterior les columnes noves.
 * (CREATE TABLE IF NOT EXISTS no modifica les taules que ja existeixen.)
 */
function migra_esquema(PDO $pdo): void
{
    $columnes = [
        'persones' => [
            'consentiment_imatge' => "TEXT NOT NULL DEFAULT 'pendent'",
            'consentiment_data' => 'TEXT',
        ],
        'professors' => [
            'certificat_delictes' => 'TEXT',
        ],
        'alumnes' => [
            'pagador_id' => 'INTEGER REFERENCES persones(id) ON DELETE SET NULL',
            'descompte_pct' => 'REAL',
        ],
        // 1.16: dades de la factura (per a justificar subvencions) i document lligat a una subvenció
        'moviments' => [
            'proveidor' => 'TEXT',
            'nif' => 'TEXT',
        ],
        'documents' => [
            'subvencio_id' => 'INTEGER REFERENCES subvencions(id) ON DELETE SET NULL',
        ],
    ];
    foreach ($columnes as $taula => $cols) {
        $existents = array_column($pdo->query("PRAGMA table_info($taula)")->fetchAll(PDO::FETCH_ASSOC), 'name');
        foreach ($cols as $col => $tipus) {
            if (!in_array($col, $existents, true)) {
                $pdo->exec("ALTER TABLE $taula ADD COLUMN $col $tipus");
            }
        }
    }
    // Les bases de dades d'abans de la 1.11 tenien quatre tipus de soci fixos: ara són files de tipus_socis.
    // En una instal·lació nova es creen en la configuració inicial, quan ja se sap l'idioma.
    if ((int)$pdo->query('SELECT COUNT(*) FROM usuaris')->fetchColumn() > 0) {
        sembra_tipus_socis($pdo);
        sembra_tipus_obres($pdo);
        sembra_subvencions($pdo);
        // 2.0: les instal·lacions anteriors són societats musicals; els mòduls nous d'altres tipus queden desactivats
        if (!$pdo->query("SELECT valor FROM config WHERE clau = 'tipus_entitat'")->fetchColumn()) {
            $desactivats = array_filter(explode(',', (string)$pdo->query("SELECT valor FROM config WHERE clau = 'moduls_desactivats'")->fetchColumn()));
            $desactivats = array_values(array_unique(array_merge($desactivats, moduls_desactivats_tipus('societat_musical'))));
            $desa = $pdo->prepare('INSERT INTO config(clau, valor) VALUES(?, ?) ON CONFLICT(clau) DO UPDATE SET valor = excluded.valor');
            $desa->execute(['tipus_entitat', 'societat_musical']);
            $desa->execute(['moduls_desactivats', implode(',', $desactivats) ?: null]);
        }
    }
}

/**
 * Organismes i requisits de justificació inicials (una sola vegada), i com es justifica normalment cada organisme.
 * Són només un punt de partida: cada societat els canvia quan vol (Subvencions → Organismes / Com es justifica).
 */
function sembra_subvencions(PDO $pdo, ?string $idioma = null): void
{
    if ($pdo->query("SELECT valor FROM config WHERE clau = 'subvencions_inicials'")->fetchColumn()) {
        return;
    }
    $es = ($idioma ?? (string)($pdo->query("SELECT valor FROM config WHERE clau = 'idioma'")->fetchColumn() ?: 'ca')) === 'es';
    $requisits = [
        'compte' => [$es ? 'Cuenta justificativa (relación de facturas y pagos)' : 'Compte justificatiu (relació de factures i pagaments)'],
        'factures' => [$es ? 'Facturas y justificantes de pago' : 'Factures i justificants de pagament'],
        'memoria' => [$es ? 'Memoria de la actividad' : 'Memòria de l\'activitat'],
        'certificat' => [$es ? 'Certificado de la secretaría con el visto bueno de la presidencia' : 'Certificat de la secretaria amb el vistiplau de la presidència'],
        'hisenda' => [$es ? 'Certificado de estar al corriente con Hacienda' : 'Certificat d\'estar al corrent amb Hisenda'],
        'ss' => [$es ? 'Certificado de estar al corriente con la Seguridad Social' : 'Certificat d\'estar al corrent amb la Seguretat Social'],
        'ajudes' => [$es ? 'Declaración de otras ayudas recibidas' : 'Declaració d\'altres ajudes rebudes'],
        'publicitat' => [$es ? 'Publicidad de la ayuda (logotipo en carteles, programas y redes)' : 'Publicitat de l\'ajuda (logotip en cartells, programes i xarxes)'],
        'fotos' => [$es ? 'Fotografías, carteles o programas de la actividad' : 'Fotografies, cartells o programes de l\'activitat'],
        'seu' => [$es ? 'Presentación por la sede electrónica' : 'Presentació per la seu electrònica'],
    ];
    $organismes = [
        [$es ? 'Generalitat' : 'Generalitat', ['compte', 'factures', 'memoria', 'hisenda', 'ss', 'ajudes', 'publicitat', 'seu']],
        [$es ? 'Diputación' : 'Diputació', ['compte', 'factures', 'memoria', 'certificat', 'hisenda', 'ss', 'ajudes', 'publicitat']],
        [$es ? 'Ayuntamiento' : 'Ajuntament', ['factures', 'memoria', 'certificat', 'fotos']],
        [$es ? 'Federación' : 'Federació', ['memoria', 'certificat']],
        [$es ? 'Otros' : 'Altres', []],
    ];
    $propia = !$pdo->inTransaction();
    if ($propia) {
        $pdo->beginTransaction();
    }
    if ((int)$pdo->query('SELECT COUNT(*) FROM organismes')->fetchColumn() === 0 && (int)$pdo->query('SELECT COUNT(*) FROM requisits_justificacio')->fetchColumn() === 0) {
        $ids = [];
        $ordre = 0;
        foreach ($requisits as $clau => [$nom]) {
            $ordre += 10;
            $pdo->prepare('INSERT INTO requisits_justificacio(nom, ordre) VALUES(?, ?)')->execute([$nom, $ordre]);
            $ids[$clau] = (int)$pdo->lastInsertId();
        }
        $ordre = 0;
        foreach ($organismes as [$nom, $reqs]) {
            $ordre += 10;
            $pdo->prepare('INSERT INTO organismes(nom, ordre) VALUES(?, ?)')->execute([$nom, $ordre]);
            $org = (int)$pdo->lastInsertId();
            foreach ($reqs as $r) {
                $pdo->prepare('INSERT INTO organisme_requisits(organisme_id, requisit_id) VALUES(?, ?)')->execute([$org, $ids[$r]]);
            }
        }
    }
    $pdo->exec("INSERT INTO config(clau, valor) VALUES('subvencions_inicials', '1') ON CONFLICT(clau) DO UPDATE SET valor = excluded.valor");
    if ($propia) {
        $pdo->commit();
    }
}

/** Tipus d'obra inicials del repertori (una sola vegada); després cada societat els canvia */
function sembra_tipus_obres(PDO $pdo, ?string $idioma = null): void
{
    if ($pdo->query("SELECT valor FROM config WHERE clau = 'tipus_obres_inicials'")->fetchColumn()) {
        return;
    }
    $idioma ??= (string)($pdo->query("SELECT valor FROM config WHERE clau = 'idioma'")->fetchColumn() ?: 'ca');
    $noms = $idioma === 'es'
        ? ['Pasodoble', 'Marcha mora', 'Marcha cristiana', 'Marcha de procesión', 'Himno', 'Obertura', 'Poema sinfónico', 'Suite', 'Fantasía', 'Selección de zarzuela', 'Banda sonora', 'Música ligera', 'Otras']
        : ['Pasdoble', 'Marxa mora', 'Marxa cristiana', 'Marxa de processó', 'Himne', 'Obertura', 'Poema simfònic', 'Suite', 'Fantasia', 'Selecció de sarsuela', 'Banda sonora', 'Música lleugera', 'Altres'];
    if ((int)$pdo->query('SELECT COUNT(*) FROM tipus_obres')->fetchColumn() === 0) {
        foreach ($noms as $i => $nom) {
            $pdo->prepare('INSERT INTO tipus_obres(nom, ordre) VALUES(?, ?)')->execute([$nom, ($i + 1) * 10]);
        }
    }
    $pdo->exec("INSERT INTO config(clau, valor) VALUES('tipus_obres_inicials', '1') ON CONFLICT(clau) DO UPDATE SET valor = excluded.valor");
}

/** Crea els tipus de soci inicials (una sola vegada) i passa els codis antics (ordinari…) a l'id del tipus */
function sembra_tipus_socis(PDO $pdo, ?string $idioma = null): void
{
    $fet = $pdo->query("SELECT valor FROM config WHERE clau = 'tipus_socis_inicials'")->fetchColumn();
    if ($fet) {
        return;
    }
    $idioma ??= (string)($pdo->query("SELECT valor FROM config WHERE clau = 'idioma'")->fetchColumn() ?: 'ca');
    $noms = $idioma === 'es'
        ? ['ordinari' => 'Ordinario', 'protector' => 'Protector', 'juvenil' => 'Juvenil', 'honorari' => 'De honor']
        : ['ordinari' => 'Ordinari', 'protector' => 'Protector', 'juvenil' => 'Juvenil', 'honorari' => 'D\'honor'];
    $propia = !$pdo->inTransaction();
    if ($propia) {
        $pdo->beginTransaction();
    }
    if ((int)$pdo->query('SELECT COUNT(*) FROM tipus_socis')->fetchColumn() === 0) {
        $ordre = 0;
        foreach ($noms as $codi => $nom) {
            $ordre += 10;
            $pdo->prepare('INSERT INTO tipus_socis(nom, ordre, full_alta, codi) VALUES(?, ?, ?, ?)')->execute([$nom, $ordre, $codi === 'honorari' ? 0 : 1, $codi]);
            $id = (string)$pdo->lastInsertId();
            $pdo->prepare('UPDATE socis SET tipus = ? WHERE tipus = ?')->execute([$id, $codi]);
            $pdo->prepare('UPDATE tarifes SET tipus_soci = ? WHERE tipus_soci = ?')->execute([$id, $codi]);
        }
    }
    $pdo->exec("INSERT INTO config(clau, valor) VALUES('tipus_socis_inicials', '1') ON CONFLICT(clau) DO UPDATE SET valor = excluded.valor");
    if ($propia) {
        $pdo->commit();
    }
}

function q(string $sql, array $params = []): PDOStatement
{
    $st = db()->prepare($sql);
    $st->execute($params);
    return $st;
}

function fila(string $sql, array $params = []): ?array
{
    $r = q($sql, $params)->fetch();
    return $r === false ? null : $r;
}

function valor(string $sql, array $params = [])
{
    $r = q($sql, $params)->fetchColumn();
    return $r === false ? null : $r;
}

function config(string $clau, ?string $defecte = null): ?string
{
    $v = valor('SELECT valor FROM config WHERE clau = ?', [$clau]);
    return $v === null ? $defecte : (string)$v;
}

function desa_config(string $clau, ?string $valor): void
{
    q('INSERT INTO config(clau, valor) VALUES(?, ?) ON CONFLICT(clau) DO UPDATE SET valor = excluded.valor', [$clau, $valor]);
}

/* ---------------- Sessió i seguretat ---------------- */

function inicia_sessio(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $dir = DADES_DIR . DIRECTORY_SEPARATOR . 'sessions';
    if (!is_dir($dir)) {
        mkdir($dir, 0770, true);
    }
    session_save_path($dir);
    session_name('compas');
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Strict', 'path' => '/']);
    session_start();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
}

function csrf_camp(): string
{
    return '<input type="hidden" name="_csrf" value="' . h($_SESSION['csrf']) . '">';
}

function comprova_csrf(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $t = $_POST['_csrf'] ?? '';
        if (!is_string($t) || !hash_equals($_SESSION['csrf'] ?? '', $t)) {
            http_response_code(400);
            exit(t('err.csrf'));
        }
    }
}

function h($v): string
{
    return htmlspecialchars((string)($v ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $ruta, array $params = []): string
{
    $params = array_merge(['r' => $ruta], $params);
    return '?' . http_build_query($params);
}

function redirigeix(string $ruta, array $params = []): never
{
    header('Location: ' . url($ruta, $params));
    exit;
}

function avis(string $text, string $tipus = 'ok'): void
{
    $_SESSION['avisos'][] = [$tipus, $text];
}

function post(string $clau, $defecte = '')
{
    $v = $_POST[$clau] ?? $defecte;
    return is_string($v) ? trim($v) : $v;
}

function get(string $clau, $defecte = '')
{
    $v = $_GET[$clau] ?? $defecte;
    return is_string($v) ? trim($v) : $v;
}

/* ---------------- Usuari actual ---------------- */

function usuari(): ?array
{
    static $u = false;
    if ($u !== false) {
        return $u;
    }
    $u = null;
    if (!empty($_SESSION['usuari_id'])) {
        $u = fila('SELECT * FROM usuaris WHERE id = ? AND actiu = 1', [$_SESSION['usuari_id']]);
        if ($u) {
            $u['capacitats'] = array_column(q(
                'SELECT DISTINCT rc.capacitat FROM usuari_rols ur JOIN rol_capacitats rc ON rc.rol_id = ur.rol_id WHERE ur.usuari_id = ?',
                [$u['id']]
            )->fetchAll(), 'capacitat');
        }
    }
    return $u;
}

function pot(string $capacitat): bool
{
    $u = usuari();
    return $u !== null && in_array($capacitat, $u['capacitats'], true);
}

function requereix(string $capacitat): void
{
    if (!pot($capacitat)) {
        http_response_code(403);
        pagina(t('err.permis'), '<div class="buit">' . h(t('err.permis_text')) . '</div>');
        exit;
    }
}

/* ---------------- Auditoria ---------------- */

function audita(string $accio, ?string $entitat = null, $id = null, ?string $detall = null): void
{
    $u = usuari();
    q('INSERT INTO auditoria(usuari_id, usuari, accio, entitat, element_id, detall) VALUES(?,?,?,?,?,?)', [
        $u['id'] ?? null, $u['usuari'] ?? null, $accio, $entitat, $id, $detall,
    ]);
}

/* ---------------- Utilitats ---------------- */

function data_llegible(?string $d): string
{
    if (!$d) {
        return '';
    }
    $t = strtotime($d);
    return $t ? date(strlen($d) > 10 ? 'd/m/Y H:i' : 'd/m/Y', $t) : $d;
}

function nom_persona(?array $p): string
{
    if (!$p) {
        return '';
    }
    return trim(($p['cognoms'] ? $p['cognoms'] . ', ' : '') . $p['nom']);
}

function curs_actual(): ?array
{
    return fila('SELECT * FROM cursos WHERE actual = 1 LIMIT 1')
        ?? fila("SELECT * FROM cursos WHERE estat = 'actiu' ORDER BY data_inici DESC LIMIT 1");
}

require SRC_DIR . '/disseny.php';
