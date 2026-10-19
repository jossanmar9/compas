# Manual de usuario de Compàs

## Primeros pasos

La primera vez que se abre Compàs pide el nombre de la sociedad musical y crea la cuenta de **administrador del sistema**. Si marcas «Cargar datos de ejemplo» tendrás personas, grupos e instrumentos de prueba para conocer el programa.

Orden recomendado para empezar de cero:

1. **Configuración**: revisa los datos de la sociedad.
2. **Cursos académicos**: crea el curso (por ejemplo `2026/2027`), márcalo como **curso actual** y añade los **periodos de evaluación** (1.er trimestre, 2.º trimestre…).
3. **Asignaturas** y **Aulas**.
4. **Personas**: da de alta a todas las personas (socios, músicos, alumnado, profesorado, tutores…).
5. Desde la ficha de cada persona, crea sus **funciones**: socio, alumno, profesor, miembro de una agrupación…
6. **Grupos**: para cada asignatura del curso, crea los grupos, asigna profesor y aula, y añade las **franjas horarias**.
7. **Matrículas**: matricula a cada alumno en el curso e inscríbelo en los grupos.
8. **Usuarios**: crea las cuentas de quien tenga que entrar al programa y vincúlalas con su persona.

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
- Los **tutores legales** son personas registradas. Desde la ficha del alumno, añade los que hagan falta e indica si pueden consultar la información académica y si reciben comunicaciones.

### Cursos, periodos y grupos

- Solo un curso puede ser el **curso actual**. Es el que se usa en el inicio, en la asistencia y en «Mi ficha».
- Un **grupo** es una asignatura impartida en un curso (por ejemplo, *Lenguaje musical · 1.º A*). Puede tener varias franjas horarias.

### Matrículas

La matrícula relaciona a un alumno con un curso. Dentro de la matrícula se añaden los **grupos** en los que está inscrito. Así se conserva el historial de cada curso.

### Horario

Muestra todas las clases del curso por días. Puedes filtrar por profesor o por aula.

### Asistencia

1. Elige el grupo y la fecha (por defecto, hoy).
2. Todos aparecen como **presentes**. Cambia solo los que faltan: **F** falta, **FJ** falta justificada, **R** retraso.
3. Pulsa **Guardar**.

Si vuelves a abrir el mismo día se ve lo que se guardó y se puede corregir. Debajo está la lista de días registrados.

### Calificaciones

Elige el grupo y el periodo y escribe la nota de cada alumno (texto libre: `7`, `8,5`, `Notable`, `Apto`…) y, si quieres, observaciones. El alumnado y los tutores la ven en «Mi ficha».

> El profesorado solo ve **sus** grupos. Para que funcione, la cuenta de usuario del profesor debe estar vinculada a su persona, y esa persona debe tener ficha de profesor.

## Agrupaciones

Banda, banda juvenil, coral, orquesta, grupos de cámara… Cada agrupación tiene sus **miembros** con el instrumento o voz y la función (miembro, director titular, director asistente, responsable). Una persona puede estar en varias agrupaciones.

## Instrumentos

- **Inventario**: cada instrumento con número de inventario, marca, modelo, número de serie, estado y ubicación. En la ficha está el historial de **cesiones** y de **mantenimientos**.
- **Cesiones**: para prestar un instrumento, crea una cesión con la persona, la fecha de entrega y la de devolución prevista. Cuando lo devuelvan, edita la cesión y pon la **fecha de devolución**. Un instrumento no se puede ceder dos veces a la vez.
- En el inicio aparecen las cesiones con la devolución **vencida**.

## Mi ficha

Cualquier usuario vinculado a una persona puede ver:

- sus datos de contacto y de socio;
- si es alumno: grupos, horario, faltas y notas del curso actual;
- si es tutor legal: lo mismo para cada alumno a su cargo (si tiene permiso de consulta);
- si es profesor: sus grupos, con acceso directo a la asistencia y las notas;
- sus agrupaciones y los instrumentos que tiene cedidos.

## Usuarios, roles y permisos

- **Usuarios**: crea una cuenta para cada persona que tenga que entrar. Puedes asignarle **varios roles**; los permisos se suman.
- **Roles y permisos**: una tabla para decidir qué puede hacer cada rol. Puedes cambiar el nombre de los roles y crear otros nuevos. El administrador del sistema siempre lo puede todo.
- Siempre debe quedar al menos un usuario activo que pueda gestionar usuarios.
- Si alguien olvida la contraseña, el administrador le pone una nueva desde su ficha de usuario. Cada uno puede cambiar la suya desde **Mi cuenta** (haciendo clic en el nombre de usuario, arriba a la derecha).

## Copias de seguridad

- Compàs hace **una copia automática cada día** que se usa y guarda las 14 últimas en `data\copies`.
- Desde **Copias de seguridad** puedes descargar una copia en cualquier momento. Guárdala fuera del ordenador (USB, nube de la sociedad…).
- **Restaurar** sustituye todos los datos por los de la copia. Los datos anteriores se guardan en la carpeta de datos con el nombre `compas-abans-restaurar-…`.

> Las copias contienen datos personales, también de menores. Guárdalas en un lugar seguro y no las envíes por canales abiertos.

## Registro de actividad

Anota quién ha creado, modificado o eliminado cada elemento, las entradas al programa (también las fallidas), las exportaciones y las copias.

## Idioma

Compàs funciona en **valenciano** y **castellano**. Cambia el idioma con los botones `VAL` / `ES` de arriba. Cada usuario puede tener su idioma.

## Preguntas frecuentes

**¿Tengo que estar conectado a Internet?** No. Compàs funciona sin Internet.

**¿Dónde están los datos?** En un solo archivo: `data\compas.db`, dentro de la carpeta del programa. La ruta exacta se ve en **Configuración**.

**¿Pueden entrar varias personas a la vez?** Sí, si se activa el acceso desde la red (ver la guía de instalación). El ordenador donde se ejecuta Compàs debe estar encendido y con el programa abierto.

**¿Cómo paso Compàs a otro ordenador?** Haz una copia de seguridad, instala Compàs en el ordenador nuevo y restáurala. También puedes copiar la carpeta `data` entera con Compàs cerrado.
