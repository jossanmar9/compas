# Manual d'usuari de Compàs

## Primers passos

La primera vegada que s'obri Compàs demana el nom de la societat musical i crea el compte d'**administrador del sistema**. Si marques «Carrega dades d'exemple» tindràs persones, grups i instruments de prova per a conéixer el programa.

Ordre recomanat per a començar de zero:

1. **Configuració**: revisa les dades de la societat.
2. **Cursos acadèmics**: crea el curs (per exemple `2026/2027`), marca'l com a **curs actual** i afig-hi els **períodes d'avaluació** (1r trimestre, 2n trimestre…).
3. **Assignatures** i **Aules**.
4. **Persones**: dona d'alta totes les persones (socis, músics, alumnat, professorat, tutors…).
5. Des de la fitxa de cada persona, crea les seues **funcions**: soci, alumne, professor, membre d'una agrupació…
6. **Grups**: per a cada assignatura del curs, crea els grups, assigna professor i aula, i afig les **franges horàries**.
7. **Matrícules**: matricula cada alumne en el curs i inscriu-lo als grups.
8. **Usuaris**: crea els comptes de qui ha d'entrar al programa i vincula'ls amb la seua persona.

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
- Els **tutors legals** són persones registrades. Des de la fitxa de l'alumne, afig-ne els que calga i indica si poden consultar la informació acadèmica i si reben comunicacions.

### Cursos, períodes i grups

- Només un curs pot ser el **curs actual**. És el que es fa servir a l'inici, en l'assistència i en «La meua fitxa».
- Un **grup** és una assignatura impartida en un curs (per exemple, *Llenguatge musical · 1r A*). Pot tindre diverses franges horàries.

### Matrícules

La matrícula relaciona un alumne amb un curs. Dins de la matrícula s'afigen els **grups** on està inscrit. Així es conserva l'historial de cada curs.

### Horari

Mostra totes les classes del curs per dies. Pots filtrar per professor o per aula.

### Assistència

1. Tria el grup i la data (per defecte, hui).
2. Tots apareixen com a **presents**. Canvia només els que falten: **F** falta, **FJ** falta justificada, **R** retard.
3. Prem **Desa**.

Si tornes a obrir el mateix dia es veu el que es va desar i es pot corregir. Davall hi ha la llista de dies registrats.

### Qualificacions

Tria el grup i el període i escriu la nota de cada alumne (text lliure: `7`, `8,5`, `Notable`, `Apte`…) i, si vols, observacions. L'alumnat i els tutors la veuen en «La meua fitxa».

> El professorat només veu els **seus** grups. Perquè funcione, el compte d'usuari del professor ha d'estar vinculat a la seua persona, i eixa persona ha de tindre fitxa de professor.

## Agrupacions

Banda, banda juvenil, coral, orquestra, grups de cambra… Cada agrupació té els seus **membres** amb l'instrument o veu i la funció (membre, director titular, director assistent, responsable). Una persona pot ser de diverses agrupacions.

## Instruments

- **Inventari**: cada instrument amb número d'inventari, marca, model, número de sèrie, estat i ubicació. A la fitxa hi ha l'historial de **cessions** i de **manteniments**.
- **Cessions**: per a deixar un instrument, crea una cessió amb la persona, la data d'entrega i la de devolució prevista. Quan el tornen, edita la cessió i posa la **data de devolució**. Un instrument no es pot cedir dues vegades alhora.
- A l'inici apareixen les cessions amb la devolució **vençuda**.

## La meua fitxa

Qualsevol usuari vinculat a una persona pot veure:

- les seues dades de contacte i de soci;
- si és alumne: grups, horari, faltes i notes del curs actual;
- si és tutor legal: el mateix per a cada alumne que té a càrrec (si té permís de consulta);
- si és professor: els seus grups, amb accés directe a l'assistència i les notes;
- les seues agrupacions i els instruments que té cedits.

## Usuaris, rols i permisos

- **Usuaris**: crea un compte per a cada persona que haja d'entrar. Pots assignar-li **diversos rols**; els permisos se sumen.
- **Rols i permisos**: una taula per a decidir què pot fer cada rol. Pots canviar el nom dels rols i crear-ne de nous. L'administrador del sistema sempre ho pot tot.
- Sempre ha de quedar almenys un usuari actiu que puga gestionar usuaris.
- Si algú oblida la contrasenya, l'administrador n'hi posa una de nova des de la seua fitxa d'usuari. Cadascú pot canviar la seua des de **El meu compte** (fent clic en el nom d'usuari, a dalt a la dreta).

## Còpies de seguretat

- Compàs fa **una còpia automàtica cada dia** que s'utilitza i guarda les 14 últimes a `data\copies`.
- Des de **Còpies de seguretat** pots baixar una còpia en qualsevol moment. Guarda-la fora de l'ordinador (USB, núvol de la societat…).
- **Restaurar** substitueix totes les dades per les de la còpia. Les dades anteriors es guarden a la carpeta de dades amb el nom `compas-abans-restaurar-…`.

> Les còpies contenen dades personals, també de menors. Guarda-les en un lloc segur i no les envies per canals oberts.

## Registre d'activitat

Anota qui ha creat, modificat o eliminat cada element, les entrades al programa (també les fallides), les exportacions i les còpies.

## Idioma

Compàs funciona en **valencià** i **castellà**. Canvia l'idioma amb els botons `VAL` / `ES` de dalt. Cada usuari pot tindre el seu idioma.

## Preguntes freqüents

**He d'estar connectat a Internet?** No. Compàs funciona sense Internet.

**On estan les dades?** En un sol fitxer: `data\compas.db`, dins de la carpeta del programa. La ruta exacta es veu a **Configuració**.

**Poden entrar diverses persones alhora?** Sí, si s'activa l'accés des de la xarxa (vegeu la guia d'instal·lació). L'ordinador on s'executa Compàs ha d'estar encés i amb el programa obert.

**Com passe Compàs a un altre ordinador?** Fes una còpia de seguretat, instal·la Compàs a l'ordinador nou i restaura-la. També pots copiar la carpeta `data` sencera amb Compàs tancat.
