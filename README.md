<p align="center"><img src="app/assets/logo.png" alt="Compàs: Gestió lliure per a societats musicals" width="260"></p>

# Compàs

**Gestió lliure per a societats musicals.** Un programa per a un ordinador amb Windows que porta en un sol lloc les persones, els socis, l'escola de música, les agrupacions i els instruments d'una societat musical.

*Gestión libre para sociedades musicales. Programa para Windows en valenciano y castellano.*

- 🖥️ **Un sol programa per a Windows**: instal·lador o versió portable. Porta PHP i la base de dades dins; no cal instal·lar res més.
- 🔌 **Sense Internet i sense servidor**: les dades es queden a l'ordinador de la societat, en un sol fitxer.
- 👥 **Dos comptes**: administrador/a i administratiu/va.
- 💾 **Còpies de seguretat** automàtiques (cada dia i en tancar) i a una carpeta externa (USB o núvol).
- 🗣️ **Valencià i castellà**.

## Mòduls (v1.0)

| Mòdul | Què fa |
|---|---|
| **Persones** | Registre únic de persones. Una mateixa persona pot ser sòcia, música, alumna, professora i tutora alhora sense duplicar dades. |
| **Socis** | Número, tipus, altes i baixes. |
| **Escola de música** | Cursos acadèmics i períodes, assignatures, aules, grups amb horari, matrícules, alumnat, professorat i tutors legals. |
| **Assistència i qualificacions** | Administració registra les faltes i les notes que passa el professorat. |
| **Butlletins** | Butlletins de notes per a imprimir o guardar en PDF, amb el logotip de la societat. |
| **Agrupacions** | Banda, coral, orquestra… amb membres, instrument o veu i direcció. |
| **Instruments** | Inventari, manteniments i cessions amb control de devolucions vençudes. |
| **Administració** | Usuaris, configuració i logotip, registre d'activitat, còpies de seguretat i restauració. |

Pròximament: gestió econòmica (quotes, rebuts, remeses), loteria, comunicacions, calendari i documents.

## Descàrrega i instal·lació

Baixa l'última versió des de **[Releases](../../releases)**:

- `Compas-X.Y.Z-installador.exe` — instal·lador (no necessita permisos d'administrador).
- `Compas-X.Y.Z-portable.zip` — sense instal·lar: descomprimeix i obri `Compas.exe`.

Guies: [instal·lació](docs/guia-installacio.md) · [instalación](docs/guia-instalacion.md)
Manuals: [manual d'usuari](docs/manual-usuari.md) · [manual de usuario](docs/manual-usuario.md) · En PDF: [manual d'usuari](docs/pdf/manual-usuari.pdf) · [manual de usuario](docs/pdf/manual-usuario.pdf) · [guia d'instal·lació](docs/pdf/guia-installacio.pdf) · [guía de instalación](docs/pdf/guia-instalacion.pdf)
Detalls tècnics: [arquitectura](docs/arquitectura.md)

## Tecnologia

- PHP 8 sense dependències externes, servit pel servidor integrat de PHP.
- SQLite (a través de PDO; preparat per a passar a MariaDB/MySQL si cal).
- HTML, CSS i JavaScript propis.
- Llançador per a Windows en C# (.NET Framework 4, inclòs en Windows).
- L'instal·lador i el zip es construeixen amb GitHub Actions en pujar una etiqueta `v*` (en minúscula).

Per a provar-lo sense Windows, amb PHP 8.1 o posterior:

```
php -S 127.0.0.1:8740 -t app app/index.php
```

## Ús d'intel·ligència artificial

Aquest projecte s'ha desenvolupat amb l'ajuda d'una intel·ligència artificial (Claude, d'Anthropic), que ha generat bona part del codi i de la documentació a partir de les especificacions i les revisions del projecte. Si hi trobes errades, obri una *issue*.

## Llicència

Compàs és programari lliure distribuït sota la llicència **GNU General Public License v2 o posterior (GPL-2.0-or-later)**. Pots utilitzar-lo, estudiar-lo, modificar-lo i redistribuir-lo d'acord amb els termes de la llicència. Consulta [LICENSE](LICENSE).

Les versions per a Windows inclouen PHP, distribuït sota la [PHP License](https://www.php.net/license/), i SQLite, de domini públic.
