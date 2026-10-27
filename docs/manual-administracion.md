# Manual de administración de Compàs

## 1. Antes de empezar

Compàs es el programa de gestión de la entidad: personas, socios, escuela de música, agrupaciones, repertorio, instrumentos, uniformes, cuotas y recibos, lotería, libro de caja, junta directiva y actas, documentos, comunicados y copias de seguridad. Funciona en Windows o Linux, sin Internet, con modo local por defecto y LAN opcional, y tiene tres roles de cuenta. Esta tabla resume qué puede hacer cada una:

| Qué se puede hacer | Administrador/a | Usuario/a | Consultor/a |
|---|:---:|:---:|:---:|
| Personas, socios y tipos de socio | ✔ | ✔ | Consulta parcial |
| Escuela: cursos, grupos, matrículas, notas y boletines | ✔ | ✔ | Consulta parcial |
| Agrupaciones, repertorio, instrumentos y uniformes | ✔ | ✔ | Consulta parcial |
| Cuotas, recibos, domiciliaciones, remesas, lotería y subvenciones | ✔ | ✔ | — |
| Libro de caja | ✔ | ✔ | — |
| Junta, actas, documentos y comunicados | ✔ | ✔ | — |
| Listados, certificados e importación desde Excel | ✔ | ✔ | — |
| Hacer y descargar copias de seguridad | ✔ | ✔ | — |
| Cambiar la propia contraseña | ✔ | ✔ | ✔ |
| Configuración de la entidad, logotipo, módulos y avisos | ✔ | — | — |
| Datos bancarios y descuentos de la escuela | ✔ | — | — |
| Crear y gestionar las cuentas | ✔ | — | — |
| Registro de actividad | ✔ | — | — |
| Restaurar y eliminar copias, carpeta externa y cifrado | ✔ | — | — |
| Recuperar el acceso con el código de recuperación | ✔ | — | — |

Este manual es para el administrador/a de Compàs: la persona que lo pone en marcha, lo configura, gestiona las cuentas y las copias de seguridad y lo actualiza. Para la gestión diaria (personas, socios, escuela, economía, junta…), ver el **Manual de usuario**.

> En este manual, el botón o el campo que hay que usar en cada paso aparece **marcado en naranja** en las capturas.

> ⚠ **Aviso de responsabilidad.** Compàs es software libre y se distribuye tal cual, sin ninguna garantía (licencia AGPL-3.0). El autor no se hace responsable de la pérdida de datos ni de ningún otro daño, directo o indirecto, que pueda derivarse del uso del programa, de un error, de una actualización o de una configuración incorrecta. Cada entidad es responsable de sus datos y de hacer copias de seguridad periódicas en un lugar externo (USB o nube).

### Abrir, cerrar y entrar

Cómo se abre, se cierra y se entra en Compàs es igual para todas las cuentas: lo explica el apartado 1 del **Manual de usuario**.

### La primera vez: configuración inicial

La primera vez que se abre Compàs aparece la **Configuración inicial**:

1. Escribe el **nombre de la entidad**.
2. Elige el **tipo de entidad**: sociedad musical, club deportivo, colla, muixeranga, colla castellera, asociación cultural, AMPA, falla, gaiata, foguera, comparsa, peña o federación. Compàs adapta los nombres (socios, falleros y falleras, deportistas, equipos…), los apartados que activa, el mes en que empieza el ejercicio y las categorías de edad. Todo se puede cambiar después.
3. Elige el **idioma** por defecto.
4. Crea el **usuario administrador** y su contraseña (al menos 8 caracteres). Apúntala en un lugar seguro.
5. Si quieres, crea también la cuenta de **usuario/a** (se puede hacer después).
6. Marca **Cargar datos de ejemplo** solo si quieres probar el programa con datos inventados.
7. Lee el **aviso de responsabilidad** y marca que lo aceptas.
8. Pulsa **Empezar**.

![Configuración inicial](imatges/es/01-configuracio-inicial.webp)

A continuación, Compàs muestra el **código de recuperación**. Sirve para poner una contraseña nueva si el administrador/a la olvida, y **solo se muestra esta vez**. Pulsa **Imprime** (o apúntalo) y guárdalo con los papeles de la entidad, no en el ordenador. Después pulsa **Ya lo he guardado, continúa**.

![Código de recuperación](imatges/es/01b-codi-recuperacio.webp)

> Para empezar sin los datos de ejemplo: entra como administrador/a en **Copias de seguridad → Vaciar los datos de gestión**, con LAN desactivado. Escribe `BUIDAR` y tu contraseña actual. Se guarda una copia manual antes de borrar **todos** los datos de gestión, incluidos los datos reales añadidos después. Se conservan configuración, logo, cuentas, permisos y catálogos de tipos y categorías; las tarifas se vacían. Las copias anteriores siguen disponibles. Después hay que volver a entrar.

## 2. Configuración de la entidad y logotipo

Menú **Administración → Configuración** (solo el administrador/a).

1. Rellena los datos de la entidad: nombre, CIF, dirección, teléfono, correo y web. Salen en los recibos, los certificados y los boletines.
2. En **Logotipo de la entidad**, pulsa el botón de elegir archivo y selecciona la imagen del logotipo (PNG, JPG o WEBP, hasta 3 MB).
3. Pulsa **Guardar**. El logotipo aparece en el menú y en los boletines.

![Configuración y logotipo](imatges/es/03-configuracio.webp)

### Tipo de entidad y ejercicio

En la misma pantalla se puede cambiar el **tipo de entidad**. Al cambiarlo, cambian los nombres de las pantallas; si marcas **Activa también los apartados y el mes de inicio propios de este tipo**, también se activan los apartados del tipo nuevo y se desactivan los que no necesita. Los datos no se tocan nunca: un apartado desactivado se puede volver a activar en **Módulos y avisos**.

El **mes en que empieza el ejercicio** decide cómo se llaman y cómo se cuentan los ejercicios: enero para el año natural («2026»), septiembre para una temporada deportiva o un curso escolar («2026-2027»), abril para el ejercicio fallero o julio para el foguerer. Compàs lo usa para los periodos de las cuotas (la anual, los semestres y los trimestres empiezan ese mes), la temporada de las licencias, el ejercicio de los cargos de fiesta y las categorías de edad.


### Módulos y avisos

El tipo de entidad activa unos apartados para empezar, pero cada entidad los elige: hay algunas que no tienen escuela, que no venden lotería o que llevan la contabilidad en otro sitio. En el menú **Administración → Módulos y avisos** se puede activar o desactivar cada apartado: escuela, agrupaciones, repertorio, instrumentos, uniformes, cuotas y recibos, lotería, libro de caja, junta y actas, documentos, comunicados, subvenciones, actividades, licencias, entidades federadas y cargos de fiesta.

Un módulo desactivado desaparece del menú, del inicio, de los listados y de las fichas de las personas, pero **no se borra ningún dato**: si se vuelve a activar, todo está como estaba. Personas, socios, listados, importación y administración siempre están activos.

En la misma pantalla se pueden ocultar los **avisos del inicio** que la entidad no quiere ver: la carpeta externa de las copias, la renovación de la junta, los documentos que caducan, el certificado de delitos del profesorado, los instrumentos por devolver, el curso actual, los plazos de las subvenciones, las licencias y revisiones médicas que caducan y las actividades de los próximos días.

![Módulos y avisos](imatges/es/80-moduls.webp)

### Datos de los certificados

La primera vez, en **Configuración → Certificados y hojas de consentimiento**, pon quién firma (normalmente el secretario/a), el cargo y, si queréis el visto bueno, el presidente/a. Pon también la **población** de la entidad, que sale al final del certificado.

![Datos de los certificados en la configuración](imatges/es/40-config-documents.webp)

## 3. Datos del banco y descuentos


El administrador/a lo configura una sola vez en **Resumen económico → Configuración de Economía**:

- **Datos bancarios de la entidad**: el NIF y el IBAN donde se cobran los recibos. Los da el banco cuando se contrata el servicio de adeudos directos SEPA («remesas» o «cuaderno 19»). Compàs calcula el **identificador de acreedor**: comprueba que es el mismo que te ha dado el banco.
- **Descuentos de la escuela**: un porcentaje para quien es socio (el alumno o quien paga) y otro para cada hermano a partir del segundo. Se aplican a las clases, no a la matrícula. En la ficha del alumno se puede poner, además, un **descuento propio** (familia numerosa, beca…) y **quién paga** las clases, si no es el tutor principal.
- **Texto al pie de los recibos**, si la entidad quiere poner uno.

![Configuración de Economía](imatges/es/57-economia-config.webp)

## 4. Copias de seguridad

Todos los datos están en un solo archivo. Compàs hace copias automáticamente:

| Cuándo | Tipo |
|---|---|
| La primera vez que se usa cada día | Automática (diaria) |
| Al cerrar Compàs, si ha habido cambios | Automática (al cerrar) |
| Cuando pulsas **Hacer una copia ahora** | Manual |
| Justo antes de restaurar | Antes de restaurar |
| Justo antes de importar datos | Antes de importar |
| Al abrir por primera vez una versión nueva de Compàs | Antes de actualizar |

Menú **Administración → Copias de seguridad**:

![Copias de seguridad](imatges/es/34-copies.webp)

### Cuántas copias se guardan

Las copias automáticas no se acumulan sin fin. Compàs guarda:

- **todas** las de los últimos 7 días;
- **una por semana** de las últimas 5 semanas;
- **una por mes** hasta el límite que elijas en **Cuánto tiempo se guardan las copias automáticas** (1 año si no se cambia).

Con una copia cada día, eso son unas veinte copias que llegan a un año atrás. Las manuales y las de «antes de restaurar» o «antes de importar» no se borran solas. La misma rotación se hace en la carpeta externa.

La pantalla dice cuánto ocupa la base de datos (y cuánto de eso son documentos) y cuánto ocupan todas las copias. Si la base de datos pasa de 200 MB, normalmente por los documentos archivados, Compàs avisa: cada copia ocupa lo mismo, así que hay que comprobar que hay espacio suficiente en el ordenador y en la carpeta externa.

### Configurar la carpeta externa (muy importante)

Si el ordenador se estropea o lo roban, las copias que hay dentro se pierden con él. Hay que guardarlas también fuera:

1. Conecta un **disco o lápiz USB**, o elige una carpeta **sincronizada con la nube** (OneDrive, Google Drive…).
2. Crea en ella una carpeta, por ejemplo `Copias Compàs`, y copia su ruta desde el Explorador de Windows (por ejemplo `E:\Copias Compàs`).
3. En **Configuración de las copias**, pega la ruta en **Carpeta externa**.
4. Pulsa **Guardar**.

![Carpeta externa de copias](imatges/es/35-copies-carpeta.webp)

A partir de ahora, cada copia se guarda también en esta carpeta. Si no está disponible (por ejemplo, el USB no está conectado) o hace más de 7 días que no se guarda nada en ella, el inicio lo avisa.

### Lista de copias, restaurar y eliminar

![Lista de copias](imatges/es/36-copies-llista.webp)

- **Descargar**: descarga esa copia.
- **Restaurar** (solo el administrador/a): sustituye todos los datos actuales por los de la copia. Antes, Compàs guarda una copia de los datos actuales («Antes de restaurar»), de manera que siempre se puede volver atrás. Después hay que volver a entrar.
- **Eliminar** (solo el administrador/a): borra la copia.

Para restaurar una copia guardada fuera (por ejemplo, desde el USB), usa **Restaurar desde un archivo**, al final de la página.

> Las copias contienen datos personales, también de menores. Guárdalas en un lugar seguro.

### Copias externas cifradas

Si la carpeta externa es un pendrive o la nube, conviene cifrarlas: si se pierde el USB, nadie podrá abrir los datos sin la contraseña.

1. En **Configuración de las copias**, marca **Cifra con contraseña las copias de la carpeta externa**.
2. Escribe una contraseña (al menos 8 caracteres) dos veces y pulsa **Guardar**.

![Copias externas cifradas](imatges/es/54-copies-xifrat.webp)

A partir de ahora, las copias de la carpeta externa terminan en `.xifrat`. Las del ordenador no se cifran. Para restaurar una, en **Restaurar desde un archivo** elige el archivo `.xifrat` y escribe la contraseña.

> **Apunta la contraseña en papel** junto con el código de recuperación. Si se olvida, las copias cifradas no se pueden recuperar.

## 5. Usuarios y contraseñas

### Cambiar tu contraseña

Cada usuario puede cambiar la suya cuando quiera:

1. En el menú, abajo del todo, pulsa **Cuenta y contraseña** (o haz clic en tu nombre de usuario, arriba a la derecha).
2. Escribe la **contraseña actual**.
3. Escribe la **contraseña nueva** dos veces (al menos 8 caracteres).
4. Pulsa **Guardar**.

Si no recuerdas la actual, no la puedes cambiar aquí: el usuario/a debe pedir una nueva al administrador/a, y el administrador/a puede recuperar el acceso desde la entrada (ver más abajo).

![Mi cuenta](imatges/es/38-meu-compte.webp)

### Gestionar las cuentas (administrador/a)

Menú **Administración → Usuarios**. Abre una cuenta para:

- cambiarle el **rol** (administrador/a o usuario/a);
- ponerle una **contraseña nueva** si la ha olvidado (déjala en blanco para no cambiarla);
- desactivarla (desmarca **Activo**) o eliminarla.

![Editar un usuario](imatges/es/37-usuari.webp)

Siempre debe quedar al menos un administrador activo.

### Si el administrador/a olvida la contraseña

1. En la pantalla de entrada, pulsa **¿Eres el administrador/a? Recupera el acceso**.
2. Escribe tu **usuario**, el **código de recuperación** que guardaste y la **contraseña nueva** dos veces.
3. Pulsa **Cambia la contraseña**. Entrarás directamente en el programa.
4. Compàs muestra un **código de recuperación nuevo**, porque el anterior ya no vale. Imprímelo y guárdalo en lugar del antiguo.

![He olvidado la contraseña](imatges/es/00b-recuperacio.webp)

**Si no encuentras el código**, pulsa **No tengo el código de recuperación** en la misma página y sigue los pasos:

1. Abre el Explorador de archivos y ve a la carpeta de datos de Compàs. La página muestra la ruta exacta: cópiala en la barra de direcciones del Explorador.
2. Crea un archivo nuevo (botón derecho → **Nuevo → Documento de texto**) y ponle de nombre `recupera-acces`.
3. Vuelve a la página de Compàs y recárgala (tecla **F5**). Ya no pedirá el código: escribe el usuario y la contraseña nueva.

El archivo solo vale durante una hora y se borra solo al usarlo. Este sistema es seguro porque solo puede crear el archivo quien ya tiene acceso al ordenador y a los datos.

### Código de recuperación

Si has perdido el código o crees que alguien lo ha visto, ve a **Administración → Usuarios** y, abajo, pulsa **Genera un código nuevo**. El anterior deja de valer. Si Compàs se instaló antes de la versión 1.3, la pantalla de inicio avisa de que no hay código hasta que lo generes.

Todas las recuperaciones, también las que fallan, quedan en el **Registro de actividad**.

### Registro de actividad

Menú **Administración → Registro de actividad** (solo el administrador/a): quién ha creado, modificado o eliminado cada cosa, las entradas al programa, los boletines impresos y las copias.

## 6. Actualizar Compàs

1. En **Ayuda**, pulsa **¿Hay versión nueva?**. Se abre en el navegador la página de la última versión (Compàs no se conecta a Internet ni comprueba nada solo). Debajo del botón se ve la versión que tienes.
2. Si la de la página es más alta, descarga el instalador (en Linux, el paquete `.deb` o el `.tar.gz`) e instálalo encima, sin desinstalar el anterior. Los datos y la configuración se conservan.
3. Abre Compàs. La primera vez, antes de preparar la base de datos para la versión nueva, Compàs hace una copia («Antes de actualizar»). Si algo fallara, los datos se quedarían como estaban y la copia estaría en la lista.

Qué trae cada versión se puede ver en **Ayuda → Qué cambia en cada versión**.

## 7. Preguntas frecuentes

**El administrador/a ha olvidado la contraseña.** Pulsa **Recupera el acceso** en la pantalla de entrada y usa el código de recuperación. Si no lo encuentras, también hay una manera sin código (apartado 5).

**¿Cómo paso Compàs a otro ordenador?** Instala Compàs en el ordenador nuevo y, en **Copias de seguridad → Restaurar desde un archivo**, carga la última copia.

**¿Dónde están los datos?** En la carpeta `data` del programa (`data\compas.db`). La ruta exacta se ve en **Configuración**, abajo.

## Red local y consulta limitada (2.3)

La [guía LAN](xarxa-local.md#compàs-22-red-local-y-usuarios) explica cómo activar el servidor, crear cuentas sin acentos, usar el rol Consultor y gestionar las copias. El consultor no accede a economía, IBAN, importes, exportaciones, informes imprimibles, archivos ni copias. La consulta parcial de escuela y agrupaciones no incluye todas las operaciones de gestión de la tabla. En LAN, cerrar el navegador solo cierra el cliente; el servidor continúa hasta que lo detenga la persona responsable. Si una ficha ha cambiado desde que la abriste, recárgala antes de guardar.
