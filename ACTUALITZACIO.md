# Actualització de qualitat per a Compàs

Este paquet està pensat per copiar-se damunt de l'arrel del repositori.

## Què incorpora

- `tests/run.php`: proves automatitzades sense PHPUnit ni Composer.
- `.github/workflows/tests.yml`: executa les proves en cada `push` a `main`,
  en cada pull request i manualment.
- Manté intacta l'arquitectura actual de Compàs i no modifica la base de dades.

## Cobertura inicial

Les proves comproven:

- normalització, format i validació d'IBAN;
- conversió de CCC espanyol a IBAN;
- validació de BIC;
- identificadors i text SEPA;
- xifrat/desxifrat de còpies;
- contrasenya de còpia incorrecta;
- detecció de còpies xifrades manipulades;
- sintaxi de tots els PHP;
- arrencada real del servidor local.

## Execució local

Des de l'arrel del repositori:

```bash
php tests/run.php
```

Algunes proves requereixen `mbstring` i `sodium`. GitHub Actions instal·la les
extensions necessàries.

## Criteri de refactorització

No s'ha fet un refactor massiu de `bootstrap.php` ni `economia.php` en esta
primera fase. Primer s'afig una xarxa de seguretat automatitzada. A partir
d'ací es pot extraure lògica de negoci de manera incremental, afegint una
prova abans de cada canvi funcional.

Això redueix molt el risc de regressions en rebuts, SEPA, còpies, migracions
i permisos.
