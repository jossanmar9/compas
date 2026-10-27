# Compàs 2.2: xarxa local i usuaris

## Un ordinador servidor, diversos navegadors

Per defecte, Compàs continua funcionant només en un ordinador. El mode LAN és opcional: Compàs i `compas.db` es queden al servidor de l'entitat i les altres persones hi entren amb el navegador. **No compartisques el fitxer SQLite per una carpeta de xarxa, NAS o sincronització al núvol.**

### Activar el mode LAN

1. Actualitza el servidor i comprova en mode local que les dades i les còpies són correctes. Crea els comptes a **Administració → Usuaris**, un per persona.
2. Assigna una IPv4 privada estable al servidor, preferiblement amb una reserva al router. Exemple: `192.168.1.20`.
3. Tanca Compàs i edita `compas.ini`. A Windows està a la carpeta del programa. A Linux pots posar estes opcions a `~/.config/compas/compas.ini`:

```ini
port = 8740
lan = 1
adreca_lan = 192.168.1.20
```

4. Obri Compàs al servidor. El llançador escolta en **eixa adreça**, no en totes les interfícies. A Windows queda obert el panell del servidor; a Linux, el terminal. En Linux, una adreça que no pertany a l'equip farà fallar l'arrancada.
5. Si el tallafoc bloqueja l'accés, la persona responsable de la xarxa ha de permetre el port TCP 8740 només des dels equips o la subxarxa privada autoritzada. Compàs no modifica el tallafoc automàticament. No òbrigues este port al router cap a Internet.
6. En els altres equips, obri `http://192.168.1.20:8740` i entra amb el compte propi. No cal instal·lar Compàs en cada client. Tots els equips han d'arribar a la IP del servidor; si canvia, actualitza `adreca_lan` i les adreces dels clients.

El mode inclòs utilitza HTTP: **no xifra el trànsit de la xarxa**. Utilitza'l només en una LAN de confiança i controlada, sense convidats o equips desconeguts. Per a ús des d'una xarxa no fiable, cal un desplegament HTTPS administrat i revisat específicament; no n'hi ha prou d'obrir ports o afegir un proxy sense revisar la identificació de les peticions locals. No està preparat per a publicar-lo directament a Internet.

El servidor integrat atén les peticions en sèrie a Windows: hi pot haver diverses persones connectades, però una operació llarga pot fer esperar les altres. En esta versió, el mode LAN està pensat per a grups menuts en un entorn controlat; no és un servidor per a càrrega elevada. SQLite continua admetent una sola escriptura alhora i gestiona les esperes. Les proves en Linux també executen peticions amb diversos processos de PHP.

Per a tornar al funcionament només local, posa `lan = 0` i reinicia Compàs. Si uses directament PHP en lloc dels llançadors, configura `COMPAS_LAN=1`, `COMPAS_LAN_IP=192.168.1.20` i fes-lo escoltar en eixa mateixa IP. `COMPAS_HOSTS` permet afegir noms de servidor explícits, separats per comes, sense eliminar la validació de l'origen. No es confia en `X-Forwarded-For` ni en altres capçaleres de proxy.

### Obrir, tancar, copiar i restaurar

- Tancar el navegador o eixir del compte només acaba la sessió del client; **no atura el servidor**.
- Mantín encés el servidor i obert el seu panell o terminal mentre altres persones treballen. **Atura i tanca** (Windows) o Ctrl+C (Linux) desconnecta tots els clients i intenta fer una còpia abans de parar. Avisa primer les altres persones.
- En LAN hi ha còpia automàtica diària i, si hi ha canvis, una altra quan arriba una petició després de 15 minuts des de l'última còpia automàtica. No és un temporitzador que treballe sense peticions: no farà una còpia mentre ningú use el programa. També es poden fer còpies manuals.
- Les còpies es serialitzen per a evitar que coincidisquen i no se sobreescriuen encara que es facen en el mateix segon. La base de dades es copia amb una instantània consistent.
- **La restauració està bloquejada en mode LAN.** Avisa els usuaris, atura el servidor, posa `lan = 0`, obri'l al servidor i restaura la còpia. Després, torna a activar LAN. Les sessions anteriors a la restauració deixen de ser vàlides.
- La configuració inicial i la recuperació d'administrador només es poden fer des del mateix servidor. El testimoni de còpia en tancar també es restringix a peticions del servidor.

## Comptes i permisos

| Rol | Accés |
|---|---|
| Administrador/a | Gestió completa, comptes, configuració i còpies; restauració en mode local. |
| Usuari/ària de gestió | Gestió diària i còpies, sense configurar el sistema ni gestionar comptes o restauracions. |
| Consultor/a | Consulta de persones, socis, escola, agrupacions, instruments i préstecs, amb imports i camps bancaris amagats. Sense modificar dades, economia, IBAN, exportacions, informes imprimibles, documents descarregables ni còpies. |

El consultor pot canviar la pròpia contrasenya i l'idioma. Els permisos es comproven al servidor també si s'escriu una URL o s'envia un formulari a mà. Els noms de compte són únics sense distingir majúscules i tenen de 3 a 40 caràcters: **a–z, números, punt, guió i guió baix; sense accents, dièresi, ñ, ç ni espais**. Els noms de les persones continuen admetent accents.

En actualitzar, els noms de compte antics que no complixen la regla s'adapten, conservant l'identificador, el rol i la contrasenya. Si el resultat ja existix, s'afig `-2`, `-3`… L'administrador veu els canvis a **Usuaris** i al registre d'activitat; ha de comunicar el nom nou a cada persona. Abans de migrar, Compàs fa la còpia d'actualització habitual.

Les sessions LAN caduquen després de 30 minuts sense activitat. Un canvi de contrasenya invalida les altres sessions d'eixe compte. Cinc intents fallits bloquegen temporalment el compte afectat; també hi ha un límit per IP per a frenar intents sobre molts comptes, sense un bloqueig global de tota l'entitat.

Si dues persones editen una mateixa fitxa amb els formularis generals, un formulari antic rep un avís de conflicte. Recarrega la fitxa, revisa què ha canviat i torna a introduir els teus canvis. No premis repetidament «Desa»: no se sobreescriu el primer canvi sense revisar-lo. Esta comprovació és dels formularis generals; les operacions específiques de rebuts i talonaris mantenen les seues transaccions i proteccions de numeració.

---

# Compàs 2.2: red local y usuarios

Por defecto, Compàs sigue funcionando solo en el ordenador donde se instala. Para varios equipos, el programa y SQLite permanecen en **un servidor de la entidad** y los clientes acceden por navegador. No compartas `compas.db` por una carpeta de red, NAS o sincronización en la nube.

## Activar LAN

1. Actualiza y comprueba las copias en modo local. Crea una cuenta por persona en **Administración → Usuarios**.
2. Reserva una IPv4 privada fija del servidor, por ejemplo `192.168.1.20`.
3. Cierra Compàs y configura `port = 8740`, `lan = 1` y `adreca_lan = 192.168.1.20` en `compas.ini`. En Linux puedes usar `~/.config/compas/compas.ini`.
4. Abre Compàs en el servidor. Mantén abierto su panel (Windows) o terminal (Linux). Autoriza el puerto TCP 8740 en el cortafuegos solo para los equipos o la subred privada autorizada; el programa no cambia el cortafuegos.
5. En cada cliente, abre `http://192.168.1.20:8740` e inicia sesión. No necesitas instalar Compàs en los clientes. Para desactivar LAN, cambia `lan = 0` y reinicia.

El modo incluido utiliza HTTP, **sin cifrado del tráfico**. Está pensado para grupos pequeños en una LAN de confianza y controlada, sin invitados ni equipos desconocidos. No abras el puerto del router a Internet. Para redes no fiables hace falta un despliegue HTTPS administrado y revisado; añadir un proxy sin revisar las peticiones locales no es suficiente. El servidor integrado procesa las peticiones en serie en Windows: las operaciones largas pueden hacer esperar a las demás personas.

Para ejecución directa con PHP, usa `COMPAS_LAN=1`, `COMPAS_LAN_IP=192.168.1.20` y la misma IP al iniciar el servidor. `COMPAS_HOSTS` añade nombres explícitos; no se confía en cabeceras de proxy como `X-Forwarded-For`.

## Sesiones, copias y permisos

Cerrar el navegador o salir de la cuenta no detiene el servidor. **Detener y cerrar** en su panel, o Ctrl+C en Linux, desconecta a todos e intenta hacer una copia. Avisa antes a los usuarios.

Además de la copia diaria, si hay cambios se hace una copia al recibir una petición después de 15 minutos desde la última copia automática. Sin peticiones no hay temporizador. Las copias manuales siguen disponibles. **Para restaurar, detén LAN y hazlo en modo local en el servidor**; después reactiva LAN. Las sesiones anteriores a la restauración caducan. La configuración inicial, la recuperación de administrador y la petición de copia al cerrar se restringen al servidor.

Hay tres roles: **administrador/a** (todo), **usuario/a de gestión** (gestión diaria y copias, sin configuración ni cuentas) y **consultor/a** (consulta de personas, socios, escuela, agrupaciones, instrumentos y préstamos, sin importes ni campos bancarios). El consultor no puede modificar datos, acceder a economía, ver IBAN, exportar, generar informes imprimibles, descargar documentos ni obtener copias; sí puede cambiar su contraseña e idioma. Los permisos también se aplican a peticiones directas.

Los nombres de usuario tienen de 3 a 40 caracteres ASCII: **a–z, números, punto, guion y guion bajo; sin acentos, diéresis, ñ, ç ni espacios**. No se pueden repetir aunque cambien las mayúsculas. Los nombres de personas admiten acentos. Al actualizar, los nombres de cuenta antiguos incompatibles se adaptan sin perder cuentas ni contraseñas; las colisiones reciben sufijos `-2`, `-3`… Los cambios aparecen en **Usuarios** y en el registro; comunica los nombres nuevos.

Las sesiones LAN caducan tras 30 minutos sin actividad. Cambiar una contraseña invalida las otras sesiones de esa cuenta. Hay límites de intentos por cuenta y por IP, sin un bloqueo global. Si otra persona ha modificado una ficha, los formularios generales rechazan el envío antiguo: recarga y revisa los cambios antes de guardar. Los recibos y talonarios mantienen sus protecciones específicas de numeración.
