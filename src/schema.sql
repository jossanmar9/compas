-- Compàs: Gestió lliure per a societats musicals
-- Esquema de la base de dades (SQLite)
-- SPDX-License-Identifier: GPL-2.0-or-later

PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS config (
    clau  TEXT PRIMARY KEY,
    valor TEXT
);

-- Persones: registre central, cada persona una sola vegada
CREATE TABLE IF NOT EXISTS persones (
    id              INTEGER PRIMARY KEY,
    nom             TEXT NOT NULL,
    cognoms         TEXT,
    data_naixement  TEXT,
    document        TEXT,
    telefon         TEXT,
    email           TEXT,
    adreca          TEXT,
    codi_postal     TEXT,
    poblacio        TEXT,
    provincia       TEXT,
    observacions    TEXT,
    actiu           INTEGER NOT NULL DEFAULT 1,
    creat           TEXT DEFAULT (datetime('now','localtime')),
    modificat       TEXT
);

CREATE TABLE IF NOT EXISTS socis (
    id           INTEGER PRIMARY KEY,
    persona_id   INTEGER NOT NULL UNIQUE REFERENCES persones(id) ON DELETE RESTRICT,
    numero       TEXT,
    tipus        TEXT,
    data_alta    TEXT,
    data_baixa   TEXT,
    estat        TEXT NOT NULL DEFAULT 'actiu',
    observacions TEXT
);

CREATE TABLE IF NOT EXISTS alumnes (
    id           INTEGER PRIMARY KEY,
    persona_id   INTEGER NOT NULL UNIQUE REFERENCES persones(id) ON DELETE RESTRICT,
    data_alta    TEXT,
    data_baixa   TEXT,
    estat        TEXT NOT NULL DEFAULT 'actiu',
    observacions TEXT
);

CREATE TABLE IF NOT EXISTS professors (
    id           INTEGER PRIMARY KEY,
    persona_id   INTEGER NOT NULL UNIQUE REFERENCES persones(id) ON DELETE RESTRICT,
    data_alta    TEXT,
    data_baixa   TEXT,
    estat        TEXT NOT NULL DEFAULT 'actiu',
    observacions TEXT
);

-- Relació alumne - tutor legal (molts a molts)
CREATE TABLE IF NOT EXISTS tutories (
    id                  INTEGER PRIMARY KEY,
    alumne_id           INTEGER NOT NULL REFERENCES alumnes(id) ON DELETE CASCADE,
    tutor_id            INTEGER NOT NULL REFERENCES persones(id) ON DELETE RESTRICT,
    tipus_relacio       TEXT,
    principal           INTEGER NOT NULL DEFAULT 0,
    rep_comunicacions   INTEGER NOT NULL DEFAULT 1,
    observacions        TEXT,
    UNIQUE (alumne_id, tutor_id)
);

CREATE TABLE IF NOT EXISTS agrupacions (
    id           INTEGER PRIMARY KEY,
    nom          TEXT NOT NULL,
    tipus        TEXT,
    descripcio   TEXT,
    estat        TEXT NOT NULL DEFAULT 'actiu',
    data_creacio TEXT
);

-- Pertinença i direcció d'agrupacions (la direcció es marca amb la funció)
CREATE TABLE IF NOT EXISTS membres (
    id             INTEGER PRIMARY KEY,
    persona_id     INTEGER NOT NULL REFERENCES persones(id) ON DELETE RESTRICT,
    agrupacio_id   INTEGER NOT NULL REFERENCES agrupacions(id) ON DELETE CASCADE,
    instrument_veu TEXT,
    funcio         TEXT NOT NULL DEFAULT 'membre',
    data_alta      TEXT,
    data_baixa     TEXT,
    estat          TEXT NOT NULL DEFAULT 'actiu',
    observacions   TEXT
);

CREATE TABLE IF NOT EXISTS cursos (
    id         INTEGER PRIMARY KEY,
    nom        TEXT NOT NULL UNIQUE,
    data_inici TEXT,
    data_fi    TEXT,
    estat      TEXT NOT NULL DEFAULT 'actiu',
    actual     INTEGER NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS periodes (
    id      INTEGER PRIMARY KEY,
    curs_id INTEGER NOT NULL REFERENCES cursos(id) ON DELETE CASCADE,
    nom     TEXT NOT NULL,
    ordre   INTEGER NOT NULL DEFAULT 1
);

CREATE TABLE IF NOT EXISTS assignatures (
    id         INTEGER PRIMARY KEY,
    nom        TEXT NOT NULL,
    descripcio TEXT,
    tipus      TEXT,
    estat      TEXT NOT NULL DEFAULT 'actiu'
);

CREATE TABLE IF NOT EXISTS aules (
    id           INTEGER PRIMARY KEY,
    nom          TEXT NOT NULL,
    capacitat    INTEGER,
    observacions TEXT
);

CREATE TABLE IF NOT EXISTS grups (
    id             INTEGER PRIMARY KEY,
    curs_id        INTEGER NOT NULL REFERENCES cursos(id) ON DELETE RESTRICT,
    assignatura_id INTEGER NOT NULL REFERENCES assignatures(id) ON DELETE RESTRICT,
    nom            TEXT NOT NULL,
    professor_id   INTEGER REFERENCES professors(id) ON DELETE SET NULL,
    aula_id        INTEGER REFERENCES aules(id) ON DELETE SET NULL,
    estat          TEXT NOT NULL DEFAULT 'actiu'
);

CREATE TABLE IF NOT EXISTS horaris (
    id          INTEGER PRIMARY KEY,
    grup_id     INTEGER NOT NULL REFERENCES grups(id) ON DELETE CASCADE,
    dia         INTEGER NOT NULL,
    hora_inici  TEXT NOT NULL,
    hora_fi     TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS matricules (
    id           INTEGER PRIMARY KEY,
    alumne_id    INTEGER NOT NULL REFERENCES alumnes(id) ON DELETE RESTRICT,
    curs_id      INTEGER NOT NULL REFERENCES cursos(id) ON DELETE RESTRICT,
    data         TEXT,
    estat        TEXT NOT NULL DEFAULT 'actiu',
    observacions TEXT,
    UNIQUE (alumne_id, curs_id)
);

CREATE TABLE IF NOT EXISTS matricula_grups (
    id           INTEGER PRIMARY KEY,
    matricula_id INTEGER NOT NULL REFERENCES matricules(id) ON DELETE CASCADE,
    grup_id      INTEGER NOT NULL REFERENCES grups(id) ON DELETE RESTRICT,
    UNIQUE (matricula_id, grup_id)
);

CREATE TABLE IF NOT EXISTS qualificacions (
    id           INTEGER PRIMARY KEY,
    grup_id      INTEGER NOT NULL REFERENCES grups(id) ON DELETE CASCADE,
    alumne_id    INTEGER NOT NULL REFERENCES alumnes(id) ON DELETE CASCADE,
    periode_id   INTEGER NOT NULL REFERENCES periodes(id) ON DELETE CASCADE,
    professor_id INTEGER REFERENCES professors(id) ON DELETE SET NULL,
    nota         TEXT,
    observacions TEXT,
    modificat    TEXT,
    UNIQUE (grup_id, alumne_id, periode_id)
);

CREATE TABLE IF NOT EXISTS instruments (
    id               INTEGER PRIMARY KEY,
    inventari        TEXT NOT NULL UNIQUE,
    tipus            TEXT NOT NULL,
    marca            TEXT,
    model            TEXT,
    num_serie        TEXT,
    estat            TEXT NOT NULL DEFAULT 'bo',
    ubicacio         TEXT,
    data_adquisicio  TEXT,
    observacions     TEXT
);

CREATE TABLE IF NOT EXISTS manteniments (
    id            INTEGER PRIMARY KEY,
    instrument_id INTEGER NOT NULL REFERENCES instruments(id) ON DELETE CASCADE,
    data          TEXT NOT NULL,
    descripcio    TEXT NOT NULL,
    cost          REAL,
    observacions  TEXT
);

CREATE TABLE IF NOT EXISTS cessions (
    id             INTEGER PRIMARY KEY,
    instrument_id  INTEGER NOT NULL REFERENCES instruments(id) ON DELETE RESTRICT,
    persona_id     INTEGER NOT NULL REFERENCES persones(id) ON DELETE RESTRICT,
    data_entrega   TEXT NOT NULL,
    data_prevista  TEXT,
    data_retorn    TEXT,
    estat_entrega  TEXT,
    estat_retorn   TEXT,
    fianca         REAL,
    observacions   TEXT
);

-- Accés: usuaris i dos rols fixos (administrador i administratiu)
CREATE TABLE IF NOT EXISTS usuaris (
    id          INTEGER PRIMARY KEY,
    usuari      TEXT NOT NULL UNIQUE COLLATE NOCASE,
    contrasenya TEXT NOT NULL,
    actiu       INTEGER NOT NULL DEFAULT 1,
    idioma      TEXT,
    ultim_acces TEXT,
    creat       TEXT DEFAULT (datetime('now','localtime'))
);

CREATE TABLE IF NOT EXISTS rols (
    id   INTEGER PRIMARY KEY,
    clau TEXT NOT NULL UNIQUE,
    nom  TEXT
);

CREATE TABLE IF NOT EXISTS rol_capacitats (
    rol_id    INTEGER NOT NULL REFERENCES rols(id) ON DELETE CASCADE,
    capacitat TEXT NOT NULL,
    PRIMARY KEY (rol_id, capacitat)
);

CREATE TABLE IF NOT EXISTS usuari_rols (
    usuari_id INTEGER NOT NULL REFERENCES usuaris(id) ON DELETE CASCADE,
    rol_id    INTEGER NOT NULL REFERENCES rols(id) ON DELETE CASCADE,
    PRIMARY KEY (usuari_id, rol_id)
);

CREATE TABLE IF NOT EXISTS auditoria (
    id          INTEGER PRIMARY KEY,
    data        TEXT NOT NULL DEFAULT (datetime('now','localtime')),
    usuari_id   INTEGER,
    usuari      TEXT,
    accio       TEXT NOT NULL,
    entitat     TEXT,
    element_id  INTEGER,
    detall      TEXT
);

CREATE INDEX IF NOT EXISTS idx_persones_cognoms ON persones(cognoms, nom);
CREATE INDEX IF NOT EXISTS idx_membres_agrup ON membres(agrupacio_id);
CREATE INDEX IF NOT EXISTS idx_grups_curs ON grups(curs_id);
CREATE INDEX IF NOT EXISTS idx_mg_grup ON matricula_grups(grup_id);
CREATE INDEX IF NOT EXISTS idx_cessions_instr ON cessions(instrument_id);
CREATE INDEX IF NOT EXISTS idx_auditoria_data ON auditoria(data);
