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

En obrir `Compas.exe` apareix una finestra menuda i s'obri el navegador a `http://localhost:8740`. **Mentre la finestra estiga oberta, Compàs funciona.** En tancar-la, s'atura.

La primera vegada demana el nom de la societat i crea el compte d'administrador. Apunta la contrasenya: si es perd i no hi ha cap altre administrador, no es pot recuperar.

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
- `xarxa = 0` — només s'hi pot entrar des d'aquest ordinador.
- `navegador = 1` — obri el navegador en arrancar.

### Utilitzar-lo des d'altres ordinadors de la xarxa

Si vols que el professorat o la secretaria hi entren des dels seus equips (o des del mòbil, amb la wifi del local):

1. Posa `xarxa = 1` a `compas.ini` i torna a obrir Compàs.
2. Windows demanarà permís al **tallafoc**: accepta només per a **xarxes privades**.
3. La finestra de Compàs mostra l'adreça per a la resta d'equips, per exemple `http://192.168.1.20:8740/`.

Tingues en compte:

- L'ordinador amb Compàs ha d'estar **encés i amb la finestra oberta**.
- La connexió dins de la xarxa local **no va xifrada** (HTTP). Fes-ho només en una xarxa de confiança, mai en una wifi oberta. No obris el port al router cap a Internet.
- Convé que l'ordinador tinga sempre la mateixa IP (es pot reservar al router).

## Còpies de seguretat

Compàs fa una còpia automàtica diària a `data\copies`. A més, baixa'n una periòdicament des de **Còpies de seguretat** i guarda-la fora de l'ordinador.

## Solució de problemes

| Problema | Què fer |
|---|---|
| «El port 8740 ja està en ús» | Canvia el `port` a `compas.ini`. |
| El navegador no s'obri | Fes clic a l'enllaç de la finestra de Compàs o escriu `http://localhost:8740` al navegador. |
| «El servidor s'ha aturat» | Mira `data\servidor.log` i `data\php-errors.log`. |
| No arranca i diu que falten fitxers | Torna a instal·lar o a descomprimir. L'antivirus pot haver posat en quarantena `php.exe`. |
| Falta `VCRUNTIME140.dll` | Instal·la el *Microsoft Visual C++ Redistributable* (x64) des de la web de Microsoft. |
| Altres equips no hi poden entrar | Comprova `xarxa = 1`, el tallafoc de Windows (xarxa privada) i que estiguen en la mateixa xarxa. |

## Per a desenvolupar

Amb PHP 8.1 o posterior (amb `pdo_sqlite` i `mbstring`) instal·lat:

```
php -S 127.0.0.1:8740 -t app app/index.php
```

Les dades es guarden a `data/` (o a la carpeta que indique la variable d'entorn `COMPAS_DADES`).

Per a publicar una versió per a Windows, puja una etiqueta en minúscula (`v1.0.0`) i GitHub Actions construeix l'instal·lador i el zip portable.
