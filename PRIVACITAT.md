# Privacitat de Compàs

**En resum: les dades queden al servidor de l’entitat i no s’envien a serveis externs. En mode LAN les consulten els clients autoritzats de la xarxa pròpia.**

## El programa

- En mode compartit, els clients de la xarxa privada usen HTTPS i un nom `.local` anunciat per mDNS, sense IP fixa ni serveis externs. Cada equip confirma una vegada el certificat propi de l’associació. PHP només escolta internament en `127.0.0.1`. No configura accés públic a Internet. Vegeu la [guia LAN](docs/xarxa-local.md).
- No es connecta a Internet per a res: no envia estadístiques d'ús ni informes d'errors, no comprova actualitzacions, no mostra publicitat i no carrega tipografies, imatges ni programes de fora.
- La finestra de Compàs mostra només les pàgines del mateix ordinador amb el component **WebView2** de Windows (a Linux, WebKitGTK); els enllaços de fora s'obrin al navegador habitual. Compàs hi desactiva SmartScreen i l'emplenament automàtic, i no hi envia res. WebView2 forma part de Windows: Microsoft l'actualitza i en pot rebre dades de diagnòstic segons la configuració de privacitat de Windows, igual que amb la resta del sistema.
- Les dades (persones, socis, alumnat, notes, agrupacions, instruments, rebuts i comptes bancaris de les domiciliacions…) es guarden en un fitxer de l'ordinador: `data\compas.db`, dins de la carpeta del programa (a Linux, `~/.local/share/compas/dades`).
- Les còpies de seguretat es guarden a l'ordinador i, si l'entitat ho configura, en la carpeta externa que trie: un llapis USB, un disc extern, una unitat de xarxa o una carpeta sincronitzada amb el núvol. En este últim cas, les còpies passen pel servei que l'entitat haja triat (OneDrive, Google Drive…), amb les seues condicions. Per això Compàs permet xifrar-les amb contrasenya.
- Les remeses bancàries són un fitxer que Compàs prepara i que la persona responsable puja a la banca electrònica de l'entitat. Compàs no es connecta al banc.
- Els documents de l'entitat que s'hi arxiven (estatuts, actes, contractes, pòlisses…) es guarden dins del mateix fitxer de dades i, per tant, també en les còpies de seguretat.
- Compàs no envia correus: els comunicats només preparen la llista d'adreces perquè la persona responsable les copie al correu de l'entitat.
- Els documents que s'imprimeixen o es guarden en PDF o CSV (butlletins, llistats, certificats…) queden on els guarde la persona que els fa.

## Qui és responsable de les dades

- L'**entitat** que utilitza Compàs és la **responsable del tractament** de les dades personals que hi introdueix. Ha de complir el Reglament general de protecció de dades (RGPD) i la Llei orgànica 3/2018 (LOPDGDD): informar les persones, demanar els consentiments que calguen, atendre els drets d'accés, rectificació, supressió, etc.
- El **desenvolupador de Compàs** (José Vicente Sanjuan) **no té accés** a les dades de cap entitat: no les rep, no les guarda i no les tracta. Per tant, no és encarregat del tractament.
- Compàs es distribueix tal com és, sense cap garantia: l'autor no es fa responsable de la pèrdua de dades ni de cap altre dany derivat del seu ús. Cada entitat ha de fer còpies de seguretat periòdiques en un lloc extern.
- Compàs ajuda a complir la normativa: tres rols amb permisos diferents, contrasenyes guardades amb hash, registre d'activitat, còpies externes xifrades i control del consentiment d'imatge.

## La web i les descàrregues

- La web de presentació ([jossanmar9.github.io/compas](https://jossanmar9.github.io/compas/)), el codi i les descàrregues estan allotjats a **GitHub**. Com qualsevol servidor, GitHub pot registrar l'adreça IP i dades tècniques de qui visita la web o baixa el programa. Vegeu la [declaració de privacitat de GitHub](https://docs.github.com/site-policy/privacy-policies/github-general-privacy-statement).
- La web no utilitza galetes, ni analítica, ni tipografies o programes de tercers. Les tipografies, que són lliures (llicència SIL Open Font License), es serveixen des de la mateixa web.
- El que s'escriu en una incidència de GitHub és públic. No hi poseu dades de persones, ni en el text ni en les captures.
- El formulari de contacte de la web és de **Google Forms**: el que s'hi escriu (nom, correu, entitat i missatge) es guarda al compte de Google de Compàs i només s'utilitza per a contestar. Google el tracta d'acord amb la seua [política de privadesa](https://policies.google.com/privacy). Si ho preferiu, podeu escriure directament a compas.lliure@gmail.com. Per a demanar que s'esborren les vostres dades, escriviu a eixa mateixa adreça.

## Contacte

Compàs és un projecte de José Vicente Sanjuan. Si teniu dubtes sobre esta declaració, escriviu a [compas.lliure@gmail.com](mailto:compas.lliure@gmail.com) o obriu una [incidència](https://github.com/jossanmar9/compas/issues/new/choose).

*Darrera actualització: octubre de 2026.*

---

# Privacidad de Compàs

**En resumen: los datos permanecen en el servidor de la entidad y no se envían a servicios externos. En modo LAN los consultan los clientes autorizados de la red propia.**

## El programa

- En modo compartido, los clientes de la red privada usan HTTPS y un nombre `.local` anunciado por mDNS, sin IP fija ni servicios externos. Cada equipo confirma una vez el certificado propio de la asociación. PHP solo escucha internamente en `127.0.0.1`. No configura acceso público a Internet. Véase la [guía LAN](docs/xarxa-local.md).
- No se conecta a Internet para nada: no envía estadísticas de uso ni informes de errores, no comprueba actualizaciones, no muestra publicidad y no carga tipografías, imágenes ni programas de fuera.
- La ventana de Compàs muestra solo las páginas del mismo ordenador con el componente **WebView2** de Windows (en Linux, WebKitGTK); los enlaces de fuera se abren en el navegador habitual. Compàs desactiva en ella SmartScreen y el autocompletado, y no envía nada. WebView2 forma parte de Windows: Microsoft lo actualiza y puede recibir datos de diagnóstico según la configuración de privacidad de Windows, igual que con el resto del sistema.
- Los datos (personas, socios, alumnado, notas, agrupaciones, instrumentos, recibos y cuentas bancarias de las domiciliaciones…) se guardan en un archivo del ordenador: `data\compas.db`, dentro de la carpeta del programa (en Linux, `~/.local/share/compas/dades`).
- Las copias de seguridad se guardan en el ordenador y, si la entidad lo configura, en la carpeta externa que elija: un pendrive, un disco externo, una unidad de red o una carpeta sincronizada con la nube. En este último caso, las copias pasan por el servicio que la entidad haya elegido (OneDrive, Google Drive…), con sus condiciones. Por eso Compàs permite cifrarlas con contraseña.
- Las remesas bancarias son un archivo que Compàs prepara y que la persona responsable sube a la banca electrónica de la entidad. Compàs no se conecta al banco.
- Los documentos de la entidad que se archivan (estatutos, actas, contratos, pólizas…) se guardan dentro del mismo archivo de datos y, por tanto, también en las copias de seguridad.
- Compàs no envía correos: los comunicados solo preparan la lista de direcciones para que la persona responsable las copie en el correo de la entidad.
- Los documentos que se imprimen o se guardan en PDF o CSV (boletines, listados, certificados…) quedan donde los guarde la persona que los hace.

## Quién es responsable de los datos

- La **entidad** que usa Compàs es la **responsable del tratamiento** de los datos personales que introduce. Debe cumplir el Reglamento general de protección de datos (RGPD) y la Ley Orgánica 3/2018 (LOPDGDD): informar a las personas, pedir los consentimientos necesarios, atender los derechos de acceso, rectificación, supresión, etc.
- El **desarrollador de Compàs** (José Vicente Sanjuan) **no tiene acceso** a los datos de ninguna entidad: no los recibe, no los guarda y no los trata. Por tanto, no es encargado del tratamiento.
- Compàs se distribuye tal cual, sin ninguna garantía: el autor no se hace responsable de la pérdida de datos ni de ningún otro daño derivado de su uso. Cada entidad debe hacer copias de seguridad periódicas en un lugar externo.
- Compàs ayuda a cumplir la normativa: tres roles con permisos diferentes, contraseñas guardadas con hash, registro de actividad, copias externas cifradas y control del consentimiento de imagen.

## La web y las descargas

- La web de presentación ([jossanmar9.github.io/compas](https://jossanmar9.github.io/compas/)), el código y las descargas están alojados en **GitHub**. Como cualquier servidor, GitHub puede registrar la dirección IP y datos técnicos de quien visita la web o descarga el programa. Véase la [declaración de privacidad de GitHub](https://docs.github.com/site-policy/privacy-policies/github-general-privacy-statement).
- La web no usa cookies, ni analítica, ni tipografías o programas de terceros. Las tipografías, que son libres (licencia SIL Open Font License), se sirven desde la propia web.
- Lo que se escribe en una incidencia de GitHub es público. No pongáis datos de personas, ni en el texto ni en las capturas.
- El formulario de contacto de la web es de **Google Forms**: lo que se escribe en él (nombre, correo, entidad y mensaje) se guarda en la cuenta de Google de Compàs y solo se usa para responder. Google lo trata según su [política de privacidad](https://policies.google.com/privacy). Si lo preferís, podéis escribir directamente a compas.lliure@gmail.com. Para pedir que se borren vuestros datos, escribid a esa misma dirección.

## Contacto

Compàs es un proyecto de José Vicente Sanjuan. Si tenéis dudas sobre esta declaración, escribid a [compas.lliure@gmail.com](mailto:compas.lliure@gmail.com) o abrid una [incidencia](https://github.com/jossanmar9/compas/issues/new/choose).

*Última actualización: octubre de 2026.*

---

# Privacy statement (English)

**Compàs does not send entity data to external services.** By default it listens only on `127.0.0.1`; optional LAN mode serves authenticated clients from the entity’s private network using HTTPS and an mDNS .local hostname, without a fixed IP. Each client explicitly trusts the association’s own certificate once; PHP only listens on internal loopback. This mode is intended for a controlled, trusted LAN. The database stays on the entity’s server. See the [LAN guide](docs/xarxa-local.md). It has no telemetry, crash reporting, update checks, advertising or remote resources. Its window uses the Windows WebView2 component (WebKitGTK on Linux) only to display local pages, with SmartScreen and autofill disabled; WebView2 is part of Windows and is updated by Microsoft under the Windows privacy settings. All data, including the bank accounts used for direct debits, is stored locally in `data\compas.db`. Direct debit files are prepared locally and uploaded to the bank by the user; Compàs never connects to the bank. Society documents archived in Compàs are stored inside the same database file and its backups. Compàs never sends e-mail; it only prepares address lists. Backups are written only to the local computer and, if the user configures it, to an external folder of their choice (optionally encrypted with a password). The music society using Compàs is the data controller; the developer (José Vicente Sanjuan) has no access to any data. Compàs is provided as is, without any warranty; the author is not liable for any data loss or other damage arising from its use, and each society must keep regular external backups. The website and downloads are hosted on GitHub ([GitHub Privacy Statement](https://docs.github.com/site-policy/privacy-policies/github-general-privacy-statement)); the website uses no cookies, analytics or third-party resources. The contact form linked from the website is a Google Form: answers are stored in the Compàs Google account and used only to reply (contact: compas.lliure@gmail.com).
