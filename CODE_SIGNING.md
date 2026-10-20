# Code signing policy

*Política de signatura de codi · Política de firma de código*

Free code signing provided by [SignPath.io](https://about.signpath.io), certificate by [SignPath Foundation](https://signpath.org).

## What is signed

Only the Windows files built from this repository's source code are signed: the launcher `Compas.exe` and the installer `Compas-X.Y.Z-installador.exe`. They are built automatically by [GitHub Actions](.github/workflows/publica.yml) from a tagged commit of this repository, and every signing request is approved manually before a release is published. Third-party components bundled with Compàs (the portable [PHP](https://www.php.net/) runtime and its libraries) are distributed exactly as published upstream and are not signed by this project.

## Team roles

The project is maintained by a single person, who holds every role:

| Role | Members |
|---|---|
| Committers and reviewers | [jossanmar9](https://github.com/jossanmar9) |
| Approvers | [jossanmar9](https://github.com/jossanmar9) |

All accounts with access to the repository and to SignPath use multi-factor authentication. Contributions from other people are only accepted through pull requests reviewed by the maintainer.

## Privacy

This program will not transfer any information to other networked systems unless specifically requested by the user or the person installing or operating it. See the full [privacy statement](PRIVACITAT.md).

---

## Política de signatura de codi (valencià)

Signatura de codi gratuïta proporcionada per [SignPath.io](https://about.signpath.io), amb certificat de la [SignPath Foundation](https://signpath.org).

Només se signen els fitxers per a Windows construïts a partir del codi d'este repositori (`Compas.exe` i l'instal·lador), generats automàticament per GitHub Actions des d'una versió etiquetada i aprovats a mà abans de publicar-los. Els components de tercers (PHP portable) es distribuïxen tal com els publica el seu projecte i no els signa Compàs. El projecte el manté una sola persona ([jossanmar9](https://github.com/jossanmar9)), que fa de desenvolupador, revisor i aprovador, amb verificació en dos passos. Compàs no transfereix cap informació a altres sistemes: vegeu la [declaració de privacitat](PRIVACITAT.md).

## Política de firma de código (castellano)

Firma de código gratuita proporcionada por [SignPath.io](https://about.signpath.io), con certificado de la [SignPath Foundation](https://signpath.org).

Solo se firman los archivos para Windows construidos a partir del código de este repositorio (`Compas.exe` y el instalador), generados automáticamente por GitHub Actions desde una versión etiquetada y aprobados a mano antes de publicarlos. Los componentes de terceros (PHP portable) se distribuyen tal como los publica su proyecto y Compàs no los firma. El proyecto lo mantiene una sola persona ([jossanmar9](https://github.com/jossanmar9)), que hace de desarrollador, revisor y aprobador, con verificación en dos pasos. Compàs no transfiere ninguna información a otros sistemas: véase la [declaración de privacidad](PRIVACITAT.md#privacidad-de-compàs).
