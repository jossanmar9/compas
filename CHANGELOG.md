# Canvis de Compàs

Ací s'apunten els canvis de cada versió, començant per la més nova, perquè qui actualitza sàpiga què trobarà.

Les versions segueixen el format `MAJOR.MENOR.CORRECCIÓ`: la **correcció** (1.2.**1**) arregla errades i porta millores menudes, la **menor** (1.**3**.0) afig funcions i la **major** (**2**.0.0) porta canvis que demanen atenció en actualitzar.

**Per a actualitzar:** tanca Compàs i executa l'instal·lador de la versió nova. Les dades i les còpies de seguretat es conserven.

*Registro de cambios de Compàs: cada versión, de la más nueva a la más antigua.*

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
