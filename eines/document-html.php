<?php
// Converteix un manual o guia (Markdown) en una pàgina HTML preparada per a imprimir en PDF.
// Ús: php eines/document-html.php docs/manual-usuari.md eixida.html
// Després: navegador sense interfície (Chrome/Edge) --print-to-pdf
// SPDX-License-Identifier: GPL-2.0-or-later

if ($argc < 3) {
    fwrite(STDERR, "Ús: php eines/document-html.php entrada.md eixida.html\n");
    exit(1);
}
require dirname(__DIR__) . '/src/bootstrap.php';

$md = file_get_contents($argv[1]);
if ($md === false) {
    fwrite(STDERR, "No es pot llegir $argv[1]\n");
    exit(1);
}
$titol = preg_match('/^# (.+)$/m', $md, $m) ? trim($m[1]) : 'Compàs';
$cos = markdown_simple(preg_replace('/^# .*\R/', '', $md, 1));
$castella = str_contains(basename($argv[1]), 'usuario') || str_contains(basename($argv[1]), 'instalacion');
$lema = $castella ? 'Gestión libre para sociedades musicales' : 'Gestió lliure per a societats musicals';
$versio = ($castella ? 'Versión ' : 'Versió ') . (getenv('COMPAS_VERSIO') ?: COMPAS_VERSIO);
$logo = 'data:image/png;base64,' . base64_encode(file_get_contents(ARREL_DIR . '/app/assets/logo.png'));

$castellaLang = $castella ? 'es' : 'ca-valencia';
$titolH = h($titol);
$lemaH = h($lema);
$versioH = h($versio);

$html = <<<HTML
<!doctype html>
<html lang="{$castellaLang}">
<head>
<meta charset="utf-8">
<title>{$titolH}</title>
<style>
@page {
  size: A4; margin: 18mm 17mm 20mm;
  @bottom-center { content: "Compàs · " counter(page) " / " counter(pages); font-family: "Segoe UI", Arial, sans-serif; font-size: 8pt; color: #8a93a3; }
}
@page :first { @bottom-center { content: none; } }
* { box-sizing: border-box; }
body { margin: 0; color: #1d2433; font-family: "Segoe UI", system-ui, Arial, sans-serif; font-size: 10.5pt; line-height: 1.5; }
.portada { height: 255mm; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; page-break-after: always; }
.portada img { width: 85mm; }
.portada h1 { font-size: 24pt; color: #14284b; margin: 14mm 0 3mm; }
.portada p { color: #5d6778; margin: 0; }
.portada .versio { margin-top: 2mm; font-size: 9.5pt; }
h2 { color: #1f5aa6; font-size: 15pt; margin: 9mm 0 3mm; padding-bottom: 1.5mm; border-bottom: 1.5px solid #e8a33d; page-break-after: avoid; }
h3 { color: #14284b; font-size: 12pt; margin: 6mm 0 2mm; page-break-after: avoid; }
h4 { font-size: 11pt; margin: 5mm 0 2mm; }
p, li { orphans: 3; widows: 3; }
ul, ol { padding-left: 6mm; }
li { margin: 1mm 0; }
code { background: #e8f0fa; padding: 0 1.2mm; border-radius: 1mm; font-family: Consolas, "Courier New", monospace; font-size: 9.5pt; }
pre { background: #f4f6fa; border: 1px solid #dde3ec; border-radius: 2mm; padding: 3mm 4mm; font-family: Consolas, "Courier New", monospace; font-size: 9pt; line-height: 1.35; white-space: pre-wrap; page-break-inside: avoid; }
blockquote { margin: 4mm 0; padding: 2.5mm 4mm; background: #fff6e5; border-left: 3px solid #e8a33d; border-radius: 1mm; page-break-inside: avoid; }
table { width: 100%; border-collapse: collapse; margin: 3mm 0; page-break-inside: avoid; }
th, td { border: 1px solid #c9d2df; padding: 1.8mm 2.5mm; text-align: left; vertical-align: top; }
th { background: #e8f0fa; font-size: 9.5pt; }
a { color: #1f5aa6; text-decoration: none; }
.taula-wrap { overflow: visible; }
</style>
</head>
<body>
<section class="portada">
  <img src="{$logo}" alt="Compàs">
  <h1>{$titolH}</h1>
  <p>{$lemaH}</p>
  <p class="versio">{$versioH}</p>
</section>
<main>{$cos}</main>
</body>
</html>
HTML;

file_put_contents($argv[2], $html);
