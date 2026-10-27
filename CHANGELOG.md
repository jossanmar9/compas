# Canvis de Compàs

Ací s'apunten els canvis de cada versió, començant per la més nova, perquè qui actualitza sàpiga què trobarà.

Les versions segueixen el format `MAJOR.MENOR.CORRECCIÓ`: la **correcció** (1.2.**1**) arregla errades i porta millores menudes, la **menor** (1.**3**.0) afig funcions i la **major** (**2**.0.0) porta canvis que demanen atenció en actualitzar.

**Per a actualitzar:** tanca Compàs i executa l'instal·lador de la versió nova. Les dades i les còpies de seguretat es conserven.

*Registro de cambios de Compàs: cada versión, de la más nueva a la más antigua.*

## [2.3.3] - 2026-10-27

- Connexió HTTPS més senzilla en Windows: el servidor prepara un únic `Connecta-Compas.exe` amb la URL i el certificat públic incorporats. Es pot copiar als clients sense ZIP ni fitxers addicionals.
- Confirmació de confiança en una finestra clara, en l’idioma del sistema. Si el certificat ja és de confiança, s’obri Compàs directament.
- Accés directe a Compàs a l’escriptori del client. Es mantenen la comprovació de l’empremta i la confirmació explícita de confiança.
- Manual PDF i instruccions actualitzats. Linux conserva `Client-Compas.zip` amb el seu assistent.

## [2.3.2] - 2026-10-27

- El servidor LAN conserva la finestra pròpia de Compàs. Tancar la finestra de treball deixa el servidor en marxa; per a aturar-lo cal usar el panell del servidor.
- Botó **Configura la xarxa local** al panell d’Inici de l’ordinador servidor, disponible per a administrador i usuari de gestió. El consultor no hi té accés. Els accessos del panell i la safata passen també pel control de permisos de l’aplicació.
- Guia PDF de xarxa local amb captures i passos senzills, inclosa en l’ajuda, les descàrregues i la web.
- Proves de permisos, peticions locals autenticades i continuïtat del servidor després de tancar la finestra.

## [2.3.1] - 2026-10-27

- Seguretat HTTPS: es descarten també les variants amb punts i guions baixos de les capçaleres internes que PHP normalitza. Un client remot no pot falsejar la seua identitat ni fer-se passar pel servidor amb eixes capçaleres.
- Prova de regressió amb peticions remotes i variants de nom de capçalera.
- La distribució 2.3.0 s’ha retirat abans de registrar cap descàrrega dels paquets. Utilitzeu la 2.3.1 o posterior per al mode LAN segur.

## [2.3.0] - 2026-10-27

- LAN més fàcil: **https://compas.local/**, sense IP fixa ni reserva DHCP, amb descobriment mDNS i seguiment dels canvis de xarxa.
- Servidor HTTPS propi amb TLS 1.2 o posterior, certificat per instal·lació, renovació automàtica i claus privades protegides. PHP només accessible en localhost a través del proxy autenticat; es descarten capçaleres falses.
- Assistent Windows **Xarxa local / HTTPS…** per a activar/desactivar el servidor i permetre només la subxarxa privada en el tallafoc. Linux: `compas --xarxa`.
- Paquet **Client-Compas.zip** amb certificat públic i assistents: confirmació explícita de l’empremta una vegada per equip, després només la URL. No inclou claus ni dades.
- Linux: paquets de servidor x86-64 (`amd64`), amb dependències de configuració HTTPS. Guia i web actualitzades.
- Proves de TLS real, permisos, galetes Secure, identitat del client i bloqueig d’accés directe a PHP.

## [2.2.1] - 2026-10-27

- Nous tipus d’entitat: **Muixeranga** i **Colla castellera**, amb seccions, assajos, actuacions, material, roba i dades d’exemple pròpies.
- Buidatge de dades de gestió des de Còpies de seguretat: conserva configuració, comptes, permisos i catàlegs; exigeix contrasenya i confirmació, amb còpia manual prèvia. Esborra també les dades reals afegides després de les d’exemple. Només amb LAN desactivat.
- Web en valencià i castellà: secció visible de xarxa local, rols i guia de configuració, i els dos nous tipus d’entitat. Actualització automàtica dels enllaços de descàrrega després de publicar cada versió.

## [2.2.0] - 2026-10-27

### Afegit
- Mode LAN opcional per a una xarxa privada de confiança: un ordinador servidor amb les dades i diversos clients amb navegador. Activació explícita a `compas.ini`, mantenint el mode només local per defecte.
- Rol **Consultor/a** de consulta limitada de persones, socis, escola, agrupacions i instruments, sense economia, camps bancaris, imports, exportacions, informes imprimibles, documents descarregables ni còpies. Protecció també de les peticions directes.
- Detecció de formularis antics en editar fitxes generals en LAN, per a evitar sobreescriure els canvis d'una altra persona.
- Proves de permisos, sessions simultànies, conflictes d'edició, noms de compte i origen de les peticions.

### Seguretat i dades
- Noms de compte ASCII de 3 a 40 caràcters, sense accents ni dièresi, únics també si només canvien les majúscules. Missatge específic per a duplicats i protecció a la base de dades.
- Migració dels noms antics amb registre dels canvis i sufixos si calen, sense perdre comptes, rols ni contrasenyes; còpia prèvia a l'actualització.
- Sessions estrictes, caducitat LAN de 30 minuts sense activitat i invalidació de les altres sessions en canviar la contrasenya.
- Límits d'entrada per compte i IP en LAN, sense bloqueig global de tota l'entitat.
- Configuració inicial, recuperació i petició de còpia en tancar restringides al servidor; el testimoni del llançador s'envia en una capçalera, no a l'adreça.
- Restauració bloquejada en mode LAN; s'ha de fer al servidor en mode local. Sessions anteriors invalidades després de restaurar.

### Còpies i documentació
- Còpies automàtiques LAN amb canvis al rebre una petició després de 15 minuts, a més de les diàries i de tancament.
- Còpies serialitzades, noms sense col·lisions i marca de canvis anterior a la instantània.
- Manuals, web, privacitat i guia LAN en valencià i castellà actualitzats. El mode inclòs és HTTP en LAN controlada; no és un desplegament públic ni per a càrrega elevada.

## [2.1.0] - 2026-10-26

### Millorat
- Preparada la capa de dades de Compàs per a treballar amb diversos usuaris simultanis, com a base del futur accés compartit en xarxa local.
- La generació de lots de rebuts reserva de manera segura la numeració correlativa i calcula l’últim número una sola vegada per lot.
- Millorada la concurrència en la generació de talonaris de loteria per evitar col·lisions de numeració.
- Reforçada la consistència de les operacions amb remeses SEPA quan coincidixen diverses operacions.
- Afegides proteccions de base de dades per evitar més d’un mandat SEPA actiu per persona i més d’una cessió activa del mateix instrument.
- Millorada la gestió dels bloquejos temporals de SQLite amb missatges comprensibles per a l’usuari.

### Rendiment
- Eliminades consultes repetitives en la numeració de lots de rebuts, reduint el temps durant el qual es manté una transacció d’escriptura.
- Es manté SQLite en mode WAL, amb `busy_timeout` i claus foranes, sense afegir cap servidor de base de dades extern.

### Proves
- Auditades les operacions sensibles a concurrència i les transaccions d’escriptura.
- Validada la generació concurrent de rebuts i talonaris sense duplicats en proves de càrrega amb múltiples escriptors.
- Validada la integritat de la base de dades després de les proves de concurrència.

### Notes
- Esta versió prepara internament Compàs per a l’ús multiusuari. L’accés des de diversos ordinadors de la mateixa xarxa local encara no forma part d’esta versió.

## [2.0.2] - 2026-10-24

### Canviat
- La icona de l’instal·lador de Windows mostra només el símbol de Compàs, sense el nom ni el lema, amb fons transparent i diverses resolucions.

## [2.0.1] - 2026-10-24

### Canviat
- Nou logotip de Compàs a la pantalla d’entrada i de configuració inicial, al llançador i a les portades dels manuals.
- Nova icona de l’aplicació, del navegador i dels paquets de Windows i Linux, a partir del mateix logotip.

## [2.0.0] - 2026-10-23

Compàs passa a ser la **gestió lliure per a associacions**: a més de les societats musicals, ara serveix per a clubs esportius, colles, associacions culturals, AMPA, falles, gaiates, fogueres, comparses, penyes i federacions.

**En actualitzar:** una instal·lació anterior continua sent una societat musical i tot es queda igual. Apareix l'apartat nou **Activitats** (al menú Entitat); els altres apartats nous (llicències, entitats federades i càrrecs de festa) estan desactivats i es poden activar a **Administració → Mòduls i avisos**. Abans d'actualitzar la base de dades, Compàs en fa una còpia.

### Afegit
- **Tipus d'entitat**, que es tria en la configuració inicial i es pot canviar a **Administració → Configuració**. Cada tipus:
  - **Adapta els noms**: socis, fallers i falleres, foguerers i foguereres, comparsistes, famílies associades o membres de la colla; escola de música, escola esportiva, activitats extraescolars o tallers; alumnat o esportistes; professorat, entrenadors o monitors; agrupacions, equips, seccions, esquadres o comissions; instruments o material; uniformes, equipacions o vestits…
  - **Activa els apartats que li calen** (i desactiva els altres; tot es pot tornar a activar). Les notes i els butlletins només hi són en les societats musicals i les colles.
  - Posa el **mes en què comença l'exercici**: gener, setembre (temporada esportiva o curs escolar), abril (exercici faller) o juliol (fogueres). Es pot canviar a la configuració. Els períodes de les quotes (l'anual, els semestres, els trimestres i els mesos) van per exercici, que es diu «2026» o «2026-2027».
  - Carrega unes **dades d'exemple** pròpies si es demanen en la instal·lació.
- **Activitats**: sopars, eixides, viatges, actes, competicions, tallers… amb data, lloc, preu per plaça i places. **Inscripcions** amb les places de cadascuna (l'import es calcula sol), avís quan s'omplin o ha passat la data límit, **llista d'inscrits** per a imprimir i **un rebut per inscripció** (els menors alumnes, a nom de qui paga les seues classes). Les activitats dels pròxims 15 dies ixen a l'inici.
- **Categories d'edat** (prebenjamí… veterà als clubs; infantil i major a les falles…), editables. La categoria es calcula amb l'edat que es fa l'any en què comença l'exercici i ix a la fitxa de cada persona. Llistat **Persones per categoria** i socis per categoria a la memòria.
- **Llicències i revisions mèdiques**: llicència federativa de cada temporada per equip, amb la caducitat i la revisió mèdica, avís a l'inici un mes abans que caduquen i llistat **Llicències per equip**.
- **Càrrecs de festa** de cada exercici (Fallera Major, Bellesa, Madrina, capitania…), amb suggeriments segons el tipus, historial, certificat i llistat per exercici.
- **Entitats federades** (federacions): cens amb el representant i la quota anual, **rebuts de la quota** a nom del representant i llistat per a imprimir.
- Els tipus d'agrupació depenen del tipus d'entitat: equip, secció, grup, comissió o esquadra.
- Rebuts de tipus «Activitat» i «Quota d'entitat federada»; generació de rebuts d'activitats i de quotes d'entitats federades a **Economia → Genera rebuts**.
- Camps de text amb suggeriments (federacions, càrrecs de festa, tipus d'entitat federada).

### Canviat
- Lema nou: «Gestió lliure per a associacions». Els textos generals diuen «entitat» en lloc de «societat», també al logotip, a la declaració de privacitat, al llançador i als paquets.
- Els llistats amagen per defecte només el que està de baixa (per exemple, una llicència en tràmit sí que es veu).
- Manuals: tipus d'entitat i exercici, taula de noms segons el tipus, i capítols nous d'activitats, categories, llicències, càrrecs de festa i entitats federades.
- Web i README reescrits per a tots els tipus d'associació.

### Corregit
- Dues actualitzacions de la base de dades en el mateix segon ja no xoquen en fer la còpia «abans d'actualitzar».

## [1.16.0] - 2026-10-23

### Afegit
- **Subvencions** (menú Economia, mòdul que es pot desactivar):
  - Cada subvenció amb l'organisme, la convocatòria, l'exercici, l'expedient, per a què és, l'estat (prevista, sol·licitada, concedida, denegada, justificada o tancada), els terminis per a demanar-la i justificar-la i els imports (pressupost, sol·licitat, concedit i el que cal justificar).
  - **Com es justifica**: una llista de requisits amb caselles que es marquen a mesura que estan fets (amb la data). En triar l'organisme es marquen sols els que demana normalment, i es poden canviar per a cada subvenció.
  - **Organismes i requisits editables** per cada societat (Generalitat, Diputació, Ajuntament, Federació, Altres i deu requisits habituals per a començar). Un requisit que ja no es demana es posa de baixa i es conserva en les subvencions antigues.
  - **Cobraments** (bestreta, pagament final…), que entren sols al llibre de caixa.
  - **Despeses imputades**: les factures del llibre de caixa que justifiquen la subvenció. Una factura no es pot imputar, entre totes les subvencions, per més del que val.
  - Resum a la fitxa (concedit, cobrat, justificat i el que falta) amb barres de progrés.
  - **Relació de despeses** per a la justificació, per a imprimir o guardar en PDF amb les signatures, i en CSV.
  - **Copia per a l'any següent**, amb els mateixos requisits i les dates un any més tard.
  - Avís a l'inici quan falten 30 dies o menys per a demanar o justificar una subvenció (es pot amagar).
  - Els documents es poden lligar a una subvenció i surten a la seua fitxa.
- Llibre de caixa: les despeses tenen proveïdor i NIF, a més del número de factura (justificant).

## [1.15.0] - 2026-10-23

### Afegit
- **Els llistats es poden ordenar per qualsevol columna**: un clic al títol de la columna ordena per ella (els socis per número, els grups per aula o professor, els rebuts per import…) i un altre clic, al revés. Compàs recorda l'ordre de cada llistat mentre treballes, l'exportació CSV ix en el mateix ordre, i davall de la taula hi ha l'enllaç per a tornar a l'ordre de sempre.
- `eines/societat-gran.php`: omple una instal·lació nova amb una societat gran inventada (341 socis, 278 alumnes, cinc agrupacions, 653 obres…) per a provar el programa amb moltes dades.

### Canviat
- L'ordre alfabètic ja no té en compte els accents ni les majúscules (Àlex va amb la A, no al final) i els números van en ordre natural (el soci 2 abans que el 10; INV-9 abans que INV-10).
- La web: captures d'una societat gran, apartats de funcions amb la seua captura, xifres, menú i botó de descàrrega pensats per al mòbil i imatge per a quan es comparteix l'enllaç.

## [1.14.2] - 2026-10-22

### Seguretat
- **Compàs només respon a este ordinador.** Ja només escoltava a 127.0.0.1; ara, a més, rebutja les peticions que arriben amb un altre nom de servidor. Així una pàgina web maliciosa oberta al mateix ordinador no pot fer-se passar per Compàs canviant la seua adreça (l'atac anomenat *DNS rebinding*), ni provar contrasenyes ni crear el primer compte d'una instal·lació nova.
- **Intents d'entrada per a tot el programa**: després de 5 contrasenyes incorrectes cal esperar 30 segons. Abans es comptaven per sessió i es podia evitar començant una sessió nova en cada intent.
- **Política de seguretat de continguts**: les pantalles de Compàs només poden carregar i enviar coses al mateix programa. Si alguna vegada s'hi colara codi, no podria enviar les dades enlloc.
- Els documents pujats (excepte els PDF) s'obrin aïllats i no poden executar res dins de Compàs.
- El canvi d'idioma ja no pot redirigir a una altra web amb adreces del tipus `/\exemple.com`.
- Proves noves de tot açò (bateria `seguretat`).

## [1.14.1] - 2026-10-22

### Seguretat
- **PHP actualitzada per les vulnerabilitats «PHP2Shell»** (CVE-2026-109618 i CVE-2026-109619). La versió per a Windows porta PHP 8.3.36 o posterior, que les corregix, i la publicació falla si la PHP que es baixa és més antiga. A Linux, Compàs usa la PHP del sistema: n'hi ha prou d'actualitzar el sistema com sempre.
- Es desactiva una opció de PHP que Compàs no fa servir (`session.upload_progress`), una de les mesures recomanades per a estes vulnerabilitats.
- El risc per a Compàs era baix: el seu servidor només escolta el mateix ordinador (127.0.0.1) i no s'hi pot accedir des de la xarxa ni des d'Internet.

## [1.14.0] - 2026-10-22

### Afegit
- **Manual d'administració**, separat del manual d'usuari: configuració de la societat, dades del banc i descomptes, còpies de seguretat, comptes i contrasenyes, com actualitzar Compàs i preguntes freqüents. A **Ajuda** hi ha una pestanya per a cada manual (la d'administració només la veu l'administrador/a), i els dos tenen el seu PDF i accés directe al menú d'inici de Windows.
- **Taula de permisos**: una taula amb què pot fer cada compte, al començament dels dos manuals, al README i a la web.
- **Preguntes freqüents a la web**: si és gratis, si cal Internet, si envia dades, Windows i Linux, diversos ordinadors, dades d'exemple, importar d'Excel, factures, què passa si s'espatlla l'ordinador i com s'actualitza.

### Canviat
- El compte «administratiu/va» ara es diu **usuari/ària** («usuario/a» en castellà), per a no confondre'l amb l'administrador/a. Només canvia el nom: els permisos són els mateixos.
- El manual d'usuari ja només explica la feina del dia a dia; el que és de l'administrador/a remet al manual d'administració.

## [1.13.4] - 2026-10-22

### Afegit
- **Hi ha versió nova?** a l'ajuda: obri al navegador la pàgina de l'última versió de Compàs, i davall es veu la versió que tens. Compàs continua sense connectar-se a Internet.
- Societats grans: quan hi ha més de 400 persones (o d'altres opcions), el camp de persona dels formularis es busca escrivint, sense tindre en compte els accents, en lloc d'un desplegable amb tota la llista. El repertori es mostra de 100 en 100, amb un enllaç per a mostrar-lo sencer i imprimir-lo, i els llistats llargs ja no mostren totes les pàgines.

### Canviat
- **Actualitzacions més segures**: la base de dades només es prepara una vegada per versió (abans es repassava en cada clic), en una sola transacció i amb una còpia «abans d'actualitzar». Les pantalles carreguen un 40 % més ràpid.
- **Cap versió no es publica si alguna prova falla**: el workflow de publicació passa abans totes les proves, també les d'integració.
- L'instal·lador de Windows ja no porta les parts de PHP que Compàs no fa servir (intl, curl, openssl…).
- Les captures dels manuals i de la web són WebP: ocupen un 40 % menys.
- El repertori ordena un catàleg de milers d'obres cinc vegades més ràpid.

## [1.13.3] - 2026-10-21

### Canviat
- **Rotació de les còpies de seguretat**: es guarden totes les còpies automàtiques dels últims 7 dies, una per setmana de les últimes 5 setmanes i una per mes fins al límit triat (1 any si no es canvia). Amb una còpia diària són unes vint còpies que arriben a un any arrere, en lloc de les 30 últimes (un mes). Les manuals no s'esborren soles. També a la carpeta externa.
- La pantalla de còpies diu quant ocupen la base de dades, els documents i totes les còpies, i avisa si la base de dades passa de 200 MB.
- **Els PDF dels manuals ja no es guarden al repositori**: els fa GitHub Actions en publicar cada versió (s'adjunten a la versió) i la web. Per a fer-los a mà: `bash eines/pdf.sh`. Cada versió feia créixer el repositori uns 25 MB.
- `eines/captures.py` pot refer només les captures que han canviat (`python3 eines/captures.py 34-36 web`), i el codi de recuperació de la captura ja no canvia en cada versió.

### Afegit
- **Proves d'integració dins del repositori** (`tests/integracio.py`): més de 800 comprovacions en valencià i castellà que arranquen Compàs de veres i recorren l'economia, la societat, els uniformes, el repertori, l'avís de responsabilitat, la importació i totes les pantalles. GitHub Actions les executa en cada `push`.
- Proves de la rotació de les còpies a `tests/run.php`.

## [1.13.2] - 2026-10-21

### Afegit
- **Resum de la llicència en valencià i castellà** (`LLICENCIA.md`), també dins de Compàs (Ajuda → Llicència). L'únic text vàlid continua sent l'original en anglés (`LICENSE`).
- L'instal·lador de Windows mostra, damunt del text en anglés de la llicència, una nota en l'idioma triat que explica què s'accepta.

### Canviat
- Web: l'avís de responsabilitat té el mateix aspecte que el quadre de Windows, i s'ha llevat el segon botó de descàrrega d'eixa secció.

## [1.13.1] - 2026-10-21

### Afegit
- **Avís de responsabilitat**: Compàs es distribueix tal com és, sense cap garantia, i l'autor no es fa responsable de la pèrdua de dades ni de cap altre dany derivat del seu ús. Apareix:
  - en la configuració inicial, on cal acceptar-lo per a continuar;
  - una vegada a l'inici de les instal·lacions que ja existien, fins que algú prem «Entesos» (queda apuntat qui i quan);
  - a l'ajuda, a la configuració, a la pantalla de còpies de seguretat i al peu de totes les pantalles;
  - als instal·ladors de Windows i Linux, al paquet .deb, a la web, al README, als manuals, a les guies d'instal·lació i a la declaració de privacitat.

## [1.13.0] - 2026-10-21 · Edició Repertori 🎼

### Afegit
- **Repertori**: el catàleg de l'arxiu musical de la societat. Cada obra té títol, tipus d'obra, compositor/a, arranjador/a, les agrupacions que la poden interpretar, número d'arxiu (es proposa el següent i no es pot repetir), ubicació física, material, estat (complet, incomplet, en revisió o retirat), si està digitalitzada i observacions.
- Pantalla del repertori amb cerca (títol, compositor, número d'arxiu, ubicació…), filtres per tipus, agrupació, estat i digitalització, ordenació (títol, compositor, número d'arxiu, tipus o ubicació), impressió i exportació a CSV.
- **Tipus d'obra** propis de cada societat (pasdoble, marxa mora, marxa cristiana, marxa de processó, obertura, poema simfònic…).
- **Importació del repertori** des d'Excel o CSV: reconeix les columnes habituals, crea els tipus que falten, assigna les agrupacions pel nom i no duplica les obres que ja hi són (es poden actualitzar si es vol). El CSV que s'exporta es pot tornar a importar.
- A la fitxa de cada agrupació, l'enllaç a les seues obres. El repertori es pot desactivar a **Mòduls i avisos**.
- Compàs només guarda el catàleg: no guarda partitures ni fitxers.

### Canviat
- La documentació de les proves automàtiques (abans `ACTUALITZACIO.md` i `README-PROVA-RENDIMENT.md`) passa a `tests/README.md`.
- S'esborra l'índex `idx_mandats_persona`, que ja cobreix `idx_mandats_persona_estat_data`.
- Les dades d'exemple porten un repertori.

## [1.12.0] - 2026-10-21

### Afegit
- Proves automatitzades per a IBAN, BIC, SEPA i còpies de seguretat xifrades.
- Integració amb GitHub Actions per a comprovar la sintaxi, executar proves i validar l'aplicació amb cada canvi.
- Prova específica de rendiment de SQLite amb una base de dades gran.

### Canviat
- Nous índexs SQLite per accelerar les consultes més habituals.
- Optimitzat el càlcul dels rebuts de l'escola, eliminant consultes repetitives per alumne.
- Optimitzada la comprovació de mandats SEPA i de rebuts ja existents, processant les consultes per lots.
- Millorada l'escalabilitat del mòdul d'economia sense canviar el funcionament ni el format de les dades.

### Intern
- Reducció de patrons N+1 en el mòdul d'economia.
- Millores de qualitat i mantenibilitat del projecte.

## [1.11.2] - 2026-10-21

### Corregit
- «de hui» en lloc de «d'hui» (davant de «hui» no s'apostrofa).

### Canviat
- Web: els mòduls es mostren en targetes soltes, que no es trenquen encara que l'última fila quede incompleta.

## [1.11.1] - 2026-10-21

### Canviat
- Uniformes: les existències de cada talla es mostren més senzilles, amb el total de peces de la societat, les que estan en préstec i les disponibles.
- Textos en valencià revisats segons la normativa de l'Acadèmia Valenciana de la Llengua (este/esta, hui, vore…).

## [1.11.0] - 2026-10-21

### Afegit
- **Uniformes**: articles (polos, samarretes, dessuadores, motxilles…) amb les seues talles, compres, i lliuraments com a venda, préstec o gratuïts. Existències per talla, amb avís si d'una talla s'han lliurat més de les comprades. En una venda, l'import es proposa amb el preu de l'article. Els lliuraments de cada persona apareixen a la seua fitxa.
- **Tipus de soci propis**: cada societat defineix els seus (nom, descripció, ordre i si ixen al full d'alta) al botó **Tipus de soci** del llistat de socis. Les tarifes, el full d'alta, els llistats, la memòria, els certificats i la importació fan servir els de la societat.
- **Mòduls i avisos** (Administració): es pot desactivar l'escola, les agrupacions, els instruments, els uniformes, les quotes i rebuts, la loteria, el llibre de caixa, la junta i actes, els documents i els comunicats, i amagar els avisos de l'inici que no es volen vore. Les dades dels mòduls desactivats no s'esborren.

### Canviat
- En actualitzar, els quatre tipus de soci d'abans (ordinari, protector, juvenil i d'honor) es converteixen en tipus de la societat, amb els mateixos socis i tarifes. El d'honor no ix al full d'alta.
- La secció del menú «Instruments» passa a dir-se «Instruments i uniformes».
- Les dades d'exemple porten uniformes.

## [1.10.0] - 2026-10-21

### Afegit
- **Junta directiva**: càrrecs (presidència, vicepresidència, secretaria, tresoreria, vocalies) amb data d'inici i de fi, i l'historial de totes les juntes. Anys de mandat configurables i avís a l'inici quan falten quatre mesos per a la renovació, amb el recordatori de comunicar els canvis al Registre d'Associacions. Botó per a tancar tots els càrrecs actuals quan entra una junta nova i certificat de la composició de la junta.
- **Certificats signats per la junta**: qui signa (secretaria) i el vistiplau (presidència) s'agafen sols dels càrrecs vigents. La configuració encara permet posar-los a mà.
- **Llibre d'actes**: reunions de junta i assemblees ordinàries i extraordinàries, amb ordre del dia, assistents, desenvolupament i acords. S'imprimeixen la convocatòria, l'acta i el certificat de cada acord.
- **Documents de la societat**: estatuts, reglament de règim intern, actes signades, inscripció al registre, CIF, pòlisses, contractes, convenis… El fitxer (PDF, imatge, document o full de càlcul, fins a 20 MB) es guarda dins de la base de dades i entra en les còpies de seguretat. Data de caducitat amb avís dos mesos abans.
- **Registre d'activitats de tractament (RGPD)**: model per a imprimir generat amb el que hi ha a Compàs.
- **Certificat de delictes de naturalesa sexual** a la fitxa del professorat, amb avís a l'inici si algun professor/a actiu no el té.
- **Full d'alta de soci** per a imprimir: dades personals, tipus de soci amb la quota, ordre de domiciliació i consentiments. La introducció i el peu es poden personalitzar a la configuració.
- **Comunicats**: llistes de correu per grups (socis, junta, professorat, famílies de l'escola, un grup o una agrupació, tothom) per a copiar en CCO o obrir el correu, i registre dels comunicats enviats. Compàs no envia cap correu.
- **Llibre de caixa**: ingressos i despeses de l'any amb saldo per banc i caixa. Els rebuts cobrats, les remeses i la loteria liquidada hi entren sense apuntar-los; la resta es pot apuntar a mà. Resum per categories, impressió i CSV.
- Permisos nous `societat.view` i `societat.edit` (els tenen els dos comptes).

### Canviat
- La web posa la privacitat al centre: una secció nova compara Compàs amb les plataformes en línia.
- Les dades d'exemple porten junta, actes, documents, comunicats i moviments de caixa.
- La declaració de privacitat explica on es guarden els documents i que els comunicats només preparen les adreces.

## [1.9.0] - 2026-10-21

### Afegit
- **Economia**, un apartat nou del menú:
  - **Tarifes**: quotes de soci per tipus i periodicitat (anual, semestral, trimestral o mensual), matrícula i classes (per assignatura o una quota d'escola fixa; mensuals, per avaluació o per curs).
  - **Generar rebuts** d'un període amb previsualització: un per soci, i a l'escola un per família, amb una línia per assignatura i alumne i els descomptes de soci, de germans i propis (família nombrosa, beca…). Generar dues vegades el mateix període no duplica res.
  - **Rebuts**: numeració correlativa per any, cobrament en efectiu, transferència o domiciliació, rebuts retornats i anul·lats, i rebuts amb el logotip per a imprimir (dos per full; amb el segell «PAGAT» si estan pagats).
  - **Domiciliacions** (mandats SEPA) amb l'IBAN validat, i l'ordre de domiciliació per a signar.
  - **Remeses bancàries**: fitxer XML d'adeutaments directes SEPA (pain.008.001.02 o .08, esquema CORE) per a pujar a la banca electrònica, i control dels rebuts que retorna el banc.
  - **Resum econòmic**: per cobrar, cobrat enguany, per períodes i qui deu diners.
- **Loteria**: sorteigs, talonaris repartits a cada persona, participacions per a imprimir (8 per full) i liquidació de cada talonari, amb el que cal comprar a l'administració i el que queda per a la societat.
- **IBAN a la fitxa de persona i de soci** (per als majors d'edat): en desar-lo es crea la domiciliació; si es canvia o s'esborra, l'anterior es dona de baixa. També s'accepta el número de compte antic de 20 xifres.
- **Importar el compte bancari des d'Excel**: si el full porta l'IBAN (i, si es vol, la data de signatura), es creen les domiciliacions; en l'alumnat, la del pare, la mare o el tutor/a.
- A la fitxa de l'alumne, **qui paga** les classes (si no és el tutor principal) i un **descompte propi**.
- A la fitxa de cada persona, les seues domiciliacions i els seus rebuts.

### Canviat
- Les dades d'exemple porten tarifes, domiciliacions, rebuts i una loteria de Nadal.
- La declaració de privacitat explica que els comptes bancaris de les domiciliacions es guarden a l'ordinador i que Compàs no es connecta al banc.

## [1.8.0] - 2026-10-21

### Canviat
- **Compàs té la seua pròpia finestra i ja no necessita cap navegador.** A Windows es fa amb el component WebView2, que porten Windows 10 i 11; a Linux, amb WebKitGTK. En tancar la finestra, es fa la còpia de seguretat i Compàs s'atura.
- Si es torna a obrir Compàs mentre ja està obert, es mostra la finestra que ja hi havia.
- Els documents per a imprimir s'obrin en una altra finestra de Compàs; els fitxers descarregats van a la carpeta Baixades, i a Linux els PDF s'obrin amb el programa habitual. Els enllaços de fora de Compàs s'obrin al navegador habitual.
- La finestra recorda la mida i la posició. A Windows, la **carpeta de dades** s'obri des del menú de la icona de dalt a l'esquerra de la finestra.
- Si el component no hi és, Compàs funciona com en la 1.7: Edge, Chrome o Chromium en mode aplicació o, si tampoc, el navegador habitual.
- El paquet .deb recomana els paquets de la finestra (`python3-gi`, `gir1.2-webkit2-4.1`), que `apt` instal·la sols.

## [1.7.0] - 2026-10-21

### Canviat
- **Compàs s'obri en una finestra pròpia**, sense pestanyes ni barra d'adreces, i ja no cal la finestra menuda: en tancar la finestra de Compàs es fa la còpia de seguretat i el programa s'atura. A Windows la posa Microsoft Edge (o Google Chrome), amb un perfil separat del navegador de l'usuari, i mentre està obert hi ha una icona a la safata del sistema amb **Obri Compàs**, **Carpeta de dades** i **Atura i tanca**. A Linux la posen Chromium, Google Chrome, Microsoft Edge o Brave, i l'accés del menú ja no obri cap terminal.
- Si no hi ha cap d'estos navegadors, o amb `finestra = 0` a `compas.ini`, funciona com abans: navegador habitual i finestra menuda (a Linux, terminal).
- El nom de l'autor, José Vicente Sanjuan, apareix a la web, al README, als manuals, a la declaració de privacitat, al peu del programa i a les propietats del programa i dels paquets.

## [1.6.0] - 2026-10-21

### Afegit
- **Paquet .deb** per a Debian, Ubuntu, Linux Mint i distribucions semblants: s'instal·la amb `sudo apt install ./compas_X.Y.Z_all.deb`, que porta també PHP i les extensions que falten. Crea l'ordre `compas` i l'accés al menú, i es desinstal·la amb `sudo apt remove compas` sense tocar les dades.
- A Linux, cada usuari pot canviar el port i les altres opcions a `~/.config/compas/compas.ini`.

### Canviat
- **Llicència:** Compàs passa a distribuir-se sota la **GNU Affero General Public License v3 o posterior (AGPL-3.0-or-later)**. Si algú el modifica i l'ofereix a través d'una xarxa, ha de donar accés al codi de la seua versió.

## [1.5.0] - 2026-10-20

### Afegit
- **Versió per a Linux** (`Compas-X.Y.Z-linux.tar.gz`): s'instal·la per a l'usuari sense `sudo` amb `./installa.sh`, crea l'accés al menú d'aplicacions i l'ordre `compas`, i utilitza el PHP del sistema. Com a Windows, en tancar fa una còpia de seguretat si hi ha hagut canvis. Les dades es guarden a `~/.local/share/compas/dades`.
- La guia d'instal·lació, el README i la web expliquen què fer si Windows no deixa obrir Compàs: l'avís de SmartScreen i el bloqueig del Control intel·ligent d'aplicacions de Windows 11, perquè el programa encara no està signat digitalment.

### Llevat
- La pàgina «Code signing policy»: de moment Compàs no està signat.

## [1.4.1] - 2026-10-20

### Afegit
- **Declaració de privacitat** (`PRIVACITAT.md`), en valencià, castellà i anglés: Compàs no recull, no envia i no comparteix cap dada. Es pot llegir a **Ajuda → Privacitat**, l'instal·lador en mostra un resum abans d'instal·lar, i n'hi ha un enllaç a la web i al README.

### Canviat
- La web ja no carrega les tipografies de Google Fonts: les mateixes tipografies lliures (SIL Open Font License) se serveixen des de la web, de manera que visitar-la no envia cap dada a tercers.
- La web inclou el mòdul «Curs nou» i la graella de mòduls queda completa.

## [1.4.0] - 2026-10-20

### Afegit
- **Importar des d'Excel o CSV** persones, socis i alumnes (amb el pare, la mare o el tutor/a). Compàs reconeix les columnes, mostra una revisió de cada fila abans d'importar i detecta les persones que ja existeixen (pel DNI o pel nom i la data de naixement), perquè no es dupliquen. Abans d'importar fa una còpia de seguretat.
- **Drets d'imatge**: a cada persona es marca si autoritza fotos i vídeos i la data de la signatura. Full de consentiment per a imprimir amb les dades de l'alumne i del tutor/a, d'una persona, d'un grup o de tots els pendents. Filtres al llistat de persones.
- **Llistats i informes** per a imprimir, guardar en PDF o exportar a CSV: alumnes d'un grup amb els telèfons dels tutors, socis per número, músics d'una agrupació per instrument i drets d'imatge.
- **Etiquetes** adhesives amb l'adreça per a les circulars (socis, famílies, músics o tothom), en tres formats de full i aprofitant fulls començats.
- **Certificats** en A4 amb el logotip: de soci, de matrícula i de pertinença a una agrupació. A Configuració s'indica qui els signa i, si es vol, el vist i plau del president/a.
- **Antiguitat i insígnies**: socis i músics que complixen 25 o 50 anys (o els que es vulguen) en un any.
- **Memòria anual**: alumnes per edat, assignatura i població, professorat, músics per agrupació i socis per tipus, per a les subvencions.
- **Passar de curs**: assistent que crea el curs nou copiant-ne els períodes, els grups, el professorat, les aules i l'horari, i pot matricular ja els alumnes que continuen.
- **Còpies externes xifrades** amb contrasenya, perquè un USB perdut no expose dades de menors. Per a restaurar-les cal la contrasenya.
- Nou apartat del menú: **Documents i dades**.

### Canviat
- Les dades d'exemple porten adreces, consentiments i socis amb 25 i 50 anys d'antiguitat, per a provar les funcions noves.
- Les bases de dades d'una versió anterior s'actualitzen soles en obrir Compàs.

## [1.3.0] - 2026-10-20

### Afegit
- **Recuperar l'accés si l'administrador/a oblida la contrasenya.** En la configuració inicial es mostra un codi de recuperació per a imprimir i guardar. Amb ell, des de la pantalla d'entrada (**Recupera l'accés**), es posa una contrasenya nova i se'n genera un codi nou. Si el codi s'ha perdut, hi ha una alternativa: crear un fitxer `recupera-acces` a la carpeta de dades, que val una hora i s'esborra en usar-lo.
- A **Usuaris** es pot generar un codi de recuperació nou. Si Compàs ja estava instal·lat, l'inici avisa l'administrador/a fins que el genere.
- Les recuperacions, també les fallides, queden al registre d'activitat. Després de cinc intents fallits cal esperar un minut.

### Canviat
- El canvi de contrasenya es troba més fàcilment: hi ha l'opció **Compte i contrasenya** al menú, i el nom d'usuari de dalt a la dreta porta la icona 👤.

## [1.2.1] - 2026-10-20

### Afegit
- Este registre de canvis. També apareix en cada versió publicada.
- El programa i l'instal·lador porten el nom i la versió de Compàs a les seues propietats de Windows (botó dret → Propietats → Detalls).
- Formularis a GitHub per a avisar d'una errada o fer una proposta, en valencià i castellà, pensats per a qui no és informàtic.

### Arreglat
- Web: el pentagrama del peu de pàgina ara té cinc línies i la doble barra final queda ajustada.

## [1.2.0] - 2026-10-19

### Llevat
- **Control d'assistència.** Compàs és un programa de gestió i no passa llista. S'han llevat la pantalla d'assistència, la columna de faltes de la fitxa de l'alumne i del butlletí, i l'apartat del manual. En actualitzar, les dades d'assistència que hi poguera haver deixen d'utilitzar-se.

### Canviat
- Les captures dels manuals es retallen a la part útil de la pantalla, sense espais buits.
- El README explica amb més detall què fa el programa, com començar i com es guarden les dades.

### Afegit
- Web de presentació a [jossanmar9.github.io/compas](https://jossanmar9.github.io/compas/), en valencià i castellà.

## [1.1.0] - 2026-10-19

### Afegit
- **Dades obligatòries de les persones.** Nom, cognoms i data de naixement són obligatoris per a tothom; DNI i telèfon, només per als majors d'edat.
- **Avís de majors d'edat sense dades.** Quan un menor fa 18 anys i li falta el DNI o el telèfon, el llistat de persones mostra un avís i la seua fila es marca en groc amb el símbol ⚠. Un filtre permet vore només les persones a qui falten dades.

### Arreglat
- No es pot posar una data de naixement futura.

## [1.0.0] - 2026-10-19

Primera versió.

### Afegit
- Programa per a un sol ordinador amb Windows: instal·lador i versió portable, sense Internet ni servidor.
- Dos comptes: administrador/a i administratiu/va.
- Persones, socis, escola de música (cursos, avaluacions, assignatures, aules, professorat, grups, horaris, matrícules i tutors legals), agrupacions i instruments (manteniments i cessions).
- Introducció de notes per grup i avaluació, i butlletins de notes per a imprimir amb el logotip de la societat.
- Còpies de seguretat automàtiques (diàries i en tancar), manuals i en una carpeta externa; restauració amb un clic.
- Registre d'activitat.
- Tot en valencià i castellà.
- Manual d'usuari pas a pas amb captures i guia d'instal·lació, en PDF i dins del programa (Ajuda).

[1.16.0]: https://github.com/jossanmar9/compas/releases/tag/v1.16.0
[1.15.0]: https://github.com/jossanmar9/compas/releases/tag/v1.15.0
[1.14.2]: https://github.com/jossanmar9/compas/releases/tag/v1.14.2
[1.14.1]: https://github.com/jossanmar9/compas/releases/tag/v1.14.1
[1.14.0]: https://github.com/jossanmar9/compas/releases/tag/v1.14.0
[1.13.4]: https://github.com/jossanmar9/compas/releases/tag/v1.13.4
[1.13.3]: https://github.com/jossanmar9/compas/releases/tag/v1.13.3
[1.13.2]: https://github.com/jossanmar9/compas/releases/tag/v1.13.2
[1.13.1]: https://github.com/jossanmar9/compas/releases/tag/v1.13.1
[1.13.0]: https://github.com/jossanmar9/compas/releases/tag/v1.13.0
[1.12.0]: https://github.com/jossanmar9/compas/releases/tag/v1.12.0
[1.11.2]: https://github.com/jossanmar9/compas/releases/tag/v1.11.2
[1.11.1]: https://github.com/jossanmar9/compas/releases/tag/v1.11.1
[1.11.0]: https://github.com/jossanmar9/compas/releases/tag/v1.11.0
[1.10.0]: https://github.com/jossanmar9/compas/releases/tag/v1.10.0
[1.9.0]: https://github.com/jossanmar9/compas/releases/tag/v1.9.0
[1.8.0]: https://github.com/jossanmar9/compas/releases/tag/v1.8.0
[1.7.0]: https://github.com/jossanmar9/compas/releases/tag/v1.7.0
[1.6.0]: https://github.com/jossanmar9/compas/releases/tag/v1.6.0
[1.5.0]: https://github.com/jossanmar9/compas/releases/tag/v1.5.0
[1.4.1]: https://github.com/jossanmar9/compas/releases/tag/v1.4.1
[1.4.0]: https://github.com/jossanmar9/compas/releases/tag/v1.4.0
[1.3.0]: https://github.com/jossanmar9/compas/releases/tag/v1.3.0
[1.2.1]: https://github.com/jossanmar9/compas/releases/tag/v1.2.1
[1.2.0]: https://github.com/jossanmar9/compas/releases/tag/v1.2.0
[1.1.0]: https://github.com/jossanmar9/compas/releases/tag/v1.1.0
[1.0.0]: https://github.com/jossanmar9/compas/releases/tag/v1.0.0
