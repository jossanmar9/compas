# Proves de Compàs

Proves automàtiques sense PHPUnit ni Composer. No toquen `data/compas.db`.

## Proves de funcionament

```bash
php tests/run.php
```

Comproven:

- la normalització, el format i la validació de l'IBAN, i la conversió del compte espanyol antic (CCC) a IBAN;
- la validació del BIC, els identificadors SEPA i el text amb el joc de caràcters SEPA;
- el xifrat i el desxifrat de les còpies de seguretat, el rebuig d'una contrasenya incorrecta i la detecció d'una còpia manipulada.

Algunes necessiten les extensions `mbstring` i `sodium` de PHP.

GitHub Actions (`.github/workflows/tests.yml`) les executa en cada `push` a `main`, en cada *pull request* i a mà. També revisa la sintaxi de tots els fitxers PHP i comprova que el servidor local arranca.

## Prova de rendiment

```bash
php tests/performance.php
```

- Crea una base de dades SQLite temporal amb l'esquema real de `src/schema.sql`.
- Comprova que hi ha els índexs addicionals.
- Genera 100.000 rebuts i 300.000 línies.
- Amb `EXPLAIN QUERY PLAN`, comprova que les consultes més habituals fan servir els índexs, i executa cada consulta 100 vegades per a mesurar el temps.
- En acabar, esborra la base de dades temporal.

No s'executa en cada `push`, perquè és lenta.

## Com afegir proves

Abans de canviar la lògica de rebuts, SEPA, còpies, migracions o permisos, afig una prova a `tests/run.php` que comprove el comportament actual. Així es veu de seguida si el canvi trenca alguna cosa.
