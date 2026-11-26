# Col·laborar amb Compàs

*Valencià · [Castellano](#castellano)*

Compàs és programari lliure per a associacions. Pots ajudar provant-lo, explicant una necessitat real, corregint documentació o traduccions, o enviant canvis de codi. Pots escriure en valencià o castellà; no cal saber programar per a participar.

## Provar el programa

Baixa l'[última versió publicada](https://github.com/jossanmar9/compas/releases/latest) per a Windows o Linux. Per a fer proves, utilitza una instal·lació separada i les dades d'exemple del teu tipus d'entitat. Consulta la [guia d'instal·lació](docs/guia-installacio.md) i el [manual d'usuari](docs/manual-usuari.md).

No utilitzes dades reals de socis, menors o comptes bancaris per a reproduir una errada o preparar captures. No adjuntes bases de dades, còpies de seguretat, documents de l'entitat, contrasenyes ni codis de recuperació.

## Errors, propostes i nous tipus d'entitat

Busca primer si ja hi ha una [incidència](https://github.com/jossanmar9/compas/issues) sobre el mateix tema. Si no, [tria el formulari adequat](https://github.com/jossanmar9/compas/issues/new/choose):

- **He trobat una errada**: indica la versió, el sistema operatiu, el tipus d'entitat, els passos per a reproduir-la, què ha passat i què esperaves. Afig només captures o missatges revisats i sense dades personals.
- **Tinc una proposta**: explica la tasca que necessites fer i com la resols ara. Un exemple inventat ajuda més que una llista de botons.
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

## Enviar una pull request

Per a canvis grans, comenta primer la proposta en una incidència. Per a una correcció menuda pots enviar directament una PR.

1. Mantín cada PR centrada en un problema o una millora i explica el comportament abans i després. Enllaça la incidència si n'hi ha.
2. Respecta l'estil del codi existent. Actualitza els textos en valencià i castellà i la documentació afectada.
3. Si canvies lògica, afig o adapta proves que comproven el comportament, especialment en rebuts, SEPA, permisos, migracions i còpies. Els canvis de documentació o d'imatges no necessiten proves noves.
4. Indica les comprovacions executades i qualsevol limitació. Si canvies pantalles, afig captures amb dades d'exemple.
5. Revisa que no puges dades personals, secrets, paquets compilats ni fitxers generats. Envia la PR contra `main` i atén els resultats de GitHub Actions i la revisió.

No cal canviar la versió ni crear etiquetes per a una contribució: la publicació la prepara el mantenidor. Les contribucions s'integren sota la llicència [AGPL-3.0-or-later](LICENSE) del projecte; inclou només materials que tingues dret a compartir. Si has fet servir IA, explica'n l'ús i revisa el resultat abans d'enviar-lo.

## Castellano

### Colaborar con Compàs

Compàs es software libre para asociaciones. Puedes ayudar probándolo, explicando una necesidad real, corrigiendo documentación o traducciones, o enviando cambios de código. Puedes escribir en valenciano o castellano; no hace falta saber programar para participar.

### Probar el programa

Descarga la [última versión publicada](https://github.com/jossanmar9/compas/releases/latest) para Windows o Linux. Para hacer pruebas, usa una instalación separada y los datos de ejemplo de tu tipo de entidad. Consulta la [guía de instalación](docs/guia-instalacion.md) y el [manual de usuario](docs/manual-usuario.md).

No uses datos reales de socios, menores o cuentas bancarias para reproducir un error o preparar capturas. No adjuntes bases de datos, copias de seguridad, documentos de la entidad, contraseñas ni códigos de recuperación.

### Errores, propuestas y nuevos tipos de entidad

Busca primero si ya hay una [incidencia](https://github.com/jossanmar9/compas/issues) sobre el mismo tema. Si no, [elige el formulario adecuado](https://github.com/jossanmar9/compas/issues/new/choose):

- **He encontrado un error**: indica la versión, el sistema operativo, el tipo de entidad, los pasos para reproducirlo, qué ha pasado y qué esperabas. Añade solo capturas o mensajes revisados y sin datos personales.
- **Tengo una propuesta**: explica la tarea que necesitas hacer y cómo la resuelves ahora. Un ejemplo inventado ayuda más que una lista de botones.
- **Nuevo tipo de entidad**: explica qué hace la asociación, los nombres que usa, los módulos que necesita y cuándo empieza el ejercicio. Si un tipo existente ya te sirve en parte, indica cuál.

Para una vulnerabilidad, **no abras una incidencia pública**: sigue [SECURITY.md](SECURITY.md#seguridad-de-compàs).

### Preparar el entorno de desarrollo

Hace falta Git, PHP 8.1 o posterior con `pdo_sqlite`, `sqlite3`, `mbstring`, `dom` y `sodium`, y Python 3.10 o posterior para las pruebas de integración. No hace falta Composer, PHPUnit ni ningún servicio de base de datos externo. GitHub Actions comprueba el proyecto con PHP 8.3.

Haz un *fork* del repositorio en tu cuenta y clónalo. Desde su carpeta, crea una rama para el cambio y arranca el programa:

```sh
git switch -c millora/descripcion-del-cambio
php -S 127.0.0.1:8740 -t app app/index.php
```

Abre `http://127.0.0.1:8740` y configura una entidad con datos de ejemplo. Los datos de esta copia se guardan en `data/`, que no se sube a Git. La variable `COMPAS_DADES` permite elegir otra carpeta. Mantén el servidor en `127.0.0.1`.

La [arquitectura](docs/arquitectura.es.md) explica la organización del código. Las entidades están en `src/entitats.php`, el esquema en `src/schema.sql`, los tipos de asociación en `src/tipus.php` y los textos en `src/lang/`. El código, los nombres de ficheros y los comentarios están en valenciano.

### Ejecutar las comprobaciones

```sh
php tests/run.php
python3 tests/integracio.py
```

En Windows, puedes usar `python` en lugar de `python3`. Si Python no encuentra `Europe/Madrid`, instala los datos de zona horaria con `python -m pip install tzdata`.

Las pruebas de integración crean instalaciones temporales con datos inventados y recorren el programa en valenciano y castellano. También puedes ejecutar solo la batería afectada, por ejemplo `python3 tests/integracio.py tipus`. En [tests/README.es.md](tests/README.es.md) se explican las baterías y la prueba de rendimiento opcional.

Revisa la sintaxis de los PHP que hayas modificado con `php -l ruta/al/fichero.php`. Si cambias la web, genérala con `php eines/web.php _site` y comprueba las dos lenguas y las pantallas pequeñas. La web y los PDF generados no se guardan en Git.

### Enviar una pull request

Para cambios grandes, comenta primero la propuesta en una incidencia. Para una corrección pequeña puedes enviar directamente una PR.

1. Mantén cada PR centrada en un problema o una mejora y explica el comportamiento antes y después. Enlaza la incidencia si la hay.
2. Respeta el estilo del código existente. Actualiza los textos en valenciano y castellano y la documentación afectada.
3. Si cambias lógica, añade o adapta pruebas que comprueben el comportamiento, especialmente en recibos, SEPA, permisos, migraciones y copias. Los cambios de documentación o de imágenes no necesitan pruebas nuevas.
4. Indica las comprobaciones ejecutadas y cualquier limitación. Si cambias pantallas, añade capturas con datos de ejemplo.
5. Revisa que no subes datos personales, secretos, paquetes compilados ni ficheros generados. Envía la PR contra `main` y atiende los resultados de GitHub Actions y la revisión.

No hace falta cambiar la versión ni crear etiquetas para una contribución: la publicación la prepara el mantenedor. Las contribuciones se integran bajo la licencia [AGPL-3.0-or-later](LICENSE) del proyecto; incluye solo materiales que tengas derecho a compartir. Si has usado IA, explica su uso y revisa el resultado antes de enviarlo.
