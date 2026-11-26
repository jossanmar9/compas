# Pruebas de Compàs

*[Valencià](README.md) · Castellano*

Pruebas automáticas sin PHPUnit ni Composer. No tocan `data/compas.db`.

## Pruebas de funcionamiento

```bash
php tests/run.php
```

Comprueban:

- la normalización, el formato y la validación del IBAN, y la conversión de la cuenta española antigua (CCC) a IBAN;
- la validación del BIC, los identificadores SEPA y el texto con el juego de caracteres SEPA;
- el cifrado y el descifrado de las copias de seguridad, el rechazo de una contraseña incorrecta y la detección de una copia manipulada;
- la rotación de las copias automáticas (cuáles se guardan y cuáles se borran);
- los nombres de las cuentas (solo ASCII, únicos también frente a SQLite);
- que todos los textos usan un solo tipo de apóstrofo (`'`).

Algunas necesitan las extensiones `mbstring` y `sodium` de PHP.

GitHub Actions (`.github/workflows/tests.yml`) las ejecuta en cada `push` a `main`, en cada *pull request* y a mano, junto con las pruebas de integración. También revisa la sintaxis de todos los ficheros PHP y comprueba que el servidor local arranca.

## Pruebas de integración

```bash
python3 tests/integracio.py              # todas, en valenciano y castellano
python3 tests/integracio.py es           # solo en castellano
python3 tests/integracio.py repertori    # solo una batería
```

Arrancan Compàs de verdad (el servidor de PHP con datos de ejemplo en una carpeta temporal) y hacen lo que haría una persona: crear, editar y eliminar, generar recibos y remesas, importar hojas, imprimir documentos, activar y desactivar módulos… Comprueban más de 5.500 cosas en unos minutos. Solo hace falta PHP y Python 3, sin paquetes adicionales, y no tocan la carpeta `data/`.

Los nombres de las baterías y de los ficheros están en valenciano, como el código.

| Batería | Ficheros (en `tests/integracio/`) | Qué comprueba |
|---|---|---|
| `economia` | `economia.py`, `iban.py`, `societat.py`, `repas.py`, `actualitzacio.py` | Tarifas, recibos, remesas SEPA, domiciliaciones y lotería (entregas de papeletas, números repetidos, formas de pago y dinero que hay que tener); IBAN en las fichas y al importar; junta, actas, documentos, hoja de alta, comunicados, RGPD y caja (con las categorías propias); un repaso de todas las pantallas (listados, CSV, fichas y formularios) sin errores de PHP; y que la base de datos solo se actualiza una vez por versión, con una copia antes. |
| `uniformes` | `uniformes_moduls.py` | Tipos de socio propios (y la migración de los antiguos), uniformes por talla, módulos y avisos desactivables. |
| `repertori` | `repertori.py`, `assistencia.py` | Catálogo, búsqueda, filtros, orden, CSV, número de archivo e importación desde una hoja; hojas de asistencia en Excel por agrupación. |
| `gran` | `gran.py` | Sociedades grandes: el campo de persona con búsqueda cuando hay cientos, el repertorio por páginas y los listados largos. |
| `entitat_gran` | `entitat_gran.py` | Carga la entidad grande de cada tipo y comprueba el volumen de datos, que no hay fechas futuras ni errores de PHP y que ninguna pantalla tarda más de 3 segundos. |
| `avis` | `avis.py` | El aviso de responsabilidad en la configuración inicial y en las instalaciones antiguas. |
| `importacio` | `importacio.php` | Importación de socios y alumnado con IBAN, números de socio (el siguiente libre, sin repetir ni volver a dar los de baja), NIA y una hoja de LibreOffice de verdad (`tests/fitxers/alumnes.ods`). Llama a las funciones de PHP directamente. |
| `tipus` | `tipus.py` | Instala cada tipo de entidad con sus datos de ejemplo y recorre todas sus pantallas (listados, fichas, documentos, formularios, CSV e informes); el vocabulario del menú, los módulos y el mes del ejercicio de cada tipo, las categorías, las licencias y sus avisos, los cargos festeros con el certificado, las entidades federadas y sus cuotas, las actividades (plazas, importes y recibos) y el cambio de tipo. |
| `subvencions` | `subvencions.py` | Organismos y requisitos editables, cobros en la caja, gastos imputados, relación para la justificación y copia para el año siguiente. |
| `buidar` | `buidar.py` | Vaciado de los datos de gestión desde Copias de seguridad: contraseña y confirmación, copia previa, solo el administrador. |
| `comptes` | `comptes.py` | Cuentas con nombres ASCII y únicos (también frente a SQLite y al migrar nombres antiguos), permisos del consultor frente a peticiones directas, sesiones que caducan al cambiar la contraseña, cookies inventadas y acceso solo desde este ordenador. |
| `interficie` | `interficie.py` | Menú corto y plegable, enlaces a lo que ha salido de él, buscador (también por el número de socio y el teléfono), «¿Qué quieres hacer?», botón de ayuda de cada pantalla, ficha de persona con acciones y pestañas, «Más datos» en los formularios, alta de alumno en una pantalla, accesibilidad (cada campo con nombre, salto al contenido, errores anunciados), número de socio (el siguiente libre, único, no se vuelve a dar), NIA (6 cifras, único, buscador) y contrato de cesión (con el tutor si es menor, desde las fichas, condiciones de la configuración). |
| `seguretat` | `seguretat.py` | Solo responde a este ordenador (DNS rebinding), cabeceras de seguridad, redirecciones del cambio de idioma, documentos aislados y límite de intentos de entrada. |

Cada fichero recibe de `tests/integracio.py` las funciones comunes: `G(consulta)` abre una página, `P(consulta, datos)` envía un formulario, `n(sql)` consulta la base de datos, `ok(condición, mensaje)` apunta el resultado, etc. Para añadir pruebas de una función nueva, crea un fichero y ponlo en una batería de `BATERIES`.

GitHub Actions también las ejecuta en cada `push`.

## Prueba de rendimiento

```bash
php tests/performance.php
```

- Crea una base de datos SQLite temporal con el esquema real de `src/schema.sql`.
- Comprueba que existen los índices adicionales.
- Genera 100.000 recibos y 300.000 líneas.
- Con `EXPLAIN QUERY PLAN`, comprueba que las consultas más habituales usan los índices, y ejecuta cada consulta 100 veces para medir el tiempo.
- Al acabar, borra la base de datos temporal.

No se ejecuta en cada `push`, porque es lenta.

## Cómo añadir pruebas

Antes de cambiar la lógica de recibos, SEPA, copias, migraciones o permisos, añade una prueba en `tests/run.php` que compruebe el comportamiento actual. Así se ve enseguida si el cambio rompe algo.
