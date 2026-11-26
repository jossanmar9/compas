# Arquitectura de Compàs

*[Valencià](arquitectura.md) · Castellano*

Compàs es un programa de gestión para una única asociación (sociedad musical, club deportivo, colla, muixeranga, colla castellera, asociación cultural, AMPA, falla, gaiata, hoguera, comparsa, peña o federación), en un solo ordenador con Windows o Linux y con una cuenta por persona (administrador/a, usuario/a de gestión o consultor/a). No tiene web pública ni acceso por red.

Los nombres de ficheros, funciones, tablas y claves del código están en valenciano, como el código.

## Piezas

```
Compas.exe (lanzador, C#)
   │  arranca
   ▼
php.exe -S 127.0.0.1:8740   (servidor integrado de PHP, portable)
   │
   ▼
app/index.php  ──►  src/  (lógica en PHP)
                     │
                     ▼
               data/compas.db  (SQLite, un solo fichero)
```

- **Lanzador** (`launcher/Compas.cs`): arranca PHP solo en `127.0.0.1`, espera a que responda y abre la ventana de Compàs (`launcher/Visor.cs`, un formulario con el control WebView2 y los datos en `%LOCALAPPDATA%\Compas\visor`). Las páginas que se abren aparte (impresiones) son otras ventanas del visor, y las direcciones de fuera van al navegador habitual. Cuando se cierra la ventana principal, Compàs se detiene. Si WebView2 no está, abre Microsoft Edge o Google Chrome con `--app` y un perfil propio en `%LOCALAPPDATA%\Compas\finestra` (con un icono en la bandeja); si no hay Edge ni Chrome, o con `finestra = 0`, abre el navegador habitual y muestra la ventana pequeña de siempre. Al cerrar, pide a PHP una copia de seguridad (con un testigo secreto que solo conocen el lanzador y PHP) y lo detiene. Lee `compas.ini`.
- **Linux** (`launcher/linux/`): `compas.sh` hace lo mismo que el lanzador de Windows con el PHP del sistema (comprueba la versión y las extensiones, arranca en `127.0.0.1`, abre la ventana de Compàs y, con `trap` al salir, pide la copia de cierre). La ventana es `compas-visor.py` (Python con GTK 3 y WebKitGTK, los datos en `~/.local/share/compas/visor`); cuando se cierra, `compas.sh` termina. Si no está, prueba Chromium, Chrome, Edge o Brave con `--app` y, si tampoco, `xdg-open` con un terminal. `installa.sh` lo copia en `~/.local/share/compas/programa` y crea la entrada del menú (`Terminal=false`) y la orden `compas`. Los datos van a `~/.local/share/compas/dades`. El paquete `.deb` (`eines/construeix-deb.sh`, con `launcher/linux/deb/`) pone el programa en `/usr/share/compas`, la orden en `/usr/bin/compas` y la entrada del menú, depende de `php-cli`, `php-sqlite3` y `php-mbstring` y recomienda `php-xml` y lo necesario para la ventana (`python3-gi`, `gir1.2-gtk-3.0`, `gir1.2-webkit2-4.1`); la configuración de cada usuario va a `~/.config/compas/compas.ini`.
- **PHP portable**: se descarga de windows.php.net al construir la versión. Extensiones: `pdo_sqlite`, `sqlite3`, `mbstring`, `fileinfo`, `opcache` y `sodium` (cifrado de las copias; `libsodium.dll` va junto a `php.exe`). Las integradas `zlib` y `SimpleXML` se usan para leer los .xlsx y los .ods.
- **Aplicación**: PHP sin dependencias ni frameworks, HTML y CSS propios y un poco de JavaScript. Funciona sin Internet.
- **Base de datos**: SQLite a través de PDO. Todo el SQL usa consultas preparadas. Se puede migrar a MariaDB/MySQL más adelante cambiando la conexión y adaptando `schema.sql`.

## Código

| Fichero | Función |
|---|---|
| `app/index.php` | Punto de entrada único y enrutador (`?r=…`). |
| `src/bootstrap.php` | Conexión, sesión, CSRF, usuario actual, permisos, auditoría. |
| `src/schema.sql` | Esquema de la base de datos (se crea solo la primera vez). |
| `src/capacitats.php` | Capacidades y roles por defecto. |
| `src/tipus.php` | Tipos de entidad (`TIPUS_ENTITAT`): los módulos que activa cada tipo, el perfil de vocabulario, el mes en que empieza el ejercicio, si la escuela pone notas, las categorías de edad y los cargos festeros para empezar. `aplica_tipus()` lo configura en la instalación y al cambiar de tipo; `any_exercici()` y `nom_exercici()` calculan el ejercicio («2026» o «2026-2027»). |
| `src/lang/vocabulari.php` | Vocabulario de cada tipo: los textos base son los de una sociedad musical y `vocabulari()` devuelve los que cambian (perfiles de escuela, grupos, socios, material y ropa sobre plantillas con huecos `{…}`). `t()` los aplica por encima de los del idioma. |
| `src/entitats.php` | Definición declarativa de cada entidad: campos, relaciones y validaciones. |
| `src/disseny.php` | Marco de cada pantalla (`pagina()`): cabecera con el buscador y el botón **?** (`enllac_ajuda()` lleva al apartado del manual de cada ruta), y el menú (`menu()`), agrupado en secciones plegables. Lo que se configura de vez en cuando no está en el menú: `menu_relacionat()` pone sus botones en la pantalla donde se usa (Grupos, Resumen económico, Configuración). `accessible()` pasa por todo el contenido: une cada `<label>` sin `for` al campo que le sigue y marca los errores con `role="alert"`; los campos sin etiqueta visible llevan `aria-label`. |
| `src/crud.php` | Listados, fichas, formularios y exportación CSV genéricos a partir de `entitats.php`. |
| `src/pages/*.php` | Pantallas específicas: inicio (con «¿Qué quieres hacer?»), buscador (`cerca.php`, también en JSON para las propuestas mientras se escribe), alta de alumno con la familia y los grupos (`alta.php`), hojas de asistencia (`assistencia.php`), horario, calificaciones, boletines y logotipo, documentos (consentimientos, certificados y contrato de cesión), listados e informes, importación, pasar de curso, economía (generación de recibos, remesas, recibos y mandatos para imprimir, configuración), lotería, caja (libro de caja), sociedad (junta, actas, documentos, hoja de alta, comunicados), RGPD, uniformes, repertorio, subvenciones (ficha con el resumen y los requisitos, relación de gastos, copia para el año siguiente, avisos), asociaciones (actividades e inscripciones, categorías de edad, licencias, entidades federadas, cargos festeros y sus informes), datos de ejemplo de cada tipo (`exemple_associacions.php`), usuarios, configuración, auditoría, copias, recuperación, ayuda. |
| `src/xlsx.php` | Escribe hojas de Excel (.xlsx) sin la extensión zip, con fórmulas y validaciones (hojas de asistencia). |
| `src/fulls.php` | Lectura de CSV, Excel (.xlsx) y LibreOffice (.ods) para la importación, sin la extensión zip: lee el directorio central del zip y descomprime con `gzinflate`. Del .ods se deshacen las celdas repetidas (`number-columns-repeated`), menos las vacías del final. |
| `src/rotacio.php` | Qué copias automáticas se conservan: todas las de los últimos 7 días, una por semana de las últimas 5 y una por mes hasta el límite configurado (`copia_mesos`, 12 por defecto). |
| `src/xifrat.php` | Cifrado de las copias externas con libsodium. |
| `src/sepa.php` | IBAN (módulo 97), identificador de acreedor SEPA, texto con el juego de caracteres SEPA y XML de la remesa (pain.008.001.02 o .08, CORE; un bloque FRST para los mandatos que no se han usado nunca y un RCUR para el resto). |
| `app/assets/document.css` | Documentos A4: consentimientos, certificados, contratos, listados y etiquetas (sobre `butlleti.css`). |
| `src/lang/ca.php`, `es.php` | Textos en valenciano y castellano. |
| `app/assets/` | Estilo, JavaScript, logotipo e icono. |
| `src/markdown.php` | Conversor de Markdown a HTML (manual dentro del programa y PDF). |
| `eines/document-html.php`, `eines/pdf.sh` | Preparan cada manual como página A4 con portada y lo imprimen en PDF con Chrome (`docs/pdf/`). Los PDF no se guardan en el repositorio: los hace GitHub Actions al publicar una versión (se adjuntan a la versión) y al publicar la web. |
| `eines/captures.py` | Genera las capturas de los manuales (`docs/imatges/ca` y `es`) haciendo cada proceso con datos de ejemplo. Requiere Python, Pillow y Playwright: `python3 eines/captures.py`. |
| `eines/entitat-gran.php` | Llena una instalación nueva con la entidad grande inventada del tipo elegido (para una sociedad musical: 341 socios, 278 alumnos, 5 agrupaciones, 653 obras, dos cursos con notas y horarios, unos 5.000 recibos…), en el idioma de la instalación y siempre igual para una misma fecha. Sirve para probar el programa con muchos datos y para las capturas de la web. |
| `src/pages/exemple.php`, `exemple_associacions.php`, `exemple_gran.php`, `exemple_noms.php`, `src/exemple/` | Datos de ejemplo: los pequeños (una veintena de personas) y la entidad grande de cada tipo (`src/exemple/societat_musical.php` y `associacio.php`), con nombres, apellidos y calles inventados. Las fechas se calculan a partir del día en que se cargan. |
| `eines/web.php`, `eines/web.css` | Generan la web de presentación (valenciano y castellano) con capturas y PDF. El workflow `web.yml` la publica en GitHub Pages. |

Para añadir una entidad sencilla: crea la tabla en `schema.sql`, defínela en `entitats.php`, añádela al menú (`src/disseny.php`) y pon los textos en `lang/`. Una entidad puede añadir botones a su ficha con `accions_fitxa` (así se hacen los botones de certificado, hoja de consentimiento, contrato de cesión y pasar de curso).

Si una versión nueva añade columnas a una tabla que ya existe, hay que ponerlas en `schema.sql` (instalaciones nuevas) y en `migra_esquema()` de `src/bootstrap.php` (bases de datos anteriores), que las añade con `ALTER TABLE` si faltan.

El esquema y las migraciones no se repasan en cada clic: `PRAGMA user_version` guarda un número calculado con la versión de Compàs y el contenido de `schema.sql`. Cuando no coincide (una versión nueva o una copia restaurada de una versión anterior), `actualitza_esquema()` hace primero una copia «antes de actualizar» y después todo el proceso en una sola transacción: si algo falla, la base de datos queda como estaba.

Los campos de relación (`ref`) con más de `MAX_DESPLEGABLE` opciones (400) no hacen un desplegable: se busca escribiendo y `?r=cerca_ref` devuelve las 30 primeras que coinciden, sin tener en cuenta las tildes.

## Modelo de datos

- **Persona** es la entidad central. Cada persona existe una sola vez.
- Las funciones son tablas separadas vinculadas a la persona: `socis`, `alumnes`, `professors`, `membres` (de agrupaciones), `tutories` (alumno ↔ tutor legal, muchos a muchos), `cessions`.
- Escuela: `cursos` → `periodes`, `grups` (asignatura + curso + profesor + aula) → `horaris`; `matricules` (alumno + curso) → `matricula_grups`; `qualificacions` (grupo + alumno + periodo). La asistencia no se guarda: `assistencia.php` hace las hojas en Excel para pasar lista a mano.
- Inventario: `instruments` → `cessions`, `manteniments`.
- Derechos de imagen: `persones.consentiment_imatge` (`pendent`, `autoritza`, `no_autoritza`) y `consentiment_data`.
- Acceso: `usuaris` (nombres ASCII de 3 a 40 caracteres, únicos sin distinguir mayúsculas; unos disparadores de SQLite lo aseguran), `rols` (tres fijos: `admin`, `administratiu`, que en pantalla se llama «usuario/a de gestión», y `consultor`, de consulta limitada), `rol_capacitats`, `usuari_rols`. Se comprueban **capacidades**, no roles; el consultor, además, solo ve las entidades de `ENTITATS_CONSULTOR` (`src/usuaris.php`), sin campos económicos ni bancarios.
- Economía: `tarifes`, `mandats`, `remeses`, `rebuts` → `rebut_linies`, `loteries` → `talonaris`, `moviments` (libro de caja: solo los apuntes manuales, con el proveedor y el NIF de las facturas; recibos, remesas, lotería y cobros de subvenciones se suman al leerlo).
- Subvenciones: `organismes` y `requisits_justificacio` (listas que edita cada entidad; `organisme_requisits`: qué pide normalmente cada organismo), `subvencions` → `subvencio_requisits` (cómo se justifica y si está hecho), `subvencio_cobraments` (entran solos en la caja) y `subvencio_despeses` (movimientos de caja imputados; un gasto no se puede imputar por más de lo que vale). `documents.subvencio_id` une los documentos a la subvención.
- Sociedad: `carrecs` (persona + cargo + fechas; vigente si no tiene `data_fi`), `reunions` (convocatoria, acta y acuerdos en texto, uno por línea), `comunicats`.
- Repertorio: `tipus_obres` (definidos por cada entidad; `sembra_tipus_obres()` crea unos cuantos la primera vez) → `obres` (número de archivo único cuando lo hay) ↔ `obra_agrupacions` (muchos a muchos con `agrupacions`). En el formulario genérico, las agrupaciones son un campo virtual de tipo `refs` (casillas). La pantalla `?r=repertori` (`src/pages/repertori.php`) hace la búsqueda, los filtros, el orden natural, la impresión y el CSV; la importación (`analitza_obres`, `executa_importacio_obres`) va por el mismo asistente que la de personas.
- 2.0: `categories` (nombre y edades; la categoría de una persona se calcula con la edad que cumple el año en que empieza el ejercicio), `activitats` → `activitat_inscripcions` (plazas, importe y `rebut_id`; los recibos tienen la clave `activitat:{actividad}:{inscripción}`), `llicencies` (persona, equipo, temporada, caducidad y revisión médica), `entitats_federades` (representante `persona_id` y cuota; recibos con la clave `entitat:{id}:{año}`) y `carrecs_festa` (persona, cargo y ejercicio en texto). Las instalaciones anteriores se quedan como sociedad musical, con los módulos nuevos que no necesita desactivados (`migra_esquema`).
- Número de socio (`socis.numero`): único (índice `socis_numero`, que no se crea si una base de datos antigua ya tiene repetidos; el inicio los lista). `seguent_numero_soci()` da uno más que el más alto entre los socios y `config.ultim_numero_soci`, que recuerda el más alto dado para que el de un socio borrado no se vuelva a dar. El NIA (`alumnes.nia`, 6 cifras) también es único (`alumnes_nia`).
- Tipos de socio: `tipus_socis`, definidos por cada entidad. `socis.tipus` y `tarifes.tipus_soci` guardan el id (en texto). Hasta la 1.10 eran cuatro códigos fijos (`ordinari`, `protector`, `juvenil`, `honorari`): `sembra_tipus_socis()` crea los cuatro tipos la primera vez (en la configuración inicial, o al abrir una base de datos antigua) y les pasa los códigos; `tipus_socis.codi` guarda su origen.
- Uniformes: `uniformes` (tallas en texto, separadas por comas) → `uniforme_compres` y `uniforme_lliuraments` (venta, préstamo o gratuito). Las existencias se calculan: compradas − entregadas + préstamos devueltos.
- Módulos: `MODULS` (en `entitats.php`) dice qué pantallas y entidades tiene cada módulo; `config.moduls_desactivats` y `config.avisos_desactivats` guardan lo que ha desactivado la entidad; el tipo de entidad desactiva algunos para empezar. `disponible()` oculta el menú, las fichas hijas y los informes, y `app/index.php` no abre las pantallas de un módulo desactivado.
- Documentos: `documents` (metadatos y caducidad) y `document_fitxers` (contenido en BLOB, `ON DELETE CASCADE`). Guardarlos en la base de datos hace que `VACUUM INTO` los incluya en cada copia, cifrada o no, sin una carpeta aparte. Se sirven con `?r=document_fitxer` (en línea para PDF, imágenes y texto; descargados el resto).
- `auditoria` registra las operaciones sensibles.
- Las claves foráneas no dejan borrar nada con historial: las bajas se marcan con estado o fecha de baja.

## Seguridad

- Solo escucha en `127.0.0.1`: no es accesible desde la red. Además, solo responde a las peticiones dirigidas a `127.0.0.1`, `localhost` o `[::1]` (contra el *DNS rebinding*: una web maliciosa que apunte su nombre a este ordenador recibe un 421). Otros nombres, si hace falta: variable `COMPAS_HOSTS`.
- Contraseñas con `password_hash`. Bloqueo temporal de 30 segundos después de 5 intentos fallidos, contados en la base de datos para todo el programa (no por sesión, que se puede evitar sin cookie).
- Recuperación del acceso (`src/pages/recuperacio.php`): código de 16 caracteres (80 bits, alfabeto sin 0/O/1/I) guardado solo como `password_hash` en `config`; de un solo uso (al usarlo se genera otro) y no vuelve atrás al restaurar una copia. Alternativa sin código: un fichero `recupera-acces` en la carpeta de datos, válido 60 minutos y borrado al usarlo (quien puede escribir en ella ya tiene acceso a `compas.db`). Solo para cuentas de administrador; límite de intentos guardado en la base de datos, no en la sesión.
- Token CSRF en todos los formularios. Cookies `HttpOnly` y `SameSite=Strict`.
- Toda la salida se escapa con `htmlspecialchars`. Política de seguridad de contenidos (CSP): el programa solo carga y envía cosas a sí mismo, de manera que un código colado no podría enviar datos fuera. Los documentos subidos (excepto los PDF) se abren aislados (`sandbox`). Las redirecciones solo van a direcciones del programa.
- La carpeta de datos queda fuera de la carpeta pública (`app/`); el servidor solo sirve `app/assets/`.
- El usuario/a no puede gestionar usuarios, configuración, registro de actividad ni restaurar copias.
- Copias con `VACUUM INTO` (consistentes aunque el programa esté abierto): diaria, al cerrar si hay cambios, manual, antes de restaurar y antes de importar; duplicadas en una carpeta externa opcional.
- Copias externas cifradas (opcional): clave derivada de la contraseña con Argon2id (`crypto_pwhash`) y cifrado en bloques de 1 MB con XChaCha20-Poly1305 (`crypto_secretstream`), que detecta cualquier modificación o corte del fichero. En el ordenador solo se guarda la clave derivada, para poder hacer las copias automáticas; la contraseña hace falta para restaurar.
- Importación: los .xlsx y los .ods se leen sin DTD (`LIBXML_NONET`, sin entidades externas); los datos entre pasos se guardan en un fichero temporal de la carpeta de datos con un nombre aleatorio ligado a la sesión, y se borran al acabar o al cabo de un día.

## Notas

- Economía (`src/pages/economia.php`): los recibos generados llevan una `clau` única (socio y periodo, o pagador, curso y periodo) para que generar dos veces el mismo periodo no duplique nada. El importe de un recibo con líneas es la suma de las líneas (`recalcula_rebut`). Numeración correlativa por año (`2026/0001`). Una remesa preparada fija el mandato de cada recibo; marcarla como cobrada pone los recibos pagados y el `ultim_us` de los mandatos (el siguiente cobro ya es RCUR). Compàs hace recibos, no facturas, para no entrar en el reglamento de los sistemas de facturación (Verifactu).
