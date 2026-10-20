# Canvis de Compàs

Ací s'apunten els canvis de cada versió, començant per la més nova, perquè qui actualitza sàpiga què trobarà.

Les versions segueixen el format `MAJOR.MENOR.CORRECCIÓ`: la **correcció** (1.2.**1**) arregla errades i porta millores menudes, la **menor** (1.**3**.0) afig funcions i la **major** (**2**.0.0) porta canvis que demanen atenció en actualitzar.

**Per a actualitzar:** tanca Compàs i executa l'instal·lador de la versió nova. Les dades i les còpies de seguretat es conserven.

*Registro de cambios de Compàs: cada versión, de la más nueva a la más antigua.*

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

[1.2.1]: https://github.com/jossanmar9/compas/releases/tag/v1.2.1
[1.2.0]: https://github.com/jossanmar9/compas/releases/tag/v1.2.0
[1.1.0]: https://github.com/jossanmar9/compas/releases/tag/v1.1.0
[1.0.0]: https://github.com/jossanmar9/compas/releases/tag/v1.0.0
