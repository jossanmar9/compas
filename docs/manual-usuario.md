# Manual de usuario de Compàs

## Primeros pasos

Compàs funciona en un solo ordenador con Windows y tiene dos cuentas:

- **Administrador/a**: lo puede todo, también gestionar usuarios, la configuración, el registro de actividad y restaurar copias.
- **Administrativo/a**: lleva la gestión diaria (personas, socios, escuela, notas, agrupaciones, instrumentos) y puede hacer copias de seguridad.

La primera vez que se abre Compàs pide el nombre de la sociedad, crea la cuenta de administrador y, si quieres, también la de administrativo. Si marcas «Cargar datos de ejemplo» tendrás personas, grupos e instrumentos de prueba para conocer el programa.

Orden recomendado para empezar de cero:

1. **Configuración**: datos de la sociedad y **logotipo** (sale en el menú y en los boletines).
2. **Copias de seguridad**: indica una **carpeta externa** (disco USB o carpeta de la nube).
3. **Cursos académicos**: crea el curso (por ejemplo `2026/2027`), márcalo como **curso actual** y añade los **periodos de evaluación** (1.er trimestre, 2.º trimestre…).
4. **Asignaturas** y **Aulas**.
5. **Personas**: da de alta a todas las personas (socios, músicos, alumnado, profesorado, tutores…).
6. Desde la ficha de cada persona, crea sus **funciones**: socio, alumno, profesor, miembro de una agrupación…
7. **Grupos**: para cada asignatura del curso, crea los grupos, asigna profesor y aula, y añade las **franjas horarias**.
8. **Matrículas**: matricula a cada alumno en el curso e inscríbelo en los grupos.

## Cómo está organizado

> Una persona se introduce **una sola vez**. Ser socio, alumno, profesor o músico son *funciones* de esa persona, no copias de sus datos.

Cada sección tiene un **listado** (con búsqueda, filtros y exportación a CSV), una **ficha** (donde se ve todo lo relacionado) y un **formulario**.

- Haz clic en cualquier fila para abrir su ficha.
- Desde la ficha, el botón **+ Añadir** de cada apartado crea un elemento ya relacionado. Por ejemplo, desde la ficha de un alumno puedes añadirle un tutor legal o una matrícula.
- Por defecto, los listados ocultan las bajas y las personas inactivas. Marca «Mostrar también las bajas» para verlas.
- **Exportar CSV** genera un archivo que se abre con Excel o LibreOffice Calc.

### ¿Por qué no puedo eliminar a una persona?

Compàs no deja eliminar nada que tenga historial relacionado (matrículas, cesiones, miembros…). Así no se pierde información. En lugar de eliminar, **márcala como inactiva** o ponle la **fecha de baja** y el estado «Baja».

## Personas y socios

- **Personas**: datos personales y de contacto. El campo «Activo» indica si sigue relacionada con la sociedad.
- **Socios**: número de socio, tipo (ordinario, protector, de honor, juvenil), fecha de alta y de baja. Una persona solo puede tener una ficha de socio.

## Escuela de música

### Alumnado, profesorado y tutores legales

- Una persona se convierte en alumno o profesor creándole la ficha correspondiente.
- Los **tutores legales** son personas registradas. Desde la ficha del alumno, añade los que hagan falta.
- La ficha del alumno muestra el resumen del curso actual: grupos, horario, faltas y notas, con un botón para **imprimir su boletín**.

### Cursos, periodos y grupos

- Solo un curso puede ser el **curso actual**. Es el que se usa en el inicio, en la asistencia y en las notas.
- Un **grupo** es una asignatura impartida en un curso (por ejemplo, *Lenguaje musical · 1.º A*). Puede tener varias franjas horarias.

### Matrículas

La matrícula relaciona a un alumno con un curso. Dentro de la matrícula se añaden los **grupos** en los que está inscrito. Así se conserva el historial de cada curso.

### Horario

Muestra todas las clases del curso por días. Puedes filtrar por profesor o por aula.

### Asistencia

1. Elige el grupo y la fecha (por defecto, hoy).
2. Todos aparecen como **presentes**. Cambia solo los que faltan: **F** falta, **FJ** falta justificada, **R** retraso.
3. Pulsa **Guardar**.

Si vuelves a abrir el mismo día se ve lo que se guardó y se puede corregir.

### Calificaciones

El profesorado pasa las notas a administración y administración las introduce:

1. Ve a **Calificaciones**, elige el **grupo** y la **evaluación**.
2. Escribe la nota de cada alumno (texto libre: `7`, `8,5`, `Notable`, `Apto`…) y, si hace falta, las observaciones del profesor.
3. Pulsa **Guardar**. Para borrar una nota, deja la nota y las observaciones en blanco.

### Boletines

Desde **Boletines** (o con el botón de la página de calificaciones o de la ficha de un alumno):

1. Elige el curso y la **evaluación**. El boletín muestra las notas de esa evaluación y de las anteriores; las observaciones son las de la evaluación elegida.
2. Elige de quién: todo el alumnado matriculado, el alumnado de un grupo o un solo alumno.
3. Si quieres, escribe un **texto al pie** (se recuerda para la próxima vez).
4. Pulsa **Preparar los boletines**. Se abre una pestaña nueva con una hoja A4 por alumno. Pulsa **Imprimir**.

> Para guardarlos en PDF (para enviarlos por correo), elige «Microsoft Print to PDF» como impresora.

El boletín lleva el logotipo y los datos de la sociedad, las notas, las faltas, las observaciones y espacio para las firmas.

## Agrupaciones

Banda, banda juvenil, coral, orquesta, grupos de cámara… Cada agrupación tiene sus **miembros** con el instrumento o voz y la función (miembro, director titular, director asistente, responsable). Una persona puede estar en varias agrupaciones.

## Instrumentos

- **Inventario**: cada instrumento con número de inventario, marca, modelo, número de serie, estado y ubicación. En la ficha está el historial de **cesiones** y de **mantenimientos**.
- **Cesiones**: para prestar un instrumento, crea una cesión con la persona, la fecha de entrega y la de devolución prevista. Cuando lo devuelvan, edita la cesión y pon la **fecha de devolución**. Un instrumento no se puede ceder dos veces a la vez.
- En el inicio aparecen las cesiones con la devolución **vencida**.

## Copias de seguridad

Todos los datos están en un solo archivo, `data\compas.db`. Compàs hace copias así:

| Cuándo | Tipo |
|---|---|
| La primera vez que se usa cada día | Automática (diaria) |
| Al cerrar Compàs, si ha habido cambios | Automática (al cerrar) |
| Cuando pulsas **Hacer una copia ahora** | Manual |
| Justo antes de restaurar | Antes de restaurar |

- Las copias se guardan en `data\copies`. Se conservan las últimas automáticas (30 por defecto, configurable); las manuales no se borran solas.
- **Carpeta externa** (muy recomendable): si el ordenador se estropea, las copias de dentro se pierden con él. En **Copias de seguridad → Configuración** indica una carpeta en un **disco USB** o en una carpeta **sincronizada con la nube** (OneDrive, Google Drive…). Cada copia se guardará también allí.
- Si no hay carpeta externa, o hace más de 7 días que no se guarda nada en ella (por ejemplo, porque el USB no está conectado), el inicio lo avisa.
- **Descargar una copia al ordenador** descarga una copia para guardarla donde quieras.

### Restaurar (solo el administrador/a)

- Desde la lista: botón **Restaurar** de la copia que quieras.
- Desde un archivo: **Restaurar desde un archivo** y elige el `.db`.

Antes de restaurar, Compàs hace una copia de los datos actuales («Antes de restaurar»), así que siempre se puede volver atrás. La configuración de la carpeta externa se conserva. Después hay que volver a entrar.

> Las copias contienen datos personales, también de menores. Guárdalas en un lugar seguro.

## Usuarios

Solo el administrador/a. Desde **Usuarios** puedes cambiar el nombre, el rol, el idioma y la contraseña de cada cuenta, o desactivarla. Si alguien olvida la contraseña, el administrador le pone una nueva. Cada uno puede cambiar la suya desde **Mi cuenta** (haciendo clic en el nombre de usuario, arriba a la derecha).

## Registro de actividad

Solo el administrador/a. Anota quién ha creado, modificado o eliminado cada elemento, las entradas al programa (también las fallidas), las exportaciones, los boletines impresos y las copias.

## Idioma

Compàs funciona en **valenciano** y **castellano**. Cambia el idioma con los botones `VAL` / `ES` de arriba. Cada usuario puede tener su idioma.

## Preguntas frecuentes

**¿Tengo que estar conectado a Internet?** No. Compàs funciona sin Internet.

**¿Dónde están los datos?** En `data\compas.db`, dentro de la carpeta del programa. La ruta exacta se ve en **Configuración**.

**¿Cómo paso Compàs a otro ordenador?** Instala Compàs en el ordenador nuevo y restaura en él la última copia (desde un archivo). También puedes copiar la carpeta `data` entera con Compàs cerrado.
