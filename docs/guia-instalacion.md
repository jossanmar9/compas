# Guía de instalación de Compàs

## Requisitos

- Windows 10 u 11 de 64 bits.
- Unos 100 MB de disco.
- No hace falta Internet, ni servidor, ni instalar PHP o bases de datos: todo va dentro.

## Opción A: instalador (recomendada)

1. Descarga `Compas-X.Y.Z-installador.exe` de la página de *Releases* del repositorio.
2. Ejecútalo. No pide permisos de administrador: se instala para el usuario actual en `%LOCALAPPDATA%\Programs\Compas`.
3. Windows SmartScreen puede avisar de que el programa no es conocido (no está firmado digitalmente). Pulsa **Más información → Ejecutar de todas formas**.
4. Al terminar, abre **Compàs** desde el menú Inicio o el escritorio.

Para **actualizar**, ejecuta el instalador de la versión nueva con Compàs cerrado. Los datos y el archivo `compas.ini` se conservan.

Si lo **desinstalas**, la carpeta `data` no se borra.

## Opción B: portable (sin instalar)

1. Descarga `Compas-X.Y.Z-portable.zip` y descomprímelo donde quieras (por ejemplo, `Documentos\Compas` o un lápiz USB).
2. Abre `Compas.exe`.

Para actualizar, descomprime la versión nueva y copia en ella la carpeta `data` de la antigua (con Compàs cerrado).

## Cómo funciona

Al abrir `Compas.exe` aparece una ventana pequeña y se abre el navegador en `http://localhost:8740`. **Mientras la ventana esté abierta, Compàs funciona.** Al cerrarla, se detiene.

La primera vez pide el nombre de la sociedad y crea la cuenta de administrador. Apunta la contraseña: si se pierde y no hay ningún otro administrador, no se puede recuperar.

## Carpeta del programa

```
Compas\
├── Compas.exe        lanzador
├── compas.ini        configuración del lanzador
├── app\  src\        el programa (PHP, HTML, CSS, JavaScript)
├── php\              PHP portable
├── docs\             manuales y guías
└── data\             LOS DATOS: compas.db, copias automáticas y registros
```

## Configuración (`compas.ini`)

Ábrelo con el Bloc de notas. Después de cambiarlo, cierra y vuelve a abrir Compàs.

- `port = 8740` — cámbialo si otro programa ya lo usa.
- `xarxa = 0` — solo se puede entrar desde este ordenador.
- `navegador = 1` — abre el navegador al arrancar.

### Usarlo desde otros ordenadores de la red

Si quieres que el profesorado o la secretaría entren desde sus equipos (o desde el móvil, con la wifi del local):

1. Pon `xarxa = 1` en `compas.ini` y vuelve a abrir Compàs.
2. Windows pedirá permiso al **cortafuegos**: acepta solo para **redes privadas**.
3. La ventana de Compàs muestra la dirección para el resto de equipos, por ejemplo `http://192.168.1.20:8740/`.

Ten en cuenta:

- El ordenador con Compàs debe estar **encendido y con la ventana abierta**.
- La conexión dentro de la red local **no va cifrada** (HTTP). Hazlo solo en una red de confianza, nunca en una wifi abierta. No abras el puerto en el router hacia Internet.
- Conviene que el ordenador tenga siempre la misma IP (se puede reservar en el router).

## Copias de seguridad

Compàs hace una copia automática diaria en `data\copies`. Además, descarga una periódicamente desde **Copias de seguridad** y guárdala fuera del ordenador.

## Solución de problemas

| Problema | Qué hacer |
|---|---|
| «El puerto 8740 ya está en uso» | Cambia el `port` en `compas.ini`. |
| El navegador no se abre | Haz clic en el enlace de la ventana de Compàs o escribe `http://localhost:8740` en el navegador. |
| «El servidor se ha detenido» | Mira `data\servidor.log` y `data\php-errors.log`. |
| No arranca y dice que faltan archivos | Vuelve a instalar o a descomprimir. El antivirus puede haber puesto en cuarentena `php.exe`. |
| Falta `VCRUNTIME140.dll` | Instala el *Microsoft Visual C++ Redistributable* (x64) desde la web de Microsoft. |
| Otros equipos no pueden entrar | Comprueba `xarxa = 1`, el cortafuegos de Windows (red privada) y que estén en la misma red. |

## Para desarrollar

Con PHP 8.1 o posterior (con `pdo_sqlite` y `mbstring`) instalado:

```
php -S 127.0.0.1:8740 -t app app/index.php
```

Los datos se guardan en `data/` (o en la carpeta que indique la variable de entorno `COMPAS_DADES`).

Para publicar una versión para Windows, sube una etiqueta en minúscula (`v1.0.0`) y GitHub Actions construye el instalador y el zip portable.
