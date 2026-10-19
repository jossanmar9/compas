# Manual d'usuari de Compàs

## Primers passos

Compàs funciona en un sol ordinador amb Windows i té dos comptes:

- **Administrador/a**: ho pot fer tot, també gestionar usuaris, la configuració, el registre d'activitat i restaurar còpies.
- **Administratiu/va**: porta la gestió diària (persones, socis, escola, notes, agrupacions, instruments) i pot fer còpies de seguretat.

La primera vegada que s'obri Compàs demana el nom de la societat, crea el compte d'administrador i, si vols, també el d'administratiu. Si marques «Carrega dades d'exemple» tindràs persones, grups i instruments de prova per a conéixer el programa.

Ordre recomanat per a començar de zero:

1. **Configuració**: dades de la societat i **logotip** (ix al menú i als butlletins).
2. **Còpies de seguretat**: indica una **carpeta externa** (disc USB o carpeta del núvol).
3. **Cursos acadèmics**: crea el curs (per exemple `2026/2027`), marca'l com a **curs actual** i afig-hi els **períodes d'avaluació** (1r trimestre, 2n trimestre…).
4. **Assignatures** i **Aules**.
5. **Persones**: dona d'alta totes les persones (socis, músics, alumnat, professorat, tutors…).
6. Des de la fitxa de cada persona, crea les seues **funcions**: soci, alumne, professor, membre d'una agrupació…
7. **Grups**: per a cada assignatura del curs, crea els grups, assigna professor i aula, i afig les **franges horàries**.
8. **Matrícules**: matricula cada alumne en el curs i inscriu-lo als grups.

## Com està organitzat

> Una persona s'introdueix **una sola vegada**. Ser soci, alumne, professor o músic són *funcions* d'eixa persona, no còpies de les seues dades.

Cada secció té un **llistat** (amb cerca, filtres i exportació a CSV), una **fitxa** (on es veu tot el que hi està relacionat) i un **formulari**.

- Fes clic en qualsevol fila per a obrir-ne la fitxa.
- Des de la fitxa, el botó **+ Afig** de cada apartat crea un element ja relacionat. Per exemple, des de la fitxa d'un alumne pots afegir-li un tutor legal o una matrícula.
- Per defecte, els llistats amaguen les baixes i les persones inactives. Marca «Mostra també les baixes» per a veure-les.
- **Exporta CSV** genera un fitxer que s'obri amb Excel o LibreOffice Calc.

### Per què no puc eliminar una persona?

Compàs no deixa eliminar res que tinga historial relacionat (matrícules, cessions, membres…). Així no es perd informació. En lloc d'eliminar, **marca-la com a inactiva** o posa-li la **data de baixa** i l'estat «Baixa».

## Persones i socis

- **Persones**: dades personals i de contacte. El camp «Actiu» indica si continua relacionada amb la societat.
- **Socis**: número de soci, tipus (ordinari, protector, d'honor, juvenil), data d'alta i de baixa. Una persona només pot tindre una fitxa de soci.

## Escola de música

### Alumnat, professorat i tutors legals

- Una persona es converteix en alumne o professor creant-li la fitxa corresponent.
- Els **tutors legals** són persones registrades. Des de la fitxa de l'alumne, afig-ne els que calga.
- La fitxa de l'alumne mostra el resum del curs actual: grups, horari, faltes i notes, amb un botó per a **imprimir-ne el butlletí**.

### Cursos, períodes i grups

- Només un curs pot ser el **curs actual**. És el que es fa servir a l'inici, en l'assistència i en les notes.
- Un **grup** és una assignatura impartida en un curs (per exemple, *Llenguatge musical · 1r A*). Pot tindre diverses franges horàries.

### Matrícules

La matrícula relaciona un alumne amb un curs. Dins de la matrícula s'afigen els **grups** on està inscrit. Així es conserva l'historial de cada curs.

### Horari

Mostra totes les classes del curs per dies. Pots filtrar per professor o per aula.

### Assistència

1. Tria el grup i la data (per defecte, hui).
2. Tots apareixen com a **presents**. Canvia només els que falten: **F** falta, **FJ** falta justificada, **R** retard.
3. Prem **Desa**.

Si tornes a obrir el mateix dia es veu el que es va desar i es pot corregir.

### Qualificacions

El professorat passa les notes a administració i administració les introdueix:

1. Ves a **Qualificacions**, tria el **grup** i l'**avaluació**.
2. Escriu la nota de cada alumne (text lliure: `7`, `8,5`, `Notable`, `Apte`…) i, si cal, les observacions del professor.
3. Prem **Desa**. Per a esborrar una nota, deixa la nota i les observacions en blanc.

### Butlletins

Des de **Butlletins** (o amb el botó de la pàgina de qualificacions o de la fitxa d'un alumne):

1. Tria el curs i l'**avaluació**. El butlletí mostra les notes d'eixa avaluació i de les anteriors; les observacions són les de l'avaluació triada.
2. Tria de qui: tot l'alumnat matriculat, l'alumnat d'un grup o un sol alumne.
3. Si vols, escriu un **text al peu** (es recorda per a la pròxima vegada).
4. Prem **Prepara els butlletins**. S'obri una pestanya nova amb un full A4 per alumne. Prem **Imprimeix**.

> Per a guardar-los en PDF (per a enviar-los per correu), tria «Microsoft Print to PDF» com a impressora.

El butlletí porta el logotip i les dades de la societat, les notes, les faltes, les observacions i espai per a les signatures.

## Agrupacions

Banda, banda juvenil, coral, orquestra, grups de cambra… Cada agrupació té els seus **membres** amb l'instrument o veu i la funció (membre, director titular, director assistent, responsable). Una persona pot ser de diverses agrupacions.

## Instruments

- **Inventari**: cada instrument amb número d'inventari, marca, model, número de sèrie, estat i ubicació. A la fitxa hi ha l'historial de **cessions** i de **manteniments**.
- **Cessions**: per a deixar un instrument, crea una cessió amb la persona, la data d'entrega i la de devolució prevista. Quan el tornen, edita la cessió i posa la **data de devolució**. Un instrument no es pot cedir dues vegades alhora.
- A l'inici apareixen les cessions amb la devolució **vençuda**.

## Còpies de seguretat

Totes les dades estan en un sol fitxer, `data\compas.db`. Compàs en fa còpies així:

| Quan | Tipus |
|---|---|
| La primera vegada que s'utilitza cada dia | Automàtica (diària) |
| En tancar Compàs, si hi ha hagut canvis | Automàtica (en tancar) |
| Quan prems **Fes una còpia ara** | Manual |
| Just abans de restaurar | Abans de restaurar |

- Les còpies es guarden a `data\copies`. Es conserven les últimes automàtiques (30 per defecte, configurable); les manuals no s'esborren soles.
- **Carpeta externa** (molt recomanable): si l'ordinador s'espatlla, les còpies de dins es perden amb ell. A **Còpies de seguretat → Configuració** indica una carpeta en un **disc USB** o en una carpeta **sincronitzada amb el núvol** (OneDrive, Google Drive…). Cada còpia es guardarà també allí.
- Si no hi ha carpeta externa, o fa més de 7 dies que no s'hi guarda res (per exemple, perquè el USB no està connectat), l'inici ho avisa.
- **Baixa una còpia a l'ordinador** descarrega una còpia per a guardar-la on vulgues.

### Restaurar (només l'administrador/a)

- Des de la llista: botó **Restaura** de la còpia que vulgues.
- Des d'un fitxer: **Restaura des d'un fitxer** i tria el `.db`.

Abans de restaurar, Compàs fa una còpia de les dades actuals («Abans de restaurar»), així que sempre es pot tornar arrere. La configuració de la carpeta externa es conserva. Després cal tornar a entrar.

> Les còpies contenen dades personals, també de menors. Guarda-les en un lloc segur.

## Usuaris

Només l'administrador/a. Des d'**Usuaris** pots canviar el nom, el rol, l'idioma i la contrasenya de cada compte, o desactivar-lo. Si algú oblida la contrasenya, l'administrador n'hi posa una de nova. Cadascú pot canviar la seua des de **El meu compte** (fent clic en el nom d'usuari, a dalt a la dreta).

## Registre d'activitat

Només l'administrador/a. Anota qui ha creat, modificat o eliminat cada element, les entrades al programa (també les fallides), les exportacions, els butlletins impresos i les còpies.

## Idioma

Compàs funciona en **valencià** i **castellà**. Canvia l'idioma amb els botons `VAL` / `ES` de dalt. Cada usuari pot tindre el seu idioma.

## Preguntes freqüents

**He d'estar connectat a Internet?** No. Compàs funciona sense Internet.

**On estan les dades?** A `data\compas.db`, dins de la carpeta del programa. La ruta exacta es veu a **Configuració**.

**Com passe Compàs a un altre ordinador?** Instal·la Compàs a l'ordinador nou i restaura-hi l'última còpia (des d'un fitxer). També pots copiar la carpeta `data` sencera amb Compàs tancat.
