# Col·laborar amb Compàs

Compàs és programari lliure per a associacions. Pots ajudar provant-lo, explicant una necessitat real, corregint documentació o traduccions, o enviant canvis de codi. Pots escriure en valencià o castellà; no cal saber programar per a participar.

## Provar el programa

Baixa l'[última versió publicada](https://github.com/jossanmar9/compas/releases/latest) per a Windows o Linux. Per a fer proves, utilitza una instal·lació separada i les dades d'exemple del teu tipus d'entitat. Consulta la [guia d'instal·lació](docs/guia-installacio.md) i el [manual d'usuari](docs/manual-usuari.md).

No utilitzes dades reals de socis, menors o comptes bancaris per a reproduir una errada o preparar captures. No adjuntes bases de dades, còpies de seguretat, documents de l'entitat, contrasenyes ni codis de recuperació.

## Errors, propostes i nous tipus d'entitat

Busca primer si ja hi ha una [incidència](https://github.com/jossanmar9/compas/issues) sobre el mateix tema. Si no, [tria el formulari adequat](https://github.com/jossanmar9/compas/issues/new/choose):

- **Errada / Bug**: indica la versió, el sistema operatiu, el tipus d'entitat, els passos per a reproduir-la, què ha passat i què esperaves. Afig només captures o missatges revisats i sense dades personals.
- **Proposta de millora**: explica la tasca que necessites fer i com la resols ara. Un exemple inventat ajuda més que una llista de botons.
- **Nou tipus d'entitat**: explica què fa l'associació, els noms que utilitza, els mòduls que necessita i quan comença l'exercici. Si un tipus existent ja et serveix parcialment, indica quin.

Per a una vulnerabilitat, **no òbrigues una incidència pública**: segueix [SECURITY.md](SECURITY.md).

## Preparar l'entorn de desenvolupament

Cal Git, PHP 8.1 o posterior amb `pdo_sqlite`, `sqlite3`, `mbstring`, `dom` i `sodium`, i Python 3.10 o posterior per a les proves d'integració. No cal Composer, PHPUnit ni cap servei de base de dades extern. GitHub Actions comprova el projecte amb PHP 8.3.

Fes un *fork* del repositori al teu compte i clona'l. Des de la seua carpeta, crea una branca per al canvi i arranca el programa:

```sh
git switch -c millora/descripcio-del-canvi
php -S 127.0.0.1:8740 -t app app/index.php
```

Obri `http://127.0.0.1:8740` i configura una entitat amb dades d'exemple. Les dades d'esta còpia es guarden a `data/`, que no es puja a Git. La variable `COMPAS_DADES` permet triar una altra carpeta. Mantín el servidor en `127.0.0.1`.

L'[arquitectura](docs/arquitectura.md) explica l'organització del codi. Les entitats estan a `src/entitats.php`, l'esquema a `src/schema.sql`, els tipus d'associació a `src/tipus.php` i els textos a `src/lang/`.

## Executar les comprovacions

```sh
php tests/run.php
python3 tests/integracio.py
```

A Windows, pots gastar `python` en lloc de `python3`. Si Python no troba `Europe/Madrid`, instal·la les dades de fus horari amb `python -m pip install tzdata`.

Les proves d'integració creen instal·lacions temporals amb dades inventades i recorren el programa en valencià i castellà. També pots executar només la bateria afectada, per exemple `python3 tests/integracio.py tipus`. En [tests/README.md](tests/README.md) s'expliquen les bateries i la prova de rendiment opcional.

Revisa la sintaxi dels PHP que hages modificat amb `php -l ruta/al/fitxer.php`. Si canvies la web, genera-la amb `php eines/web.php _site` i comprova les dues llengües i les pantalles menudes. La web i els PDF generats no es guarden a Git.

### Servidor HTTPS i descobriment local

El nucli continua en PHP; `launcher/xarxa` és un auxiliar en Go per a TLS i mDNS, inclòs ja compilat en els paquets. Per a modificar-lo cal Go 1.27 o posterior:

```sh
go -C launcher/xarxa test ./...
go -C launcher/xarxa build -o ../../dist/compas-xarxa .
python3 tests/https.py dist/compas-xarxa
```

A Windows, posa l’eixida a `../../dist/compas-xarxa.exe` i passa `dist/compas-xarxa.exe` a la prova. El test usa certificats i dades temporals: **no instal·la autoritats de confiança ni canvia el tallafoc del sistema**. GitHub també prova l’arrancador Linux real per HTTPS. Si canvies dependències, actualitza `go.mod`, `go.sum` i els avisos de llicència; executa `govulncheck`.

## Enviar una pull request

Per a canvis grans, comenta primer la proposta en una incidència. Per a una correcció menuda pots enviar directament una PR.

1. Mantín cada PR centrada en un problema o una millora i explica el comportament abans i després. Enllaça la incidència si n'hi ha.
2. Respecta l'estil del codi existent. Actualitza els textos en valencià i castellà i la documentació afectada.
3. Si canvies lògica, afig o adapta proves que comproven el comportament, especialment en rebuts, SEPA, permisos, migracions i còpies. Els canvis de documentació o d'imatges no necessiten proves noves.
4. Indica les comprovacions executades i qualsevol limitació. Si canvies pantalles, afig captures amb dades d'exemple.
5. Revisa que no puges dades personals, secrets, paquets compilats ni fitxers generats. Envia la PR contra `main` i atén els resultats de GitHub Actions i la revisió.

No cal canviar la versió ni crear etiquetes per a una contribució: la publicació la prepara el mantenidor. Les contribucions s'integren sota la llicència [AGPL-3.0-or-later](LICENSE) del projecte; inclou només materials que tingues dret a compartir. Si has fet servir IA, explica'n l'ús i revisa el resultat abans d'enviar-lo.

## Castellano

Puedes participar en castellano y sin conocimientos de programación. Prueba Compàs en una instalación separada con datos de ejemplo; busca primero si ya existe una incidencia y elige **Error / Bug**, **Propuesta de mejora** o **Nuevo tipo de entidad**. No publiques datos personales, bases de datos, copias, contraseñas ni códigos de recuperación. Las vulnerabilidades se comunican en privado siguiendo [SECURITY.md](SECURITY.md).

Para contribuir código, haz un *fork*, trabaja en una rama y envía una PR contra `main` con el problema, el cambio y las comprobaciones realizadas. Usa los requisitos y comandos indicados arriba; actualiza las dos lenguas y añade pruebas cuando cambie la lógica. En Windows, `python` puede sustituir a `python3`, y `python -m pip install tzdata` resuelve la falta de datos de zona horaria. No necesitas cambiar la versión ni publicar etiquetas. Las contribuciones se distribuyen con la licencia AGPL-3.0-or-later del proyecto.
