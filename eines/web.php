<?php
// Genera la web de presentació de Compàs (GitHub Pages) en valencià i castellà.
// Ús: php eines/web.php carpeta-eixida
// Copia el logotip, unes quantes captures (docs/imatges) i els PDF (docs/pdf).
// SPDX-License-Identifier: GPL-2.0-or-later

declare(strict_types=1);

$arrel = dirname(__DIR__);
$eixida = rtrim($argv[1] ?? $arrel . '/_site', '/');
$repo = 'https://github.com/jossanmar9/compas';
$versio = getenv('COMPAS_VERSIO') ?: (preg_match("/COMPAS_VERSIO = '([^']+)'/", file_get_contents($arrel . '/src/bootstrap.php'), $m) ? $m[1] : '');

$captures = ['02-inici', '14-alumne-resum', '27-qualificacions', '29-butlleti-impres', '25-horari', '19-agrupacio-fitxa', '34-copies'];

$T = [
    'ca' => [
        'lang' => 'ca-valencia', 'altre' => 'es', 'altre_nom' => 'Castellano', 'altre_url' => 'es/',
        'titol' => 'Compàs: gestió lliure per a societats musicals',
        'descripcio' => "Programa lliure i gratuït per a gestionar una societat musical des d'un ordinador amb Windows: persones, socis, escola de música, notes, butlletins, agrupacions i instruments.",
        'lema' => 'Gestió lliure per a societats musicals',
        'hero' => 'Tota la societat musical, en un sol programa.',
        'hero_text' => "Socis, alumnat, professorat, notes, butlletins, músics i instruments, en un ordinador de la societat. Sense Internet, sense quotes i en valencià i castellà.",
        'descarrega' => 'Descarrega per a Windows',
        'versio' => 'Versió %s. Gratuït i lliure.',
        'manual' => 'Llig el manual',
        'nav_que' => 'Què fa', 'nav_veure' => 'Captures', 'nav_com' => 'Com començar', 'nav_codi' => 'Codi',
        'que_titol' => 'Què pots fer amb Compàs',
        'que_intro' => 'Pensat per a qui porta la secretaria i l\'escola de música, encara que no siga informàtic.',
        'moduls' => [
            ['Persones', 'Cada persona s\'introdueix una sola vegada. Pot ser sòcia, música, alumna, professora i mare d\'un alumne alhora, sense repetir dades.'],
            ['Socis', 'Número, tipus, altes i baixes. Llistats que s\'exporten a Excel per a fer etiquetes o circulars.'],
            ['Escola de música', 'Cursos, assignatures, aules, grups amb horari, matrícules i tutors legals de l\'alumnat menor.'],
            ['Qualificacions', 'El professorat passa les notes i administració les introdueix per grup i avaluació, amb les observacions.'],
            ['Butlletins', 'Butlletins de notes amb el logotip de la societat, per a imprimir o guardar en PDF. Tots alhora o d\'un en un.'],
            ['Agrupacions', 'Banda, banda juvenil, coral, orquestra… Qui toca què i qui dirigeix cada formació.'],
            ['Instruments', 'Inventari, reparacions i préstecs, amb avís quan una devolució s\'ha passat de data.'],
            ['Còpies de seguretat', 'Còpia automàtica cada dia i en tancar, també en un USB o en el núvol. Es restaura amb un clic.'],
        ],
        'veure_titol' => 'Així es veu',
        'veure_intro' => 'Captures reals del programa amb dades d\'exemple.',
        'captures' => [
            '02-inici' => 'La pantalla d\'inici: el resum de la societat, les classes de hui i els avisos.',
            '14-alumne-resum' => 'La fitxa d\'un alumne: grups, horari, notes i tutors legals.',
            '27-qualificacions' => 'Les notes d\'un grup i una avaluació, amb les observacions del professor.',
            '29-butlleti-impres' => 'Un butlletí de notes preparat per a imprimir, amb el logotip de la societat.',
            '25-horari' => 'L\'horari de tota l\'escola, que es pot filtrar per professor o per aula.',
            '19-agrupacio-fitxa' => 'Els membres d\'una agrupació, amb l\'instrument i la funció de cadascú.',
            '34-copies' => 'Les còpies de seguretat: automàtiques, manuals i en una carpeta externa.',
        ],
        'com_titol' => 'Com començar',
        'passos' => [
            ['Descarrega l\'instal·lador', 'Des de la pàgina de descàrregues, baixa <code>Compas-…-installador.exe</code>. També hi ha una versió portable que no cal instal·lar.'],
            ['Instal·la\'l', 'No demana permisos d\'administrador. Windows pot avisar que és un programa poc conegut: tria «Més informació» i «Executa igualment».'],
            ['Comença', 'Escriu el nom de la societat i crea els comptes d\'administrador i d\'administratiu. Si vols, carrega dades d\'exemple per a provar-lo.'],
        ],
        'dades_titol' => 'Les dades es queden a la societat',
        'dades' => [
            'Compàs funciona en un ordinador de la societat i no envia res a Internet.',
            'Les dades d\'alumnes menors i socis no passen per cap servidor de fora.',
            'Té dos comptes: administrador i administratiu, i un registre de qui ha fet cada canvi.',
        ],
        'req_titol' => 'Què cal',
        'req' => ['Un ordinador amb Windows 10 o 11 de 64 bits', 'Uns 100 MB de disc', 'Un llapis USB o una carpeta al núvol per a les còpies (recomanat)'],
        'docs_titol' => 'Manuals',
        'docs' => [['manual-usuari.pdf', 'Manual d\'usuari, pas a pas amb captures'], ['guia-installacio.pdf', 'Guia d\'instal·lació']],
        'lliure_titol' => 'Programari lliure',
        'lliure' => 'Compàs es distribueix sota la llicència GPL v2 o posterior: el pots utilitzar, estudiar, modificar i compartir. El codi és públic a GitHub.',
        'ia' => 'S\'ha desenvolupat amb l\'ajuda d\'una intel·ligència artificial (Claude, d\'Anthropic).',
        'codi' => 'Codi a GitHub',
        'novetats' => 'Què canvia en cada versió',
        'errades' => 'Has trobat una errada o tens una proposta? Obri una incidència a GitHub.',
        'fi' => 'Fet per a les societats musicals.',
        'anterior' => 'Anterior', 'seguent' => 'Següent',
    ],
    'es' => [
        'lang' => 'es', 'altre' => 'ca', 'altre_nom' => 'Valencià', 'altre_url' => '../',
        'titol' => 'Compàs: gestión libre para sociedades musicales',
        'descripcio' => 'Programa libre y gratuito para gestionar una sociedad musical desde un ordenador con Windows: personas, socios, escuela de música, notas, boletines, agrupaciones e instrumentos.',
        'lema' => 'Gestión libre para sociedades musicales',
        'hero' => 'Toda la sociedad musical, en un solo programa.',
        'hero_text' => 'Socios, alumnado, profesorado, notas, boletines, músicos e instrumentos, en un ordenador de la sociedad. Sin Internet, sin cuotas y en valenciano y castellano.',
        'descarrega' => 'Descargar para Windows',
        'versio' => 'Versión %s. Gratuito y libre.',
        'manual' => 'Leer el manual',
        'nav_que' => 'Qué hace', 'nav_veure' => 'Capturas', 'nav_com' => 'Cómo empezar', 'nav_codi' => 'Código',
        'que_titol' => 'Qué puedes hacer con Compàs',
        'que_intro' => 'Pensado para quien lleva la secretaría y la escuela de música, aunque no sea informático.',
        'moduls' => [
            ['Personas', 'Cada persona se introduce una sola vez. Puede ser socia, música, alumna, profesora y madre de un alumno a la vez, sin repetir datos.'],
            ['Socios', 'Número, tipo, altas y bajas. Listados que se exportan a Excel para hacer etiquetas o circulares.'],
            ['Escuela de música', 'Cursos, asignaturas, aulas, grupos con horario, matrículas y tutores legales del alumnado menor.'],
            ['Calificaciones', 'El profesorado pasa las notas y administración las introduce por grupo y evaluación, con las observaciones.'],
            ['Boletines', 'Boletines de notas con el logotipo de la sociedad, para imprimir o guardar en PDF. Todos a la vez o de uno en uno.'],
            ['Agrupaciones', 'Banda, banda juvenil, coral, orquesta… Quién toca qué y quién dirige cada formación.'],
            ['Instrumentos', 'Inventario, reparaciones y préstamos, con aviso cuando una devolución se ha pasado de fecha.'],
            ['Copias de seguridad', 'Copia automática cada día y al cerrar, también en un USB o en la nube. Se restaura con un clic.'],
        ],
        'veure_titol' => 'Así se ve',
        'veure_intro' => 'Capturas reales del programa con datos de ejemplo.',
        'captures' => [
            '02-inici' => 'La pantalla de inicio: el resumen de la sociedad, las clases de hoy y los avisos.',
            '14-alumne-resum' => 'La ficha de un alumno: grupos, horario, notas y tutores legales.',
            '27-qualificacions' => 'Las notas de un grupo y una evaluación, con las observaciones del profesor.',
            '29-butlleti-impres' => 'Un boletín de notas preparado para imprimir, con el logotipo de la sociedad.',
            '25-horari' => 'El horario de toda la escuela, que se puede filtrar por profesor o por aula.',
            '19-agrupacio-fitxa' => 'Los miembros de una agrupación, con el instrumento y la función de cada uno.',
            '34-copies' => 'Las copias de seguridad: automáticas, manuales y en una carpeta externa.',
        ],
        'com_titol' => 'Cómo empezar',
        'passos' => [
            ['Descarga el instalador', 'Desde la página de descargas, baja <code>Compas-…-installador.exe</code>. También hay una versión portable que no hace falta instalar.'],
            ['Instálalo', 'No pide permisos de administrador. Windows puede avisar de que es un programa poco conocido: elige «Más información» y «Ejecutar de todas formas».'],
            ['Empieza', 'Escribe el nombre de la sociedad y crea las cuentas de administrador y de administrativo. Si quieres, carga datos de ejemplo para probarlo.'],
        ],
        'dades_titol' => 'Los datos se quedan en la sociedad',
        'dades' => [
            'Compàs funciona en un ordenador de la sociedad y no envía nada a Internet.',
            'Los datos de alumnos menores y socios no pasan por ningún servidor de fuera.',
            'Tiene dos cuentas: administrador y administrativo, y un registro de quién ha hecho cada cambio.',
        ],
        'req_titol' => 'Qué hace falta',
        'req' => ['Un ordenador con Windows 10 u 11 de 64 bits', 'Unos 100 MB de disco', 'Un lápiz USB o una carpeta en la nube para las copias (recomendado)'],
        'docs_titol' => 'Manuales',
        'docs' => [['manual-usuario.pdf', 'Manual de usuario, paso a paso con capturas'], ['guia-instalacion.pdf', 'Guía de instalación']],
        'lliure_titol' => 'Software libre',
        'lliure' => 'Compàs se distribuye bajo la licencia GPL v2 o posterior: lo puedes usar, estudiar, modificar y compartir. El código es público en GitHub.',
        'ia' => 'Se ha desarrollado con la ayuda de una inteligencia artificial (Claude, de Anthropic).',
        'codi' => 'Código en GitHub',
        'novetats' => 'Qué cambia en cada versión',
        'errades' => '¿Has encontrado un error o tienes una propuesta? Abre una incidencia en GitHub.',
        'fi' => 'Hecho para las sociedades musicales.',
        'anterior' => 'Anterior', 'seguent' => 'Siguiente',
    ],
];

function e(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function pagina(array $t, string $l, string $base, string $repo, string $versio, array $captures): string
{
    $descarrega = $repo . '/releases/latest';
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
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Atkinson+Hyperlegible:ital,wght@0,400;0,700;1,400&family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,700;12..96,800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{$base}estil.css">
</head>
<body>
<header class="barra">
  <a class="marca" href="./"><img src="{$base}img/icona.png" alt="" width="36" height="36"><span>Compàs</span></a>
  <nav aria-label="Seccions">
    <a href="#que">{$t['nav_que']}</a>
    <a href="#captures">{$t['nav_veure']}</a>
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
  </div>
  <figure class="hero-captura">
    <img src="{$base}img/{$l}/02-inici.jpg" alt="{$t['captures']['02-inici']}" width="1920" height="1200">
  </figure>
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
    <img id="captura" src="{$base}img/{$l}/{$primera}.jpg" alt="{$t['captures'][$primera]}">
    <figcaption id="peu">{$t['captures'][$primera]}</figcaption>
  </figure>
  <ul class="miniatures" aria-label="{$t['veure_titol']}">{$miniatures}</ul>
</section>

<section id="comencar" class="seccio">
  <div class="cap"><h2>{$t['com_titol']}</h2></div>
  <ol class="passos">{$passos}</ol>
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
  <p><img src="{$base}img/icona.png" alt="" width="28" height="28"> <span>Compàs, {$any}. {$t['fi']}</span> <a href="{$repo}/blob/main/LICENSE">GPL-2.0-or-later</a></p>
</footer>

<script>
document.querySelectorAll('.miniatures button').forEach(b => {
  b.addEventListener('click', () => {
    const img = document.getElementById('captura');
    img.src = b.dataset.src; img.alt = b.dataset.peu;
    document.getElementById('peu').textContent = b.dataset.peu;
    document.querySelectorAll('.miniatures button').forEach(x => x.removeAttribute('aria-current'));
    b.setAttribute('aria-current', 'true');
  });
});
</script>
</body>
</html>
HTML;
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
foreach (['ca', 'es'] as $l) {
    foreach ($captures as $c) {
        copy("$arrel/docs/imatges/$l/$c.jpg", "$eixida/img/$l/$c.jpg");
    }
}
foreach (glob($arrel . '/docs/pdf/*.pdf') as $pdf) {
    copy($pdf, $eixida . '/pdf/' . basename($pdf));
}
file_put_contents($eixida . '/index.html', pagina($T['ca'], 'ca', '', $repo, $versio, $captures));
file_put_contents($eixida . '/es/index.html', pagina($T['es'], 'es', '../', $repo, $versio, $captures));
file_put_contents($eixida . '/.nojekyll', '');
echo "Web generada a $eixida\n";
