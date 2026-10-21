<?php
// Genera la web de presentació de Compàs (GitHub Pages) en valencià i castellà.
// Ús: php eines/web.php carpeta-eixida
// Copia el logotip, unes quantes captures (docs/imatges) i els PDF (docs/pdf)
// Copyright (C) 2026 José Vicente Sanjuan
// SPDX-License-Identifier: AGPL-3.0-or-later

declare(strict_types=1);

$arrel = dirname(__DIR__);
$eixida = rtrim($argv[1] ?? $arrel . '/_site', '/');
// Si es canvia el compte de GitHub, només cal canviar estes línies
$repo = 'https://github.com/jossanmar9/compas';
$correu = 'compas.lliure@gmail.com';
$formulari = 'https://forms.gle/qFcmkXbef3sCvywT8'; // enllaç al formulari de contacte (Google Forms), si n'hi ha
$versio = getenv('COMPAS_VERSIO') ?: (preg_match("/COMPAS_VERSIO = '([^']+)'/", file_get_contents($arrel . '/src/bootstrap.php'), $m) ? $m[1] : '');

$captures = ['web-inici', 'web-alumne', 'web-notes', 'web-butlleti', 'web-horari', 'web-rebuts', 'web-economia', 'web-loteria', 'web-agrupacio', 'web-llistat', 'web-copies'];

$T = [
    'ca' => [
        'lang' => 'ca-valencia', 'altre' => 'es', 'altre_nom' => 'Castellano', 'altre_url' => 'es/',
        'titol' => 'Compàs: gestió lliure per a societats musicals',
        'descripcio' => "Programa lliure i gratuït per a gestionar una societat musical des d'un ordinador amb Windows o Linux: persones, socis, escola de música, notes, butlletins, agrupacions, instruments, quotes, rebuts i loteria.",
        'lema' => 'Gestió lliure per a societats musicals',
        'hero' => 'Tota la societat musical, en un sol programa.',
        'hero_text' => "Socis, escola, músics, quotes, junta i documents, en un ordinador de la societat. Sense núvol, sense Internet i sense recollir cap dada. Gratuït i en valencià i castellà.",
        'nav_priv' => 'Privacitat',
        'priv_titol' => 'La privacitat, al centre',
        'priv_intro' => 'Compàs està fet al revés que les plataformes en línia: les dades de les persones de la societat (també les de l\'alumnat menor d\'edat) no ixen mai de l\'ordinador de la societat.',
        'priv_punts' => [
            ['Sense núvol', 'No hi ha cap servidor. El programa i les dades estan en un ordinador de la societat, i ningú no hi pot entrar des d\'Internet.'],
            ['No recull cap dada', 'Sense comptes en cap plataforma, sense analítica, sense telemetria i sense publicitat. Ni el programa ni esta web envien res enlloc.'],
            ['Funciona sense Internet', 'No cal connexió per a res. Les remeses del banc i els correus es preparen ací i els envieu vosaltres.'],
            ['Les dades són vostres', 'Programari lliure i gratuït, sense quotes ni límit de membres. Si un dia el deixeu de fer servir, les dades continuen a la vostra carpeta.'],
        ],
        'comp_titol' => 'Compàs i les plataformes en línia',
        'comp_cols' => ['', 'Compàs', 'Plataformes en línia'],
        'comp' => [
            ['On estan les dades', 'A l\'ordinador de la societat', 'Als servidors de l\'empresa'],
            ['Recollida de dades', 'Cap', 'Comptes d\'usuari i dades d\'ús'],
            ['Internet', 'No cal', 'Imprescindible'],
            ['Preu', 'Gratuït, sense límit de membres', 'Normalment, segons el nombre de membres'],
            ['Si el servei tanca o canvia de preu', 'El programa i les dades continuen sent vostres', 'Depeneu de l\'empresa'],
            ['Aplicació per als músics', 'No: ho porta la secretaria, que comparteix PDF i llistes', 'Sí, cada músic amb el seu compte'],
        ],
        'descarrega' => 'Descarrega (Windows i Linux)',
        'versio' => 'Versió %s. Gratuït i lliure.',
        'manual' => 'Llig el manual',
        'nav_que' => 'Què fa', 'nav_veure' => 'Captures', 'nav_com' => 'Com començar', 'nav_qui' => 'Per què', 'nav_codi' => 'Codi',
        'qui_titol' => 'Per què Compàs',
        'qui' => [
            'Soc José Vicente Sanjuan. Durant sis anys vaig ser president de la Societat Musical de Massalfassar, i en tot eixe temps vaig trobar a faltar un programa per a gestionar la societat que fora fàcil, útil i gratuït.',
            'La majoria de les bandes viuen amb els comptes molt justos, i els programes de gestió costen diners, i no pocs. Jo no sé programar, però estic al dia del que passa amb la intel·ligència artificial, i quan vaig tindre l\'oportunitat de provar-ne una vaig pensar: «I si faig el programa que necessitava la meua societat? Segur que també li servirà a més d\'una.»',
            'I ací està Compàs. No és perfecte ni brillant, però crec que és suficient. Proveu-lo, mireu-lo i, si vos agrada, gasteu-lo i parleu-ne amb altres societats musicals, escoles de música o acadèmies: també el poden fer servir.',
        ],
        'qui_signa' => 'José Vicente Sanjuan',
        'qui_contacte' => 'Si teniu dubtes o propostes, o voleu contar-me com vos va, escriviu-me a %s.',
        'qui_formulari' => 'També podeu omplir este formulari.',
        'qui_errades' => 'Si sabeu fer-ho, les errades també es poden avisar a GitHub.',
        'qui_lema' => 'Fet per l\'expresident d\'una societat musical, per a les societats musicals.',
        'qui_llig' => 'Per què el vaig fer',
        'anterior' => 'Captura anterior', 'seguent' => 'Captura següent',
        'contacte' => 'Contacte',
        'que_titol' => 'Què pots fer amb Compàs',
        'que_intro' => 'Pensat per a qui porta la secretaria i l\'escola de música, encara que no siga informàtic.',
        'moduls' => [
            ['Persones', 'Cada persona s\'introdueix una sola vegada. Pot ser sòcia, música, alumna, professora i mare d\'un alumne alhora, sense repetir dades.'],
            ['Socis', 'Número, tipus, altes i baixes. Etiquetes per a les circulars i la llista de qui fa 25 o 50 anys de soci.'],
            ['Escola de música', 'Cursos, assignatures, aules, grups amb horari, matrícules i tutors legals de l\'alumnat menor.'],
            ['Qualificacions', 'El professorat passa les notes i administració les introdueix per grup i avaluació, amb les observacions.'],
            ['Butlletins', 'Butlletins de notes amb el logotip de la societat, per a imprimir o guardar en PDF. Tots alhora o d\'un en un.'],
            ['Curs nou', 'Un assistent prepara el curs següent a partir de l\'anterior: trimestres, grups, professorat i horaris.'],
            ['Agrupacions', 'Banda, banda juvenil, coral, orquestra… Qui toca què i qui dirigeix cada formació.'],
            ['Instruments', 'Inventari, reparacions i préstecs, amb avís quan una devolució s\'ha passat de data.'],
            ['Quotes de soci', 'Tarifes per tipus de soci i periodicitat. Els rebuts de tot un trimestre, fets amb un clic i sense duplicar-ne cap.'],
            ['Matrícula i classes', 'Un rebut per família amb la matrícula o les classes de cada fill i els descomptes de soci i de germans.'],
            ['Remeses bancàries', 'Domiciliacions amb l\'ordre per a signar i el fitxer SEPA per a pujar al banc. Control dels rebuts retornats.'],
            ['Loteria', 'Talonaris repartits, participacions per a imprimir i qui ha liquidat i qui no.'],
            ['Junta i actes', 'Càrrecs amb historial i avís de renovació. Convocatòries, actes i certificats d\'acords a punt per a signar.'],
            ['Documents', 'Estatuts, reglament, assegurances i contractes guardats a Compàs, amb avís abans que caduquen.'],
            ['Comunicats', 'Llistes de correu de socis, músics o famílies per a enviar en còpia oculta, i registre del que s\'ha enviat.'],
            ['Llibre de caixa', 'Ingressos i despeses amb saldo. Els rebuts, les remeses i la loteria hi entren sols.'],
            ['Drets d\'imatge', 'Qui autoritza fotos i vídeos, amb els fulls de consentiment preparats per a signar.'],
            ['Llistats i certificats', 'Alumnes d\'un grup amb els telèfons de les famílies, dades per a la memòria anual i certificats amb el logotip.'],
            ['Importació des d\'Excel', 'Porta els socis i l\'alumnat que ja tens en Excel, sense duplicar ningú.'],
            ['Còpies de seguretat', 'Còpia automàtica cada dia i en tancar, també en un USB o en el núvol, xifrada amb contrasenya si vols.'],
        ],
        'veure_titol' => 'Així es veu',
        'veure_intro' => 'Captures reals del programa amb dades d\'exemple.',
        'captures' => [
            'web-inici' => 'La pantalla d\'inici: el resum de la societat, les classes de hui i els avisos.',
            'web-alumne' => 'La fitxa d\'un alumne: grups, horari, notes i tutors legals.',
            'web-notes' => 'Les notes d\'un grup i una avaluació, amb les observacions del professor.',
            'web-butlleti' => 'Un butlletí de notes preparat per a imprimir, amb el logotip de la societat.',
            'web-horari' => 'L\'horari de tota l\'escola, que es pot filtrar per professor o per aula.',
            'web-rebuts' => 'Els rebuts de l\'escola: un per família, amb el detall de cada alumne, abans de crear-los.',
            'web-economia' => 'El resum econòmic: el que hi ha per cobrar, el cobrat i qui deu diners.',
            'web-loteria' => 'La loteria de Nadal: talonaris repartits, venudes, tornades i diners lliurats.',
            'web-agrupacio' => 'Els membres d\'una agrupació, amb l\'instrument i la funció de cadascú.',
            'web-llistat' => 'Els alumnes d\'un grup amb els telèfons de les famílies, per al professorat.',
            'web-copies' => 'Les còpies de seguretat: automàtiques, manuals i en una carpeta externa.',
        ],
        'com_titol' => 'Com començar',
        'passos' => [
            ['Descarrega l\'instal·lador', 'Des de la pàgina de descàrregues, baixa <code>Compas-…-installador.exe</code>. També hi ha una versió portable que no cal instal·lar i paquets per a Linux (<code>.deb</code> per a Debian i Ubuntu, i <code>.tar.gz</code>).'],
            ['Instal·la\'l', 'No demana permisos d\'administrador. Com que encara no està signat, Windows pot avisar: llig el quadre de baix.'],
            ['Comença', 'Escriu el nom de la societat i crea els comptes d\'administrador i d\'administratiu. Si vols, carrega dades d\'exemple per a provar-lo.'],
        ],
        'dades_titol' => 'Les dades es queden a la societat',
        'dades' => [
            'Compàs funciona en un ordinador de la societat i no envia res a Internet.',
            'Les dades d\'alumnes menors i socis no passen per cap servidor de fora.',
            'Té dos comptes: administrador i administratiu, i un registre de qui ha fet cada canvi.',
            'Esta web tampoc no té galetes ni analítica, i les tipografies (lliures) se serveixen des d\'ací mateix.',
        ],
        'avis_titol' => 'Si Windows no et deixa obrir-lo',
        'avis' => [
            ['«Windows ha protegit l\'ordinador»', 'És només un avís perquè el programa encara no està signat digitalment. Prem «Més informació» i «Executa igualment».'],
            ['«Una directiva de Control d\'aplicacions ha bloquejat aquest fitxer»', 'És el Control intel·ligent d\'aplicacions d\'alguns ordinadors nous amb Windows 11, que bloqueja tots els programes no signats, també la versió portable. Cal desactivar-lo a Seguretat de Windows o utilitzar un altre ordinador o la versió per a Linux.'],
        ],
        'avis_mes' => 'Explicació pas a pas a la guia d\'instal·lació',
        'req_titol' => 'Què cal',
        'req' => ['Un ordinador amb Windows 10 o 11 de 64 bits, o amb Linux i PHP 8.1 o posterior', 'Uns 100 MB de disc', 'Un llapis USB o una carpeta al núvol per a les còpies (recomanat)'],
        'docs_titol' => 'Manuals',
        'docs' => [['manual-usuari.pdf', 'Manual d\'usuari, pas a pas amb captures'], ['guia-installacio.pdf', 'Guia d\'instal·lació']],
        'lliure_titol' => 'Programari lliure',
        'lliure' => 'Compàs es distribueix sota la llicència GNU AGPL v3 o posterior: el pots utilitzar, estudiar, modificar i compartir. Les versions modificades que s’oferisquen a través d’una xarxa també han de posar el seu codi font a disposició dels usuaris. El codi és públic a GitHub.',
        'autor' => 'Un projecte de José Vicente Sanjuan.',
        'ia' => 'S\'ha desenvolupat amb l\'ajuda d\'una intel·ligència artificial (Claude, d\'Anthropic).',
        'codi' => 'Codi a GitHub',
        'novetats' => 'Què canvia en cada versió',
        'privacitat' => 'Privacitat',
        'errades' => 'Has trobat una errada o tens una proposta? Obri una incidència a GitHub.',
        'fi' => 'Fet per a les societats musicals.',
        'anterior' => 'Anterior', 'seguent' => 'Següent',
    ],
    'es' => [
        'lang' => 'es', 'altre' => 'ca', 'altre_nom' => 'Valencià', 'altre_url' => '../',
        'titol' => 'Compàs: gestión libre para sociedades musicales',
        'descripcio' => 'Programa libre y gratuito para gestionar una sociedad musical desde un ordenador con Windows o Linux: personas, socios, escuela de música, notas, boletines, agrupaciones, instrumentos, cuotas, recibos y lotería.',
        'lema' => 'Gestión libre para sociedades musicales',
        'hero' => 'Toda la sociedad musical, en un solo programa.',
        'hero_text' => 'Socios, escuela, músicos, cuotas, junta y documentos, en un ordenador de la sociedad. Sin nube, sin Internet y sin recopilar ningún dato. Gratuito y en valenciano y castellano.',
        'nav_priv' => 'Privacidad',
        'priv_titol' => 'La privacidad, en el centro',
        'priv_intro' => 'Compàs está hecho al revés que las plataformas en línea: los datos de las personas de la sociedad (también los del alumnado menor de edad) no salen nunca del ordenador de la sociedad.',
        'priv_punts' => [
            ['Sin nube', 'No hay ningún servidor. El programa y los datos están en un ordenador de la sociedad, y nadie puede entrar desde Internet.'],
            ['No recopila ningún dato', 'Sin cuentas en ninguna plataforma, sin analítica, sin telemetría y sin publicidad. Ni el programa ni esta web envían nada a ningún sitio.'],
            ['Funciona sin Internet', 'No hace falta conexión para nada. Las remesas del banco y los correos se preparan aquí y los enviáis vosotros.'],
            ['Los datos son vuestros', 'Software libre y gratuito, sin cuotas ni límite de miembros. Si un día lo dejáis de usar, los datos siguen en vuestra carpeta.'],
        ],
        'comp_titol' => 'Compàs y las plataformas en línea',
        'comp_cols' => ['', 'Compàs', 'Plataformas en línea'],
        'comp' => [
            ['Dónde están los datos', 'En el ordenador de la sociedad', 'En los servidores de la empresa'],
            ['Recopilación de datos', 'Ninguna', 'Cuentas de usuario y datos de uso'],
            ['Internet', 'No hace falta', 'Imprescindible'],
            ['Precio', 'Gratuito, sin límite de miembros', 'Normalmente, según el número de miembros'],
            ['Si el servicio cierra o cambia de precio', 'El programa y los datos siguen siendo vuestros', 'Dependéis de la empresa'],
            ['Aplicación para los músicos', 'No: lo lleva la secretaría, que comparte PDF y listas', 'Sí, cada músico con su cuenta'],
        ],
        'descarrega' => 'Descargar (Windows y Linux)',
        'versio' => 'Versión %s. Gratuito y libre.',
        'manual' => 'Leer el manual',
        'nav_que' => 'Qué hace', 'nav_veure' => 'Capturas', 'nav_com' => 'Cómo empezar', 'nav_qui' => 'Por qué', 'nav_codi' => 'Código',
        'qui_titol' => 'Por qué Compàs',
        'qui' => [
            'Soy José Vicente Sanjuan. Durante seis años fui presidente de la Societat Musical de Massalfassar, y en todo ese tiempo eché en falta un programa para gestionar la sociedad que fuera fácil, útil y gratuito.',
            'La mayoría de las bandas viven con las cuentas muy justas, y los programas de gestión cuestan dinero, y no poco. Yo no sé programar, pero estoy al día de lo que pasa con la inteligencia artificial, y cuando tuve la oportunidad de probar una pensé: «¿Y si hago el programa que necesitaba mi sociedad? Seguro que también le sirve a más de una».',
            'Y aquí está Compàs. No es perfecto ni brillante, pero creo que es suficiente. Probadlo, miradlo y, si os gusta, usadlo y habladlo con otras sociedades musicales, escuelas de música o academias: también lo pueden usar.',
        ],
        'qui_signa' => 'José Vicente Sanjuan',
        'qui_contacte' => 'Si tenéis dudas o propuestas, o queréis contarme cómo os va, escribidme a %s.',
        'qui_formulari' => 'También podéis rellenar este formulario.',
        'qui_errades' => 'Si sabéis hacerlo, los errores también se pueden avisar en GitHub.',
        'qui_lema' => 'Hecho por el expresidente de una sociedad musical, para las sociedades musicales.',
        'qui_llig' => 'Por qué lo hice',
        'anterior' => 'Captura anterior', 'seguent' => 'Captura siguiente',
        'contacte' => 'Contacto',
        'que_titol' => 'Qué puedes hacer con Compàs',
        'que_intro' => 'Pensado para quien lleva la secretaría y la escuela de música, aunque no sea informático.',
        'moduls' => [
            ['Personas', 'Cada persona se introduce una sola vez. Puede ser socia, música, alumna, profesora y madre de un alumno a la vez, sin repetir datos.'],
            ['Socios', 'Número, tipo, altas y bajas. Etiquetas para las circulares y la lista de quién cumple 25 o 50 años de socio.'],
            ['Escuela de música', 'Cursos, asignaturas, aulas, grupos con horario, matrículas y tutores legales del alumnado menor.'],
            ['Calificaciones', 'El profesorado pasa las notas y administración las introduce por grupo y evaluación, con las observaciones.'],
            ['Boletines', 'Boletines de notas con el logotipo de la sociedad, para imprimir o guardar en PDF. Todos a la vez o de uno en uno.'],
            ['Curso nuevo', 'Un asistente prepara el curso siguiente a partir del anterior: trimestres, grupos, profesorado y horarios.'],
            ['Agrupaciones', 'Banda, banda juvenil, coral, orquesta… Quién toca qué y quién dirige cada formación.'],
            ['Instrumentos', 'Inventario, reparaciones y préstamos, con aviso cuando una devolución se ha pasado de fecha.'],
            ['Cuotas de socio', 'Tarifas por tipo de socio y periodicidad. Los recibos de todo un trimestre, hechos con un clic y sin duplicar ninguno.'],
            ['Matrícula y clases', 'Un recibo por familia con la matrícula o las clases de cada hijo y los descuentos de socio y de hermanos.'],
            ['Remesas bancarias', 'Domiciliaciones con la orden para firmar y el archivo SEPA para subir al banco. Control de los recibos devueltos.'],
            ['Lotería', 'Talonarios repartidos, participaciones para imprimir y quién ha liquidado y quién no.'],
            ['Junta y actas', 'Cargos con historial y aviso de renovación. Convocatorias, actas y certificados de acuerdos listos para firmar.'],
            ['Documentos', 'Estatutos, reglamento, seguros y contratos guardados en Compàs, con aviso antes de que caduquen.'],
            ['Comunicados', 'Listas de correo de socios, músicos o familias para enviar en copia oculta, y registro de lo que se ha enviado.'],
            ['Libro de caja', 'Ingresos y gastos con saldo. Los recibos, las remesas y la lotería entran solos.'],
            ['Derechos de imagen', 'Quién autoriza fotos y vídeos, con las hojas de consentimiento preparadas para firmar.'],
            ['Listados y certificados', 'Alumnos de un grupo con los teléfonos de las familias, datos para la memoria anual y certificados con el logotipo.'],
            ['Importación desde Excel', 'Trae los socios y el alumnado que ya tienes en Excel, sin duplicar a nadie.'],
            ['Copias de seguridad', 'Copia automática cada día y al cerrar, también en un USB o en la nube, cifrada con contraseña si quieres.'],
        ],
        'veure_titol' => 'Así se ve',
        'veure_intro' => 'Capturas reales del programa con datos de ejemplo.',
        'captures' => [
            'web-inici' => 'La pantalla de inicio: el resumen de la sociedad, las clases de hoy y los avisos.',
            'web-alumne' => 'La ficha de un alumno: grupos, horario, notas y tutores legales.',
            'web-notes' => 'Las notas de un grupo y una evaluación, con las observaciones del profesor.',
            'web-butlleti' => 'Un boletín de notas preparado para imprimir, con el logotipo de la sociedad.',
            'web-horari' => 'El horario de toda la escuela, que se puede filtrar por profesor o por aula.',
            'web-rebuts' => 'Los recibos de la escuela: uno por familia, con el detalle de cada alumno, antes de crearlos.',
            'web-economia' => 'El resumen económico: lo que hay por cobrar, lo cobrado y quién debe dinero.',
            'web-loteria' => 'La lotería de Navidad: talonarios repartidos, vendidas, devueltas y dinero entregado.',
            'web-agrupacio' => 'Los miembros de una agrupación, con el instrumento y la función de cada uno.',
            'web-llistat' => 'Los alumnos de un grupo con los teléfonos de las familias, para el profesorado.',
            'web-copies' => 'Las copias de seguridad: automáticas, manuales y en una carpeta externa.',
        ],
        'com_titol' => 'Cómo empezar',
        'passos' => [
            ['Descarga el instalador', 'Desde la página de descargas, baja <code>Compas-…-installador.exe</code>. También hay una versión portable que no hace falta instalar y paquetes para Linux (<code>.deb</code> para Debian y Ubuntu, y <code>.tar.gz</code>).'],
            ['Instálalo', 'No pide permisos de administrador. Como todavía no está firmado, Windows puede avisar: lee el cuadro de abajo.'],
            ['Empieza', 'Escribe el nombre de la sociedad y crea las cuentas de administrador y de administrativo. Si quieres, carga datos de ejemplo para probarlo.'],
        ],
        'dades_titol' => 'Los datos se quedan en la sociedad',
        'dades' => [
            'Compàs funciona en un ordenador de la sociedad y no envía nada a Internet.',
            'Los datos de alumnos menores y socios no pasan por ningún servidor de fuera.',
            'Tiene dos cuentas: administrador y administrativo, y un registro de quién ha hecho cada cambio.',
            'Esta web tampoco tiene cookies ni analítica, y las tipografías (libres) se sirven desde aquí mismo.',
        ],
        'avis_titol' => 'Si Windows no te deja abrirlo',
        'avis' => [
            ['«Windows protegió su PC»', 'Es solo un aviso porque el programa todavía no está firmado digitalmente. Pulsa «Más información» y «Ejecutar de todas formas».'],
            ['«Una directiva de Control de aplicaciones bloqueó este archivo»', 'Es el Control inteligente de aplicaciones de algunos ordenadores nuevos con Windows 11, que bloquea todos los programas sin firmar, también la versión portable. Hay que desactivarlo en Seguridad de Windows o usar otro ordenador o la versión para Linux.'],
        ],
        'avis_mes' => 'Explicación paso a paso en la guía de instalación',
        'req_titol' => 'Qué hace falta',
        'req' => ['Un ordenador con Windows 10 u 11 de 64 bits, o con Linux y PHP 8.1 o posterior', 'Unos 100 MB de disco', 'Un lápiz USB o una carpeta en la nube para las copias (recomendado)'],
        'docs_titol' => 'Manuales',
        'docs' => [['manual-usuario.pdf', 'Manual de usuario, paso a paso con capturas'], ['guia-instalacion.pdf', 'Guía de instalación']],
        'lliure_titol' => 'Software libre',
        'lliure' => 'Compàs se distribuye bajo la licencia GNU AGPL v3 o posterior: puedes utilizarlo, estudiarlo, modificarlo y compartirlo. Las versiones modificadas que se ofrezcan a través de una red también deben poner su código fuente a disposición de los usuarios. El código es público en GitHub.',
        'autor' => 'Un proyecto de José Vicente Sanjuan.',
        'ia' => 'Se ha desarrollado con la ayuda de una inteligencia artificial (Claude, de Anthropic).',
        'codi' => 'Código en GitHub',
        'novetats' => 'Qué cambia en cada versión',
        'privacitat' => 'Privacidad',
        'errades' => '¿Has encontrado un error o tienes una propuesta? Abre una incidencia en GitHub.',
        'fi' => 'Hecho para las sociedades musicales.',
        'anterior' => 'Anterior', 'seguent' => 'Siguiente',
    ],
];

function e(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function pagina(array $t, string $l, string $base, string $repo, string $versio, array $captures, string $correu, string $formulari): string
{
    $descarrega = $repo . '/releases/latest';
    $qui = implode('', array_map(fn($p) => '<p>' . e($p) . '</p>', $t['qui']));
    $enllacCorreu = '<a href="mailto:' . e($correu) . '">' . e($correu) . '</a>';
    $contacte = sprintf(e($t['qui_contacte']), $enllacCorreu)
        . ($formulari !== '' ? ' <a href="' . e($formulari) . '">' . e($t['qui_formulari']) . '</a>' : '');
    $privPunts = implode('', array_map(fn($p) => '<div class="punt-priv"><h3>' . e($p[0]) . '</h3><p>' . e($p[1]) . '</p></div>', $t['priv_punts']));
    $compCap = implode('', array_map(fn($c) => '<th>' . e($c) . '</th>', $t['comp_cols']));
    $compFiles = implode('', array_map(fn($f) => '<tr><th>' . e($f[0]) . '</th><td class="si">' . e($f[1]) . '</td><td>' . e($f[2]) . '</td></tr>', $t['comp']));
    $moduls = '';
    foreach ($t['moduls'] as [$nom, $text]) {
        $moduls .= '<div class="modul"><dt>' . e($nom) . '</dt><dd>' . e($text) . '</dd></div>';
    }
    $passos = '';
    foreach ($t['passos'] as [$nom, $text]) {
        $passos .= '<li><h3>' . e($nom) . '</h3><p>' . $text . '</p></li>';
    }
    $dades = implode('', array_map(fn($d) => '<li>' . e($d) . '</li>', $t['dades']));
    $req = implode('', array_map(fn($d) => '<li>' . e($d) . '</li>', $t['req']));
    $avisos = implode('', array_map(fn($a) => '<dt>' . e($a[0]) . '</dt><dd>' . e($a[1]) . '</dd>', $t['avis']));
    $guia = $repo . '/blob/main/docs/' . ($l === 'es' ? 'guia-instalacion.md#si-windows-no-te-deja-abrirlo' : 'guia-installacio.md#si-windows-no-et-deixa-obrir-lo');
    $docs = implode('', array_map(fn($d) => '<li><a href="' . $base . 'pdf/' . e($d[0]) . '">' . e($d[1]) . '</a> <span class="pes">PDF</span></li>', $t['docs']));

    $miniatures = '';
    $primera = $captures[0];
    foreach ($captures as $i => $c) {
        $src = $base . 'img/' . $l . '/' . $c . '.jpg';
        $miniatures .= '<li><button type="button" data-src="' . e($src) . '" data-peu="' . e($t['captures'][$c]) . '"' . ($i === 0 ? ' aria-current="true"' : '')
            . ' aria-label="' . e($t['captures'][$c]) . '"><img src="' . e($src) . '" alt="" loading="lazy"></button></li>';
    }
    $versioText = $versio ? sprintf($t['versio'], $versio) : '';
    $any = date('Y');

    return <<<HTML
<!doctype html>
<html lang="{$t['lang']}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{$t['titol']}</title>
<meta name="description" content="{$t['descripcio']}">
<meta property="og:title" content="{$t['titol']}">
<meta property="og:description" content="{$t['descripcio']}">
<meta property="og:image" content="{$base}img/logo.png">
<link rel="icon" href="{$base}img/icona.png">
<link rel="alternate" hreflang="{$t['altre']}" href="{$t['altre_url']}">
<link rel="preload" href="{$base}fonts/atkinson-hyperlegible-regular.woff" as="font" type="font/woff" crossorigin>
<link rel="stylesheet" href="{$base}estil.css">
</head>
<body>
<header class="barra">
  <a class="marca" href="./"><img src="{$base}img/icona.png" alt="" width="36" height="36"><span>Compàs</span></a>
  <nav aria-label="Seccions">
    <a href="#privacitat">{$t['nav_priv']}</a>
    <a href="#que">{$t['nav_que']}</a>
    <a href="#captures">{$t['nav_veure']}</a>
    <a href="#qui">{$t['nav_qui']}</a>
    <a href="#comencar">{$t['nav_com']}</a>
    <a href="{$repo}">{$t['nav_codi']}</a>
    <a class="idioma" href="{$t['altre_url']}" hreflang="{$t['altre']}" lang="{$t['altre']}">{$t['altre_nom']}</a>
  </nav>
</header>

<main>
<section class="hero">
  <div class="pentagrama" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i></div>
  <div class="hero-text">
    <p class="lema">{$t['lema']}</p>
    <h1>{$t['hero']}</h1>
    <p class="entrada">{$t['hero_text']}</p>
    <div class="accions">
      <a class="boto" href="{$descarrega}">{$t['descarrega']}</a>
      <a class="boto secundari" href="{$base}pdf/{$t['docs'][0][0]}">{$t['manual']}</a>
    </div>
    <p class="versio">{$versioText}</p>
    <p class="qui-lema">{$t['qui_lema']} <a href="#qui">{$t['qui_llig']} →</a></p>
  </div>
  <figure class="hero-captura">
    <img src="{$base}img/{$l}/web-inici.jpg" alt="{$t['captures']['web-inici']}" width="1920" height="1200">
  </figure>
</section>

<section id="privacitat" class="seccio privacitat">
  <div class="cap">
    <h2>{$t['priv_titol']}</h2>
    <p>{$t['priv_intro']}</p>
  </div>
  <div class="punts-priv">{$privPunts}</div>
  <h3 class="comp-titol">{$t['comp_titol']}</h3>
  <div class="taula-comp-wrap"><table class="taula-comp"><thead><tr>{$compCap}</tr></thead><tbody>{$compFiles}</tbody></table></div>
</section>

<section id="que" class="seccio">
  <div class="cap">
    <h2>{$t['que_titol']}</h2>
    <p>{$t['que_intro']}</p>
  </div>
  <dl class="moduls">{$moduls}</dl>
</section>

<section id="captures" class="seccio galeria">
  <div class="cap">
    <h2>{$t['veure_titol']}</h2>
    <p>{$t['veure_intro']}</p>
  </div>
  <figure class="principal">
    <div class="visor">
      <img id="captura" src="{$base}img/{$l}/{$primera}.jpg" alt="{$t['captures'][$primera]}">
      <button type="button" class="fletxa ant" aria-label="{$t['anterior']}">‹</button>
      <button type="button" class="fletxa seg" aria-label="{$t['seguent']}">›</button>
    </div>
    <figcaption id="peu" aria-live="polite">{$t['captures'][$primera]}</figcaption>
  </figure>
  <ul class="miniatures" aria-label="{$t['veure_titol']}">{$miniatures}</ul>
</section>

<section id="qui" class="seccio qui">
  <h2>{$t['qui_titol']}</h2>
  <blockquote>{$qui}
    <footer>— {$t['qui_signa']}</footer>
  </blockquote>
  <p class="contacte">{$contacte}</p>
  <p class="nota"><a href="{$repo}/issues/new/choose">{$t['qui_errades']}</a></p>
</section>

<section id="comencar" class="seccio">
  <div class="cap"><h2>{$t['com_titol']}</h2></div>
  <ol class="passos">{$passos}</ol>
  <aside class="avis-windows">
    <h3>{$t['avis_titol']}</h3>
    <dl>{$avisos}</dl>
    <p><a href="{$guia}">{$t['avis_mes']}</a></p>
  </aside>
  <p class="accions"><a class="boto" href="{$descarrega}">{$t['descarrega']}</a></p>
</section>

<section class="seccio dues">
  <div>
    <h2>{$t['dades_titol']}</h2>
    <ul class="punts">{$dades}</ul>
  </div>
  <div>
    <h2>{$t['req_titol']}</h2>
    <ul class="punts">{$req}</ul>
    <h2 class="h2-segon">{$t['docs_titol']}</h2>
    <ul class="docs">{$docs}</ul>
  </div>
</section>

<section class="seccio lliure">
  <h2>{$t['lliure_titol']}</h2>
  <p>{$t['lliure']}</p>
  <p class="nota">{$t['ia']}</p>
  <p><a class="boto secundari" href="{$repo}">{$t['codi']}</a></p>
  <p class="nota"><a href="{$repo}/blob/main/CHANGELOG.md">{$t['novetats']}</a> · <a href="{$repo}/issues/new/choose">{$t['errades']}</a></p>
</section>
</main>

<footer class="peu">
  <div class="doble-barra" aria-hidden="true"></div>
  <p><img src="{$base}img/icona.png" alt="" width="28" height="28"> <span>Compàs · © {$any} José Vicente Sanjuan. {$t['fi']}</span> <a href="{$repo}/blob/main/LICENSE">AGPL-3.0-or-later</a> <a href="{$repo}/blob/main/PRIVACITAT.md">{$t['privacitat']}</a> <a href="mailto:{$correu}">{$t['contacte']}: {$correu}</a></p>
</footer>

<script>
(() => {
  const botons = [...document.querySelectorAll('.miniatures button')];
  const img = document.getElementById('captura');
  let actual = 0;
  const mostra = i => {
    actual = (i + botons.length) % botons.length;
    const b = botons[actual];
    img.src = b.dataset.src; img.alt = b.dataset.peu;
    document.getElementById('peu').textContent = b.dataset.peu;
    botons.forEach(x => x.removeAttribute('aria-current'));
    b.setAttribute('aria-current', 'true');
    b.scrollIntoView({ block: 'nearest', inline: 'nearest', behavior: 'smooth' });
  };
  botons.forEach((b, i) => b.addEventListener('click', () => mostra(i)));
  document.querySelector('.fletxa.ant').addEventListener('click', () => mostra(actual - 1));
  document.querySelector('.fletxa.seg').addEventListener('click', () => mostra(actual + 1));
  // Tecles ← → quan la galeria està a la vista
  const galeria = document.getElementById('captures');
  document.addEventListener('keydown', e => {
    if (e.target.closest('input, textarea')) return;
    const r = galeria.getBoundingClientRect();
    if (r.bottom < 0 || r.top > innerHeight) return;
    if (e.key === 'ArrowLeft') mostra(actual - 1);
    if (e.key === 'ArrowRight') mostra(actual + 1);
  });
  // Lliscar amb el dit (mòbils i tauletes)
  let x0 = null;
  img.addEventListener('touchstart', e => { x0 = e.touches[0].clientX; }, { passive: true });
  img.addEventListener('touchend', e => {
    if (x0 === null) return;
    const dx = e.changedTouches[0].clientX - x0;
    if (Math.abs(dx) > 40) mostra(actual + (dx < 0 ? 1 : -1));
    x0 = null;
  });
})();
</script>
</body>
</html>
HTML;
}

/** Els enllaços que porten fora de la pàgina (GitHub, PDF, formulari…) s'obrin en una pestanya nova */
function pestanya_nova(string $html): string
{
    return preg_replace_callback('/<a\s([^>]*?)href="([^"]+)"([^>]*)>/', function ($m) {
        $href = $m[2];
        $fora = preg_match('#^https?://#', $href) || str_ends_with($href, '.pdf');
        if (!$fora || str_contains($m[0], 'target=')) {
            return $m[0];
        }
        return '<a ' . $m[1] . 'href="' . $href . '"' . $m[3] . ' target="_blank" rel="noopener">';
    }, $html);
}

// Carpetes i recursos
foreach (['', '/es', '/img/ca', '/img/es', '/pdf'] as $d) {
    if (!is_dir($eixida . $d)) {
        mkdir($eixida . $d, 0775, true);
    }
}
copy($arrel . '/app/assets/logo.png', $eixida . '/img/logo.png');
copy($arrel . '/app/assets/icona.png', $eixida . '/img/icona.png');
copy(__DIR__ . '/web.css', $eixida . '/estil.css');
// Tipografies lliures (SIL OFL) servides des de la mateixa web: cap petició a servidors de tercers
if (!is_dir($eixida . '/fonts')) {
    mkdir($eixida . '/fonts', 0775, true);
}
foreach (glob(__DIR__ . '/fonts/*') as $f) {
    copy($f, $eixida . '/fonts/' . basename($f));
}
foreach (['ca', 'es'] as $l) {
    foreach ($captures as $c) {
        copy("$arrel/docs/imatges/$l/$c.jpg", "$eixida/img/$l/$c.jpg");
    }
}
foreach (glob($arrel . '/docs/pdf/*.pdf') as $pdf) {
    copy($pdf, $eixida . '/pdf/' . basename($pdf));
}
file_put_contents($eixida . '/index.html', pestanya_nova(pagina($T['ca'], 'ca', '', $repo, $versio, $captures, $correu, $formulari)));
file_put_contents($eixida . '/es/index.html', pestanya_nova(pagina($T['es'], 'es', '../', $repo, $versio, $captures, $correu, $formulari)));
file_put_contents($eixida . '/.nojekyll', '');
echo "Web generada a $eixida\n";
