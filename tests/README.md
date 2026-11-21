# Proves de Compàs

Proves automàtiques sense PHPUnit ni Composer. No toquen `data/compas.db`.

## Proves de funcionament

```bash
php tests/run.php
```

Comproven:

- la normalització, el format i la validació de l'IBAN, i la conversió del compte espanyol antic (CCC) a IBAN;
- la validació del BIC, els identificadors SEPA i el text amb el joc de caràcters SEPA;
- el xifrat i el desxifrat de les còpies de seguretat, el rebuig d'una contrasenya incorrecta i la detecció d'una còpia manipulada;
- la rotació de les còpies automàtiques (quines es guarden i quines s'esborren);
- els noms dels comptes (només ASCII, únics també davant SQLite);
- que tots els textos fan servir un sol tipus d'apòstrof (`'`).

Algunes necessiten les extensions `mbstring` i `sodium` de PHP.

GitHub Actions (`.github/workflows/tests.yml`) les executa en cada `push` a `main`, en cada *pull request* i a mà, juntament amb les proves d'integració. També revisa la sintaxi de tots els fitxers PHP i comprova que el servidor local arranca.

## Proves d'integració

```bash
python3 tests/integracio.py              # totes, en valencià i castellà
python3 tests/integracio.py ca           # només en valencià
python3 tests/integracio.py repertori    # només una bateria
```

Arranquen Compàs de veres (el servidor de PHP amb dades d'exemple en una carpeta temporal) i fan el que faria una persona: crear, editar i eliminar, generar rebuts i remeses, importar fulls, imprimir documents, activar i desactivar mòduls… Comproven més de 5.500 coses en uns minuts. Només cal PHP i Python 3, sense paquets addicionals, i no toquen la carpeta `data/`.

| Bateria | Fitxers (a `tests/integracio/`) | Què comprova |
|---|---|---|
| `economia` | `economia.py`, `iban.py`, `societat.py`, `repas.py`, `actualitzacio.py` | Tarifes, rebuts, remeses SEPA, domiciliacions i loteria; IBAN a les fitxes i en importar; junta, actes, documents, full d'alta, comunicats, RGPD i caixa; un repàs de totes les pantalles (llistats, CSV, fitxes i formularis) sense errors de PHP; i que la base de dades només s'actualitza una vegada per versió, amb una còpia abans. |
| `uniformes` | `uniformes_moduls.py` | Tipus de soci propis (i la migració dels antics), uniformes per talla, mòduls i avisos desactivables. |
| `repertori` | `repertori.py`, `assistencia.py` | Catàleg, cerca, filtres, ordre, CSV, número d'arxiu i importació des d'un full; fulls d'assistència en Excel per agrupació. |
| `gran` | `gran.py` | Societats grans: el camp de persona amb cerca quan n'hi ha centenars, el repertori per pàgines i els llistats llargs. |
| `entitat_gran` | `entitat_gran.py` | Carrega l'entitat gran de cada tipus i comprova el volum de dades, que no hi ha dates futures ni errors de PHP i que cap pantalla tarda més de 3 segons. |
| `avis` | `avis.py` | L'avís de responsabilitat en la configuració inicial i en les instal·lacions antigues. |
| `importacio` | `importacio.php` | Importació de socis i alumnat amb IBAN (crida les funcions de PHP directament). |
| `tipus` | `tipus.py` | Instal·la cada tipus d'entitat amb les seues dades d'exemple i en recorre totes les pantalles (llistats, fitxes, documents, formularis, CSV i informes); el vocabulari del menú, els mòduls i el mes de l'exercici de cada tipus, les categories, les llicències i els seus avisos, els càrrecs de festa amb el certificat, les entitats federades i les seues quotes, les activitats (places, imports i rebuts) i el canvi de tipus. |
| `subvencions` | `subvencions.py` | Organismes i requisits editables, cobraments a la caixa, despeses imputades, relació per a la justificació i còpia per a l'any següent. |
| `buidar` | `buidar.py` | Buidatge de les dades de gestió des de Còpies de seguretat: contrasenya i confirmació, còpia prèvia, només l'administrador. |
| `comptes` | `comptes.py` | Comptes amb noms ASCII i únics (també davant SQLite i en migrar noms antics), permisos del consultor davant peticions directes, sessions que caduquen en canviar la contrasenya, galetes inventades i accés només des d'este ordinador. |
| `interficie` | `interficie.py` | Menú curt i plegable, enllaços al que n'ha eixit, cercador (també pel número de soci i el telèfon), «Què vols fer?», botó d'ajuda de cada pantalla, fitxa de persona amb accions i pestanyes, «Més dades» als formularis i alta d'alumne en una pantalla. |
| `seguretat` | `seguretat.py` | Només respon a este ordinador (DNS rebinding), capçaleres de seguretat, redireccions del canvi d'idioma, documents aïllats i límit d'intents d'entrada. |

Cada fitxer rep de `tests/integracio.py` les funcions comunes: `G(consulta)` obri una pàgina, `P(consulta, dades)` envia un formulari, `n(sql)` consulta la base de dades, `ok(condició, missatge)` apunta el resultat, etc. Per a afegir proves d'una funció nova, crea un fitxer i posa'l en una bateria de `BATERIES`.

GitHub Actions també les executa en cada `push`.

## Prova de rendiment

```bash
php tests/performance.php
```

- Crea una base de dades SQLite temporal amb l'esquema real de `src/schema.sql`.
- Comprova que hi ha els índexs addicionals.
- Genera 100.000 rebuts i 300.000 línies.
- Amb `EXPLAIN QUERY PLAN`, comprova que les consultes més habituals fan servir els índexs, i executa cada consulta 100 vegades per a mesurar el temps.
- En acabar, esborra la base de dades temporal.

No s'executa en cada `push`, perquè és lenta.

## Com afegir proves

Abans de canviar la lògica de rebuts, SEPA, còpies, migracions o permisos, afig una prova a `tests/run.php` que comprove el comportament actual. Així es veu de seguida si el canvi trenca alguna cosa.
