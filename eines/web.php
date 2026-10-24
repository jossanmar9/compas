<?php
// Genera la web de presentació de Compàs (GitHub Pages) en valencià i castellà.
// Ús: php eines/web.php carpeta-eixida
// Copia el logotip, unes quantes captures (docs/imatges) i els PDF (docs/pdf, si s'han generat amb eines/pdf.sh)
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

$web = 'https://jossanmar9.github.io/compas/'; // adreça pública de la web (per a la imatge que es veu en compartir l'enllaç)
// Captures de la galeria (eines/captures.py web, amb la societat gran inventada d'eines/societat-gran.php, un club i una falla)
$captures = ['web-inici', 'web-club', 'web-activitat', 'web-falla', 'web-socis', 'web-alumne', 'web-horari', 'web-notes', 'web-butlleti', 'web-agrupacio', 'web-repertori', 'web-economia',
    'web-rebuts', 'web-remesa', 'web-loteria', 'web-junta', 'web-llistat', 'web-copies'];

$T = [
    'ca' => [
        'lang' => 'ca-valencia', 'og_locale' => 'ca_ES', 'altre' => 'es', 'altre_nom' => 'Castellano', 'altre_url' => 'es/',
        'titol' => 'Compàs: gestió lliure per a associacions',
        'descripcio' => "Programa lliure i gratuït per a gestionar una associació des d'un ordinador amb Windows o Linux: societats musicals, clubs esportius, colles, associacions culturals, AMPA, falles, gaiates, fogueres, comparses, penyes i federacions. Socis, quotes, rebuts, activitats, escola, loteria, caixa i junta.",
        'lema' => 'Gestió lliure per a associacions',
        'hero' => 'Tota l\'associació, en un sol programa.',
        'hero_text' => "Socis, quotes, activitats, escola, equips, junta i documents, en un ordinador de l'entitat. Per a societats musicals, clubs, colles, AMPA, falles, comparses i molt més. Sense núvol, sense Internet i sense recollir cap dada. Gratuït i en valencià i castellà.",
        'nav_tipus' => 'Per a qui',
        'tipus_titol' => 'Una sola aplicació per a cada tipus d\'entitat',
        'tipus_intro' => 'En instal·lar-lo tries el tipus d\'entitat i Compàs s\'hi adapta: els noms de les pantalles, els apartats que necessites i quan comença l\'exercici. Es pot canviar quan vulgues.',
        'tipus' => [
            ['🎺', 'Societats musicals', 'Escola de música amb notes i butlletins, banda i agrupacions, repertori i instruments.'],
            ['⚽', 'Clubs esportius', 'Equips, escola esportiva, llicències i revisions mèdiques, categories i temporada de setembre.'],
            ['🥁', 'Colles', 'Seccions de dolçaina i tabal, gegants o muixeranga, escola, material i roba.'],
            ['🎭', 'Associacions culturals', 'Tallers i cursos, grups de teatre, dansa o cor, i activitats amb inscripció.'],
            ['🎒', 'AMPA i AFA', 'Famílies associades, activitats extraescolars amb rebuts per família i comissions.'],
            ['🔥', 'Falles', 'Cens de fallers i falleres, Fallera Major i càrrecs, quotes, loteria i exercici d\'abril.'],
            ['🏮', 'Gaiates', 'Membres de la comissió, madrines i càrrecs, quotes i loteria.'],
            ['✨', 'Fogueres', 'Foguerers i foguereres, belleses i dames, quotes i loteria.'],
            ['🛡️', 'Comparses', 'Comparsistes i esquadres, vestits, capitania i càrrecs de festa.'],
            ['🍻', 'Penyes', 'Socis, quotes, loteria, sopars i eixides.'],
            ['🏢', 'Federacions', 'Entitats federades amb el representant, la quota anual i els seus rebuts.'],
        ],
        'nav_priv' => 'Privacitat',
        'priv_titol' => 'La privacitat, al centre',
        'priv_intro' => 'Compàs està fet al revés que les plataformes en línia: les dades de les persones de l\'entitat (també les dels menors) no ixen mai de l\'ordinador de l\'entitat.',
        'priv_punts' => [
            ['Sense núvol', 'No hi ha cap servidor. El programa i les dades estan en un ordinador de l\'entitat, i ningú no hi pot entrar des d\'Internet.'],
            ['No recull cap dada', 'Sense comptes en cap plataforma, sense analítica, sense telemetria i sense publicitat. Ni el programa ni esta web envien res enlloc.'],
            ['Funciona sense Internet', 'No cal connexió per a res. Les remeses del banc i els correus es preparen ací i els envieu vosaltres.'],
            ['Les dades són vostres', 'Programari lliure i gratuït, sense quotes ni límit de membres. Si un dia el deixeu de fer servir, les dades continuen a la vostra carpeta.'],
        ],
        'comp_titol' => 'Compàs i les plataformes en línia',
        'comp_cols' => ['', 'Compàs', 'Plataformes en línia'],
        'comp' => [
            ['On estan les dades', 'A l\'ordinador de l\'entitat', 'Als servidors de l\'empresa'],
            ['Recollida de dades', 'Cap', 'Comptes d\'usuari i dades d\'ús'],
            ['Internet', 'No cal', 'Imprescindible'],
            ['Preu', 'Gratuït, sense límit de membres', 'Normalment, segons el nombre de membres'],
            ['Si el servei tanca o canvia de preu', 'El programa i les dades continuen sent vostres', 'Depeneu de l\'empresa'],
            ['Aplicació per als socis', 'No: ho porta la secretaria, que comparteix PDF i llistes', 'Sí, cada soci amb el seu compte'],
        ],
        'descarrega' => 'Descarrega (Windows i Linux)',
        'versio' => 'Versió %s. Gratuït i lliure.',
        'manual' => 'Llig el manual',
        'nav_que' => 'Què fa', 'nav_veure' => 'Captures', 'nav_com' => 'Com començar', 'nav_qui' => 'Per què', 'nav_codi' => 'Codi',
        'qui_titol' => 'Per què Compàs',
        'qui' => [
            'Soc José Vicente Sanjuan. Durant sis anys vaig ser president de la Societat Musical de Massalfassar, i en tot eixe temps vaig trobar a faltar un programa per a gestionar la societat que fora fàcil, útil i gratuït.',
            'La majoria de les bandes viuen amb els comptes molt justos, i els programes de gestió costen diners, i no pocs. Jo no sé programar, però estic al dia del que passa amb la intel·ligència artificial, i quan vaig tindre l\'oportunitat de provar-ne una vaig pensar: «I si faig el programa que necessitava la meua societat? Segur que també li servirà a més d\'una.»',
            'I ací està Compàs. No és perfecte ni brillant, però crec que és suficient. Proveu-lo, mireu-lo i, si vos agrada, gasteu-lo i parleu-ne amb altres entitats. Ara, a més de les societats musicals, el poden fer servir clubs esportius, colles, associacions culturals, AMPA, falles, gaiates, fogueres, comparses, penyes i federacions.',
        ],
        'qui_signa' => 'José Vicente Sanjuan',
        'qui_contacte' => 'Si teniu dubtes o propostes, o voleu contar-me com vos va, escriviu-me a %s.',
        'qui_formulari' => 'També podeu omplir este formulari.',
        'qui_errades' => 'Si sabeu fer-ho, les errades també es poden avisar a GitHub.',
        'qui_lema' => 'Fet per l\'expresident d\'una societat musical, per a totes les associacions.',
        'qui_llig' => 'Per què el vaig fer',
        'nav_faq' => 'Preguntes',
        'faq_titol' => 'Preguntes freqüents',
        'faq_intro' => 'El que més ens pregunten les entitats abans de provar-lo. Si teniu un altre dubte, escriviu-nos.',
        'comptes_titol' => 'Qui pot fer què',
        'comptes_intro' => 'Compàs té dos comptes: el de l\'administrador/a (normalment, la presidència o qui porta l\'informàtica) i el d\'usuari/ària (la secretaria o la tresoreria). Els dos fan tota la feina del dia a dia; només l\'administrador/a pot tocar la configuració, els comptes i la restauració de còpies.',
        'comptes_cols' => ['Què es pot fer', 'Administrador/a', 'Usuari/ària'],
        'comptes' => [
            ['Persones, socis i tipus de soci', true],
            ['Escola: cursos, grups, matrícules, notes i butlletins', true],
            ['Agrupacions o equips, repertori, instruments i uniformes', true],
            ['Activitats, llicències, càrrecs de festa i entitats federades', true],
            ['Quotes, rebuts, domiciliacions, remeses, loteria i subvencions', true],
            ['Llibre de caixa', true],
            ['Junta, actes, documents i comunicats', true],
            ['Llistats, certificats i importació des d\'Excel', true],
            ['Fer i baixar còpies de seguretat', true],
            ['Canviar la pròpia contrasenya', true],
            ['Configuració de l\'entitat, tipus, logotip, mòduls i avisos', false],
            ['Dades bancàries i descomptes de l\'escola', false],
            ['Crear i gestionar els comptes', false],
            ['Registre d\'activitat', false],
            ['Restaurar i eliminar còpies, carpeta externa i xifrat', false],
            ['Recuperar l\'accés amb el codi de recuperació', false],
        ],
        'faq' => [
            ['Serveix per a la nostra entitat?', 'Compàs té tipus per a societats musicals, clubs esportius, colles, associacions culturals, AMPA, falles, gaiates, fogueres, comparses, penyes i federacions. Si la vostra no és cap d\'estes, trieu la que més s\'hi assemble i activeu o desactiveu els apartats que necessiteu.'],
            ['És gratis de veres?', 'Sí. És programari lliure: sense quotes, sense límit de membres i sense versió «de pagament». No hi ha res a contractar.'],
            ['Cal Internet?', 'No. Compàs funciona sense connexió. Els fitxers de remeses per al banc i els correus es preparen al programa i els envieu vosaltres des de la banca electrònica o el vostre correu.'],
            ['Compàs envia les nostres dades a algun lloc?', 'No. Les dades es queden a l\'ordinador de l\'entitat i, si la configureu, a la carpeta externa de còpies (un USB o una carpeta al núvol que trieu vosaltres). Ni el programa ni esta web recullen res.'],
            ['Funciona en Windows i en Linux?', 'Sí. Per a Windows 10 o 11 de 64 bits hi ha un instal·lador i una versió portable; per a Linux, un paquet .deb i un .tar.gz. No hi ha versió per a Mac ni per al mòbil.'],
            ['Es pot gastar des de diversos ordinadors alhora?', 'No: Compàs s\'instal·la en un ordinador de l\'entitat i s\'usa en eixe ordinador. Per a canviar d\'ordinador, n\'hi ha prou de restaurar l\'última còpia al nou.'],
            ['Puc provar-lo sense posar les dades de l\'entitat?', 'Sí. En instal·lar-lo, podeu demanar que es carreguen dades d\'exemple del vostre tipus d\'entitat: socis, activitats, rebuts… inventats. Quan el conegueu, comenceu de zero amb les dades de veres.'],
            ['Tenim els socis en un Excel. Cal escriure\'ls tots?', 'No. Importa dades llig fitxers .xlsx i .csv: trieu quina columna és cada dada, reviseu la vista prèvia i s\'importen. Les persones que ja hi ha no es dupliquen.'],
            ['Compàs fa factures?', 'No, fa rebuts. Les factures s\'han de fer amb un programa de facturació que complisca la normativa (Verifactu).'],
            ['I si s\'espatlla l\'ordinador?', 'Per això hi ha les còpies de seguretat. Compàs en fa una cada vegada que es tanca, i convé configurar una carpeta externa perquè també es guarden fora de l\'ordinador. Amb l\'última còpia, ho recupereu tot en un altre ordinador.'],
            ['Com s\'actualitza?', 'A Ajuda, el botó «Hi ha versió nova?» obri la pàgina de l\'última versió. Si n\'hi ha una de nova, s\'instal·la damunt de l\'anterior i les dades es conserven; abans d\'actualitzar, Compàs en fa una còpia.'],
        ],
        'anterior' => 'Captura anterior', 'seguent' => 'Captura següent',
        'contacte' => 'Contacte',
        'xifres' => [['0 €', 'per sempre, sense límit de membres'], ['0', 'dades que isquen de l\'ordinador'], ['Sense Internet', 'funciona sense connexió'], ['VAL · ES', 'en valencià i en castellà']],
        'blocs' => [
            ['Persones i socis', 'Una fitxa per persona i tota l\'entitat en un sol lloc, encara que una mateixa persona siga sòcia, jugadora i mare d\'alumnes.', 'web-socis', [0, 1, 28, 18, 16, 20]],
            ['Clubs, falles, comparses i molt més', 'Cada tipus d\'entitat té el que li cal: llicències i revisions mèdiques per als clubs, càrrecs de festa per a falles i comparses, entitats federades per a les federacions, i activitats amb inscripcions per a tothom.', 'web-club', [29, 24, 25, 26, 27]],
            ['Escola', 'De música, esportiva, de tallers o d\'extraescolars: inscripcions, grups i horaris del professorat i dels espais. Les notes i els butlletins estan disponibles per a societats musicals i colles.', 'web-horari', [2, 3, 4, 5, 19]],
            ['Agrupacions, equips i material', 'Qui toca o juga en cada formació, quines obres teniu a l\'arxiu i on està cada instrument, cada equipació i cada vestit.', 'web-repertori', [6, 7, 8, 9]],
            ['Economia', 'Quotes, classes i remeses al banc sense fulls de càlcul: els rebuts es fan sols i no se\'n duplica cap.', 'web-rebuts', [10, 11, 23, 12, 13, 17]],
            ['Junta, documents i còpies', 'La part institucional de l\'entitat en ordre, i les dades sempre amb còpia.', 'web-junta', [14, 15, 21, 22]],
        ],
        'nav_menu' => 'Menú',
        'que_titol' => 'Què pots fer amb Compàs',
        'que_intro' => 'Pensat per a qui porta la secretaria d\'una entitat, encara que no siga informàtic.',
        'moduls' => [
            ['Persones', 'Cada persona s\'introdueix una sola vegada. Pot ser sòcia, membre d\'un equip o secció, alumna, monitora i mare d\'un alumne alhora, sense repetir dades.'],
            ['Socis', 'Número, els tipus de soci de cada entitat, altes i baixes. Etiquetes per a les circulars i la llista de qui fa 25 o 50 anys de soci.'],
            ['Escola', 'Cursos o temporades, assignatures, esports o tallers, aules, grups amb horari, matrícules i tutors legals de l\'alumnat menor.'],
            ['Qualificacions', 'El professorat passa les notes i administració les introdueix per grup i avaluació, amb les observacions.'],
            ['Butlletins', 'Butlletins de notes amb el logotip de l\'entitat, per a imprimir o guardar en PDF. Tots alhora o d\'un en un.'],
            ['Curs nou', 'Un assistent prepara el curs següent a partir de l\'anterior: trimestres, grups, professorat i horaris.'],
            ['Agrupacions, equips i seccions', 'Banda i coral, equips, seccions de la colla, esquadres o comissions: qui en forma part, amb quin instrument o posició, i qui ho dirigeix.'],
            ['Repertori', 'El catàleg de l\'arxiu musical: quines obres té la societat musical, per a quines agrupacions, en quin estat i on trobar-les.'],
            ['Instruments i material', 'Inventari, reparacions i préstecs, amb avís quan una devolució s\'ha passat de data.'],
            ['Uniformes i equipacions', 'Polos, equipacions, vestits i motxilles per talla: el que s\'ha comprat, el que s\'ha venut o deixat i el que queda.'],
            ['Quotes de soci', 'Tarifes per tipus de soci i periodicitat. Els rebuts de tot un trimestre, fets amb un clic i sense duplicar-ne cap.'],
            ['Matrícula i classes', 'Un rebut per família amb la matrícula o les classes de cada fill i els descomptes de soci i de germans.'],
            ['Remeses bancàries', 'Domiciliacions amb l\'ordre per a signar i el fitxer SEPA per a pujar al banc. Control dels rebuts retornats.'],
            ['Loteria', 'Talonaris repartits, participacions per a imprimir i qui ha liquidat i qui no.'],
            ['Junta i actes', 'Càrrecs amb historial i avís de renovació. Convocatòries, actes i certificats d\'acords a punt per a signar.'],
            ['Documents', 'Estatuts, reglament, assegurances i contractes guardats a Compàs, amb avís abans que caduquen.'],
            ['Comunicats', 'Llistes de correu de socis, membres o famílies per a enviar en còpia oculta, i registre del que s\'ha enviat.'],
            ['Llibre de caixa', 'Ingressos i despeses amb saldo. Els rebuts, les remeses i la loteria hi entren sols.'],
            ['Drets d\'imatge', 'Qui autoritza fotos i vídeos, amb els fulls de consentiment preparats per a signar.'],
            ['Llistats i certificats', 'Alumnes d\'un grup amb els telèfons de les famílies, dades per a la memòria anual i certificats amb el logotip.'],
            ['Importació des d\'Excel', 'Porta els socis, l\'alumnat i el repertori que ja tens en Excel, sense duplicar res.'],
            ['A la mida de cada entitat', 'Activa o desactiva els apartats que no fas servir i els avisos que no vols vore. Les dades no s\'esborren.'],
            ['Còpies de seguretat', 'Còpia automàtica cada dia i en tancar, també en un USB o en el núvol, xifrada amb contrasenya si vols.'],
            ['Subvencions', 'Terminis per a demanar-les i justificar-les, com es justifica cada una segons l\'organisme, cobraments i la relació de factures per a la justificació.'],
            ['Activitats', 'Sopars, eixides, viatges, actes o competicions amb places, inscripcions, llista d\'inscrits i un rebut per inscripció.'],
            ['Llicències', 'Llicències federatives de cada temporada per equip, amb avís abans que caduquen la llicència o la revisió mèdica.'],
            ['Càrrecs de festa', 'Fallera Major, Bellesa, Madrina o capitania de cada exercici, amb l\'historial i el certificat.'],
            ['Entitats federades', 'Per a federacions: cada entitat amb el seu representant i la quota anual, cobrada amb rebuts.'],
            ['Categories d\'edat', 'Aleví, cadet, infantil, major…: la categoria de cada persona es calcula sola segons l\'exercici.'],
            ['Tipus d\'entitat', 'Els noms (socis, fallers, comparsistes, famílies…), els apartats i l\'exercici s\'adapten a la vostra entitat.'],
        ],
        'veure_titol' => 'Així es veu',
        'veure_intro' => 'Captures reals del programa: una societat musical inventada de 341 socis, 278 alumnes, cinc agrupacions i 653 obres a l\'arxiu, un club esportiu i una falla.',
        'captures' => [
            'web-inici' => 'La pantalla d\'inici: el resum de l\'entitat, les classes de hui i els avisos.',
            'web-club' => 'Un club esportiu: les llicències de la temporada per equip, amb l\'avís de les revisions mèdiques que caduquen.',
            'web-activitat' => 'Una activitat amb les inscripcions, les places lliures i els rebuts.',
            'web-falla' => 'Una falla: fallers i falleres, càrrecs de festa, loteria i les pròximes activitats.',
            'web-socis' => 'Els socis, amb el número, el tipus i la data d\'alta. Es busquen per nom o número.',
            'web-repertori' => 'El repertori: 653 obres amb el número d\'arxiu, les agrupacions i on està cada carpeta.',
            'web-remesa' => 'Una remesa preparada per al banc: els rebuts domiciliats i el fitxer SEPA per a pujar-lo.',
            'web-junta' => 'La junta directiva actual i les anteriors, amb l\'avís de quan toca renovar-la.',
            'web-alumne' => 'La fitxa d\'un alumne: grups, horari, notes i tutors legals.',
            'web-notes' => 'Les notes d\'un grup i una avaluació, amb les observacions del professor.',
            'web-butlleti' => 'Un butlletí de notes preparat per a imprimir, amb el logotip de l\'entitat.',
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
            ['Comença', 'Escriu el nom de l\'entitat, tria el tipus (societat musical, club, falla…) i crea els comptes d\'administrador/a i d\'usuari/ària. Si vols, carrega dades d\'exemple per a provar-lo.'],
        ],
        'dades_titol' => 'Les dades es queden a l\'entitat',
        'dades' => [
            'Compàs funciona en un ordinador de l\'entitat i no envia res a Internet.',
            'Les dades de menors i socis no passen per cap servidor de fora.',
            'Té dos comptes: administrador/a i usuari/ària, i un registre de qui ha fet cada canvi.',
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
        'docs' => [['manual-usuari.pdf', 'Manual d\'usuari, pas a pas amb captures'], ['manual-administracio.pdf', 'Manual d\'administració: configuració, comptes, còpies i actualitzacions'], ['guia-installacio.pdf', 'Guia d\'instal·lació']],
        'lliure_titol' => 'Programari lliure',
        'lliure' => 'Compàs es distribueix sota la llicència GNU AGPL v3 o posterior: el pots utilitzar, estudiar, modificar i compartir. Les versions modificades que s’oferisquen a través d’una xarxa també han de posar el seu codi font a disposició dels usuaris. El codi és públic a GitHub.',
        'autor' => 'Un projecte de José Vicente Sanjuan.',
        'ia' => 'S\'ha desenvolupat amb l\'ajuda d\'una intel·ligència artificial (Claude, d\'Anthropic).',
        'codi' => 'Codi a GitHub',
        'novetats' => 'Què canvia en cada versió',
        'privacitat' => 'Privacitat',
        'llicencia' => 'Resum de la llicència',
        'errades' => 'Has trobat una errada o tens una proposta? Obri una incidència a GitHub.',
        'fi' => 'Fet per a les associacions.',
        'resp_titol' => 'Avís de responsabilitat',
        'resp' => 'Compàs és programari lliure i es distribueix tal com és, sense cap garantia (llicència AGPL-3.0). L\'autor no es fa responsable de la pèrdua de dades ni de cap altre dany, directe o indirecte, que puga derivar-se de l\'ús del programa, d\'una errada, d\'una actualització o d\'una configuració incorrecta. Cada entitat és responsable de les seues dades i de fer-ne còpies de seguretat periòdiques en un lloc extern (USB o núvol).',
        'anterior' => 'Anterior', 'seguent' => 'Següent',
    ],
    'es' => [
        'lang' => 'es', 'og_locale' => 'es_ES', 'altre' => 'ca', 'altre_nom' => 'Valencià', 'altre_url' => '../',
        'titol' => 'Compàs: gestión libre para asociaciones',
        'descripcio' => 'Programa libre y gratuito para gestionar una asociación desde un ordenador con Windows o Linux: sociedades musicales, clubes deportivos, collas, asociaciones culturales, AMPA, fallas, gaiatas, fogueres, comparsas, peñas y federaciones. Socios, cuotas, recibos, actividades, escuela, lotería, caja y junta.',
        'lema' => 'Gestión libre para asociaciones',
        'hero' => 'Toda la asociación, en un solo programa.',
        'hero_text' => 'Socios, cuotas, actividades, escuela, equipos, junta y documentos, en un ordenador de la entidad. Para sociedades musicales, clubes, collas, AMPA, fallas, comparsas y mucho más. Sin nube, sin Internet y sin recopilar ningún dato. Gratuito y en valenciano y castellano.',
        'nav_tipus' => 'Para quién',
        'tipus_titol' => 'Una sola aplicación para cada tipo de entidad',
        'tipus_intro' => 'Al instalarlo eliges el tipo de entidad y Compàs se adapta: los nombres de las pantallas, los apartados que necesitas y cuándo empieza el ejercicio. Se puede cambiar cuando quieras.',
        'tipus' => [
            ['🎺', 'Sociedades musicales', 'Escuela de música con notas y boletines, banda y agrupaciones, repertorio e instrumentos.'],
            ['⚽', 'Clubes deportivos', 'Equipos, escuela deportiva, licencias y revisiones médicas, categorías y temporada de septiembre.'],
            ['🥁', 'Collas', 'Secciones de dulzaina y tabal, gigantes o muixeranga, escuela, material y ropa.'],
            ['🎭', 'Asociaciones culturales', 'Talleres y cursos, grupos de teatro, danza o coro, y actividades con inscripción.'],
            ['🎒', 'AMPA y AFA', 'Familias asociadas, actividades extraescolares con recibos por familia y comisiones.'],
            ['🔥', 'Fallas', 'Censo de falleros y falleras, Fallera Mayor y cargos, cuotas, lotería y ejercicio de abril.'],
            ['🏮', 'Gaiatas', 'Miembros de la comisión, madrinas y cargos, cuotas y lotería.'],
            ['✨', 'Fogueres', 'Foguerers y foguereras, bellezas y damas, cuotas y lotería.'],
            ['🛡️', 'Comparsas', 'Comparsistas y escuadras, trajes, capitanía y cargos de fiesta.'],
            ['🍻', 'Peñas', 'Socios, cuotas, lotería, cenas y salidas.'],
            ['🏢', 'Federaciones', 'Entidades federadas con el representante, la cuota anual y sus recibos.'],
        ],
        'nav_priv' => 'Privacidad',
        'priv_titol' => 'La privacidad, en el centro',
        'priv_intro' => 'Compàs está hecho al revés que las plataformas en línea: los datos de las personas de la entidad (también los de los menores) no salen nunca del ordenador de la entidad.',
        'priv_punts' => [
            ['Sin nube', 'No hay ningún servidor. El programa y los datos están en un ordenador de la entidad, y nadie puede entrar desde Internet.'],
            ['No recopila ningún dato', 'Sin cuentas en ninguna plataforma, sin analítica, sin telemetría y sin publicidad. Ni el programa ni esta web envían nada a ningún sitio.'],
            ['Funciona sin Internet', 'No hace falta conexión para nada. Las remesas del banco y los correos se preparan aquí y los enviáis vosotros.'],
            ['Los datos son vuestros', 'Software libre y gratuito, sin cuotas ni límite de miembros. Si un día lo dejáis de usar, los datos siguen en vuestra carpeta.'],
        ],
        'comp_titol' => 'Compàs y las plataformas en línea',
        'comp_cols' => ['', 'Compàs', 'Plataformas en línea'],
        'comp' => [
            ['Dónde están los datos', 'En el ordenador de la entidad', 'En los servidores de la empresa'],
            ['Recopilación de datos', 'Ninguna', 'Cuentas de usuario y datos de uso'],
            ['Internet', 'No hace falta', 'Imprescindible'],
            ['Precio', 'Gratuito, sin límite de miembros', 'Normalmente, según el número de miembros'],
            ['Si el servicio cierra o cambia de precio', 'El programa y los datos siguen siendo vuestros', 'Dependéis de la empresa'],
            ['Aplicación para los socios', 'No: lo lleva la secretaría, que comparte PDF y listas', 'Sí, cada socio con su cuenta'],
        ],
        'descarrega' => 'Descargar (Windows y Linux)',
        'versio' => 'Versión %s. Gratuito y libre.',
        'manual' => 'Leer el manual',
        'nav_que' => 'Qué hace', 'nav_veure' => 'Capturas', 'nav_com' => 'Cómo empezar', 'nav_qui' => 'Por qué', 'nav_codi' => 'Código',
        'qui_titol' => 'Por qué Compàs',
        'qui' => [
            'Soy José Vicente Sanjuan. Durante seis años fui presidente de la Societat Musical de Massalfassar, y en todo ese tiempo eché en falta un programa para gestionar la sociedad que fuera fácil, útil y gratuito.',
            'La mayoría de las bandas viven con las cuentas muy justas, y los programas de gestión cuestan dinero, y no poco. Yo no sé programar, pero estoy al día de lo que pasa con la inteligencia artificial, y cuando tuve la oportunidad de probar una pensé: «¿Y si hago el programa que necesitaba mi sociedad? Seguro que también le sirve a más de una».',
            'Y aquí está Compàs. No es perfecto ni brillante, pero creo que es suficiente. Probadlo, miradlo y, si os gusta, usadlo y habladlo con otras entidades. Ahora, además de las sociedades musicales, lo pueden usar clubes deportivos, collas, asociaciones culturales, AMPA, fallas, gaiatas, fogueres, comparsas, peñas y federaciones.',
        ],
        'qui_signa' => 'José Vicente Sanjuan',
        'qui_contacte' => 'Si tenéis dudas o propuestas, o queréis contarme cómo os va, escribidme a %s.',
        'qui_formulari' => 'También podéis rellenar este formulario.',
        'qui_errades' => 'Si sabéis hacerlo, los errores también se pueden avisar en GitHub.',
        'qui_lema' => 'Hecho por el expresidente de una sociedad musical, para todas las asociaciones.',
        'qui_llig' => 'Por qué lo hice',
        'nav_faq' => 'Preguntas',
        'faq_titol' => 'Preguntas frecuentes',
        'faq_intro' => 'Lo que más nos preguntan las entidades antes de probarlo. Si tenéis otra duda, escribidnos.',
        'comptes_titol' => 'Quién puede hacer qué',
        'comptes_intro' => 'Compàs tiene dos cuentas: la del administrador/a (normalmente, la presidencia o quien lleva la informática) y la de usuario/a (la secretaría o la tesorería). Las dos hacen todo el trabajo del día a día; solo el administrador/a puede tocar la configuración, las cuentas y la restauración de copias.',
        'comptes_cols' => ['Qué se puede hacer', 'Administrador/a', 'Usuario/a'],
        'comptes' => [
            ['Personas, socios y tipos de socio', true],
            ['Escuela: cursos, grupos, matrículas, notas y boletines', true],
            ['Agrupaciones o equipos, repertorio, instrumentos y uniformes', true],
            ['Actividades, licencias, cargos de fiesta y entidades federadas', true],
            ['Cuotas, recibos, domiciliaciones, remesas, lotería y subvenciones', true],
            ['Libro de caja', true],
            ['Junta, actas, documentos y comunicados', true],
            ['Listados, certificados e importación desde Excel', true],
            ['Hacer y descargar copias de seguridad', true],
            ['Cambiar la propia contraseña', true],
            ['Configuración de la entidad, tipo, logotipo, módulos y avisos', false],
            ['Datos bancarios y descuentos de la escuela', false],
            ['Crear y gestionar las cuentas', false],
            ['Registro de actividad', false],
            ['Restaurar y eliminar copias, carpeta externa y cifrado', false],
            ['Recuperar el acceso con el código de recuperación', false],
        ],
        'faq' => [
            ['¿Sirve para nuestra entidad?', 'Compàs tiene tipos para sociedades musicales, clubes deportivos, collas, asociaciones culturales, AMPA, fallas, gaiatas, fogueres, comparsas, peñas y federaciones. Si la vuestra no es ninguna de estas, elegid la que más se parezca y activad o desactivad los apartados que necesitéis.'],
            ['¿Es gratis de verdad?', 'Sí. Es software libre: sin cuotas, sin límite de miembros y sin versión «de pago». No hay nada que contratar.'],
            ['¿Hace falta Internet?', 'No. Compàs funciona sin conexión. Los ficheros de remesas para el banco y los correos se preparan en el programa y los enviáis vosotros desde la banca electrónica o vuestro correo.'],
            ['¿Compàs envía nuestros datos a algún sitio?', 'No. Los datos se quedan en el ordenador de la entidad y, si la configuráis, en la carpeta externa de copias (un USB o una carpeta en la nube que elegís vosotros). Ni el programa ni esta web recogen nada.'],
            ['¿Funciona en Windows y en Linux?', 'Sí. Para Windows 10 u 11 de 64 bits hay un instalador y una versión portable; para Linux, un paquete .deb y un .tar.gz. No hay versión para Mac ni para el móvil.'],
            ['¿Se puede usar desde varios ordenadores a la vez?', 'No: Compàs se instala en un ordenador de la entidad y se usa en ese ordenador. Para cambiar de ordenador, basta con restaurar la última copia en el nuevo.'],
            ['¿Puedo probarlo sin poner los datos de la entidad?', 'Sí. Al instalarlo, podéis pedir que se carguen datos de ejemplo de vuestro tipo de entidad: socios, actividades, recibos… inventados. Cuando lo conozcáis, empezad de cero con los datos reales.'],
            ['Tenemos los socios en un Excel. ¿Hay que escribirlos todos?', 'No. Importar datos lee ficheros .xlsx y .csv: elegís qué columna es cada dato, revisáis la vista previa y se importan. Las personas que ya están no se duplican.'],
            ['¿Compàs hace facturas?', 'No, hace recibos. Las facturas se tienen que hacer con un programa de facturación que cumpla la normativa (Verifactu).'],
            ['¿Y si se estropea el ordenador?', 'Para eso están las copias de seguridad. Compàs hace una cada vez que se cierra, y conviene configurar una carpeta externa para que también se guarden fuera del ordenador. Con la última copia, lo recuperáis todo en otro ordenador.'],
            ['¿Cómo se actualiza?', 'En Ayuda, el botón «¿Hay versión nueva?» abre la página de la última versión. Si hay una nueva, se instala encima de la anterior y los datos se conservan; antes de actualizar, Compàs hace una copia.'],
        ],
        'anterior' => 'Captura anterior', 'seguent' => 'Captura siguiente',
        'contacte' => 'Contacto',
        'xifres' => [['0 €', 'para siempre, sin límite de miembros'], ['0', 'datos que salgan del ordenador'], ['Sin Internet', 'funciona sin conexión'], ['VAL · ES', 'en valenciano y en castellano']],
        'blocs' => [
            ['Personas y socios', 'Una ficha por persona y toda la entidad en un solo lugar, aunque una misma persona sea socia, jugadora y madre de alumnos.', 'web-socis', [0, 1, 28, 18, 16, 20]],
            ['Clubes, fallas, comparsas y mucho más', 'Cada tipo de entidad tiene lo que necesita: licencias y revisiones médicas para los clubes, cargos de fiesta para fallas y comparsas, entidades federadas para las federaciones, y actividades con inscripciones para todos.', 'web-club', [29, 24, 25, 26, 27]],
            ['Escuela', 'De música, deportiva, de talleres o de extraescolares: inscripciones, grupos y horarios del profesorado y de los espacios. Las notas y los boletines están disponibles para sociedades musicales y collas.', 'web-horari', [2, 3, 4, 5, 19]],
            ['Agrupaciones, equipos y material', 'Quién toca o juega en cada formación, qué obras tenéis en el archivo y dónde está cada instrumento, cada equipación y cada traje.', 'web-repertori', [6, 7, 8, 9]],
            ['Economía', 'Cuotas, clases y remesas al banco sin hojas de cálculo: los recibos se hacen solos y no se duplica ninguno.', 'web-rebuts', [10, 11, 23, 12, 13, 17]],
            ['Junta, documentos y copias', 'La parte institucional de la entidad en orden, y los datos siempre con copia.', 'web-junta', [14, 15, 21, 22]],
        ],
        'nav_menu' => 'Menú',
        'que_titol' => 'Qué puedes hacer con Compàs',
        'que_intro' => 'Pensado para quien lleva la secretaría de una entidad, aunque no sea informático.',
        'moduls' => [
            ['Personas', 'Cada persona se introduce una sola vez. Puede ser socia, miembro de un equipo o sección, alumna, monitora y madre de un alumno a la vez, sin repetir datos.'],
            ['Socios', 'Número, los tipos de socio de cada entidad, altas y bajas. Etiquetas para las circulares y la lista de quién cumple 25 o 50 años de socio.'],
            ['Escuela', 'Cursos o temporadas, asignaturas, deportes o talleres, aulas, grupos con horario, matrículas y tutores legales del alumnado menor.'],
            ['Calificaciones', 'El profesorado pasa las notas y administración las introduce por grupo y evaluación, con las observaciones.'],
            ['Boletines', 'Boletines de notas con el logotipo de la entidad, para imprimir o guardar en PDF. Todos a la vez o de uno en uno.'],
            ['Curso nuevo', 'Un asistente prepara el curso siguiente a partir del anterior: trimestres, grupos, profesorado y horarios.'],
            ['Agrupaciones, equipos y secciones', 'Banda y coral, equipos, secciones de la colla, escuadras o comisiones: quién forma parte, con qué instrumento o posición, y quién lo dirige.'],
            ['Repertorio', 'El catálogo del archivo musical: qué obras tiene la sociedad musical, para qué agrupaciones, en qué estado y dónde encontrarlas.'],
            ['Instrumentos y material', 'Inventario, reparaciones y préstamos, con aviso cuando una devolución se ha pasado de fecha.'],
            ['Uniformes y equipaciones', 'Polos, equipaciones, trajes y mochilas por talla: lo que se ha comprado, lo que se ha vendido o prestado y lo que queda.'],
            ['Cuotas de socio', 'Tarifas por tipo de socio y periodicidad. Los recibos de todo un trimestre, hechos con un clic y sin duplicar ninguno.'],
            ['Matrícula y clases', 'Un recibo por familia con la matrícula o las clases de cada hijo y los descuentos de socio y de hermanos.'],
            ['Remesas bancarias', 'Domiciliaciones con la orden para firmar y el archivo SEPA para subir al banco. Control de los recibos devueltos.'],
            ['Lotería', 'Talonarios repartidos, participaciones para imprimir y quién ha liquidado y quién no.'],
            ['Junta y actas', 'Cargos con historial y aviso de renovación. Convocatorias, actas y certificados de acuerdos listos para firmar.'],
            ['Documentos', 'Estatutos, reglamento, seguros y contratos guardados en Compàs, con aviso antes de que caduquen.'],
            ['Comunicados', 'Listas de correo de socios, miembros o familias para enviar en copia oculta, y registro de lo que se ha enviado.'],
            ['Libro de caja', 'Ingresos y gastos con saldo. Los recibos, las remesas y la lotería entran solos.'],
            ['Derechos de imagen', 'Quién autoriza fotos y vídeos, con las hojas de consentimiento preparadas para firmar.'],
            ['Listados y certificados', 'Alumnos de un grupo con los teléfonos de las familias, datos para la memoria anual y certificados con el logotipo.'],
            ['Importación desde Excel', 'Trae los socios y el alumnado que ya tienes en Excel, sin duplicar a nadie.'],
            ['A la medida de cada entidad', 'Activa o desactiva los apartados que no usas y los avisos que no quieres ver. Los datos no se borran.'],
            ['Copias de seguridad', 'Copia automática cada día y al cerrar, también en un USB o en la nube, cifrada con contraseña si quieres.'],
            ['Subvenciones', 'Plazos para pedirlas y justificarlas, cómo se justifica cada una según el organismo, cobros y la relación de facturas para la justificación.'],
            ['Actividades', 'Cenas, salidas, viajes, actos o competiciones con plazas, inscripciones, lista de inscritos y un recibo por inscripción.'],
            ['Licencias', 'Licencias federativas de cada temporada por equipo, con aviso antes de que caduquen la licencia o la revisión médica.'],
            ['Cargos de fiesta', 'Fallera Mayor, Belleza, Madrina o capitanía de cada ejercicio, con el historial y el certificado.'],
            ['Entidades federadas', 'Para federaciones: cada entidad con su representante y la cuota anual, cobrada con recibos.'],
            ['Categorías de edad', 'Alevín, cadete, infantil, mayor…: la categoría de cada persona se calcula sola según el ejercicio.'],
            ['Tipo de entidad', 'Los nombres (socios, falleros, comparsistas, familias…), los apartados y el ejercicio se adaptan a vuestra entidad.'],
        ],
        'veure_titol' => 'Así se ve',
        'veure_intro' => 'Capturas reales del programa: una sociedad musical inventada de 341 socios, 278 alumnos, cinco agrupaciones y 653 obras en el archivo, un club deportivo y una falla.',
        'captures' => [
            'web-inici' => 'La pantalla de inicio: el resumen de la entidad, las clases de hoy y los avisos.',
            'web-club' => 'Un club deportivo: las licencias de la temporada por equipo, con el aviso de las revisiones médicas que caducan.',
            'web-activitat' => 'Una actividad con las inscripciones, las plazas libres y los recibos.',
            'web-falla' => 'Una falla: falleros y falleras, cargos de fiesta, lotería y las próximas actividades.',
            'web-socis' => 'Los socios, con el número, el tipo y la fecha de alta. Se buscan por nombre o número.',
            'web-repertori' => 'El repertorio: 653 obras con el número de archivo, las agrupaciones y dónde está cada carpeta.',
            'web-remesa' => 'Una remesa preparada para el banco: los recibos domiciliados y el archivo SEPA para subirlo.',
            'web-junta' => 'La junta directiva actual y las anteriores, con el aviso de cuándo toca renovarla.',
            'web-alumne' => 'La ficha de un alumno: grupos, horario, notas y tutores legales.',
            'web-notes' => 'Las notas de un grupo y una evaluación, con las observaciones del profesor.',
            'web-butlleti' => 'Un boletín de notas preparado para imprimir, con el logotipo de la entidad.',
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
            ['Empieza', 'Escribe el nombre de la entidad, elige el tipo (sociedad musical, club, falla…) y crea las cuentas de administrador/a y de usuario/a. Si quieres, carga datos de ejemplo para probarlo.'],
        ],
        'dades_titol' => 'Los datos se quedan en la entidad',
        'dades' => [
            'Compàs funciona en un ordenador de la entidad y no envía nada a Internet.',
            'Los datos de menores y socios no pasan por ningún servidor de fuera.',
            'Tiene dos cuentas: administrador/a y usuario/a, y un registro de quién ha hecho cada cambio.',
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
        'docs' => [['manual-usuario.pdf', 'Manual de usuario, paso a paso con capturas'], ['manual-administracion.pdf', 'Manual de administración: configuración, cuentas, copias y actualizaciones'], ['guia-instalacion.pdf', 'Guía de instalación']],
        'lliure_titol' => 'Software libre',
        'lliure' => 'Compàs se distribuye bajo la licencia GNU AGPL v3 o posterior: puedes utilizarlo, estudiarlo, modificarlo y compartirlo. Las versiones modificadas que se ofrezcan a través de una red también deben poner su código fuente a disposición de los usuarios. El código es público en GitHub.',
        'autor' => 'Un proyecto de José Vicente Sanjuan.',
        'ia' => 'Se ha desarrollado con la ayuda de una inteligencia artificial (Claude, de Anthropic).',
        'codi' => 'Código en GitHub',
        'novetats' => 'Qué cambia en cada versión',
        'privacitat' => 'Privacidad',
        'llicencia' => 'Resumen de la licencia',
        'errades' => '¿Has encontrado un error o tienes una propuesta? Abre una incidencia en GitHub.',
        'fi' => 'Hecho para las asociaciones.',
        'resp_titol' => 'Aviso de responsabilidad',
        'resp' => 'Compàs es software libre y se distribuye tal cual, sin ninguna garantía (licencia AGPL-3.0). El autor no se hace responsable de la pérdida de datos ni de ningún otro daño, directo o indirecto, que pueda derivarse del uso del programa, de un error, de una actualización o de una configuración incorrecta. Cada entidad es responsable de sus datos y de hacer copias de seguridad periódicas en un lugar externo (USB o nube).',
        'anterior' => 'Anterior', 'seguent' => 'Siguiente',
    ],
];

$portada = [
    'ca' => [
    'hero_text' => 'Gestiona persones, socis, quotes, rebuts, activitats, economia, junta i documentació des del teu ordinador.',
    'demo' => 'Veure com funciona',
    'download' => 'Descarrega Compàs',
    'trust' => '✓ Windows i Linux · ✓ Gratuït · ✓ Sense núvol · ✓ Programari lliure',
    'benefits' => [['🖥️', 'Local', 'Les dades estan al teu ordinador.'], ['🔒', 'Privat', 'Sense telemetria ni comptes externs.'], ['💶', 'Gratuït', 'Sense quotes ni límit de socis.'], ['🐧', 'Lliure', 'Windows i Linux. Codi obert.']],
    'areas' => [['👥', 'Persones i socis', 'Fitxes, tipus de soci i importació des d’Excel.'], ['💳', 'Quotes i rebuts', 'Domiciliacions, SEPA i remeses bancàries.'], ['💰', 'Economia', 'Llibre de caixa, loteria i subvencions.'], ['📅', 'Activitats', 'Inscripcions, agrupacions i equips.'], ['📄', 'Gestió', 'Junta, actes, documents i certificats.'], ['🎓', 'Escola', 'Cursos, grups i matrícules. Notes i butlletins per a societats musicals i colles.']],
    'all' => 'Veure totes les funcions',
    'tipus_titol' => 'Compàs s’adapta a la teua associació',
    'tipus_intro' => 'En la primera configuració tries el tipus d’entitat i Compàs adapta els mòduls i la terminologia.',
    'veure_titol' => 'Mira Compàs en funcionament',
    'veure_intro' => 'Pantalles reals del programa amb dades inventades. Tria què vols vore.',
    'priv_titol' => 'Les dades de la teua associació són de la teua associació.',
    'flow' => ['Associació', 'Ordinador', 'Base de dades SQLite'],
    'absent' => ['Sense servidor de Compàs', 'Sense compte extern de Compàs', 'Sense telemetria', 'Sense publicitat', 'Sense subscripció'],
    'qui_titol' => 'Fet des de l’experiència d’una associació',
    'qui' => ['Durant sis anys vaig ser president de la Societat Musical de Massalfassar i vaig trobar a faltar una eina fàcil, útil i gratuïta per gestionar-la.', 'Soc José Vicente Sanjuan. Compàs va començar amb les necessitats de la meua societat i ha crescut per a servir també altres associacions. L’he desenvolupat amb l’ajuda d’intel·ligència artificial (Claude, d’Anthropic), a partir de les necessitats i les revisions del projecte.', 'Proveu-lo amb dades d’exemple i, si vos és útil, compartiu-lo amb altres entitats. Els dubtes i les propostes ajuden a millorar-lo.'],
    'com_titol' => 'Comença amb Compàs',
    'free' => 'Compàs és gratuït. Sense registre. Sense subscripció.',
    'latest' => 'Descarrega l’última versió',
    'installer' => 'Instal·lador',
    'portable' => 'Portable',
    'deb' => 'Debian, Ubuntu i Linux Mint',
    'tar' => 'Altres distribucions Linux',
    'help' => 'Instal·lació i ajuda',
    'accounts' => 'Comptes i permisos',
    'gallery' => ['Inici', 'Socis', 'Economia', 'Rebuts', 'Escola', 'Junta'],
],
    'es' => [
    'hero_text' => 'Gestiona personas, socios, cuotas, recibos, actividades, economía, junta y documentación desde tu ordenador.',
    'demo' => 'Ver cómo funciona',
    'download' => 'Descarga Compàs',
    'trust' => '✓ Windows y Linux · ✓ Gratuito · ✓ Sin nube · ✓ Software libre',
    'benefits' => [['🖥️', 'Local', 'Los datos están en tu ordenador.'], ['🔒', 'Privado', 'Sin telemetría ni cuentas externas.'], ['💶', 'Gratuito', 'Sin cuotas ni límite de socios.'], ['🐧', 'Libre', 'Windows y Linux. Código abierto.']],
    'areas' => [['👥', 'Personas y socios', 'Fichas, tipos de socio e importación desde Excel.'], ['💳', 'Cuotas y recibos', 'Domiciliaciones, SEPA y remesas bancarias.'], ['💰', 'Economía', 'Libro de caja, lotería y subvenciones.'], ['📅', 'Actividades', 'Inscripciones, agrupaciones y equipos.'], ['📄', 'Gestión', 'Junta, actas, documentos y certificados.'], ['🎓', 'Escuela', 'Cursos, grupos y matrículas. Notas y boletines para sociedades musicales y collas.']],
    'all' => 'Ver todas las funciones',
    'tipus_titol' => 'Compàs se adapta a tu asociación',
    'tipus_intro' => 'En la primera configuración eliges el tipo de entidad y Compàs adapta los módulos y la terminología.',
    'veure_titol' => 'Mira Compàs en funcionamiento',
    'veure_intro' => 'Pantallas reales del programa con datos inventados. Elige qué quieres ver.',
    'priv_titol' => 'Los datos de tu asociación son de tu asociación.',
    'flow' => ['Asociación', 'Ordenador', 'Base de datos SQLite'],
    'absent' => ['Sin servidor de Compàs', 'Sin cuenta externa de Compàs', 'Sin telemetría', 'Sin publicidad', 'Sin suscripción'],
    'qui_titol' => 'Hecho desde la experiencia de una asociación',
    'qui' => ['Durante seis años fui presidente de la Societat Musical de Massalfassar y eché en falta una herramienta fácil, útil y gratuita para gestionarla.', 'Soy José Vicente Sanjuan. Compàs empezó con las necesidades de mi sociedad y ha crecido para servir también a otras asociaciones. Lo he desarrollado con ayuda de inteligencia artificial (Claude, de Anthropic), a partir de las necesidades y las revisiones del proyecto.', 'Probadlo con datos de ejemplo y, si os resulta útil, compartidlo con otras entidades. Las dudas y las propuestas ayudan a mejorarlo.'],
    'com_titol' => 'Empieza con Compàs',
    'free' => 'Compàs es gratuito. Sin registro. Sin suscripción.',
    'latest' => 'Descarga la última versión',
    'installer' => 'Instalador',
    'portable' => 'Portable',
    'deb' => 'Debian, Ubuntu y Linux Mint',
    'tar' => 'Otras distribuciones Linux',
    'help' => 'Instalación y ayuda',
    'accounts' => 'Cuentas y permisos',
    'gallery' => ['Inicio', 'Socios', 'Economía', 'Recibos', 'Escuela', 'Junta'],
],
];
foreach ($portada as $idioma => $textos) {
    $T[$idioma] = array_replace($T[$idioma], $textos);
}

function e(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function pagina(array $t, string $l, string $base, string $repo, string $versio, array $captures, string $correu, string $formulari, string $web): string
{
    $descarrega = $repo . '/releases/latest';
    $qui = implode('', array_map(fn($p) => '<p>' . e($p) . '</p>', $t['qui']));
    $enllacCorreu = '<a href="mailto:' . e($correu) . '">' . e($correu) . '</a>';
    $contacte = sprintf(e($t['qui_contacte']), $enllacCorreu)
        . ($formulari !== '' ? ' <a href="' . e($formulari) . '">' . e($t['qui_formulari']) . '</a>' : '');
    $compCap = implode('', array_map(fn($c) => '<th scope="col">' . e($c) . '</th>', $t['comp_cols']));
    $compFiles = implode('', array_map(fn($f) => '<tr><th scope="row">' . e($f[0]) . '</th><td class="si">' . e($f[1]) . '</td><td>' . e($f[2]) . '</td></tr>', $t['comp']));
    $tipus = implode('', array_map(fn($x) => '<li><span aria-hidden="true">' . e($x[0]) . '</span><h3>' . e($x[1]) . '</h3></li>', $t['tipus']));
    $avantatges = implode('', array_map(fn($x) => '<li><span class="ic" aria-hidden="true">' . e($x[0]) . '</span><h2>' . e($x[1]) . '</h2><p>' . e($x[2]) . '</p></li>', $t['benefits']));
    $arees = implode('', array_map(fn($x) => '<article class="area"><span class="ic" aria-hidden="true">' . e($x[0]) . '</span><h3>' . e($x[1]) . '</h3><p>' . e($x[2]) . '</p></article>', $t['areas']));
    $moduls = implode('', array_map(fn($x) => '<div class="modul"><dt>' . e($x[0]) . '</dt><dd>' . e($x[1]) . '</dd></div>', $t['moduls']));
    $flux = implode('<span class="fletxa-flux" aria-hidden="true">→</span>', array_map(fn($x) => '<span>' . e($x) . '</span>', $t['flow']));
    $absents = implode('', array_map(fn($x) => '<li>✓ ' . e($x) . '</li>', $t['absent']));
    $downloads = '<article><span class="ic" aria-hidden="true">🪟</span><h3>Windows 10 / 11</h3><a href="' . $descarrega . '/download/Compas-' . e($versio) . '-installador.exe">↓ ' . e($t['installer']) . '</a><a href="' . $descarrega . '/download/Compas-' . e($versio) . '-portable.zip">↓ ' . e($t['portable']) . '</a></article>'
        . '<article><span class="ic" aria-hidden="true">🐧</span><h3>Linux</h3><a href="' . $descarrega . '/download/compas_' . e($versio) . '_all.deb">↓ .deb <small>' . e($t['deb']) . '</small></a><a href="' . $descarrega . '/download/Compas-' . e($versio) . '-linux.tar.gz">↓ .tar.gz <small>' . e($t['tar']) . '</small></a></article>';
    $compartir = $web . ($l === 'es' ? 'img/es/' : 'img/ca/') . 'web-compartir.png';
    $urlPagina = $web . ($l === 'es' ? 'es/' : '');
    $passos = '';
    foreach ($t['passos'] as [$nom, $text]) {
        $passos .= '<li><h3>' . e($nom) . '</h3><p>' . $text . '</p></li>';
    }
    $dades = implode('', array_map(fn($d) => '<li>' . e($d) . '</li>', $t['dades']));
    $req = implode('', array_map(fn($d) => '<li>' . e($d) . '</li>', $t['req']));
    $comptesCap = implode('', array_map(fn($c) => '<th>' . e($c) . '</th>', $t['comptes_cols']));
    $comptes = implode('', array_map(fn($f) => '<tr><th>' . e($f[0]) . '</th><td class="permis">✔</td><td class="permis' . ($f[1] ? '">✔' : ' no">—') . '</td></tr>', $t['comptes']));
    $faq = implode('', array_map(fn($f) => '<details><summary>' . e($f[0]) . '</summary><p>' . e($f[1]) . '</p></details>', $t['faq']));
    $avisos = implode('', array_map(fn($a) => '<dt>' . e($a[0]) . '</dt><dd>' . e($a[1]) . '</dd>', $t['avis']));
    $guia = $repo . '/blob/main/docs/' . ($l === 'es' ? 'guia-instalacion.md#si-windows-no-te-deja-abrirlo' : 'guia-installacio.md#si-windows-no-et-deixa-obrir-lo');
    // Els PDF es generen en publicar la web (workflow web.yml); si no hi són, s'enllacen els de l'última versió de GitHub
    $pdf = fn(string $f) => is_file(dirname(__DIR__) . '/docs/pdf/' . $f) ? $base . 'pdf/' . $f : $repo . '/releases/latest/download/' . $f;
    $docs = implode('', array_map(fn($d) => '<li><a href="' . e($pdf($d[0])) . '">' . e($d[1]) . '</a> <span class="pes">PDF</span></li>', $t['docs']));
    $manualPdf = e($pdf($t['docs'][0][0]));

    $miniatures = '';
    $primera = 'web-inici';
    $galeria = ['web-inici', 'web-socis', 'web-economia', 'web-rebuts', 'web-horari', 'web-junta'];
    foreach ($galeria as $i => $c) {
        $src = $base . 'img/' . $l . '/' . $c . '.webp';
        $miniatures .= '<li><button type="button" data-src="' . e($src) . '" data-peu="' . e($t['captures'][$c]) . '"' . ($i === 0 ? ' aria-current="true"' : '')
            . ' aria-label="' . e($t['captures'][$c]) . '"><img src="' . e($src) . '" alt="" loading="lazy"><span>' . e($t['gallery'][$i]) . '</span></button></li>';
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
<meta property="og:type" content="website">
<meta property="og:url" content="{$urlPagina}">
<meta property="og:image" content="{$compartir}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:locale" content="{$t['og_locale']}">
<meta name="twitter:card" content="summary_large_image">
<meta name="theme-color" content="#14284b">
<link rel="icon" href="{$base}img/icona.png">
<link rel="alternate" hreflang="{$t['altre']}" href="{$t['altre_url']}">
<link rel="preload" href="{$base}fonts/atkinson-hyperlegible-regular.woff" as="font" type="font/woff" crossorigin>
<link rel="stylesheet" href="{$base}estil.css">
</head>
<body>
<header class="cap-web"><div class="barra">
  <a class="marca" href="./"><img src="{$base}img/logo.png" alt="" width="64" height="64"><span>Compàs</span></a>
  <nav aria-label="{$t['nav_menu']}">
    <a href="#entitats">{$t['nav_tipus']}</a>
    <a href="#que">{$t['nav_que']}</a>
    <a href="#captures">{$t['nav_veure']}</a>
    <a href="#qui">{$t['nav_qui']}</a>
    <a class="nav-baixa" href="#comencar">{$t['download']}</a>
    <a href="#preguntes">{$t['nav_faq']}</a>
    <a class="idioma" href="{$t['altre_url']}" hreflang="{$t['altre']}" lang="{$t['altre']}">{$t['altre_nom']}</a>
  </nav>
</div></header>

<main>
<section class="hero">
  <div class="hero-text">
    <div class="hero-marca"><img src="{$base}img/logo.png" alt="Compàs" width="160" height="160"><div><p class="nom">Compàs</p><p class="lema">{$t['lema']}</p></div></div>
    <h1>{$t['hero']}</h1>
    <p class="entrada">{$t['hero_text']}</p>
    <div class="accions">
      <a class="boto" href="#comencar">↓ {$t['download']}</a>
      <a class="boto secundari" href="#captures">{$t['demo']} →</a>
    </div>
    <p class="versio">{$versioText}</p>
    <p class="confianca">{$t['trust']}</p>
  </div>
  <figure class="ordinador"><div class="pantalla"><div class="camera" aria-hidden="true"></div><img src="{$base}img/{$l}/web-inici.webp" alt="{$t['captures']['web-inici']}" width="1920" height="1200" fetchpriority="high"></div><div class="peu-ordinador" aria-hidden="true"></div></figure>
</section>

<ul class="avantatges seccio">{$avantatges}</ul>

<section id="entitats" class="seccio entitats">
  <div class="cap">
    <h2>{$t['tipus_titol']}</h2>
    <p>{$t['tipus_intro']}</p>
  </div>
  <ul class="tipus-entitats">{$tipus}</ul>
</section>

<section id="que" class="seccio">
  <div class="cap">
    <h2>{$t['que_titol']}</h2>
    <p>{$t['que_intro']}</p>
  </div>
  <div class="arees">{$arees}</div>
  <details class="totes-funcions"><summary>{$t['all']} →</summary><dl class="moduls">{$moduls}</dl></details>
</section>

<section id="captures" class="seccio galeria">
  <div class="cap">
    <h2>{$t['veure_titol']}</h2>
    <p>{$t['veure_intro']}</p>
  </div>
  <figure class="principal">
    <div class="visor">
      <img id="captura" src="{$base}img/{$l}/{$primera}.webp" alt="{$t['captures'][$primera]}">
      <button type="button" class="fletxa ant" aria-label="{$t['anterior']}">‹</button>
      <button type="button" class="fletxa seg" aria-label="{$t['seguent']}">›</button>
    </div>
    <figcaption id="peu" aria-live="polite">{$t['captures'][$primera]}</figcaption>
  </figure>
  <ul class="miniatures" aria-label="{$t['veure_titol']}">{$miniatures}</ul>
</section>

<section id="privacitat" class="seccio privacitat">
  <div class="cap"><p class="etiqueta">{$t['nav_priv']}</p><h2>{$t['priv_titol']}</h2></div>
  <div class="flux" aria-label="Compàs">{$flux}</div>
  <ul class="absents">{$absents}</ul>
  <p class="nota">{$t['dades'][0]}</p>
  <h3 class="comp-titol">{$t['comp_titol']}</h3>
  <div class="taula-comp-wrap"><table class="taula-comp"><thead><tr>{$compCap}</tr></thead><tbody>{$compFiles}</tbody></table></div>
</section>

<div class="separador" aria-hidden="true"></div>

<section id="qui" class="seccio qui">
  <h2>{$t['qui_titol']}</h2>
  <blockquote>{$qui}
    <footer>— {$t['qui_signa']}</footer>
  </blockquote>
  <p class="contacte">{$contacte}</p>
  <p class="nota"><a href="{$repo}/issues/new/choose">{$t['qui_errades']}</a></p>
</section>

<section id="comencar" class="seccio descarregues">
  <div class="cap"><h2>{$t['com_titol']}</h2><p>{$t['free']}</p></div>
  <div class="paquets">{$downloads}</div>
  <p><a class="boto" href="{$descarrega}">↓ {$t['latest']}</a></p>
  <p class="nota">{$versioText}</p>
  <p class="enllacos"><a href="{$manualPdf}">{$t['manual']}</a> · <a href="{$repo}">{$t['nav_codi']}</a> · <a href="{$repo}/blob/main/LICENSE">AGPL-3.0</a></p>
  <details class="ajuda"><summary>{$t['help']}</summary><ol class="passos">{$passos}</ol><p><a href="{$guia}">{$t['avis_mes']}</a></p><ul class="punts">{$req}</ul><ul class="docs">{$docs}</ul></details>
</section>

<section id="preguntes" class="seccio preguntes">
  <div class="cap">
    <h2>{$t['faq_titol']}</h2>
    <p>{$t['faq_intro']}</p>
  </div>
  <div class="faq">{$faq}</div>
  <details class="ajuda"><summary>{$t['accounts']}</summary><h3 class="comp-titol">{$t['comptes_titol']}</h3>
  <p class="comptes-intro">{$t['comptes_intro']}</p>
  <div class="taula-comp-wrap"><table class="taula-comp taula-comptes"><thead><tr>{$comptesCap}</tr></thead><tbody>{$comptes}</tbody></table></div></details>
</section>

<section class="seccio lliure">
  <h2>{$t['lliure_titol']}</h2>
  <p>{$t['lliure']}</p>
  <p class="nota">{$t['ia']}</p>
  <p><a class="boto secundari" href="{$repo}">{$t['codi']}</a></p>
  <p class="nota"><a href="{$repo}/blob/main/CHANGELOG.md">{$t['novetats']}</a> · <a href="{$repo}/issues/new/choose">{$t['errades']}</a></p>
</section>
</main>

<div class="baixa-fixa" hidden><a class="boto" href="#comencar">↓ {$t['download']}</a></div>

<footer class="peu">
  <div class="doble-barra" aria-hidden="true"></div>
  <p><img src="{$base}img/icona.png" alt="" width="28" height="28"> <span>Compàs · © {$any} José Vicente Sanjuan. {$t['fi']}</span> <a href="{$repo}/blob/main/LICENSE">AGPL-3.0-or-later</a> <a href="{$repo}/blob/main/LLICENCIA.md">{$t['llicencia']}</a> <a href="{$repo}/blob/main/PRIVACITAT.md">{$t['privacitat']}</a> <a href="mailto:{$correu}">{$t['contacte']}: {$correu}</a></p>
  <p class="peu-resp">{$t['resp']}</p>
</footer>

<script>
// Mòbil: el botó de descàrrega es queda a baix quan els botons de dalt ja no es veuen
(() => {
  const fixa = document.querySelector('.baixa-fixa');
  const dalt = document.querySelector('.hero .accions');
  if (!fixa || !dalt || !('IntersectionObserver' in window)) return;
  fixa.hidden = false;
  new IntersectionObserver(([e]) => fixa.classList.toggle('visible', !e.isIntersecting && e.boundingClientRect.top < 0)).observe(dalt);
})();
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
    const llista = b.closest('.miniatures');
    llista.scrollLeft = b.parentElement.offsetLeft - llista.offsetLeft;
  };
  botons.forEach((b, i) => b.addEventListener('click', () => mostra(i)));
  document.querySelector('.fletxa.ant').addEventListener('click', () => mostra(actual - 1));
  document.querySelector('.fletxa.seg').addEventListener('click', () => mostra(actual + 1));
  // Tecles ← → quan la galeria està a la vista
  const galeria = document.getElementById('captures');
  document.addEventListener('keydown', e => {
    if (e.target.closest('input, textarea, select, summary, [contenteditable]')) return;
    const r = galeria.getBoundingClientRect();
    if (r.bottom < 0 || r.top > innerHeight) return;
    if (e.key === 'ArrowLeft') { e.preventDefault(); mostra(actual - 1); }
    if (e.key === 'ArrowRight') { e.preventDefault(); mostra(actual + 1); }
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
copy($arrel . '/docs/imatges/logo-compas.png', $eixida . '/img/logo.png');
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
        copy("$arrel/docs/imatges/$l/$c.webp", "$eixida/img/$l/$c.webp");
    }
    copy("$arrel/docs/imatges/$l/web-compartir.png", "$eixida/img/$l/web-compartir.png");
}
foreach (glob($arrel . '/docs/pdf/*.pdf') as $pdf) {
    copy($pdf, $eixida . '/pdf/' . basename($pdf));
}
file_put_contents($eixida . '/index.html', pestanya_nova(pagina($T['ca'], 'ca', '', $repo, $versio, $captures, $correu, $formulari, $web)));
file_put_contents($eixida . '/es/index.html', pestanya_nova(pagina($T['es'], 'es', '../', $repo, $versio, $captures, $correu, $formulari, $web)));
file_put_contents($eixida . '/.nojekyll', '');
echo "Web generada a $eixida\n";
