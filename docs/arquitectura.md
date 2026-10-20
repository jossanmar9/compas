# Arquitectura de Compàs

Compàs és un programa de gestió per a una única societat musical, en un sol ordinador amb Windows i amb dos comptes (administrador/a i administratiu/va). No té web pública ni accés per xarxa.

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

- **Llançador** (`launcher/Compas.cs`): finestra menuda que arranca PHP només a `127.0.0.1`, espera que responga i obri el navegador. En tancar, demana a PHP una còpia de seguretat (amb un testimoni secret que només coneixen el llançador i PHP) i l'atura. Llig `compas.ini`.
- **PHP portable**: es baixa de windows.php.net en construir la versió. Extensions: `pdo_sqlite`, `sqlite3`, `mbstring`, `fileinfo`, `opcache` i `sodium` (xifrat de les còpies; `libsodium.dll` va al costat de `php.exe`). Les integrades `zlib` i `SimpleXML` s'utilitzen per a llegir els .xlsx.
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
| `src/pages/*.php` | Pantalles específiques: inici, horari, qualificacions, butlletins i logotip, documents (consentiments i certificats), llistats i informes, importació, passar de curs, usuaris, configuració, auditoria, còpies, recuperació, ajuda. |
| `src/fulls.php` | Lectura de CSV i Excel (.xlsx) per a la importació, sense l'extensió zip: llig el directori central del zip i descomprimeix amb `gzinflate`. |
| `src/xifrat.php` | Xifrat de les còpies externes amb libsodium. |
| `app/assets/document.css` | Documents A4: consentiments, certificats, llistats i etiquetes (sobre `butlleti.css`). |
| `src/lang/ca.php`, `es.php` | Textos en valencià i castellà. |
| `app/assets/` | Estil, JavaScript, logotip i icona. |
| `src/markdown.php` | Conversor de Markdown a HTML (manual dins del programa i PDF). |
| `eines/document-html.php` | Prepara cada manual com a pàgina A4 amb portada; GitHub Actions la imprimeix en PDF amb Chrome o Edge (`docs/pdf/`). |
| `eines/captures.py` | Genera les captures dels manuals (`docs/imatges/ca` i `es`) fent cada procés amb dades d'exemple. Requereix Python, Pillow i Playwright: `python3 eines/captures.py`. |
| `eines/web.php`, `eines/web.css` | Generen la web de presentació (valencià i castellà) amb captures i PDF. El workflow `web.yml` la publica a GitHub Pages. |

Per a afegir una entitat senzilla: crea la taula a `schema.sql`, defineix-la a `entitats.php`, afig-la al menú (`src/disseny.php`) i posa els textos a `lang/`. Una entitat pot afegir botons a la seua fitxa amb `accions_fitxa` (així es fan els botons de certificat, full de consentiment i passar de curs).

Si una versió nova afig columnes a una taula que ja existeix, cal posar-les a `schema.sql` (instal·lacions noves) i a `migra_esquema()` de `src/bootstrap.php` (bases de dades d'abans), que les afig amb `ALTER TABLE` si falten.

## Model de dades

- **Persona** és l'entitat central. Cada persona existeix una sola vegada.
- Les funcions són taules separades vinculades a la persona: `socis`, `alumnes`, `professors`, `membres` (d'agrupacions), `tutories` (alumne ↔ tutor legal, molts a molts), `cessions`.
- Escola: `cursos` → `periodes`, `grups` (assignatura + curs + professor + aula) → `horaris`; `matricules` (alumne + curs) → `matricula_grups`; `qualificacions` (grup + alumne + període). Compàs no registra l'assistència: és un programa de gestió.
- Inventari: `instruments` → `cessions`, `manteniments`.
- Drets d'imatge: `persones.consentiment_imatge` (`pendent`, `autoritza`, `no_autoritza`) i `consentiment_data`.
- Accés: `usuaris`, `rols` (dos fixos: `admin` i `administratiu`), `rol_capacitats`, `usuari_rols`. Es comproven **capacitats**, no rols.
- `auditoria` registra les operacions sensibles.
- Les claus foranes no deixen esborrar res amb historial: les baixes es marquen amb estat o data de baixa.

## Seguretat

- Només escolta a `127.0.0.1`: no és accessible des de la xarxa.
- Contrasenyes amb `password_hash`. Bloqueig temporal després de 5 intents fallits.
- Recuperació de l'accés (`src/pages/recuperacio.php`): codi de 16 caràcters (80 bits, alfabet sense 0/O/1/I) guardat només com a `password_hash` a `config`; d'un sol ús (en usar-lo se'n genera un altre) i no torna arrere en restaurar una còpia. Alternativa sense codi: un fitxer `recupera-acces` a la carpeta de dades, vàlid 60 minuts i esborrat en usar-lo (qui pot escriure-hi ja té accés a `compas.db`). Només per a comptes d'administrador; límit d'intents guardat a la base de dades, no a la sessió.
- Token CSRF en tots els formularis. Cookies `HttpOnly` i `SameSite=Strict`.
- Tota l'eixida s'escapa amb `htmlspecialchars`.
- La carpeta de dades queda fora de la carpeta pública (`app/`); el servidor només serveix `app/assets/`.
- L'administratiu/va no pot gestionar usuaris, configuració, registre d'activitat ni restaurar còpies.
- Còpies amb `VACUUM INTO` (consistents encara que el programa estiga obert): diària, en tancar si hi ha canvis, manual, abans de restaurar i abans d'importar; duplicades a una carpeta externa opcional.
- Còpies externes xifrades (opcional): clau derivada de la contrasenya amb Argon2id (`crypto_pwhash`) i xifrat en blocs d'1 MB amb XChaCha20-Poly1305 (`crypto_secretstream`), que detecta qualsevol modificació o tall del fitxer. A l'ordinador només es guarda la clau derivada, per a poder fer les còpies automàtiques; la contrasenya fa falta per a restaurar.
- Importació: els .xlsx es llegeixen sense DTD (`LIBXML_NONET`, sense entitats externes); les dades entre passos es guarden en un fitxer temporal de la carpeta de dades amb un nom aleatori lligat a la sessió, i s'esborren en acabar o al cap d'un dia.

## Pendent (pròximes versions)

- Gestió econòmica: quotes, rebuts, remeses, ingressos i despeses.
- Loteria: talonaris, participacions i liquidacions.
- Comunicacions i avisos.
- Calendari d'assajos, concerts i activitats; convocatòries i repertori de les agrupacions.
- Documents adjunts.
