# Arquitectura de Compàs

Compàs és un programa de gestió per a una única societat musical. No té web pública: només la part interna de gestió.

## Peces

```
Compas.exe (llançador, C#)
   │  arranca
   ▼
php.exe -S 127.0.0.1:8740   (servidor integrat de PHP, portable)
   │
   ▼
app/index.php  ──►  src/  (lògica en PHP)
                     │
                     ▼
               data/compas.db  (SQLite, un sol fitxer)
```

- **Llançador** (`launcher/Compas.cs`): finestra menuda que arranca PHP, espera que responga, obri el navegador i atura PHP en tancar. Llig `compas.ini`.
- **PHP portable**: es baixa de windows.php.net en construir la versió. Extensions: `pdo_sqlite`, `sqlite3`, `mbstring`, `fileinfo`, `opcache`.
- **Aplicació**: PHP sense dependències ni frameworks, HTML i CSS propis i un poc de JavaScript. Funciona sense Internet.
- **Base de dades**: SQLite a través de PDO. Tot l'SQL usa consultes preparades. Es pot migrar a MariaDB/MySQL més endavant canviant la connexió i adaptant `schema.sql`.

## Codi

| Fitxer | Funció |
|---|---|
| `app/index.php` | Punt d'entrada únic i encaminador (`?r=…`). |
| `src/bootstrap.php` | Connexió, sessió, CSRF, usuari actual, permisos, auditoria. |
| `src/schema.sql` | Esquema de la base de dades (es crea sol la primera vegada). |
| `src/capacitats.php` | Capacitats i rols per defecte. |
| `src/entitats.php` | Definició declarativa de cada entitat: camps, relacions i validacions. |
| `src/crud.php` | Llistats, fitxes, formularis i exportació CSV genèrics a partir de `entitats.php`. |
| `src/pages/*.php` | Pantalles específiques: inici, «La meua fitxa», horari, assistència, qualificacions, usuaris, rols, configuració, auditoria, còpies, ajuda. |
| `src/lang/ca.php`, `es.php` | Textos en valencià i castellà. |
| `app/assets/` | Estil, JavaScript, logotip i icona. |

Per a afegir una entitat senzilla: crea la taula a `schema.sql`, defineix-la a `entitats.php`, afig-la al menú (`src/disseny.php`) i posa els textos a `lang/`.

## Model de dades

- **Persona** és l'entitat central. Cada persona existeix una sola vegada.
- Les funcions són taules separades vinculades a la persona: `socis`, `alumnes`, `professors`, `membres` (d'agrupacions), `tutories` (alumne ↔ tutor legal, molts a molts), `cessions`.
- Escola: `cursos` → `periodes`, `grups` (assignatura + curs + professor + aula) → `horaris`; `matricules` (alumne + curs) → `matricula_grups`; `assistencia` (grup + alumne + data); `qualificacions` (grup + alumne + període).
- Inventari: `instruments` → `cessions`, `manteniments`.
- Accés: `usuaris` (opcionalment vinculat a una persona), `rols`, `rol_capacitats`, `usuari_rols`. Es comproven **capacitats**, no rols.
- `auditoria` registra les operacions sensibles.
- Les claus foranes no deixen esborrar res amb historial: les baixes es marquen amb estat o data de baixa.

## Seguretat

- Per defecte només escolta a `127.0.0.1`. L'accés des de la xarxa s'activa explícitament.
- Contrasenyes amb `password_hash`. Bloqueig temporal després de 5 intents fallits.
- Token CSRF en tots els formularis. Cookies `HttpOnly` i `SameSite=Strict`.
- Tota l'eixida s'escapa amb `htmlspecialchars`.
- La carpeta de dades queda fora de la carpeta pública (`app/`); el servidor només serveix `app/assets/`.
- El professorat només veu i modifica l'assistència i les notes dels seus grups (si no té la capacitat `escola.totsgrups`).
- Còpia automàtica diària (14 últimes) i còpia/restauració manual.

## Pendent (pròximes versions)

- Gestió econòmica: quotes, rebuts, remeses, ingressos i despeses.
- Loteria: talonaris, participacions i liquidacions.
- Comunicacions i avisos.
- Calendari d'assajos, concerts i activitats; convocatòries i repertori de les agrupacions.
- Documents adjunts.
