# Arquitectura de Compàs

Compàs gestiona una associació en Windows o Linux. Per defecte funciona en un ordinador; opcionalment un servidor compartix l’aplicació per HTTPS en una xarxa privada, amb un nom `.local` descobert automàticament i sense IP fixa. Hi ha comptes individuals amb tres rols: administració, gestió i consulta limitada. El nucli és PHP i SQLite; l’auxiliar Go només fa HTTPS i descobriment mDNS.

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

- **Llançador** (`launcher/Compas.cs`): arranca PHP només a `127.0.0.1`, espera que responga i obri la finestra de Compàs (`launcher/Visor.cs`, un formulari amb el control WebView2 i les dades a `%LOCALAPPDATA%\Compas\visor`). Les pàgines que s'obrin a part (impressions) són altres finestres del visor, i les adreces de fora van al navegador habitual. Quan es tanca la finestra principal, Compàs s'atura. Si WebView2 no hi és, obri Microsoft Edge o Google Chrome amb `--app` i un perfil propi a `%LOCALAPPDATA%\Compas\finestra` (amb una icona a la safata); si no hi ha Edge ni Chrome, o amb `finestra = 0`, obri el navegador habitual i mostra la finestra menuda de sempre. En tancar, demana a PHP una còpia de seguretat (amb un testimoni secret que només coneixen el llançador i PHP) i l'atura. Llig `compas.ini`.
- **Linux** (`launcher/linux/`): `compas.sh` fa el mateix que el llançador de Windows amb el PHP del sistema (comprova la versió i les extensions, arranca a `127.0.0.1`, obri la finestra de Compàs i, amb `trap` en eixir, demana la còpia de tancament). La finestra és `compas-visor.py` (Python amb GTK 3 i WebKitGTK, les dades a `~/.local/share/compas/visor`); quan es tanca, `compas.sh` acaba. Si no hi és, prova Chromium, Chrome, Edge o Brave amb `--app` i, si tampoc, `xdg-open` amb un terminal. `installa.sh` el copia a `~/.local/share/compas/programa` i crea l'entrada del menú (`Terminal=false`) i l'ordre `compas`. Les dades van a `~/.local/share/compas/dades`. El paquet `.deb` (`eines/construeix-deb.sh`, amb `launcher/linux/deb/`) posa el programa a `/usr/share/compas`, l'ordre a `/usr/bin/compas` i l'entrada del menú, depén de `php-cli`, `php-sqlite3` i `php-mbstring` i recomana `php-xml` i el necessari per a la finestra (`python3-gi`, `gir1.2-gtk-3.0`, `gir1.2-webkit2-4.1`); la configuració de cada usuari va a `~/.config/compas/compas.ini`.
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
| `src/tipus.php` | Tipus d'entitat (`TIPUS_ENTITAT`): els mòduls que activa cada tipus, el perfil de vocabulari, el mes en què comença l'exercici, si l'escola posa notes, les categories d'edat i els càrrecs de festa per a començar. `aplica_tipus()` ho configura en la instal·lació i en canviar de tipus; `any_exercici()` i `nom_exercici()` calculen l'exercici («2026» o «2026-2027»). |
| `src/lang/vocabulari.php` | Vocabulari de cada tipus: els textos base són els d'una societat musical i `vocabulari()` en torna els que canvien (perfils d'escola, grups, socis, material i roba sobre plantilles amb ranures `{…}`). `t()` els aplica per sobre dels de l'idioma. |
| `src/entitats.php` | Definició declarativa de cada entitat: camps, relacions i validacions. |
| `src/crud.php` | Llistats, fitxes, formularis i exportació CSV genèrics a partir de `entitats.php`. |
| `src/pages/*.php` | Pantalles específiques: inici, horari, qualificacions, butlletins i logotip, documents (consentiments i certificats), llistats i informes, importació, passar de curs, economia (generació de rebuts, remeses, rebuts i mandats per a imprimir, configuració), loteria, caixa (llibre de caixa), societat (junta, actes, documents, full d'alta, comunicats), rgpd, uniformes, repertori, subvencions (fitxa amb el resum i els requisits, relació de despeses, còpia per a l'any següent, avisos), associacions (activitats i inscripcions, categories d'edat, llicències, entitats federades, càrrecs de festa i els seus informes), dades d'exemple de cada tipus (`exemple_associacions.php`), usuaris, configuració, auditoria, còpies, recuperació, ajuda. |
| `src/fulls.php` | Lectura de CSV i Excel (.xlsx) per a la importació, sense l'extensió zip: llig el directori central del zip i descomprimeix amb `gzinflate`. |
| `src/rotacio.php` | Quines còpies automàtiques es conserven: totes les dels últims 7 dies, una per setmana de les últimes 5 i una per mes fins al límit configurat (`copia_mesos`, 12 per defecte). |
| `src/xifrat.php` | Xifrat de les còpies externes amb libsodium. |
| `src/sepa.php` | IBAN (mòdul 97), identificador de creditor SEPA, text amb el joc de caràcters SEPA i XML de la remesa (pain.008.001.02 o .08, CORE; un bloc FRST per als mandats que no s'han usat mai i un RCUR per a la resta). |
| `app/assets/document.css` | Documents A4: consentiments, certificats, llistats i etiquetes (sobre `butlleti.css`). |
| `src/lang/ca.php`, `es.php` | Textos en valencià i castellà. |
| `app/assets/` | Estil, JavaScript, logotip i icona. |
| `src/markdown.php` | Conversor de Markdown a HTML (manual dins del programa i PDF). |
| `eines/document-html.php`, `eines/pdf.sh` | Preparen cada manual com a pàgina A4 amb portada i l'imprimeixen en PDF amb Chrome (`docs/pdf/`). Els PDF no es guarden al repositori: els fa GitHub Actions en publicar una versió (s'adjunten a la versió) i en publicar la web. |
| `eines/captures.py` | Genera les captures dels manuals (`docs/imatges/ca` i `es`) fent cada procés amb dades d'exemple. Requereix Python, Pillow i Playwright: `python3 eines/captures.py`. |
| `eines/societat-gran.php` | Omple una instal·lació nova amb una societat gran inventada (341 socis, 278 alumnes, 5 agrupacions, 653 obres, dos cursos amb notes i horaris, uns 5.000 rebuts…), en l'idioma de la instal·lació i sempre igual. Serveix per a provar el programa amb moltes dades i per a les captures de la web. |
| `eines/web.php`, `eines/web.css` | Generen la web de presentació (valencià i castellà) amb captures i PDF. El workflow `web.yml` la publica a GitHub Pages. |

Per a afegir una entitat senzilla: crea la taula a `schema.sql`, defineix-la a `entitats.php`, afig-la al menú (`src/disseny.php`) i posa els textos a `lang/`. Una entitat pot afegir botons a la seua fitxa amb `accions_fitxa` (així es fan els botons de certificat, full de consentiment i passar de curs).

Si una versió nova afig columnes a una taula que ja existeix, cal posar-les a `schema.sql` (instal·lacions noves) i a `migra_esquema()` de `src/bootstrap.php` (bases de dades d'abans), que les afig amb `ALTER TABLE` si falten.

L'esquema i les migracions no es repassen en cada clic: `PRAGMA user_version` guarda un número calculat amb la versió de Compàs i el contingut de `schema.sql`. Quan no coincideix (una versió nova o una còpia restaurada d'una versió anterior), `actualitza_esquema()` fa primer una còpia «abans d'actualitzar» i després tot el procés en una sola transacció: si alguna cosa falla, la base de dades queda com estava.

Els camps de relació (`ref`) amb més de `MAX_DESPLEGABLE` opcions (400) no fan un desplegable: es busca escrivint i `?r=cerca_ref` torna les 30 primeres que coincideixen, sense tindre en compte els accents.

## Model de dades

- **Persona** és l'entitat central. Cada persona existeix una sola vegada.
- Les funcions són taules separades vinculades a la persona: `socis`, `alumnes`, `professors`, `membres` (d'agrupacions), `tutories` (alumne ↔ tutor legal, molts a molts), `cessions`.
- Escola: `cursos` → `periodes`, `grups` (assignatura + curs + professor + aula) → `horaris`; `matricules` (alumne + curs) → `matricula_grups`; `qualificacions` (grup + alumne + període). Compàs no registra l'assistència: és un programa de gestió.
- Inventari: `instruments` → `cessions`, `manteniments`.
- Drets d'imatge: `persones.consentiment_imatge` (`pendent`, `autoritza`, `no_autoritza`) i `consentiment_data`.
- Accés: `usuaris`, `rols` (dos fixos: `admin` i `administratiu`, que a la pantalla es diu «usuari/ària»), `rol_capacitats`, `usuari_rols`. Es comproven **capacitats**, no rols.
- Economia: `tarifes`, `mandats`, `remeses`, `rebuts` → `rebut_linies`, `loteries` → `talonaris`, `moviments` (llibre de caixa: només els apunts manuals, amb el proveïdor i el NIF de les factures; rebuts, remeses, loteria i cobraments de subvencions s'hi sumen en llegir-lo).
- Subvencions: `organismes` i `requisits_justificacio` (llistes que edita cada entitat; `organisme_requisits`: què demana normalment cada organisme), `subvencions` → `subvencio_requisits` (com es justifica i si està fet), `subvencio_cobraments` (entren sols a la caixa) i `subvencio_despeses` (moviments de caixa imputats; una despesa no es pot imputar per més del que val). `documents.subvencio_id` lliga els documents a la subvenció.
- Societat: `carrecs` (persona + càrrec + dates; vigent si no té `data_fi`), `reunions` (convocatòria, acta i acords en text, un per línia), `comunicats`.
- Repertori: `tipus_obres` (definits per cada entitat; `sembra_tipus_obres()` en crea uns quants la primera vegada) → `obres` (número d'arxiu únic quan n'hi ha) ↔ `obra_agrupacions` (molts a molts amb `agrupacions`). Al formulari genèric, les agrupacions són un camp virtual de tipus `refs` (caselles). La pantalla `?r=repertori` (`src/pages/repertori.php`) fa la cerca, els filtres, l'ordre natural, la impressió i el CSV; la importació (`analitza_obres`, `executa_importacio_obres`) va pel mateix assistent que la de persones.
- 2.0: `categories` (nom i edats; la categoria d'una persona es calcula amb l'edat que fa l'any en què comença l'exercici), `activitats` → `activitat_inscripcions` (places, import i `rebut_id`; els rebuts tenen la clau `activitat:{activitat}:{inscripció}`), `llicencies` (persona, equip, temporada, caducitat i revisió mèdica), `entitats_federades` (representant `persona_id` i quota; rebuts amb la clau `entitat:{id}:{any}`) i `carrecs_festa` (persona, càrrec i exercici en text). Les instal·lacions anteriors es queden com a societat musical, amb els mòduls nous que no li calen desactivats (`migra_esquema`).
- Tipus de soci: `tipus_socis`, definits per cada entitat. `socis.tipus` i `tarifes.tipus_soci` guarden l'id (en text). Fins a la 1.10 eren quatre codis fixos (`ordinari`, `protector`, `juvenil`, `honorari`): `sembra_tipus_socis()` crea els quatre tipus la primera vegada (en la configuració inicial, o en obrir una base de dades antiga) i hi passa els codis; `tipus_socis.codi` en guarda l'origen.
- Uniformes: `uniformes` (talles en text, separades per comes) → `uniforme_compres` i `uniforme_lliuraments` (venda, préstec o gratuït). Les existències es calculen: comprades − lliurades + préstecs tornats.
- Mòduls: `MODULS` (a `entitats.php`) diu quines pantalles i entitats té cada mòdul; `config.moduls_desactivats` i `config.avisos_desactivats` guarden el que ha desactivat l'entitat; el tipus d'entitat en desactiva uns per a començar. `disponible()` amaga el menú, les fitxes filles i els informes, i `app/index.php` no obri les pantalles d'un mòdul desactivat.
- Documents: `documents` (metadades i caducitat) i `document_fitxers` (contingut en BLOB, `ON DELETE CASCADE`). Guardar-los a la base de dades fa que `VACUUM INTO` els incloga en cada còpia, xifrada o no, sense una carpeta a banda. Es serveixen amb `?r=document_fitxer` (en línia per a PDF, imatges i text; descarregats la resta).
- `auditoria` registra les operacions sensibles.
- Les claus foranes no deixen esborrar res amb historial: les baixes es marquen amb estat o data de baixa.

## Seguretat

- Només escolta a `127.0.0.1`: no és accessible des de la xarxa. A més, només respon a les peticions adreçades a `127.0.0.1`, `localhost` o `[::1]` (contra el *DNS rebinding*: una web maliciosa que apunte el seu nom cap a este ordinador rep un 421). Altres noms, si cal: variable `COMPAS_HOSTS`.
- Contrasenyes amb `password_hash`. Bloqueig temporal de 30 segons després de 5 intents fallits, comptats a la base de dades per a tot el programa (no per sessió, que es pot evitar sense galeta).
- Recuperació de l'accés (`src/pages/recuperacio.php`): codi de 16 caràcters (80 bits, alfabet sense 0/O/1/I) guardat només com a `password_hash` a `config`; d'un sol ús (en usar-lo se'n genera un altre) i no torna arrere en restaurar una còpia. Alternativa sense codi: un fitxer `recupera-acces` a la carpeta de dades, vàlid 60 minuts i esborrat en usar-lo (qui pot escriure-hi ja té accés a `compas.db`). Només per a comptes d'administrador; límit d'intents guardat a la base de dades, no a la sessió.
- Token CSRF en tots els formularis. Cookies `HttpOnly` i `SameSite=Strict`.
- Tota l'eixida s'escapa amb `htmlspecialchars`. Política de seguretat de continguts (CSP): el programa només carrega i envia coses a si mateix, de manera que un codi colat no podria enviar dades fora. Els documents pujats (excepte els PDF) s'obrin aïllats (`sandbox`). Les redireccions només van a adreces del programa.
- La carpeta de dades queda fora de la carpeta pública (`app/`); el servidor només serveix `app/assets/`.
- L'usuari/ària no pot gestionar usuaris, configuració, registre d'activitat ni restaurar còpies.
- Còpies amb `VACUUM INTO` (consistents encara que el programa estiga obert): diària, en tancar si hi ha canvis, manual, abans de restaurar i abans d'importar; duplicades a una carpeta externa opcional.
- Còpies externes xifrades (opcional): clau derivada de la contrasenya amb Argon2id (`crypto_pwhash`) i xifrat en blocs d'1 MB amb XChaCha20-Poly1305 (`crypto_secretstream`), que detecta qualsevol modificació o tall del fitxer. A l'ordinador només es guarda la clau derivada, per a poder fer les còpies automàtiques; la contrasenya fa falta per a restaurar.
- Importació: els .xlsx es llegeixen sense DTD (`LIBXML_NONET`, sense entitats externes); les dades entre passos es guarden en un fitxer temporal de la carpeta de dades amb un nom aleatori lligat a la sessió, i s'esborren en acabar o al cap d'un dia.

## Pendent (pròximes versions)

- Calendari d'assajos, entrenaments, partits i actes (amb els espais municipals); assistència.
- Repertori de cada concert (SGAE).

## Notes

- Economia (`src/pages/economia.php`): els rebuts generats porten una `clau` única (soci i període, o pagador, curs i període) perquè generar dues vegades el mateix període no duplique res. L'import d'un rebut amb línies és la suma de les línies (`recalcula_rebut`). Numeració correlativa per any (`2026/0001`). Una remesa preparada fixa el mandat de cada rebut; marcar-la com a cobrada posa els rebuts pagats i l'`ultim_us` dels mandats (el següent cobrament ja és RCUR). Compàs fa rebuts, no factures, per a no entrar en el reglament dels sistemes de facturació (Verifactu).

## Mode LAN segur (2.3)

```text
Navegador → https://compas.local:443 → compas-xarxa (Go, TLS + mDNS)
                                      → 127.0.0.1:8740 (PHP) → SQLite
```

Els llançadors deixen PHP en loopback i passen un secret aleatori als dos processos. El proxy valida la IP privada, Host i SNI, esborra les capçaleres aportades pel navegador i remet la identitat real amb el secret intern. `src/xarxa.php` comprova eixe secret abans de servir qualsevol ruta o recurs i conserva la distinció entre servidor i client remot. Només després marca HTTPS, perquè les galetes siguen Secure. El secret de còpia en tancar és diferent i no es permet introduir-lo pel proxy.

Cada instal·lació té una CA pròpia i claus protegides; els clients accepten explícitament l’empremta una vegada. Els ZIP de clients contenen només material públic. El certificat del servidor es renova amb la mateixa CA. mDNS només usa interfícies privades IPv4, detecta conflictes inicials i torna a obrir la connexió si canvien les interfícies o les IP. No hi ha cap servei de descobriment extern ni configuració del router. El servidor no és per a Internet o càrrega elevada; PHP continua atenent les peticions en sèrie a Windows.
