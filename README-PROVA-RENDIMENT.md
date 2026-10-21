# Prova de rendiment de Compàs

Afig `tests/performance.php`.

La prova:
- crea una base SQLite temporal;
- carrega l'esquema real de `src/schema.sql`;
- comprova els 23 índexs addicionals;
- genera 100.000 rebuts i 300.000 línies;
- usa `EXPLAIN QUERY PLAN` per comprovar índexs en consultes representatives;
- executa 100 vegades cada consulta i mostra el temps;
- elimina la base temporal al final.

No toca `data/compas.db`.

El workflow existent executa `php tests/run.php`; per això este test de càrrega
es deixa separat, per no alentir tots els pushes. Es pot executar manualment amb:

    php tests/performance.php
