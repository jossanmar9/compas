# Manual d'usuari de Compàs

## 1. Abans de començar

Compàs és el programa de gestió de la societat musical: persones, socis, escola de música, agrupacions, instruments i còpies de seguretat. Funciona en **un sol ordinador amb Windows**, sense Internet, i té dos comptes:

| Compte | Què pot fer |
|---|---|
| **Administrador/a** | Tot. També gestionar usuaris, la configuració, el registre d'activitat i restaurar còpies. |
| **Administratiu/va** | La gestió diària: persones, socis, escola, notes, butlletins, agrupacions, instruments i fer còpies. |

> En aquest manual, el botó o el camp que cal utilitzar en cada pas apareix **marcat en taronja** a les captures.

### Obrir i tancar Compàs

1. Fes doble clic a la icona **Compàs** de l'escriptori o del menú Inici.
2. S'obri una finestra menuda de Compàs i, al cap d'uns segons, el navegador amb el programa.
3. **No tanques la finestra menuda** mentre treballes: és la que fa funcionar Compàs.
4. Per a acabar, tanca la finestra menuda. Compàs farà una còpia de seguretat si hi ha hagut canvis i s'aturarà.

Si el navegador no s'obri sol, prem **Obri Compàs** a la finestra menuda.

### Entrar

Escriu el teu usuari i la contrasenya i prem **Entra**. A baix pots triar l'idioma.

![Pantalla d'entrada](imatges/ca/00-entrada.jpg)

Si oblides la contrasenya, l'administrador te'n pot posar una de nova. Si qui l'ha oblidada és l'administrador/a, prem **Recupera l'accés** (vegeu l'apartat 16).

### La primera vegada: configuració inicial

La primera vegada que s'obri Compàs apareix la **Configuració inicial**:

1. Escriu el **nom de la societat musical**.
2. Tria l'**idioma** per defecte.
3. Crea l'**usuari administrador** i la seua contrasenya (almenys 8 caràcters). Apunta-la en un lloc segur.
4. Si vols, crea també el compte d'**administratiu/va** (es pot fer després).
5. Marca **Carrega dades d'exemple** només si vols provar el programa amb dades inventades.
6. Prem **Comença**.

![Configuració inicial](imatges/ca/01-configuracio-inicial.jpg)

Tot seguit, Compàs mostra el **codi de recuperació**. Serveix per a posar una contrasenya nova si l'administrador/a l'oblida, i **només es mostra esta vegada**. Prem **Imprimeix** (o apunta'l) i guarda'l amb els papers de la societat, no a l'ordinador. Després prem **Ja l'he guardat, continua**.

![Codi de recuperació](imatges/ca/01b-codi-recuperacio.jpg)

> Per a començar a treballar de veres sense les dades d'exemple, tanca Compàs, esborra la carpeta `data` del programa i torna a obrir-lo.

## 2. La pantalla principal

![Pantalla d'inici](imatges/ca/02-inici.jpg)

- **Menú lateral** (esquerra): totes les seccions, agrupades per Escola de música, Agrupacions, Instruments, Documents i dades (llistats, informes i importació) i Administració.
- **Dalt a la dreta**: canvi d'idioma (`VAL` / `ES`), el teu nom d'usuari (per a canviar la contrasenya) i **Ix** per a eixir.
- **Inici**: un resum amb el nombre de persones, socis, matriculats, grups, músics i instruments cedits. Fes clic en qualsevol xifra per a anar a la llista.
- **Avisos**: les cessions d'instruments amb la devolució vençuda, les classes de hui i els avisos de còpies de seguretat.

## 3. Com es treballa amb Compàs

Totes les seccions funcionen igual. Si n'aprens una, les saps totes.

### Llistats

![Llistat de persones](imatges/ca/04-persones-llista.jpg)

- **Cerca**: escriu un nom, cognom, telèfon… i prem **Filtra**.
- **Mostra també les baixes**: per defecte s'amaguen les persones inactives i les baixes.
- **Exporta CSV**: descarrega la llista per a obrir-la amb Excel o LibreOffice Calc.
- **+ Nou**: crea un element nou.
- Fes clic en qualsevol fila per a obrir-ne la **fitxa**.

### Fitxes

La fitxa mostra totes les dades d'un element i tot el que hi està relacionat. Per exemple, la fitxa d'una persona mostra si és sòcia, alumna, professora, música…

![Fitxa d'una persona](imatges/ca/06-persona-fitxa.jpg)

- **Edita**: canvia les dades.
- **Elimina**: només es pot si no té res relacionat (vegeu «Baixes» més avall).
- **+ Afig** de cada apartat: crea un element **ja vinculat** a aquesta fitxa. És la manera més ràpida de treballar.

### Formularis

- Els camps amb asterisc (*) són obligatoris.
- En els desplegables llargs (persones, grups…) hi ha una **lupa** damunt: escriu-hi per a filtrar les opcions.
- Prem **Desa** per a guardar o **Cancel·la** per a tornar sense guardar.

### Baixes: per què no s'elimina res

Compàs no deixa eliminar res que tinga historial (matrícules, notes, cessions…), perquè no es perda informació. En lloc d'eliminar:

- una **persona** que ja no té relació amb la societat: edita-la i desmarca **Actiu**;
- un **soci, alumne, professor o músic**: edita'l, posa l'**estat «Baixa»** i la **data de baixa**.

Les baixes desapareixen dels llistats, però es poden tornar a veure marcant **Mostra també les baixes**.

## 4. Configuració de la societat i logotip

Menú **Administració → Configuració** (només l'administrador/a).

1. Omple les dades de la societat: nom, CIF, adreça, telèfon, correu i web. Ixen als butlletins.
2. A **Logotip de la societat**, prem el botó de triar fitxer i selecciona la imatge del logotip (PNG, JPG o WEBP, fins a 3 MB).
3. Prem **Desa**. El logotip apareix al menú i als butlletins.

![Configuració i logotip](imatges/ca/03-configuracio.jpg)

## 5. Preparar el curs acadèmic

Abans de matricular cal tindre el curs preparat. Ordre recomanat: **curs → períodes → assignatures i aules → professorat → grups → horaris**.

### 5.1 Curs i períodes d'avaluació

1. Menú **Cursos acadèmics → + Nou**.
2. Nom (per exemple `2026/2027`), data d'inici i de finalització.
3. Marca **Curs actual**. Només n'hi pot haver un; és el que s'utilitza en les notes i els butlletins.
4. Prem **Desa**. S'obri la fitxa del curs.
5. A l'apartat **Períodes d'avaluació**, prem **+ Afig** i crea'n un per a cada avaluació (`1r trimestre`, `2n trimestre`, `3r trimestre`), amb l'ordre 1, 2, 3.

![Fitxa del curs amb els períodes, grups i matrícules](imatges/ca/21-curs-fitxa.jpg)

### 5.2 Assignatures i aules

- Menú **Assignatures → + Nou**: nom (Llenguatge musical, Trompeta, Clarinet…) i tipus.
- Menú **Aules → + Nou**: nom i capacitat.

Només cal crear-les una vegada: es reutilitzen cada curs.

### 5.3 Professorat

Un professor és una **persona** amb fitxa de professor:

1. Dona d'alta la persona (apartat 6) o busca-la si ja existeix.
2. A la seua fitxa, en l'apartat **Professorat**, prem **+ Afig**.
3. Comprova la data d'alta i prem **Desa**.

![Alta d'un professor](imatges/ca/20-professor-nou.jpg)

### 5.4 Grups i horaris

Un **grup** és una assignatura impartida en un curs per un professor: per exemple *Llenguatge musical · 1r A* o *Trompeta · Individual*.

1. Menú **Grups → + Nou**.
2. Tria el **curs**, l'**assignatura**, escriu el **nom** del grup i tria el **professor** i l'**aula**.
3. Prem **Desa**. S'obri la fitxa del grup.

![Nou grup](imatges/ca/22-grup-nou.jpg)

4. A l'apartat **Horari**, prem **+ Afig**, tria el **dia** i les hores d'inici i de fi, i prem **Desa**. Repeteix-ho si el grup té classe més d'un dia.

![Afegir una franja horària](imatges/ca/23-horari-nou.jpg)

![Fitxa del grup amb l'horari](imatges/ca/24-grup-fitxa.jpg)

Des de la fitxa del grup també pots anar directament a les **Qualificacions** del grup.

### 5.5 Horari general

El menú **Horari** mostra totes les classes de la setmana. Pots filtrar per professor o per aula per a imprimir l'horari de cadascú.

![Horari general](imatges/ca/25-horari.jpg)

### 5.6 Passar al curs següent

Quan s'acosta el curs nou no cal crear-ho tot des de zero:

1. Menú **Cursos acadèmics**, obri el curs que acaba i prem **⏭ Passa al curs següent**.
2. Comprova el **nom** i les **dates** del curs nou (Compàs els proposa: 2027/2028, un any més).
3. Tria què es copia: els **períodes d'avaluació**, els **grups** amb el professor/a i l'aula, i l'**horari**. Si vols, també pots **matricular ja els alumnes que continuen** (sense grups).
4. Deixa marcat **Marca el curs nou com a curs actual** i, si vols, **Tanca** el curs anterior.
5. Prem **Crea el curs nou**.

![Assistent per a passar de curs](imatges/ca/50-passa-curs.jpg)

Després només cal ajustar el que canvie (un grup nou, un altre professor…) i inscriure els alumnes als grups. Les notes no es copien mai, i el curs anterior es conserva sencer.

## 6. Donar d'alta una persona

> **Una persona s'introdueix una sola vegada.** Ser alumne, soci, músic, professor o tutor legal són *funcions* que s'afigen a la seua fitxa. Abans de crear-ne una, **busca-la** al llistat per si ja existeix.

1. Menú **Persones → + Nou**.
2. Omple les dades obligatòries i la resta que tingues (correu, adreça…):

| Dada | Obligatòria |
|---|---|
| Nom, cognoms i data de naixement | Sempre |
| DNI/NIE i telèfon | Només si la persona és **major d'edat** (18 anys o més) |

3. Deixa marcat **Actiu**.
4. Prem **Desa**.

En posar una data de naixement de major d'edat, els camps DNI i telèfon passen a ser obligatoris i apareix l'asterisc al costat.

![Formulari de persona nova](imatges/ca/05-persona-nova.jpg)

S'obri la **fitxa de la persona**, amb un apartat per a cada funció: Socis, Alumnat, Professorat, Músics i membres, Tutors legals (quan és tutor d'algú) i Cessions.

### Quan un alumne menor fa 18 anys

Dels menors no cal tindre el DNI ni el telèfon. Quan una persona fa 18 anys i no té eixes dades, Compàs ho avisa:

- Dalt del llistat de **Persones** apareix un **avís groc** amb el nombre de persones a qui falten dades.
- La seua fila es marca **en groc** amb el símbol ⚠. Si passes el ratolí per damunt, diu quines dades falten.
- A la seua fitxa també apareix l'avís, amb un enllaç per a editar-la.

![Avís de persones amb dades que falten](imatges/ca/04-persones-llista.jpg)

Prem **Mostra-les** (o tria el filtre **Falten dades obligatòries**) per a veure només eixes persones. Obri cadascuna, prem **Edita**, completa el DNI i el telèfon i desa. L'avís desapareix quan ja no en falta cap.

![Persones amb dades que falten](imatges/ca/04b-persones-avis.jpg)

### Drets d'imatge (fotos i vídeos)

Abans de publicar fotos o vídeos en la web o les xarxes, la societat ha de tindre l'autorització de cada persona; per als menors, la del pare, la mare o el tutor/a.

1. Obri la fitxa de la persona (o de l'alumne) i prem **🖨 Full de consentiment**. Ix un full A4 amb les dades de l'alumne i del tutor/a, el text de l'autorització i l'espai per a signar.
2. Quan el tornen signat, edita la persona i, a **Drets d'imatge**, tria **Autoritza fotos i vídeos** o **No autoritza**. Si no poses la **data de la signatura**, es posa la d'avui.

![Drets d'imatge a la fitxa de la persona](imatges/ca/41-persona-consentiment.jpg)

![Full de consentiment per a signar](imatges/ca/42-consentiment-full.jpg)

Per a repartir-los a principi de curs, a **Llistats i informes → Drets d'imatge** pots imprimir de colp els fulls de tots els pendents o els d'un grup (apartat 13). Al llistat de **Persones**, el filtre **Alumnes sense consentiment d'imatge signat** mostra qui falta.

> El text del full és un model. Revisa'l amb la junta i, si cal, afig-hi el que vulgues a **Configuració → Text addicional del full de consentiment**.

## 7. Alta d'un alumne (pas a pas)

Exemple complet: un xiquet nou que s'apunta a Llenguatge musical i Clarinet. El tutor legal (la mare o el pare) també ha d'estar donat d'alta com a persona.

### Pas 1. La persona

Dona d'alta l'alumne com a **persona** (apartat 6). Si el tutor legal encara no està donat d'alta, dona'l d'alta també.

### Pas 2. La fitxa d'alumne

1. A la fitxa de la persona, en l'apartat **Alumnat**, prem **+ Afig**.

![Afegir la fitxa d'alumne des de la persona](imatges/ca/06-persona-fitxa.jpg)

2. La persona ja ve triada. Comprova la **data d'alta** i prem **Desa**.

![Fitxa d'alumne nova](imatges/ca/07-alumne-nou.jpg)

S'obri la **fitxa de l'alumne**, on es completen els passos següents.

### Pas 3. El tutor legal

1. A l'apartat **Tutors legals**, prem **+ Afig**.

![Fitxa de l'alumne acabat de crear](imatges/ca/08-alumne-fitxa.jpg)

2. Al desplegable **Tutor legal**, escriu a la lupa el nom del pare, la mare o el tutor i tria'l.
3. Tria la **relació** (mare, pare, tutor/a legal…) i marca **Principal** si és el contacte principal.
4. Prem **Desa**. Repeteix-ho si hi ha més d'un tutor.

![Afegir el tutor legal](imatges/ca/09-tutor-nou.jpg)

> Germans: tots dos alumnes poden tindre el mateix tutor. No cal donar-lo d'alta dues vegades.

### Pas 4. La matrícula del curs

1. A la fitxa de l'alumne, en l'apartat **Matrícules**, prem **+ Afig**.
2. Comprova que el **curs** és l'actual i prem **Desa**.

![Matrícula nova](imatges/ca/10-matricula-nova.jpg)

### Pas 5. Inscriure'l als grups

S'obri la fitxa de la matrícula.

1. A l'apartat **Inscripcions als grups**, prem **+ Afig**.

![Fitxa de la matrícula](imatges/ca/11-matricula-fitxa.jpg)

2. Tria el **grup** (per exemple *Llenguatge musical · 1r A*) i prem **Desa**.
3. Repeteix-ho per a cada assignatura (per exemple *Clarinet · Individual*).

![Inscripció en un grup](imatges/ca/12-inscripcio-grup.jpg)

![Matrícula amb els grups inscrits](imatges/ca/13-matricula-grups.jpg)

### Resultat

La fitxa de l'alumne mostra el resum del curs: grups, professors, horari i notes de cada avaluació, el botó per a **imprimir-ne el butlletí**, els tutors legals i les matrícules de tots els cursos.

![Fitxa de l'alumne amb el resum del curs](imatges/ca/14-alumne-resum.jpg)

### En començar un curs nou

1. Crea el curs nou amb **Passa al curs següent** (apartat 5.6): copia els períodes, els grups i l'horari, i pot matricular ja els alumnes que continuen.
2. Inscriu cada alumne als grups del curs nou des de la seua matrícula (pas 5).

Les matrícules i les notes dels cursos anteriors es conserven a la fitxa de l'alumne.

### Quan un alumne deixa l'escola

Edita la seua fitxa d'alumne i posa l'estat **Baixa** i la **data de baixa**. Si a més ja no és res de la societat, desmarca **Actiu** a la seua fitxa de persona.

## 8. Socis

### Alta d'un soci

1. Busca o dona d'alta la **persona** (apartat 6).
2. A la seua fitxa, en l'apartat **Socis**, prem **+ Afig**.
3. Escriu el **número de soci**, tria el **tipus** (ordinari, protector, d'honor, juvenil) i comprova la **data d'alta**.
4. Prem **Desa**.

![Alta d'un soci](imatges/ca/15-soci-nou.jpg)

### Llistat de socis

El menú **Socis** mostra tots els socis actius. Pots buscar pel nom o pel número i exportar la llista a CSV (per exemple, per a fer etiquetes o enviar una circular).

![Llistat de socis](imatges/ca/16-socis-llista.jpg)

### Baixa d'un soci

1. Obri el soci i prem **Edita**.
2. Canvia l'**estat** a **Baixa** i posa la **data de baixa**.
3. Prem **Desa**.

![Baixa d'un soci](imatges/ca/17-soci-baixa.jpg)

El soci desapareix del llistat, però es conserva amb el seu historial.

## 9. Músics i agrupacions

### Alta d'un músic en una agrupació

1. Busca o dona d'alta la **persona** (apartat 6).
2. A la seua fitxa, en l'apartat **Músics i membres**, prem **+ Afig**.
3. Tria l'**agrupació** (Banda, Banda juvenil, Coral…), escriu l'**instrument o veu** i tria la **funció**: membre, director/a titular, director/a assistent o responsable.
4. Prem **Desa**.

![Alta d'un músic](imatges/ca/18-membre-nou.jpg)

Una persona pot ser de diverses agrupacions: repeteix-ho per a cadascuna.

### Fitxa de l'agrupació

Menú **Agrupacions** → obri l'agrupació. Mostra tots els seus membres amb l'instrument i la funció. Des d'ací també pots afegir membres amb **+ Afig**.

![Fitxa d'una agrupació](imatges/ca/19-agrupacio-fitxa.jpg)

Per a crear una agrupació nova: menú **Agrupacions → + Nou**.

## 10. Qualificacions

El professorat passa les notes a administració i administració les introdueix.

1. Menú **Qualificacions**.
2. Tria el **grup** i l'**avaluació** (1r trimestre, 2n trimestre…).
3. Escriu la nota de cada alumne. És text lliure: `7`, `8,5`, `Notable`, `Apte`…
4. Si el professor ha passat observacions, escriu-les a **Observacions acadèmiques**. Ixen al butlletí.
5. Prem **Desa**.

![Introduir les notes](imatges/ca/27-qualificacions.jpg)

Per a esborrar una nota, deixa en blanc la nota i les observacions i desa.

## 11. Butlletins de notes

1. Menú **Butlletins** (o el botó **Butlletins** de la pàgina de qualificacions, o **Imprimeix el butlletí** de la fitxa d'un alumne).
2. Tria l'**avaluació**. El butlletí mostra les notes d'aquesta avaluació i de les anteriors.
3. Tria **de qui**: tot l'alumnat matriculat, l'alumnat d'un grup o un sol alumne.
4. Si vols, escriu un **text al peu** (per exemple, la data de les audicions). Es recorda per a la pròxima vegada.
5. Prem **Prepara els butlletins**.

![Preparar els butlletins](imatges/ca/28-butlletins.jpg)

S'obri una pestanya nova amb un full A4 per alumne. Prem **Imprimeix el butlletí** (dalt de tot).

![Butlletí preparat per a imprimir](imatges/ca/29-butlleti-impres.jpg)

> Per a guardar-los en PDF (per a enviar-los per correu), tria **Microsoft Print to PDF** com a impressora.

## 12. Instruments i cessions

### Alta d'un instrument

1. Menú **Inventari → + Nou**.
2. Omple el **número d'inventari** i el **tipus** (obligatoris) i la resta de dades: marca, model, número de sèrie, estat, ubicació i data d'adquisició.
3. Prem **Desa**.

![Alta d'un instrument](imatges/ca/30-instrument-nou.jpg)

A la fitxa de l'instrument hi ha l'historial de **cessions** i de **manteniments** (reparacions, revisions…).

### Deixar un instrument (cessió)

1. A la fitxa de l'instrument, en l'apartat **Cessions**, prem **+ Afig**.
2. Tria la **persona**, comprova la **data d'entrega** i posa la **devolució prevista**, l'estat en què s'entrega i la fiança si n'hi ha.
3. Prem **Desa**.

![Nova cessió](imatges/ca/31-cessio-nova.jpg)

Un instrument no es pot deixar a dues persones alhora.

### Quan el tornen

1. Obri la cessió i prem **Edita**.
2. Posa la **data de devolució** i l'**estat en la devolució**.
3. Prem **Desa**. L'instrument torna a estar disponible.

![Registrar la devolució](imatges/ca/32-cessio-retorn.jpg)

### Llistat de cessions

Menú **Cessions**. Amb el filtre pots veure les **cessions actives** o només les que tenen la **devolució vençuda**. Les vençudes també apareixen a l'inici.

![Llistat de cessions](imatges/ca/33-cessions-llista.jpg)

## 13. Llistats, certificats i memòria

Menú **Documents i dades → Llistats i informes**. Cada llistat es pot veure a la pantalla, **imprimir** (o guardar en PDF) i **exportar a CSV** per a obrir-lo amb Excel.

![Llistats i informes](imatges/ca/43-llistats.jpg)

### Alumnes d'un grup

Tria el curs i el grup. Ix cada alumne amb l'edat, el pare, la mare o el tutor/a i els seus telèfons i correus. És el llistat que necessita el professorat. En groc, els que no autoritzen fotos.

![Alumnes d'un grup amb els telèfons dels tutors](imatges/ca/44-llistat-grup.jpg)

### Socis per número i músics per instrument

- **Socis per número**: tots els socis d'alta, ordenats pel número, amb DNI i contacte. Pots triar un tipus de soci.
- **Músics per instrument**: tria una agrupació i ix la plantilla agrupada per instrument o veu, amb el nombre de músics de cadascun.

### Drets d'imatge

Qui pot eixir en fotos i vídeos: el resum (quants autoritzen, quants no i quants falten) i la llista, amb els que no autoritzen en groc. Abans d'un concert, imprimeix-la per a qui fa les fotos. El botó **Fulls de consentiment dels pendents** imprimeix els fulls que falten.

![Drets d'imatge](imatges/ca/45-drets-imatge.jpg)

### Etiquetes per a les circulars

1. Tria **per a qui**: socis, famílies de l'alumnat (els tutors que reben comunicacions), músics o totes les persones.
2. Tria el **format** del full d'etiquetes (ho diu la capsa: 24, 21 o 14 per full).
3. Si el full ja està començat, indica quantes etiquetes **ja s'han gastat**.
4. Prem **Prepara les etiquetes** i imprimeix amb la **mida real (100 %)** i **sense marges**.

![Etiquetes](imatges/ca/46-etiquetes.jpg)

> Fes primer una prova en paper normal amb **Mostra les vores** i posa-la damunt del full d'etiquetes a contrallum.

### Antiguitat i insígnies

Tria l'**any** i els **anys que es premien** (per defecte 25 i 50). Ix qui els complix eixe any, com a soci i com a músic. Marca **Mostra també la llista completa** per a veure tothom ordenat per antiguitat.

![Antiguitat i insígnies](imatges/ca/47-antiguitat.jpg)

L'antiguitat es compta des de la data d'alta. Per als músics, des de la primera alta en qualsevol agrupació.

### Memòria anual i subvencions

Tria el curs. Ix el que solen demanar la Diputació, l'IVC o l'ajuntament: alumnes per edat (amb menors i majors), per assignatura i per població, professorat i grups, músics de cada agrupació (amb els menors), socis per tipus i les altes i baixes de l'any.

![Dades per a la memòria anual](imatges/ca/48-memoria.jpg)

### Certificats

A la fitxa d'un **soci**, d'una **matrícula** o d'un **músic** (a **Músics i membres**) hi ha el botó **🖨 Certificat**. Ix un certificat en A4 amb el logotip, les dades de la persona i la data d'avui, preparat per a signar. Serveix per a beques, el conservatori, la feina…

![Certificat de soci](imatges/ca/49-certificat.jpg)

La primera vegada, a **Configuració → Certificats i fulls de consentiment**, posa qui signa (normalment el secretari/ària), el càrrec i, si voleu el vist i plau, el president/a. Posa també la **població** de la societat, que ix al final del certificat.

![Dades dels certificats a la configuració](imatges/ca/40-config-documents.jpg)

## 14. Importar dades des d'Excel

Si la societat ja té els socis o l'alumnat en un Excel, no cal tornar-los a escriure. Menú **Documents i dades → Importa dades**.

### Pas 1. El fitxer

1. Tria què vols importar: **Persones**, **Socis** (amb el número, el tipus i la data d'alta) o **Alumnat** (amb el pare, la mare o el tutor/a, si el full els porta).
2. Tria el fitxer: Excel (**.xlsx**) o **CSV**. Si és un .xls antic, obri'l amb Excel i guarda'l com a .xlsx.
3. Deixa marcat **La primera fila té els títols de les columnes** si és el cas, i prem **Següent**.

![Importar: triar el fitxer](imatges/ca/51-importa.jpg)

Si comences de zero, baixa una **plantilla**, omple-la i puja-la.

### Pas 2. Què és cada columna

Compàs reconeix les columnes habituals (Nom, Cognoms, DNI, Telèfon, Data de naixement…) i ja les deixa triades. Comprova-les i corregeix les que falten. Si el nom i els cognoms estan junts, tria **Nom i cognoms junts**. Les columnes que no tries no s'importen.

![Importar: què és cada columna](imatges/ca/52-importa-columnes.jpg)

### Pas 3. Revisar i importar

Abans d'importar res, Compàs mostra cada fila:

| Estat | Què vol dir | Què es fa |
|---|---|---|
| **Nova** | No està a Compàs | Es crea |
| **Ja existeix** | Mateix DNI, o mateix nom i data de naixement | No es duplica: només es completen les dades que li falten |
| **Possible duplicat** | Mateix nom, però no es pot assegurar | Marca **És la mateixa persona** si ho és; si no, es crea nova |
| **Repetida al fitxer** / **Error** | Està dues vegades o no té nom | No s'importa |

A la columna **Avisos** veuràs, per exemple, dates que no s'entenen o persones a qui falten dades obligatòries (quedaran en groc, com sempre).

![Importar: revisió abans d'importar](imatges/ca/53-importa-revisa.jpg)

Prem **Importa**. Abans d'importar, Compàs fa una còpia de seguretat («Abans d'importar»): si el resultat no és el que esperaves, la pots restaurar (apartat 15).

## 15. Còpies de seguretat

Totes les dades estan en un sol fitxer. Compàs en fa còpies automàticament:

| Quan | Tipus |
|---|---|
| La primera vegada que s'utilitza cada dia | Automàtica (diària) |
| En tancar Compàs, si hi ha hagut canvis | Automàtica (en tancar) |
| Quan prems **Fes una còpia ara** | Manual |
| Just abans de restaurar | Abans de restaurar |
| Just abans d'importar dades | Abans d'importar |

Menú **Administració → Còpies de seguretat**:

![Còpies de seguretat](imatges/ca/34-copies.jpg)

### Configurar la carpeta externa (molt important)

Si l'ordinador s'espatlla o el roben, les còpies que hi ha dins es perden amb ell. Cal guardar-les també fora:

1. Connecta un **disc o llapis USB**, o tria una carpeta **sincronitzada amb el núvol** (OneDrive, Google Drive…).
2. Crea-hi una carpeta, per exemple `Copies Compàs`, i copia'n la ruta des de l'Explorador de Windows (per exemple `E:\Copies Compàs`).
3. A **Configuració de les còpies**, enganxa la ruta a **Carpeta externa**.
4. Prem **Desa**.

![Carpeta externa de còpies](imatges/ca/35-copies-carpeta.jpg)

A partir d'ara, cada còpia es guarda també en aquesta carpeta. Si no està disponible (per exemple, el USB no està connectat) o fa més de 7 dies que no s'hi guarda res, l'inici ho avisa.

### Llista de còpies, restaurar i eliminar

![Llista de còpies](imatges/ca/36-copies-llista.jpg)

- **Baixa**: descarrega aquella còpia.
- **Restaura** (només l'administrador/a): substitueix totes les dades actuals per les de la còpia. Abans, Compàs guarda una còpia de les dades actuals («Abans de restaurar»), de manera que sempre es pot tornar arrere. Després cal tornar a entrar.
- **Elimina** (només l'administrador/a): esborra la còpia.

Per a restaurar una còpia guardada fora (per exemple, des del USB), utilitza **Restaura des d'un fitxer**, al final de la pàgina.

> Les còpies contenen dades personals, també de menors. Guarda-les en un lloc segur.

### Còpies externes xifrades

Si la carpeta externa és un llapis USB o el núvol, convé xifrar-les: si es perd el USB, ningú podrà obrir les dades sense la contrasenya.

1. A **Configuració de les còpies**, marca **Xifra amb contrasenya les còpies de la carpeta externa**.
2. Escriu una contrasenya (almenys 8 caràcters) dues vegades i prem **Desa**.

![Còpies externes xifrades](imatges/ca/54-copies-xifrat.jpg)

A partir d'ara, les còpies de la carpeta externa acaben en `.xifrat`. Les de l'ordinador no es xifren. Per a restaurar-ne una, a **Restaura des d'un fitxer** tria el fitxer `.xifrat` i escriu la contrasenya.

> **Apunta la contrasenya en paper** junt amb el codi de recuperació. Si s'oblida, les còpies xifrades no es poden recuperar.

## 16. Usuaris i contrasenyes

### Canviar la teua contrasenya

Cada usuari pot canviar la seua quan vulga:

1. Al menú, a baix de tot, prem **Compte i contrasenya** (o fes clic en el teu nom d'usuari, dalt a la dreta).
2. Escriu la **contrasenya actual**.
3. Escriu la **contrasenya nova** dues vegades (almenys 8 caràcters).
4. Prem **Desa**.

Si no recordes l'actual, no la pots canviar ací: l'administratiu/va ha de demanar-ne una de nova a l'administrador/a, i l'administrador/a pot recuperar l'accés des de l'entrada (vegeu més avall).

![El meu compte](imatges/ca/38-meu-compte.jpg)

### Gestionar els comptes (administrador/a)

Menú **Administració → Usuaris**. Obri un compte per a:

- canviar-li el **rol** (administrador/a o administratiu/va);
- posar-li una **contrasenya nova** si l'ha oblidada (deixa-la en blanc per a no canviar-la);
- desactivar-lo (desmarca **Actiu**) o eliminar-lo.

![Editar un usuari](imatges/ca/37-usuari.jpg)

Sempre ha de quedar almenys un administrador actiu.

### Si l'administrador/a oblida la contrasenya

1. A la pantalla d'entrada, prem **Eres l'administrador/a? Recupera l'accés**.
2. Escriu el teu **usuari**, el **codi de recuperació** que vas guardar i la **contrasenya nova** dues vegades.
3. Prem **Canvia la contrasenya**. Entraràs directament al programa.
4. Compàs mostra un **codi de recuperació nou**, perquè l'anterior ja no val. Imprimeix-lo i guarda'l en lloc de l'antic.

![He oblidat la contrasenya](imatges/ca/00b-recuperacio.jpg)

**Si no trobes el codi**, prem **No tinc el codi de recuperació** a la mateixa pàgina i segueix els passos:

1. Obri l'Explorador de fitxers i ves a la carpeta de dades de Compàs. La pàgina mostra la ruta exacta: copia-la a la barra d'adreces de l'Explorador.
2. Crea-hi un fitxer nou (botó dret → **Nou → Document de text**) i posa-li de nom `recupera-acces`.
3. Torna a la pàgina de Compàs i recarrega-la (tecla **F5**). Ja no demanarà el codi: escriu l'usuari i la contrasenya nova.

El fitxer només val durant una hora i s'esborra sol en usar-lo. Este sistema és segur perquè només pot crear el fitxer qui ja té accés a l'ordinador i a les dades.

### Codi de recuperació

Si has perdut el codi o creus que algú l'ha vist, ves a **Administració → Usuaris** i, a baix, prem **Genera un codi nou**. L'anterior deixa de valdre. Si Compàs es va instal·lar abans de la versió 1.3, la pantalla d'inici avisa que no hi ha codi fins que el generes.

Totes les recuperacions, també les que fallen, queden al **Registre d'activitat**.

### Registre d'activitat

Menú **Administració → Registre d'activitat** (només l'administrador/a): qui ha creat, modificat o eliminat cada cosa, les entrades al programa, els butlletins impresos i les còpies.

## 17. Ajuda

El menú **Ajuda** mostra aquest manual dins del programa. Els botons de dalt obrin el manual i la guia d'instal·lació en PDF.

![Ajuda](imatges/ca/39-ajuda.jpg)

## 18. Preguntes freqüents

**He donat d'alta una persona dues vegades. Què faig?** Si la segona no té res vinculat, obri-la i prem **Elimina**. Si ja té coses vinculades, edita-les perquè apunten a la persona bona i després elimina la repetida.

**Per què una persona apareix en groc?** Li falten dades obligatòries: normalment, ha fet 18 anys i no té DNI o telèfon. Completa-les (apartat 6).

**No trobe un alumne al llistat.** Pot estar de baixa: marca **Mostra també les baixes**.

**No em deixa eliminar una cosa.** Té historial relacionat. Posa-la de baixa o com a inactiva (apartat 3).

**L'alumne no apareix en les notes del grup.** Comprova que té matrícula en el curs actual, en estat Actiu, i que està inscrit en aquell grup (apartat 7, pas 5).

**El butlletí no mostra una assignatura.** L'alumne no està inscrit en aquell grup dins de la seua matrícula.

**L'administrador/a ha oblidat la contrasenya.** Prem **Recupera l'accés** a la pantalla d'entrada i utilitza el codi de recuperació. Si no el trobes, també hi ha una manera sense codi (apartat 16).

**Hem de passar a Compàs els socis que tenim en Excel.** Utilitza **Importa dades** (apartat 14): no cal escriure'ls a mà, i les persones que ja hi ha no es dupliquen.

**Qui pot eixir en les fotos del concert?** **Llistats i informes → Drets d'imatge** (apartat 13).

**Compàs envia les nostres dades a algun lloc?** No. Tot es queda a l'ordinador i, si la configureu, a la carpeta externa de còpies. Ho explica la declaració de privacitat: menú **Ajuda → Privacitat**.

**He d'estar connectat a Internet?** No.

**On estan les dades?** A la carpeta `data` del programa (`data\compas.db`). La ruta exacta es veu a **Configuració**.

**Com passe Compàs a un altre ordinador?** Instal·la Compàs a l'ordinador nou i, a **Còpies de seguretat → Restaura des d'un fitxer**, carrega l'última còpia.
