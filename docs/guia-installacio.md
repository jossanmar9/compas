# Guia d'instal·lació de Compàs

## Requisits

- Windows 10 o 11 de 64 bits.
- Uns 100 MB de disc.
- No cal Internet, ni servidor, ni instal·lar PHP o bases de dades: tot va dins.

## Opció A: instal·lador (recomanada)

1. Baixa `Compas-X.Y.Z-installador.exe` de la pàgina de *Releases* del repositori.
2. Executa'l. No demana permisos d'administrador: s'instal·la per a l'usuari actual a `%LOCALAPPDATA%\Programs\Compas`.
3. Windows SmartScreen pot avisar que el programa no és conegut (no està signat digitalment). Prem **Més informació → Executa igualment**.
4. En acabar, obri **Compàs** des del menú Inici o l'escriptori.

Per a **actualitzar**, executa l'instal·lador de la versió nova amb Compàs tancat. Les dades i el fitxer `compas.ini` es conserven.

Si el **desinstal·les**, la carpeta `data` no s'esborra.

## Opció B: portable (sense instal·lar)

1. Baixa `Compas-X.Y.Z-portable.zip` i descomprimeix-lo on vulgues (per exemple, `Documents\Compas` o un llapis USB).
2. Obri `Compas.exe`.

Per a actualitzar, descomprimeix la versió nova i copia-hi la carpeta `data` de l'antiga (amb Compàs tancat).

## Com funciona

En obrir `Compas.exe` apareix una finestra menuda i s'obri el navegador a `http://localhost:8740`. **Mentre la finestra estiga oberta, Compàs funciona.** En tancar-la, fa una còpia de seguretat (si hi ha hagut canvis) i s'atura.

Compàs només es pot utilitzar des d'aquest ordinador: no és accessible des de la xarxa ni des d'Internet.

La primera vegada demana el nom de la societat i crea el compte d'administrador i, opcionalment, el d'administratiu/va. Apunta la contrasenya: si es perd i no hi ha cap altre administrador, no es pot recuperar.

## Carpeta del programa

```
Compas\
├── Compas.exe        llançador
├── compas.ini        configuració del llançador
├── app\  src\        el programa (PHP, HTML, CSS, JavaScript)
├── php\              PHP portable
├── docs\             manuals i guies
└── data\             LES DADES: compas.db, còpies automàtiques i registres
```

## Configuració (`compas.ini`)

Obri'l amb el Bloc de notes. Després de canviar-lo, tanca i torna a obrir Compàs.

- `port = 8740` — canvia'l si un altre programa ja l'utilitza.
- `navegador = 1` — obri el navegador en arrancar.

## Còpies de seguretat

Compàs fa còpies automàtiques cada dia i en tancar el programa (si hi ha hagut canvis), a `data\copies`. **Configura una carpeta externa** (disc USB o carpeta del núvol) a **Còpies de seguretat** perquè cada còpia es guarde també fora de l'ordinador. Vegeu el manual d'usuari.

## Solució de problemes

| Problema | Què fer |
|---|---|
| «El port 8740 ja està en ús» | Canvia el `port` a `compas.ini`. |
| El navegador no s'obri | Fes clic a l'enllaç de la finestra de Compàs o escriu `http://localhost:8740` al navegador. |
| «El servidor s'ha aturat» | Mira `data\servidor.log` i `data\php-errors.log`. |
| No arranca i diu que falten fitxers | Torna a instal·lar o a descomprimir. L'antivirus pot haver posat en quarantena `php.exe`. |
| Falta `VCRUNTIME140.dll` | Instal·la el *Microsoft Visual C++ Redistributable* (x64) des de la web de Microsoft. |

## Per a desenvolupar

Amb PHP 8.1 o posterior (amb `pdo_sqlite` i `mbstring`) instal·lat:

```
php -S 127.0.0.1:8740 -t app app/index.php
```

Les dades es guarden a `data/` (o a la carpeta que indique la variable d'entorn `COMPAS_DADES`).

Per a publicar una versió per a Windows, puja una etiqueta en minúscula (`v1.0.0`) i GitHub Actions construeix l'instal·lador i el zip portable.
