# Compàs 2.3 · Xarxa local amb HTTPS

## Una adreça per a tota l’associació

El servidor guarda Compàs i la base de dades. La resta entra en **https://compas.local/** amb el navegador i un compte propi. **No cal IP fixa, reserva DHCP ni compartir el fitxer SQLite.** El nom local s’anuncia amb mDNS i s’adapta als canvis de la xarxa. El servidor ha d’estar encés i a la mateixa xarxa privada.

Per defecte, Compàs continua funcionant només en un ordinador. El mode compartit és opcional. Els paquets de servidor d’esta versió són per a Windows x64 i Linux x86-64.

## Preparar el servidor una vegada

### Windows

1. Obri Compàs i prem **Xarxa local / HTTPS…** en el panell del servidor o en el menú de la icona de la safata.
2. Marca **Compartir Compàs en la xarxa privada**. Deixa `compas.local` o tria un altre nom acabat en `.local` si ja hi ha un altre servidor a la xarxa.
3. Prem **Desa i reinicia**. Confirma la regla del tallafoc; Windows demanarà permís d’administrador només per a preparar eixa regla. Es permeten HTTPS (TCP 443) i descobriment (UDP 5353) en el perfil **Privat**, des de la subxarxa local, només per al servidor de Compàs. La connexió de xarxa de Windows ha d’estar marcada com a privada.
4. En arrancar, Compàs genera el seu certificat i et mostra l’empremta SHA-256. Confirma que vols confiar en el certificat del teu servidor per al teu usuari. Este pas no executa PHP com a administrador.
5. Crea un compte per persona a **Administració → Usuaris**. Deixa el panell del servidor obert.

### Linux

Executa **`compas --xarxa`**, o l’assistent **Compàs · Xarxa local HTTPS** si has fet la instal·lació d’usuari. Tria el nom i confirma els passos: permís per a obrir el port 443, confiança en el certificat i, si uses UFW actiu, regles només per a adreces privades. Pot demanar `sudo`. Necessita `libcap2-bin`, `ca-certificates` i `openssl` (Debian/Ubuntu). Torna a obrir Compàs després de configurar-lo.

L’assistent guarda les opcions a `~/.config/compas/compas.ini`. En una actualització del paquet `.tar.gz` pot caldre repetir l’assistent perquè el permís del nou executable no es conserva en substituir-lo.

## Preparar cada equip client una vegada

1. Al servidor Windows, prem **Preparar altres equips**. A Linux, el paquet està a `~/.local/share/compas/dades/https/Client-Compas.zip`.
2. Passa el ZIP directament als equips de l’associació, per exemple amb un USB. No conté dades ni claus privades: només el certificat públic, la URL i l’assistent.
3. Compara l’empremta SHA-256 amb la que mostra el servidor. **No confies en un certificat si no coneixes el servidor o l’empremta no coincidix.**
4. Windows: descomprimeix-lo i obri **Connecta-Compas.exe**. Confirma la confiança per al teu usuari. Linux: executa **`bash connecta.sh`** i confirma la instal·lació del certificat. Els navegadors amb magatzem propi poden requerir importar `compas-ca.crt` com a autoritat de confiança.
5. A partir d’ara, només cal obrir **https://compas.local/** i entrar amb el compte propi. No cal instal·lar l’aplicació completa en els clients.

`.local` necessita que la xarxa permeta mDNS. En Linux, el client necessita Avahi/libnss-mdns o una configuració equivalent de systemd-resolved. Una xarxa de convidats, l’aïllament Wi-Fi, una VPN o una política que bloquege multicast poden impedir el descobriment. HTTPS no evita eixes restriccions; la persona responsable de la xarxa les ha de revisar. No es configura el router ni s’obrin ports cap a Internet.

## Què fa HTTPS

El trànsit entre navegador i servidor va xifrat. Cada instal·lació genera una autoritat local pròpia; no usa certificats compartits entre associacions ni serveis externs. El certificat del servidor es renova automàticament amb la mateixa autoritat. Les claus es guarden protegides a la carpeta `https` de les dades: **no les compartisques ni les puges a GitHub**. Conserva-les en una còpia externa protegida si vols recuperar la mateixa confiança després de perdre el servidor; les còpies `.db` no inclouen eixa carpeta. Si es perden o canvien les claus, cal confiar en el nou certificat en els clients.

PHP només escolta en `127.0.0.1`. El servidor HTTPS li passa la identitat del client amb un secret intern, i descarta les capçaleres que el navegador intenta falsejar. Les galetes de sessió del mode compartit són Secure, HttpOnly i SameSite. El programa continua pensat per a una xarxa privada de confiança i grups menuts; no és un allotjament públic d’Internet. A Windows, PHP atén les peticions en sèrie: una operació llarga pot fer esperar altres usuaris.

## Comptes, còpies i tancament

- **Administrador/a:** gestió completa, comptes i configuració.
- **Administratiu/iva:** gestió diària, sense administrar comptes ni restaurar.
- **Consultor/a:** consulta limitada de persones, socis, escola, agrupacions i material; sense economia, IBAN, exports, documents descarregables ni còpies.
- Noms de compte únics, de 3 a 40 caràcters ASCII (`a-z`, `A-Z`, números, punt, guió i guió baix), sense accents ni dièresi.
- Tancar un navegador no atura el servidor. **Atura i tanca** o Ctrl+C desconnecta tots els clients i intenta fer una còpia abans de parar.
- Hi ha còpia diària i, si hi ha canvis, periòdica en ús. Mantín també còpies externes.
- Restaurar una còpia o buidar dades de gestió exigeix desactivar primer el mode compartit. A Windows, desmarca l’opció en l’assistent; a Linux, executa `compas --xarxa` i tria no compartir. Torna a obrir Compàs.

Configuració manual equivalent:

```ini
lan = 1
nom_lan = compas.local
port_https = 443
```

`adreca_lan` de la 2.2 ja no és necessària en els nous arrancadors. Si el port 443 està ocupat, pots triar-ne un altre, però la URL haurà d’incloure’l (per exemple `https://compas.local:8443/`) i cal ajustar la regla del tallafoc. No poses dos servidors amb el mateix nom a la mateixa xarxa.

---

# Compàs 2.3 · Red local con HTTPS

El servidor guarda la aplicación y SQLite. Los demás equipos entran en **https://compas.local/** con navegador y cuenta propia, sin IP fija ni reserva en el router. El nombre se descubre mediante mDNS y el servidor debe estar encendido en la misma red privada.

**Servidor Windows:** abre **Red local / HTTPS…**, marca compartir, elige un nombre `.local` y pulsa **Guardar y reiniciar**. Confirma el permiso de cortafuegos solo para el perfil privado y subred local. Al arrancar, confirma el certificado de tu servidor; PHP no se ejecuta como administrador.

**Servidor Linux x86-64:** ejecuta `compas --xarxa` o el asistente del menú y confirma los pasos. Puede pedir `sudo` para el puerto 443, el certificado y UFW. Necesita `libcap2-bin`, `ca-certificates` y `openssl`. Reinicia Compàs.

**Clientes:** el servidor prepara `Client-Compas.zip` (botón **Preparar otros equipos** en Windows; carpeta de datos `https` en Linux). Pásalo directamente, compara la huella SHA-256 y abre `Connecta-Compas.exe` en Windows o `bash connecta.sh` en Linux. Solo hay que aceptar la confianza una vez. Después basta con abrir la URL. Los navegadores con almacén propio pueden requerir importar `compas-ca.crt`. Linux necesita resolución `.local` (Avahi/libnss-mdns o equivalente). Si se bloquea multicast, hay aislamiento Wi-Fi o red de invitados, el responsable de la red tendrá que revisarlo.

HTTPS cifra el tráfico; no configura acceso público a Internet. Cada instalación tiene una autoridad propia, con renovación automática del certificado del servidor. No compartas las claves de la carpeta `https`; los ZIP de clientes no las contienen. Las copias `.db` tampoco incluyen las claves: guarda una copia externa protegida si quieres conservar la confianza al recuperar el servidor. Si se pierden, los clientes deberán aceptar el nuevo certificado.

Los roles y límites son los mismos que en modo local: administración completa, gestión diaria y consulta limitada. Cerrar el navegador no detiene el servidor. Para restaurar o vaciar datos, desactiva antes el modo compartido en el asistente. No compartas el archivo SQLite mediante NAS, carpetas de red ni sincronización. El servidor está pensado para grupos pequeños en una red controlada; en Windows las peticiones PHP se atienden en serie.
