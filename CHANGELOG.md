# Canvis de Compàs

Ací s'apunten els canvis de cada versió, començant per la més nova, perquè qui actualitza sàpiga què trobarà.

Les versions segueixen el format `MAJOR.MENOR.CORRECCIÓ`: la **correcció** (1.2.**1**) arregla errades i porta millores menudes, la **menor** (1.**3**.0) afig funcions i la **major** (**2**.0.0) porta canvis que demanen atenció en actualitzar.

**Per a actualitzar:** tanca Compàs i executa l'instal·lador de la versió nova. Les dades i les còpies de seguretat es conserven.

*Registro de cambios de Compàs: cada versión, de la más nueva a la más antigua.*

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
