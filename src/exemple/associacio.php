<?php
// Una entitat gran i inventada de qualsevol tipus que no siga una societat musical (club, colla, muixeranga, associació
// cultural, AMPA, falla, gaiata, foguera, comparsa, penya o federació), per a provar Compàs amb un volum realista:
// centenars de persones amb les seues famílies, socis, grups, escola o tallers, llicències, càrrecs de festa, loteria,
// activitats amb inscripcions, rebuts de dos exercicis, remeses, junta, reunions, documents, caixa i subvencions.
// L'executa carrega_exemple_gran() dins d'una funció: hi arriben $AVUI (la data de «hui» de les dades), $configura
// (si cal posar també el nom i l'adreça de l'entitat inventada) i $tipus. Les dates es calculen a partir de $AVUI.
// Copyright (C) 2026 José Vicente Sanjuan
// SPDX-License-Identifier: AGPL-3.0-or-later

$ES = config('idioma') === 'es';
$tr = fn(string $ca, string $es) => $ES ? $es : $ca;
$info = info_tipus($tipus);
$te = fn(string $modul) => in_array($modul, $info['moduls'], true);
$C = (int)substr($AVUI, 0, 4);                                                         // any natural
$A = (int)date('n', strtotime($AVUI)) >= 9 ? $C : $C - 1;                               // curs (escola, tallers) que va de setembre a juny
$EX = any_exercici($AVUI);                                                             // exercici o temporada en curs
$M0 = mes_inici_exercici();
$iniciEx = fn(int $any) => sprintf('%d-%02d-01', $any, $M0);
$rnd = fn(int $a, int $b) => mt_rand($a, $b);
$tria = fn(array $a) => $a[array_rand($a)];
$prob = fn(float $p) => mt_rand() / mt_getrandmax() < $p;
$id = fn() => (int)db()->lastInsertId();
$data = fn(string $ini, string $fi) => date('Y-m-d', mt_rand(strtotime($ini), max(strtotime($ini), min(strtotime($fi), strtotime($AVUI)))));
$abans = fn(int $dies) => date('Y-m-d', strtotime("$AVUI -$dies days"));
$despres = fn(int $dies) => date('Y-m-d', strtotime("$AVUI +$dies days"));
$festa = in_array($tipus, ['falla', 'gaiata', 'foguera', 'comparsa'], true);

// ---------- Perfil de cada tipus: grups, escola, roba, material i activitats ----------
// grups: [nom ca, nom es, tipus d'agrupació, persones, edat mínima, edat màxima, funcions o posicions]
// escola: [activitat ca, activitat es, preu mensual, [[grup ca, grup es, edat mínima, edat màxima, alumnes], …]]
// activitats: [nom ca, nom es, tipus, dies (negatiu: ja ha passat), hora, preu, places, proporció d'inscrits]
$posicions = [$tr('Porter/a', 'Portero/a'), 'Defensa', $tr('Migcampista', 'Centrocampista'), $tr('Davanter/a', 'Delantero/a')];
$P = [
    'club_esportiu' => [
        'nom' => ['Club Esportiu Vilanova', 'Club Deportivo Vilanova'], 'socis' => 420, 'quota' => ['anual', 60, 25],
        'grups' => [['Prebenjamí A', 'Prebenjamín A', 'equip', 14, 6, 7, $posicions], ['Prebenjamí B', 'Prebenjamín B', 'equip', 13, 6, 7, $posicions],
            ['Benjamí A', 'Benjamín A', 'equip', 15, 8, 9, $posicions], ['Benjamí B', 'Benjamín B', 'equip', 14, 8, 9, $posicions],
            ['Aleví A', 'Alevín A', 'equip', 16, 10, 11, $posicions], ['Aleví B', 'Alevín B', 'equip', 15, 10, 11, $posicions],
            ['Infantil', 'Infantil', 'equip', 18, 12, 13, $posicions], ['Cadet', 'Cadete', 'equip', 18, 14, 15, $posicions],
            ['Juvenil', 'Juvenil', 'equip', 20, 16, 18, $posicions], ['Sènior A', 'Sénior A', 'equip', 22, 19, 33, $posicions],
            ['Sènior B', 'Sénior B', 'equip', 20, 19, 34, $posicions], ['Femení', 'Femenino', 'equip', 18, 16, 32, $posicions],
            ['Veterans', 'Veteranos', 'equip', 16, 35, 55, $posicions]],
        'escola' => [['Escola de futbol', 'Escuela de fútbol', 25, [['Iniciació (4-5 anys)', 'Iniciación (4-5 años)', 4, 5, 22], ['Prebenjamí', 'Prebenjamín', 6, 7, 26]]],
            ['Tecnificació de porters', 'Tecnificación de porteros', 20, [['Grup únic', 'Grupo único', 8, 16, 12]]],
            ['Futbol sala', 'Fútbol sala', 22, [['Benjamí i aleví', 'Benjamín y alevín', 8, 11, 20], ['Infantil i cadet', 'Infantil y cadete', 12, 15, 18]]]],
        'aules' => [['Camp municipal 1', 'Campo municipal 1', 60], ['Camp municipal 2', 'Campo municipal 2', 60], ['Pavelló', 'Pabellón', 40]],
        'uniformes' => [['Equipació de joc', 'Equipación de juego', 'samarreta', '6, 8, 10, 12, 14, S, M, L, XL', 45, 'venda'],
            ['Xandall del club', 'Chándal del club', 'jaqueta', '6, 8, 10, 12, 14, S, M, L, XL', 38, 'venda'], ['Motxilla', 'Mochila', 'motxilla', null, 18, 'venda'],
            ['Peto d\'entrenament', 'Peto de entrenamiento', 'altre_uniforme', 'S, M, L', 4, 'prestec']],
        'material' => [['Porteria plegable', 'Portería plegable', 4, 'Kipsta'], ['Joc de pilotes (10)', 'Juego de balones (10)', 12, 'Joma'], ['Farmaciola', 'Botiquín', 6, null],
            ['Joc de cons (20)', 'Juego de conos (20)', 8, null], ['Marcador portàtil', 'Marcador portátil', 2, null]],
        'activitats' => [['Torneig de Nadal', 'Torneo de Navidad', 'competicio', 33, '10:00', 5, 180, .5], ['Campus d\'estiu', 'Campus de verano', 'curs_taller', -120, '09:00', 120, 80, .25],
            ['Sopar de final de temporada', 'Cena de final de temporada', 'apat', -150, '21:30', 22, 250, .35], ['Viatge al torneig de Salou', 'Viaje al torneo de Salou', 'viatge', 160, '07:00', 95, 60, .12],
            ['Presentació d\'equips', 'Presentación de equipos', 'acte', -45, '18:00', null, null, .6], ['Xarrada sobre nutrició esportiva', 'Charla sobre nutrición deportiva', 'reunio_oberta', 12, '19:30', null, 60, .08]],
        'loteria' => 60, 'llicencies' => ['Federació de Futbol de la Comunitat Valenciana', 'Federación de Fútbol de la Comunidad Valenciana'],
    ],
    'colla' => [
        'nom' => ['Colla de Dolçainers i Tabaleters La Xafigà', 'Colla de Dulzaineros y Tabaleteros La Xafigà'], 'socis' => 160, 'quota' => ['semestral', 20, 8],
        'grups' => [['Dolçaina', 'Dulzaina', 'seccio', 26, 10, 70, ['Dolçaina']], ['Tabal', 'Tabal', 'seccio', 16, 10, 70, ['Tabal']],
            ['Gegants', 'Gigantes', 'seccio', 16, 16, 60, [$tr('Geganter/a', 'Gigantero/a')]], ['Cabets', 'Cabezudos', 'seccio', 18, 8, 40, [$tr('Cabet', 'Cabezudo')]],
            ['Bastoners', 'Bastoneros', 'grup', 20, 10, 50, [$tr('Bastoner/a', 'Bastonero/a')]]],
        'escola' => [['Escola de dolçaina', 'Escuela de dulzaina', 22, [['Iniciació', 'Iniciación', 8, 14, 14], ['Perfeccionament', 'Perfeccionamiento', 12, 60, 10]]],
            ['Escola de tabal', 'Escuela de tabal', 18, [['Grup únic', 'Grupo único', 8, 60, 12]]]],
        'aules' => [['Local de la colla', 'Local de la colla', 30]],
        'uniformes' => [['Brusa de la colla', 'Blusa de la colla', 'camisa', 'S, M, L, XL', 28, 'venda'], ['Mocador', 'Pañuelo', 'altre_uniforme', null, 6, 'gratuit'],
            ['Faixa', 'Faja', 'altre_uniforme', null, 9, 'venda']],
        'material' => [['Dolçaina', 'Dulzaina', 12, null], ['Tabal', 'Tabal', 8, null], ['Gegant', 'Gigante', 4, null], ['Cabet', 'Cabezudo', 10, null]],
        'activitats' => [['Trobada de colles', 'Encuentro de colles', 'acte', 26, '17:00', null, null, .5], ['Cercavila de les festes', 'Pasacalles de las fiestas', 'acte', -60, '19:00', null, null, .7],
            ['Dinar de germanor', 'Comida de hermandad', 'apat', -20, '14:00', 18, 150, .45], ['Eixida a l\'Aplec', 'Salida al Aplec', 'eixida', 75, '08:00', 15, 55, .3]],
        'loteria' => 25,
    ],
    'muixeranga' => [
        'nom' => ['Muixeranga de Vilanova', 'Muixeranga de Vilanova'], 'socis' => 210, 'quota' => ['anual', 35, 12],
        'grups' => [['Base i pinya', 'Base y piña', 'seccio', 80, 16, 70, ['Base', $tr('Pinya', 'Piña'), $tr('Mans', 'Manos')]],
            ['Segones i terceres', 'Segundas y terceras', 'seccio', 36, 14, 50, [$tr('Segona', 'Segunda'), $tr('Tercera', 'Tercera')]],
            ['Xicalla', 'Chiquillería', 'seccio', 30, 6, 13, [$tr('Alçadora', 'Alzadora'), $tr('Enxaneta', 'Enxaneta')]],
            ['Música', 'Música', 'seccio', 12, 12, 65, ['Dolçaina', 'Tabal']], ['Equip tècnic', 'Equipo técnico', 'seccio', 9, 25, 60, [$tr('Cap de colla', 'Jefe/a de colla'), $tr('Tècnic/a', 'Técnico/a')]]],
        'uniformes' => [['Camisa de la colla', 'Camisa de la colla', 'camisa', '6, 8, 10, 12, S, M, L, XL', 25, 'venda'], ['Faixa', 'Faja', 'altre_uniforme', null, 12, 'venda'],
            ['Mocador', 'Pañuelo', 'altre_uniforme', null, 5, 'gratuit'], ['Casc de la xicalla', 'Casco de la chiquillería', 'altre_uniforme', null, 30, 'prestec']],
        'material' => [['Matalàs d\'assaig', 'Colchoneta de ensayo', 10, null], ['Dolçaina', 'Dulzaina', 4, null], ['Tabal', 'Tabal', 3, null], ['Casc', 'Casco', 30, null]],
        'activitats' => [['Actuació de les festes patronals', 'Actuación de las fiestas patronales', 'acte', 19, '12:00', null, null, .6],
            ['Diada de la colla', 'Diada de la colla', 'acte', -70, '11:00', null, null, .7], ['Assaig obert a famílies', 'Ensayo abierto a familias', 'reunio_oberta', 8, '19:00', null, null, .2],
            ['Sopar de la diada', 'Cena de la diada', 'apat', -70, '21:30', 16, 200, .5], ['Trobada de muixerangues', 'Encuentro de muixerangas', 'viatge', 48, '08:30', 12, 110, .45]],
        'loteria' => 35,
    ],
    'colla_castellera' => [
        'nom' => ['Colla Castellera de Vilanova', 'Colla Castellera de Vilanova'], 'socis' => 230, 'quota' => ['anual', 35, 12],
        'grups' => [['Pinya', 'Piña', 'seccio', 90, 16, 70, [$tr('Baix', 'Bajo'), $tr('Crossa', 'Muleta'), $tr('Contrafort', 'Contrafuerte'), $tr('Agulla', 'Aguja'), $tr('Mans', 'Manos')]],
            ['Tronc', 'Tronco', 'seccio', 34, 15, 45, [$tr('Segon', 'Segundo'), $tr('Terç', 'Tercero'), $tr('Quart', 'Cuarto')]],
            ['Canalla', 'Chiquillería', 'seccio', 28, 6, 13, [$tr('Dosos', 'Dosos'), $tr('Acotxador/a', 'Acotxador/a'), $tr('Enxaneta', 'Enxaneta')]],
            ['Gralles i timbals', 'Grallas y timbales', 'seccio', 12, 12, 65, ['Gralla', $tr('Timbal', 'Timbal')]], ['Equip tècnic', 'Equipo técnico', 'seccio', 10, 25, 60, [$tr('Cap de colla', 'Jefe/a de colla'), $tr('Tècnic/a', 'Técnico/a')]]],
        'uniformes' => [['Camisa de la colla', 'Camisa de la colla', 'camisa', '6, 8, 10, 12, S, M, L, XL', 25, 'venda'], ['Faixa', 'Faja', 'altre_uniforme', null, 12, 'venda'],
            ['Casc de la canalla', 'Casco de la chiquillería', 'altre_uniforme', null, 30, 'prestec']],
        'material' => [['Matalàs d\'assaig', 'Colchoneta de ensayo', 10, null], ['Gralla', 'Gralla', 6, null], ['Timbal', 'Timbal', 3, null], ['Casc', 'Casco', 28, null]],
        'activitats' => [['Diada castellera', 'Diada castellera', 'acte', 19, '12:00', null, null, .7], ['Actuació a la plaça Major', 'Actuación en la plaza Mayor', 'acte', -30, '12:00', null, null, .6],
            ['Assaig obert a famílies', 'Ensayo abierto a familias', 'reunio_oberta', 8, '19:00', null, null, .2], ['Sopar de la diada', 'Cena de la diada', 'apat', 19, '21:30', 16, 220, .45],
            ['Eixida a Valls', 'Salida a Valls', 'viatge', 62, '06:30', 25, 110, .4]],
        'loteria' => 35,
    ],
    'associacio_cultural' => [
        'nom' => ['Associació Cultural L\'Envelat', 'Asociación Cultural L\'Envelat'], 'socis' => 420, 'quota' => ['anual', 30, 10],
        'grups' => [['Grup de teatre', 'Grupo de teatro', 'grup', 24, 16, 75, [$tr('Actor/actriu', 'Actor/actriz'), $tr('Tècnic/a', 'Técnico/a')]],
            ['Cor de veus', 'Coro de voces', 'coral', 34, 18, 80, ['Soprano', $tr('Contralt', 'Contralto'), 'Tenor', $tr('Baix', 'Bajo')]],
            ['Grup de dansa', 'Grupo de danza', 'grup', 22, 12, 45, [$tr('Ballador/a', 'Bailarín/a')]], ['Rondalla', 'Rondalla', 'grup', 14, 25, 80, [$tr('Guitarra', 'Guitarra'), $tr('Llaüt', 'Laúd'), 'Bandúrria']],
            ['Club de lectura', 'Club de lectura', 'grup', 18, 25, 85, []]],
        'escola' => [['Teatre infantil', 'Teatro infantil', 20, [['De 7 a 10 anys', 'De 7 a 10 años', 7, 10, 16], ['De 11 a 14 anys', 'De 11 a 14 años', 11, 14, 14]]],
            ['Dansa urbana', 'Danza urbana', 22, [['Infantil', 'Infantil', 7, 11, 18], ['Juvenil', 'Juvenil', 12, 17, 16]]],
            ['Fotografia', 'Fotografía', 25, [['Adults', 'Adultos', 18, 75, 14]]], ['Pintura', 'Pintura', 24, [['Adults', 'Adultos', 18, 85, 15]]],
            ['Anglés de conversa', 'Inglés de conversación', 28, [['Nivell mitjà', 'Nivel medio', 18, 70, 12]]], ['Ioga', 'Yoga', 26, [['Matins', 'Mañanas', 30, 80, 16], ['Vesprades', 'Tardes', 20, 70, 18]]]],
        'aules' => [['Sala gran', 'Sala grande', 40], ['Aula 1', 'Aula 1', 18], ['Aula 2', 'Aula 2', 18], ['Escenari', 'Escenario', 30]],
        'uniformes' => [['Samarreta de l\'associació', 'Camiseta de la asociación', 'samarreta', 'S, M, L, XL', 10, 'venda'], ['Bossa de tela', 'Bolsa de tela', 'altre_uniforme', null, 5, 'venda']],
        'material' => [['Focus d\'escenari', 'Foco de escenario', 12, null], ['Equip de so', 'Equipo de sonido', 2, 'Behringer'], ['Projector', 'Proyector', 2, 'Epson'], ['Guitarra', 'Guitarra', 3, null]],
        'activitats' => [['Estrena de teatre', 'Estreno de teatro', 'acte', 22, '20:00', 8, 180, .3], ['Excursió al Penyagolosa', 'Excursión al Penyagolosa', 'eixida', -35, '08:00', 12, 55, .1],
            ['Viatge cultural a València', 'Viaje cultural a Valencia', 'viatge', 50, '08:00', 45, 55, .12], ['Presentació de llibre', 'Presentación de libro', 'reunio_oberta', -10, '19:30', null, null, .08],
            ['Concert de Nadal del cor', 'Concierto de Navidad del coro', 'acte', 34, '19:00', null, null, .2], ['Sopar dels socis', 'Cena de los socios', 'apat', -140, '21:30', 20, 160, .25]],
        'loteria' => 30,
    ],
    'ampa' => [
        'nom' => ['AMPA del CEIP Les Eres', 'AMPA del CEIP Les Eres'], 'socis' => 270, 'quota' => ['anual', 25, 0],
        'escola' => [['Anglés', 'Inglés', 22, [['Infantil', 'Infantil', 4, 5, 20], ['1r i 2n', '1.º y 2.º', 6, 7, 22], ['3r i 4t', '3.º y 4.º', 8, 9, 20], ['5é i 6é', '5.º y 6.º', 10, 11, 18]]],
            ['Robòtica', 'Robótica', 28, [['De 7 a 9 anys', 'De 7 a 9 años', 7, 9, 14], ['De 10 a 12 anys', 'De 10 a 12 años', 10, 12, 14]]],
            ['Escacs', 'Ajedrez', 15, [['Grup únic', 'Grupo único', 6, 12, 16]]], ['Futbol sala', 'Fútbol sala', 18, [['Benjamí', 'Benjamín', 6, 9, 24], ['Aleví', 'Alevín', 10, 12, 22]]],
            ['Patinatge', 'Patinaje', 20, [['Iniciació', 'Iniciación', 4, 8, 18], ['Avançat', 'Avanzado', 8, 12, 14]]], ['Música i moviment', 'Música y movimiento', 16, [['Infantil', 'Infantil', 3, 5, 18]]],
            ['Teatre', 'Teatro', 18, [['Grup únic', 'Grupo único', 8, 12, 16]]], ['Matinera', 'Madrugadores', 30, [['De 7.30 a 9', 'De 7.30 a 9', 3, 12, 40]]]],
        'aules' => [['Gimnàs', 'Gimnasio', 40], ['Aula de música', 'Aula de música', 25], ['Biblioteca', 'Biblioteca', 25], ['Pati', 'Patio', 60], ['Aula 3B', 'Aula 3B', 25]],
        'grups' => [['Comissió de festes', 'Comisión de fiestas', 'comissio', 12, 30, 55, []], ['Comissió de menjador', 'Comisión de comedor', 'comissio', 8, 30, 55, []],
            ['Comissió d\'extraescolars', 'Comisión de extraescolares', 'comissio', 7, 30, 55, []]],
        'uniformes' => [['Xandall de l\'escola', 'Chándal del colegio', 'jaqueta', '3, 4, 6, 8, 10, 12, 14', 32, 'venda'], ['Samarreta d\'educació física', 'Camiseta de educación física', 'samarreta', '3, 4, 6, 8, 10, 12, 14', 9, 'venda'],
            ['Bata d\'infantil', 'Bata de infantil', 'altre_uniforme', '3, 4, 5', 12, 'venda']],
        'activitats' => [['Castanyada', 'Castañada', 'acte', -22, '16:30', null, null, .3], ['Festa de Carnestoltes', 'Fiesta de Carnaval', 'acte', 95, '16:30', null, null, .4],
            ['Escola de Nadal', 'Escuela de Navidad', 'curs_taller', 34, '09:00', 60, 80, .15], ['Escola de Pasqua', 'Escuela de Pascua', 'curs_taller', -210, '09:00', 50, 70, .14],
            ['Xarrada sobre pantalles i famílies', 'Charla sobre pantallas y familias', 'reunio_oberta', 9, '17:00', null, 80, .12], ['Festa de fi de curs', 'Fiesta de fin de curso', 'acte', -150, '18:00', 5, 400, .45]],
    ],
    'falla' => [
        'nom' => ['Falla Plaça del Mercat', 'Falla Plaza del Mercado'], 'socis' => 340, 'quota' => ['mensual', 32, 12, 'protector' => 8],
        'uniformes' => [['Brusó de la comissió', 'Blusón de la comisión', 'camisa', '6, 8, 10, 12, S, M, L, XL, XXL', 30, 'venda'], ['Mocador de la falla', 'Pañuelo de la falla', 'altre_uniforme', null, 7, 'venda'],
            ['Samarreta de la falla', 'Camiseta de la falla', 'samarreta', '6, 8, 10, 12, S, M, L, XL', 12, 'venda']],
        'activitats' => [['Presentació de la Fallera Major', 'Presentación de la Fallera Mayor', 'acte', 45, '19:00', 25, 400, .5], ['Sopar de pa i porta', 'Cena de pan y porta', 'apat', -12, '21:30', null, null, .4],
            ['Paella de germanor', 'Paella de hermandad', 'apat', -75, '14:00', 12, 300, .55], ['Playback infantil', 'Playback infantil', 'competicio', 26, '17:30', null, null, .15],
            ['Cavalcada del Ninot', 'Cabalgata del Ninot', 'acte', 110, '18:00', null, null, .35], ['Viatge a la Gala Fallera', 'Viaje a la Gala Fallera', 'viatge', 60, '16:00', 30, 110, .2],
            ['Sopar de la Cridà', 'Cena de la Cridà', 'apat', 100, '21:30', 20, 280, .45]],
        'loteria' => 140,
    ],
    'gaiata' => [
        'nom' => ['Gaiata 7 Sant Roc', 'Gaiata 7 San Roque'], 'socis' => 230, 'quota' => ['mensual', 28, 10],
        'uniformes' => [['Brusó de la gaiata', 'Blusón de la gaiata', 'camisa', '6, 8, 10, 12, S, M, L, XL', 28, 'venda'], ['Mocador', 'Pañuelo', 'altre_uniforme', null, 6, 'venda']],
        'activitats' => [['Proclamació de la Madrina', 'Proclamación de la Madrina', 'acte', 40, '19:00', 20, 300, .5], ['Sopar de germanor', 'Cena de hermandad', 'apat', -30, '21:30', 18, 200, .45],
            ['Romeria de les Canyes', 'Romería de les Canyes', 'eixida', 120, '07:00', null, null, .5], ['Concurs de paelles', 'Concurso de paellas', 'competicio', -90, '12:00', 10, 150, .35],
            ['Encesa de la gaiata', 'Encendido de la gaiata', 'acte', 125, '20:00', null, null, .4]],
        'loteria' => 90,
    ],
    'foguera' => [
        'nom' => ['Foguera Carrer de la Mar', 'Foguera Calle del Mar'], 'socis' => 280, 'quota' => ['mensual', 30, 11],
        'uniformes' => [['Brusó de la foguera', 'Blusón de la foguera', 'camisa', '6, 8, 10, 12, S, M, L, XL', 28, 'venda'], ['Mocador', 'Pañuelo', 'altre_uniforme', null, 6, 'venda']],
        'activitats' => [['Proclamació de les Belleses', 'Proclamación de las Bellezas', 'acte', 50, '19:00', 22, 320, .5], ['Sopar de germanor', 'Cena de hermandad', 'apat', -25, '21:30', 18, 220, .45],
            ['Concurs de paelles', 'Concurso de paellas', 'competicio', -100, '12:00', 10, 160, .3], ['Viatge a la Gala de les Fogueres', 'Viaje a la Gala de las Hogueras', 'viatge', 80, '16:00', 28, 110, .2],
            ['Plantà infantil', 'Plantà infantil', 'acte', 200, '18:00', null, null, .4]],
        'loteria' => 100,
    ],
    'comparsa' => [
        'nom' => ['Comparsa Contrabandistes', 'Comparsa Contrabandistas'], 'socis' => 300, 'quota' => ['trimestral', 45, 15],
        'grups' => [['Esquadra Especial', 'Escuadra Especial', 'esquadra', 14, 18, 55, [$tr('Cap d\'esquadra', 'Cabo de escuadra'), $tr('Fester/a', 'Festero/a')]],
            ['Esquadra Negra', 'Escuadra Negra', 'esquadra', 14, 18, 55, [$tr('Cap d\'esquadra', 'Cabo de escuadra'), $tr('Fester/a', 'Festero/a')]],
            ['Esquadra del Tio Pep', 'Escuadra del Tío Pep', 'esquadra', 13, 18, 60, [$tr('Cap d\'esquadra', 'Cabo de escuadra'), $tr('Fester/a', 'Festero/a')]],
            ['Esquadra La Revolta', 'Escuadra La Revolta', 'esquadra', 13, 18, 50, [$tr('Cap d\'esquadra', 'Cabo de escuadra'), $tr('Fester/a', 'Festero/a')]],
            ['Esquadra Femenina', 'Escuadra Femenina', 'esquadra', 14, 18, 55, [$tr('Cap d\'esquadra', 'Cabo de escuadra'), $tr('Festera', 'Festera')]],
            ['Esquadra Juvenil', 'Escuadra Juvenil', 'esquadra', 16, 14, 17, [$tr('Cap d\'esquadra', 'Cabo de escuadra'), $tr('Fester/a', 'Festero/a')]],
            ['Esquadra Infantil A', 'Escuadra Infantil A', 'esquadra', 16, 6, 9, [$tr('Fester/a', 'Festero/a')]], ['Esquadra Infantil B', 'Escuadra Infantil B', 'esquadra', 16, 10, 13, [$tr('Fester/a', 'Festero/a')]],
            ['Esquadra de Veterans', 'Escuadra de Veteranos', 'esquadra', 12, 50, 80, [$tr('Cap d\'esquadra', 'Cabo de escuadra'), $tr('Fester/a', 'Festero/a')]],
            ['Comissió de festes', 'Comisión de fiestas', 'comissio', 9, 25, 65, []]],
        'uniformes' => [['Vestit de contrabandista', 'Traje de contrabandista', 'altre_uniforme', '8, 10, 12, S, M, L, XL', 320, 'prestec'], ['Vestit d\'esquadra especial', 'Traje de escuadra especial', 'altre_uniforme', 'S, M, L, XL', 650, 'prestec'],
            ['Polo de la comparsa', 'Polo de la comparsa', 'polo', '8, 10, 12, S, M, L, XL', 18, 'venda']],
        'activitats' => [['Entrada de moros i cristians', 'Entrada de moros y cristianos', 'acte', 120, '18:00', null, null, .7], ['Sopar de l\'Esquadra', 'Cena de la Escuadra', 'apat', -40, '21:30', 22, 280, .45],
            ['Mig any fester', 'Medio año festero', 'acte', 15, '19:00', null, null, .5], ['Dinar de germanor', 'Comida de hermandad', 'apat', -110, '14:00', 18, 280, .5],
            ['Assaig de l\'entrada', 'Ensayo de la entrada', 'acte', 108, '20:00', null, null, .4]],
        'loteria' => 90,
    ],
    'penya' => [
        'nom' => ['Penya El Botijó', 'Peña El Botijó'], 'socis' => 190, 'quota' => ['anual', 50, 15],
        'uniformes' => [['Samarreta de la penya', 'Camiseta de la peña', 'samarreta', 'S, M, L, XL, XXL', 12, 'venda'], ['Mocador', 'Pañuelo', 'altre_uniforme', null, 5, 'venda']],
        'activitats' => [['Sopar de festes', 'Cena de fiestas', 'apat', -60, '21:30', 22, 190, .55], ['Viatge a la final', 'Viaje a la final', 'viatge', 55, '07:00', 40, 55, .25],
            ['Calçotada', 'Calçotada', 'apat', 110, '13:30', 18, 120, .4], ['Eixida a la neu', 'Salida a la nieve', 'eixida', 75, '06:30', 60, 50, .2],
            ['Torneig de truc', 'Torneo de truc', 'competicio', 18, '17:00', 5, 64, .2]],
        'loteria' => 60,
    ],
    'federacio' => [
        'nom' => ['Federació d\'Entitats de la Plana', 'Federación de Entidades de la Plana'], 'socis' => 40, 'quota' => ['anual', 20, 0], 'federades' => 64,
        'activitats' => [['Assemblea general', 'Asamblea general', 'reunio_oberta', -200, '18:00', null, null, .9], ['Curs de gestió d\'entitats', 'Curso de gestión de entidades', 'curs_taller', 25, '18:30', 15, 40, 1],
            ['Trobada anual d\'entitats', 'Encuentro anual de entidades', 'acte', 60, '10:00', 12, 300, 1], ['Jornada sobre subvencions', 'Jornada sobre subvenciones', 'curs_taller', -40, '18:30', null, 60, 1]],
    ],
][$tipus];

// ---------- Persones ----------
['h' => $nomsH, 'd' => $nomsD, 'cognoms' => $cognomsL, 'carrers' => $carrers] = noms_exemple($ES);
$pobles = [['Vilanova de la Plana', '12999', 0.80], ['Benicompàs', '12998', 0.12], ['L\'Alqueria Nova', '12997', 0.08]];
$dniN = 41000000;
$persones = []; // id => [nom, cognoms, naixement, sexe, llar]
$persona = function (string $sexe, string $naix, ?string $cognoms = null, ?array $llar = null) use (&$dniN, &$persones, $nomsH, $nomsD, $cognomsL, $carrers, $pobles, $rnd, $tria, $prob, $AVUI, $tr) {
    $nom = $tria($sexe === 'H' ? $nomsH : $nomsD);
    $cognoms ??= $tria($cognomsL) . ' ' . $tria($cognomsL);
    $adult = $naix <= date('Y-m-d', strtotime("$AVUI -18 years"));
    $dniN += $rnd(1, 900000);
    $dni = $dniN . 'TRWAGMYFPDXBNJZSQVHLCKE'[$dniN % 23];
    if (!$llar) {
        $x = mt_rand() / mt_getrandmax();
        $p = $x < $pobles[0][2] ? $pobles[0] : ($x < $pobles[0][2] + $pobles[1][2] ? $pobles[1] : $pobles[2]);
        $llar = [$tria($carrers) . ', ' . $rnd(1, 120) . ($prob(.3) ? ', ' . $rnd(1, 6) . 'a' : ''), $p[1], $p[0]];
    }
    $sense = $adult && $prob(.03); // alguns adults sense DNI o telèfon (avisos de dades que falten)
    $email = $adult || $prob(.15) ? strtolower(strtr(iconv('UTF-8', 'ASCII//TRANSLIT', $nom . '.' . explode(' ', $cognoms)[0]), [' ' => '', "'" => ''])) . $rnd(1, 99) . '@exemple.org' : null;
    $cons = $prob(.82) ? 'autoritza' : ($prob(.5) ? 'no_autoritza' : 'pendent');
    q('INSERT INTO persones(nom, cognoms, data_naixement, document, telefon, email, adreca, codi_postal, poblacio, provincia, consentiment_imatge, consentiment_data, creat)
       VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)', [
        $nom, $cognoms, $naix, $adult && !$sense ? $dni : ($prob(.4) ? $dni : null),
        $adult && !$sense ? '6' . $rnd(10, 99) . ' ' . $rnd(100, 999) . ' ' . $rnd(100, 999) : null,
        $email, $llar[0], $llar[1], $llar[2], $tr('Castelló', 'Castellón'), $cons, $cons === 'pendent' ? null : date('Y-m-d', strtotime('-' . $rnd(10, 900) . ' days', strtotime($AVUI))),
        date('Y-m-d H:i:s', strtotime('-' . $rnd(30, 1500) . ' days', strtotime($AVUI))),
    ]);
    $pid = (int)db()->lastInsertId();
    $persones[$pid] = [$nom, $cognoms, $naix, $sexe, $llar];
    return $pid;
};
$sexe = fn() => mt_rand(0, 1) ? 'H' : 'D';
$naixEdat = fn(int $min, int $max) => date('Y-m-d', strtotime($AVUI . ' -' . $rnd($min, $max) . ' years -' . $rnd(0, 364) . ' days'));
$edat = function (int $p) use (&$persones, $AVUI): int { // (no pot ser «fn»: ha de veure les persones que es creen després)
    return (int)((strtotime($AVUI) - strtotime($persones[$p][2])) / (365.25 * 86400));
};
// Una família: mare (i pare, quasi sempre) amb els fills de les edats que es demanen, a la mateixa casa
$families = []; // [[tutor principal, altre tutor o null], [fills]]
$familia = function (array $edats) use ($persona, $naixEdat, $tria, $prob, $cognomsL, &$persones, &$families): array {
    $cog1 = $tria($cognomsL);
    $cog2 = $tria($cognomsL);
    $major = max($edats);
    $mare = $persona('D', $naixEdat(max(26, $major + 22), min(58, $major + 42)), $cog2 . ' ' . $tria($cognomsL));
    $llar = $persones[$mare][4];
    $pare = $prob(.8) ? $persona('H', $naixEdat(max(27, $major + 23), min(60, $major + 45)), $cog1 . ' ' . $tria($cognomsL), $llar) : null;
    $fills = [];
    foreach ($edats as $e) {
        $fills[] = $persona(mt_rand(0, 1) ? 'H' : 'D', $naixEdat($e, $e), $cog1 . ' ' . $cog2, $llar);
    }
    $tutors = $pare && $prob(.25) ? [$pare, $mare] : [$mare, $pare];
    $families[] = [$tutors, $fills];
    return [$tutors, $fills];
};

db()->beginTransaction();

// ---------- Configuració ----------
$nomEntitat = $tr($P['nom'][0], $P['nom'][1]);
$ibanN = 0;
$iban = function () use (&$ibanN) {
    $ibanN++;
    $ccc = ['2100', '0049', '3058', '2038', '0182'][$ibanN % 5] . sprintf('%04d', 1000 + $ibanN % 900) . sprintf('%02d', $ibanN % 97) . sprintf('%010d', 4000000 + $ibanN * 7);
    return 'ES' . sprintf('%02d', 98 - mod97($ccc . '142800')) . $ccc;
};
foreach ([
    'societat_nom' => $nomEntitat, 'societat_cif' => 'G12888888', 'societat_adreca' => $tr('Carrer Major, 12', 'Calle Mayor, 12'),
    'societat_poblacio' => '12999 Vilanova de la Plana', 'societat_telefon' => '964 000 111', 'societat_email' => 'secretaria@exemple.org',
    'sepa_nom' => $nomEntitat, 'sepa_nif' => 'G12888888', 'sepa_sufix' => '000', 'sepa_iban' => $iban(),
    'descompte_soci' => $te('escola') ? '10' : '', 'descompte_germans' => $te('escola') ? '5' : '', 'junta_anys_mandat' => '4',
] as $c => $v) {
    if ($v !== '' && ($configura || (!str_starts_with($c, 'societat_') && (string)config($c) === ''))) {
        desa_config($c, $c === 'sepa_nom' && !$configura ? (config('societat_nom') ?: $v) : $v);
    }
}
$tipusSoci = [];
foreach (q('SELECT id, codi FROM tipus_socis')->fetchAll() as $t) {
    $tipusSoci[$t['codi']] = (int)$t['id'];
}

// ---------- Grups (equips, seccions, esquadres, comissions…) i qui en forma part ----------
$agr = [];
$membresDe = [];   // agrupació => [persones]
$adultsActius = []; // adults que participen (i que seran socis)
$menorsActius = [];
$perEdat = [];      // edat => persones menors ja creades, per a reaprofitar-les en un altre grup
if ($te('agrupacions') || $tipus === 'ampa') {
    foreach ($P['grups'] ?? [] as $g => [$ca, $es, $tp, $n, $emin, $emax, $rols]) {
        q('INSERT INTO agrupacions(nom, tipus, data_creacio) VALUES(?,?,?)', [$tr($ca, $es), $tp, $data('1985-01-01', ($C - 2) . '-09-01')]);
        $a = $id();
        $agr[$g] = $a;
        for ($k = 0; $k < $n; $k++) {
            $p = null;
            if ($emax < 18) {
                // Menors: de vegades germans d'algú que ja està en un altre grup
                $e = $rnd($emin, $emax);
                if (!empty($perEdat[$e]) && $prob(.15)) {
                    $p = array_pop($perEdat[$e]);
                } else {
                    [, $fills] = $familia($prob(.35) ? [$e, $rnd(3, 16)] : [$e]);
                    $p = $fills[0];
                    foreach (array_slice($fills, 1) as $f) {
                        $perEdat[$edat($f)][] = $f;
                    }
                }
                $menorsActius[$p] = true;
            } elseif ($emin >= 18 || $prob(.85)) {
                $p = $persona($tipus === 'comparsa' && str_contains($ca, 'Femenina') ? 'D' : $sexe(), $naixEdat(max(18, $emin), $emax));
                $adultsActius[$p] = true;
            } else {
                [, $fills] = $familia([$rnd($emin, 17)]);
                $p = $fills[0];
                $menorsActius[$p] = true;
            }
            $rol = $rols ? ($k === 0 && count($rols) > 1 && str_contains($rols[0], $tr('Cap', 'Cabo')) ? $rols[0] : $tria($rols)) : null;
            q('INSERT INTO membres(persona_id, agrupacio_id, instrument_veu, data_alta) VALUES(?,?,?,?)', [$p, $a, $rol, $data(($C - 8) . '-09-01', $iniciEx($EX))]);
            $membresDe[$a][] = $p;
        }
        // Qui el dirigix o l'entrena
        if (!in_array($tp, ['comissio'], true) && $tipus !== 'comparsa') {
            $dir = $persona($sexe(), $naixEdat(26, 60));
            $adultsActius[$dir] = true;
            q("INSERT INTO membres(persona_id, agrupacio_id, funcio, data_alta) VALUES(?,?, 'director_titular', ?)", [$dir, $a, $data(($C - 6) . '-01-01', $iniciEx($EX))]);
            $membresDe[$a]['dir'] = $dir;
            if ($tipus === 'club_esportiu' && $prob(.6)) {
                $seg = $persona($sexe(), $naixEdat(19, 45));
                $adultsActius[$seg] = true;
                q("INSERT INTO membres(persona_id, agrupacio_id, funcio, data_alta) VALUES(?,?, 'director_assistent', ?)", [$seg, $a, $data(($C - 3) . '-01-01', $iniciEx($EX))]);
            }
        } elseif ($tp === 'comissio') {
            q("UPDATE membres SET funcio = 'responsable' WHERE id = (SELECT MIN(id) FROM membres WHERE agrupacio_id = ?)", [$a]);
        }
        // Alguns que ho han deixat
        for ($k = 0; $k < max(1, intdiv($n, 8)); $k++) {
            q("INSERT INTO membres(persona_id, agrupacio_id, instrument_veu, data_alta, data_baixa, estat) VALUES(?,?,?,?,?, 'baixa')",
                [$persona($sexe(), $naixEdat(max(18, $emin + 2), max(20, $emax + 6))), $a, $rols ? $tria($rols) : null, $data(($C - 10) . '-01-01', ($C - 4) . '-01-01'), $data(($C - 3) . '-01-01', $abans(30))]);
        }
    }
}

// ---------- Escola, tallers o extraescolars ----------
$alumnes = [];
if ($te('escola') && !empty($P['escola'])) {
    $cursos = [];
    foreach ([[$A - 1, 'tancat', 0], [$A, 'actiu', 1]] as [$a0, $estat, $actual]) {
        $nom = $M0 === 9 ? nom_exercici($a0) : $a0 . '/' . ($a0 + 1);
        q('INSERT INTO cursos(nom, data_inici, data_fi, estat, actual) VALUES(?,?,?,?,?)', [$nom, "$a0-09-15", ($a0 + 1) . '-06-19', $estat, $actual]);
        $cursos[$a0] = $id();
    }
    $aules = [];
    foreach ($P['aules'] as [$ca, $es, $cap]) {
        q('INSERT INTO aules(nom, capacitat) VALUES(?,?)', [$tr($ca, $es), $cap]);
        $aules[] = $id();
    }
    // Monitors i monitores (amb el certificat de delictes sexuals; a un li falta i a un altre li ha caducat)
    $monitors = [];
    $nm = max(3, (int)ceil(count($P['escola']) * 1.3));
    for ($k = 0; $k < $nm; $k++) {
        $p = $persona($sexe(), $naixEdat(20, 55));
        q('INSERT INTO professors(persona_id, data_alta, certificat_delictes) VALUES(?,?,?)',
            [$p, $data(($C - 6) . '-09-01', "$A-09-10"), $k === 1 ? null : ($k === 2 ? ($C - 6) . '-09-02' : $data(($C - 2) . '-06-01', "$A-09-10"))]);
        $monitors[] = $id();
    }
    $slots = ['16:00', '17:00', '17:30', '18:00', '18:30', '19:00', '20:00'];
    $tornM = 0;
    foreach ($P['escola'] as $i => [$ca, $es, $preu, $grups]) {
        q("INSERT INTO assignatures(nom, tipus) VALUES(?, 'altra')", [$tr($ca, $es)]);
        $as = $id();
        q("INSERT INTO tarifes(nom, tipus, assignatura_id, periodicitat, import) VALUES(?, 'classe', ?, 'mensual', ?)", [$tr($ca, $es), $as, $preu]);
        foreach ($cursos as $a0 => $cid) {
            foreach ($grups as $j => [$gca, $ges, $emin, $emax, $n]) {
                $prof = $monitors[$tornM++ % count($monitors)];
                q('INSERT INTO grups(curs_id, assignatura_id, nom, professor_id, aula_id) VALUES(?,?,?,?,?)', [$cid, $as, $tr($gca, $ges), $prof, $aules[($i + $j) % count($aules)]]);
                $g = $id();
                $hora = $ca === 'Matinera' ? '07:30' : $slots[($i * 2 + $j) % count($slots)];
                $fi = date('H:i', strtotime("2000-01-01 $hora +" . ($ca === 'Matinera' ? 90 : 60) . ' minutes'));
                foreach ($ca === 'Matinera' ? [1, 2, 3, 4, 5] : [[1, 3], [2, 4], [1, 4], [2, 5], [3, 5]][($i + $j) % 5] as $dia) {
                    q('INSERT INTO horaris(grup_id, dia, hora_inici, hora_fi) VALUES(?,?,?,?)', [$g, $dia, $hora, $fi]);
                }
                // Alumnes: els d'edat que ja hi ha (jugadors, fills de socis…) i, si en falten, famílies noves
                $dif = $A - $a0; // al curs passat tenien un any menys
                for ($k = 0; $k < $n - ($dif ? $rnd(0, 3) : 0); $k++) {
                    $e = $rnd($emin, $emax) + $dif;
                    $p = null;
                    if ($emax < 18) {
                        foreach ($perEdat[$e] ?? [] as $x => $cand) {
                            if ($prob(.5)) {
                                $p = $cand;
                                unset($perEdat[$e][$x]);
                                break;
                            }
                        }
                        if (!$p) {
                            [, $fills] = $familia($prob(.4) ? [$e, $rnd(3, 14)] : [$e]);
                            $p = $fills[0];
                            foreach (array_slice($fills, 1) as $f) {
                                $perEdat[$edat($f)][] = $f;
                            }
                        }
                    } else {
                        $p = $persona($sexe(), $naixEdat($e, $e));
                    }
                    if (!isset($alumnes[$p])) {
                        q('INSERT INTO alumnes(persona_id, data_alta) VALUES(?,?)', [$p, $data(($a0 - 3) . '-09-01', "$a0-09-20")]);
                        $alumnes[$p] = ['id' => $id(), 'matricules' => []];
                        foreach ($families as [$tutors, $fills]) {
                            if (in_array($p, $fills, true)) {
                                foreach (array_filter($tutors) as $t => $tut) {
                                    q('INSERT INTO tutories(alumne_id, tutor_id, tipus_relacio, principal, rep_comunicacions) VALUES(?,?,?,?,?)',
                                        [$alumnes[$p]['id'], $tut, $persones[$tut][3] === 'D' ? 'mare' : 'pare', $t === 0 ? 1 : 0, $t === 0 || $prob(.6) ? 1 : 0]);
                                }
                                break;
                            }
                        }
                    }
                    if (!isset($alumnes[$p]['matricules'][$a0])) {
                        q('INSERT INTO matricules(alumne_id, curs_id, data) VALUES(?,?,?)', [$alumnes[$p]['id'], $cid, $data(($a0) . '-06-15', "$a0-09-20")]);
                        $alumnes[$p]['matricules'][$a0] = $id();
                    }
                    q('INSERT OR IGNORE INTO matricula_grups(matricula_id, grup_id) VALUES(?,?)', [$alumnes[$p]['matricules'][$a0], $g]);
                }
            }
        }
    }
    q("INSERT INTO tarifes(nom, tipus, periodicitat, import) VALUES(?, 'matricula', 'unica', ?)", [$tr('Inscripció', 'Inscripción'), $tipus === 'club_esportiu' ? 30 : 15]);
    // Alguns ho han deixat
    foreach (array_slice(array_keys($alumnes), 0, max(3, intdiv(count($alumnes), 25))) as $p) {
        q("UPDATE alumnes SET estat = 'baixa', data_baixa = ? WHERE persona_id = ?", [$abans($rnd(20, 200)), $p]);
    }
}

// ---------- Socis, fallers, comparsistes, famílies associades… ----------
$socis = [];
$fesSoci = function (int $p, string $codi, string $alta) use (&$socis, $tipusSoci) {
    if (isset($socis[$p])) {
        return;
    }
    q('INSERT INTO socis(persona_id, tipus, data_alta) VALUES(?,?,?)', [$p, $tipusSoci[$codi] ?? null, $alta]);
    $socis[$p] = true;
};
$quotaJuvenil = ($P['quota'][2] ?? 0) > 0 && $tipus !== 'ampa';
if ($tipus === 'ampa') {
    // Una persona sòcia per família
    foreach ($families as [$tutors]) {
        if ($prob(.88)) {
            $fesSoci($tutors[0], 'ordinari', $data(($C - 7) . '-09-01', "$A-10-15"));
        }
    }
} elseif ($festa) {
    // En una falla (o una comparsa) tota la família hi està apuntada: els adults i la xicalla
    while (count($socis) < $P['socis'] * .92) {
        if ($prob(.42)) {
            $n = $prob(.55) ? 1 : ($prob(.75) ? 2 : 3);
            [$tutors, $fills] = $familia(array_map(fn() => $rnd(0, 16), range(1, $n)));
            foreach (array_filter($tutors) as $t) {
                $fesSoci($t, 'ordinari', $data(($C - 20) . '-04-01', $iniciEx($EX)));
            }
            foreach ($fills as $f) {
                $fesSoci($f, $edat($f) < 18 ? 'juvenil' : 'ordinari', date('Y-m-d', max(strtotime($persones[$f][2]), strtotime($data(($C - 12) . '-04-01', $iniciEx($EX))))));
            }
        } else {
            $fesSoci($persona($sexe(), $naixEdat(18, 85)), 'ordinari', $data(($C - 30) . '-04-01', $iniciEx($EX)));
        }
    }
    foreach (array_keys($adultsActius + $menorsActius) as $p) {
        $fesSoci($p, $edat($p) < 18 ? 'juvenil' : 'ordinari', $data(($C - 15) . '-01-01', $iniciEx($EX)));
    }
} else {
    foreach (array_keys($adultsActius) as $p) {
        if ($prob(.9)) {
            $fesSoci($p, 'ordinari', $data(($C - 15) . '-01-01', $iniciEx($EX)));
        }
    }
    if ($quotaJuvenil) {
        foreach (array_keys($menorsActius) as $p) {
            if ($prob(.6)) {
                $fesSoci($p, 'juvenil', $data(($C - 6) . '-01-01', $iniciEx($EX)));
            }
        }
    }
    foreach ($families as [$tutors]) {
        if ($prob(.45)) {
            $fesSoci($tutors[0], $prob(.85) ? 'ordinari' : 'protector', $data(($C - 15) . '-01-01', $iniciEx($EX)));
        }
    }
}
while (count($socis) < $P['socis']) {
    $x = mt_rand(1, 100);
    $fesSoci($persona($sexe(), $naixEdat(20, 88)), $x <= 75 ? 'ordinari' : ($x <= 96 ? 'protector' : 'honorari'), $data(($C - 30) . '-01-01', $iniciEx($EX)));
}
$motiusBaixa = $ES ? ['Cambio de residencia', 'Lo ha pedido', 'Impago de cuotas', null] : ['Canvi de residència', 'Ho ha demanat', 'Impagament de quotes', null];
for ($k = 0; $k < max(8, intdiv($P['socis'], 14)); $k++) {
    q("INSERT INTO socis(persona_id, numero, tipus, data_alta, data_baixa, estat, observacions) VALUES(?,?,?,?,?, 'baixa', ?)",
        [$persona($sexe(), $naixEdat(25, 90)), null, $tipusSoci['ordinari'], $data(($C - 20) . '-01-01', ($C - 5) . '-01-01'), $data(($C - 4) . '-01-01', $abans(20)), $tria($motiusBaixa)]);
}
// Números per ordre d'alta, també els dels qui ja s'han donat de baixa (no es tornen a donar)
$k = 0;
foreach (q('SELECT id FROM socis ORDER BY data_alta, id')->fetchAll(PDO::FETCH_COLUMN) as $s) {
    q('UPDATE socis SET numero = ? WHERE id = ?', [(string)(++$k), $s]);
}

// ---------- Entitats federades (federacions) ----------
if ($tipus === 'federacio') {
    $tipusEnt = $ES ? ['Sociedad musical', 'Club deportivo', 'Asociación cultural', 'Falla', 'Comparsa', 'AMPA', 'Colla', 'Peña']
        : ['Societat musical', 'Club esportiu', 'Associació cultural', 'Falla', 'Comparsa', 'AMPA', 'Colla', 'Penya'];
    $nomsEnt = ['Unió Musical', 'Club Esportiu', 'Associació Cultural', 'Falla', 'Comparsa', 'AMPA', 'Colla', 'Penya'];
    $nomsEntEs = ['Unión Musical', 'Club Deportivo', 'Asociación Cultural', 'Falla', 'Comparsa', 'AMPA', 'Colla', 'Peña'];
    $llocsEnt = ['Vilanova', 'Benicompàs', 'l\'Alqueria', 'la Serra', 'el Pla', 'la Vall', 'Sant Joan', 'la Mar', 'el Raval', 'Montcabrer', 'la Font', 'les Eres', 'Sant Roc', 'la Plana', 'el Castell', 'les Moreres'];
    $poblesEnt = ['Vilanova de la Plana', 'Benicompàs', 'L\'Alqueria Nova', 'Castelló de la Plana', 'Vila-real', 'Borriana', 'Onda', 'Almassora', 'Nules', 'Benicàssim'];
    $vistos = [];
    for ($k = 0; $k < $P['federades']; $k++) {
        do {
            $t = $rnd(0, 7);
            $nomE = ($ES ? $nomsEntEs[$t] : $nomsEnt[$t]) . ' ' . $tria($llocsEnt);
        } while (isset($vistos[$nomE]));
        $vistos[$nomE] = true;
        $rep = $persona($sexe(), $naixEdat(30, 70));
        $baixa = $k >= $P['federades'] - 4;
        q('INSERT INTO entitats_federades(nom, numero, tipus, cif, poblacio, telefon, email, persona_id, data_alta, data_baixa, quota, estat) VALUES(?,?,?,?,?,?,?,?,?,?,?,?)', [
            $nomE, (string)($k + 1), $tipusEnt[$t], sprintf('G12%06d', 100000 + $k * 37), $tria($poblesEnt), '964 ' . $rnd(100, 999) . ' ' . $rnd(100, 999),
            'info' . ($k + 1) . '@exemple.org', $k % 17 === 5 ? null : $rep, $data('1995-01-01', ($C - 1) . '-12-31'), $baixa ? $abans($rnd(30, 400)) : null,
            [60, 90, 120, 150, 200][$k % 5], $baixa ? 'baixa' : 'actiu',
        ]);
    }
}

// ---------- Llicències federatives i revisions mèdiques (clubs) ----------
if ($te('llicencies') && !empty($P['llicencies'])) {
    $fed = $tr($P['llicencies'][0], $P['llicencies'][1]);
    $temporada = nom_exercici($EX);
    $n = 0;
    foreach ($membresDe as $a => $llista) {
        foreach ($llista as $clau => $p) {
            $n++;
            $estat = $n % 41 === 7 ? 'tramit' : 'actiu';
            $revisio = $n % 29 === 3 ? $abans($rnd(1, 40)) : ($n % 23 === 4 ? $despres($rnd(3, 25)) : date('Y-m-d', strtotime($iniciEx($EX + 1) . ' +' . $rnd(0, 60) . ' days')));
            q('INSERT INTO llicencies(persona_id, agrupacio_id, federacio, numero, temporada, data_alta, data_caducitat, revisio_medica, import, estat, observacions) VALUES(?,?,?,?,?,?,?,?,?,?,?)', [
                $p, $a, $fed, sprintf('%s-%06d', $clau === 'dir' ? 'T' : 'J', 120000 + $n * 13), $temporada, $data($iniciEx($EX), date('Y-m-d', strtotime($iniciEx($EX) . ' +45 days'))),
                date('Y-m-t', strtotime($iniciEx($EX + 1) . ' -1 month')), $revisio, $clau === 'dir' ? 35 : ($edat($p) < 18 ? 42 : 68), $estat,
                $clau === 'dir' ? $tr('Llicència d\'entrenador/a', 'Licencia de entrenador/a') : null,
            ]);
        }
    }
    // Les de la temporada passada, ja caducades
    foreach (array_slice(q("SELECT persona_id, agrupacio_id, numero, import FROM llicencies ORDER BY id")->fetchAll(), 0, (int)($n * .7)) as $l) {
        q("INSERT INTO llicencies(persona_id, agrupacio_id, federacio, numero, temporada, data_alta, data_caducitat, revisio_medica, import, estat) VALUES(?,?,?,?,?,?,?,?,?, 'baixa')", [
            $l['persona_id'], $l['agrupacio_id'], $fed, $l['numero'], nom_exercici($EX - 1), $iniciEx($EX - 1), date('Y-m-t', strtotime($iniciEx($EX) . ' -1 month')),
            date('Y-m-d', strtotime($iniciEx($EX - 1) . ' +20 days')), $l['import']]);
    }
}

// ---------- Material (instruments, porteries, gegants…), préstecs i manteniments ----------
if ($te('instruments') && !empty($P['material'])) {
    $mat = [];
    $n = 0;
    foreach ($P['material'] as [$ca, $es, $q, $marca]) {
        for ($k = 0; $k < $q; $k++) {
            $n++;
            $estat = $prob(.08) ? 'regular' : ($prob(.04) ? 'reparacio' : 'bo');
            q('INSERT INTO instruments(inventari, tipus, marca, estat, ubicacio, data_adquisicio, observacions) VALUES(?,?,?,?,?,?,?)', [
                sprintf('MAT-%03d', $n), $tr($ca, $es), $marca, $estat, $tr('Magatzem', 'Almacén'), $data('2005-01-01', $abans(30)), $estat === 'reparacio' ? $tr('Al taller', 'En el taller') : null]);
            $mat[] = $id();
        }
    }
    $candidats = array_keys($adultsActius + $menorsActius);
    foreach (array_slice($mat, 0, (int)(count($mat) * .3)) as $k => $ins) {
        $p = $tria($candidats);
        q('INSERT INTO cessions(instrument_id, persona_id, data_entrega, data_prevista, estat_entrega) VALUES(?,?,?,?,?)',
            [$ins, $p, $data($abans(300), $abans(5)), $k % 9 === 4 ? $abans($rnd(3, 30)) : $despres($rnd(30, 240)), $tr('Bo', 'Bueno')]);
    }
    for ($k = 0; $k < intdiv(count($mat), 3); $k++) {
        q('INSERT INTO manteniments(instrument_id, data, descripcio, cost) VALUES(?,?,?,?)', [$tria($mat), $data($abans(900), $abans(3)),
            $tria($ES ? ['Revisión', 'Reparación', 'Limpieza', 'Cambio de piezas'] : ['Revisió', 'Reparació', 'Neteja', 'Canvi de peces']), $rnd(10, 240)]);
    }
}

// ---------- Roba i material que es ven, es presta o es dona ----------
if ($te('uniformes') && !empty($P['uniformes'])) {
    $gent = array_keys($socis + $adultsActius + $menorsActius + array_fill_keys(array_keys($alumnes), true));
    foreach ($P['uniformes'] as [$ca, $es, $tp, $talles, $preu, $mode]) {
        q('INSERT INTO uniformes(nom, tipus, talles, preu) VALUES(?,?,?,?)', [$tr($ca, $es), $tp, $talles, $preu]);
        $u = $id();
        $tt = $talles ? array_map('trim', explode(',', $talles)) : [null];
        foreach ([$abans(400), $abans(50)] as $d) {
            foreach ($tt as $t) {
                q('INSERT INTO uniforme_compres(uniforme_id, data, talla, quantitat, preu_unitat, proveidor) VALUES(?,?,?,?,?,?)',
                    [$u, $d, $t, $t === null ? $rnd(30, 80) : $rnd(5, 20), round($preu * .55, 2), $tria([$tr('Brodats La Plana', 'Bordados La Plana'), $tr('Tèxtil Maestrat', 'Textil Maestrazgo')])]);
            }
        }
        $quants = (int)(count($gent) * ($mode === 'prestec' ? .25 : .35));
        foreach (array_slice($gent, 0, $quants * 3) as $k => $p) {
            if ($k % 3 !== $u % 3) {
                continue;
            }
            $t = null;
            if ($talles) {
                $e = $edat($p);
                $t = $e < 18 && in_array((string)min(14, max(3, $e - $e % 2)), $tt, true) ? (string)min(14, max(3, $e - $e % 2)) : $tria(array_values(array_filter($tt, fn($x) => !ctype_digit($x))) ?: $tt);
            }
            q('INSERT INTO uniforme_lliuraments(uniforme_id, persona_id, talla, data, tipus, import) VALUES(?,?,?,?,?,?)',
                [$u, $p, $t, $data($abans(380), $abans(2)), $mode, $mode === 'venda' ? $preu : null]);
        }
    }
}

// ---------- Càrrecs de festa (falles, gaiates, fogueres, comparses): els d'enguany i els dels sis exercicis anteriors ----------
if ($te('carrecs_festa') && !empty($info['carrecs'])) {
    $carrecs = $info['carrecs'][$ES ? 'es' : 'ca'];
    $adults = array_values(array_filter(array_keys($socis), fn($p) => $edat($p) >= 18 && $edat($p) <= 40));
    $infants = array_values(array_filter(array_keys($socis), fn($p) => $edat($p) >= 6 && $edat($p) <= 12));
    shuffle($adults);
    shuffle($infants);
    for ($e = $EX - 6; $e <= $EX; $e++) {
        $proclamacio = date('Y-m-d', strtotime($iniciEx($e) . ' +' . (int)(180 + ($e % 3) * 7) . ' days'));
        if ($proclamacio > $AVUI && $e === $EX) {
            $proclamacio = $abans($rnd(5, 40));
        }
        foreach ($carrecs as $k => $c) {
            $infantil = str_contains(mb_strtolower($c), 'infantil');
            $quants = str_contains(mb_strtolower($c), $tr('dama', 'dama')) ? 4 : 1;
            for ($q = 0; $q < $quants; $q++) {
                $p = $infantil ? array_pop($infants) : array_pop($adults);
                if ($p) {
                    q('INSERT INTO carrecs_festa(persona_id, carrec, exercici, data) VALUES(?,?,?,?)', [$p, $c, nom_exercici($e), $proclamacio]);
                }
            }
        }
    }
}

// ---------- Tarifes, domiciliacions i rebuts de quotes ----------
[$periodicitat, $quotaOrd, $quotaJuv] = $P['quota'];
q("INSERT INTO tarifes(nom, tipus, tipus_soci, periodicitat, import) VALUES(?, 'quota_soci', ?, ?, ?)", [$tr('Quota ordinària', 'Cuota ordinaria'), $tipusSoci['ordinari'], $periodicitat, $quotaOrd]);
if ($quotaJuv > 0) {
    q("INSERT INTO tarifes(nom, tipus, tipus_soci, periodicitat, import) VALUES(?, 'quota_soci', ?, ?, ?)", [$tr('Quota infantil i juvenil', 'Cuota infantil y juvenil'), $tipusSoci['juvenil'], $periodicitat, $quotaJuv]);
}
q("INSERT INTO tarifes(nom, tipus, tipus_soci, periodicitat, import) VALUES(?, 'quota_soci', ?, ?, ?)", [$tr('Quota protectora', 'Cuota protectora'), $tipusSoci['protector'], $periodicitat, $P['quota']['protector'] ?? max(5, round($quotaOrd * .6))]);
q("INSERT INTO tarifes(nom, tipus, tipus_soci, periodicitat, import) VALUES(?, 'quota_soci', ?, ?, 0)", [$tr('Soci d\'honor', 'Socio de honor'), $tipusSoci['honorari'], $periodicitat]);
// Domiciliacions: de qui paga (adults socis i famílies de l'alumnat)
$pagadors = [];
foreach (array_keys($socis) as $p) {
    $pagadors[$p] = true;
}
foreach ($families as [$tutors]) {
    $pagadors[$tutors[0]] = true;
}
foreach (array_keys($pagadors) as $p) {
    if ($edat($p) >= 18 && $prob(.68)) {
        q('INSERT INTO mandats(persona_id, iban, data_signatura) VALUES(?,?,?)', [$p, $iban(), $data(($C - 10) . '-01-01', $abans(10))]);
        $m = $id();
        q('UPDATE mandats SET referencia = ? WHERE id = ?', [referencia_mandat($m), $m]);
    }
}
if ($tipus === 'federacio') {
    foreach (q("SELECT DISTINCT persona_id FROM entitats_federades WHERE persona_id IS NOT NULL")->fetchAll(PDO::FETCH_COLUMN) as $p) {
        if ($prob(.7)) {
            q('INSERT INTO mandats(persona_id, iban, data_signatura) VALUES(?,?,?)', [$p, $iban(), $data(($C - 8) . '-01-01', $abans(10))]);
            $m = $id();
            q('UPDATE mandats SET referencia = ? WHERE id = ?', [referencia_mandat($m), $m]);
        }
    }
}
db()->commit();

$res = [];
foreach ([$EX - 1, $EX] as $any) {
    foreach (periodes_quotes($any) as $codi => [, $per, $ini]) {
        if ($per === $periodicitat && $ini <= $AVUI) {
            crea_rebuts(calcula_quotes($any, $codi, $res), date('Y-m-d', strtotime("$ini +4 days")));
        }
    }
    if ($tipus === 'federacio' && $iniciEx($any) <= $AVUI) {
        crea_rebuts(calcula_federades($any, $res), date('Y-m-d', strtotime($iniciEx($any) . ' +14 days')));
    }
}
if ($te('escola') && !empty($cursos)) {
    foreach ($cursos as $cid) {
        $cf = fila('SELECT * FROM cursos WHERE id = ?', [$cid]);
        crea_rebuts(calcula_escola($cf, 'MAT', $res), min($AVUI, date('Y-m-d', strtotime($cf['data_inici'] . ' -5 days'))));
        foreach (array_keys(periodes_escola($cf)) as $codi) {
            if ($codi[0] === 'M' && $codi !== 'MAT' && substr($codi, 1) . '-03' <= $AVUI) {
                crea_rebuts(calcula_escola($cf, $codi, $res), substr($codi, 1) . '-03');
            }
        }
    }
}

// ---------- Loteria de Nadal: la de l'any passat (tancada) i la d'enguany ----------
db()->beginTransaction();
if ($te('loteria') && !empty($P['loteria'])) {
    $L = (int)date('n', strtotime($AVUI)) >= 10 ? $C : $C - 1;
    $venedors = array_values(array_filter(array_keys($socis), fn($p) => $edat($p) >= 18));
    foreach ([[$L - 1, 'tancat', sprintf('%05d', $rnd(0, 99999))], [$L, 'actiu', sprintf('%05d, %05d', $rnd(0, 99999), $rnd(0, 99999))]] as [$any, $est, $nums]) {
        q('INSERT INTO loteries(nom, data_sorteig, numeros, preu, juga, participacions_talonari, estat, peu) VALUES(?,?,?,?,?,?,?,?)', [
            $tr('Loteria de Nadal ', 'Lotería de Navidad ') . $any, "$any-12-22", $nums, $festa ? 3 : 5, $festa ? 2.4 : 4, 50, $est,
            $tr('Caduca als tres mesos del sorteig.', 'Caduca a los tres meses del sorteo.')]);
        $lo = $id();
        shuffle($venedors);
        exemple_entregues_loteria($lo, array_slice($venedors, 0, $P['loteria']), $any, $any === $L - 1, $festa ? 3 : 5, $festa ? 2.4 : 4, $rnd, $prob, $tria, $abans, $AVUI);
    }
}

// ---------- Activitats amb inscripcions ----------
$activitatsCobrar = [];
$possibles = array_keys($socis);
foreach ($P['activitats'] as $k => [$ca, $es, $tp, $dies, $hora, $preu, $places, $prop]) {
    $d = $dies < 0 ? $abans(-$dies) : $despres($dies);
    $passada = $d < $AVUI;
    q('INSERT INTO activitats(nom, tipus, data, hora, lloc, preu, places, data_limit, estat, agrupacio_id) VALUES(?,?,?,?,?,?,?,?,?,?)', [
        $tr($ca, $es), $tp, $d, $hora, $tria([$tr('Local social', 'Local social'), $tr('Plaça Major', 'Plaza Mayor'), $tr('Centre cívic', 'Centro cívico')]), $preu, $places,
        $preu ? date('Y-m-d', strtotime("$d -5 days")) : null, $passada ? 'realitzada' : ($preu && $dies < 5 ? 'inscripcio_tancada' : 'inscripcio_oberta'),
        $tipus === 'club_esportiu' && $tp === 'competicio' && $agr ? $agr[array_key_first($agr)] : null]);
    $a = $id();
    shuffle($possibles);
    $quants = min((int)round(count($possibles) * $prop), $places ? (int)($places * .9) : 9999);
    if ($tipus === 'federacio') {
        $possibles = q('SELECT persona_id FROM entitats_federades WHERE persona_id IS NOT NULL')->fetchAll(PDO::FETCH_COLUMN);
        $quants = (int)(count($possibles) * $prop * $tria([.4, .6, .8]));
    }
    $ocupades = 0;
    foreach (array_slice($possibles, 0, $quants) as $j => $p) {
        $pl = $tp === 'apat' && $prob(.3) ? $rnd(2, 4) : 1;
        if ($places && $ocupades + $pl > $places) {
            break;
        }
        $ocupades += $pl;
        q('INSERT INTO activitat_inscripcions(activitat_id, persona_id, places, import, data) VALUES(?,?,?,?,?)',
            [$a, $p, $pl, $preu ? $preu * $pl : null, date('Y-m-d', strtotime("$d -" . $rnd(6, 40) . ' days'))]);
    }
    if ($preu) {
        $activitatsCobrar[] = [$a, $passada];
    }
}
db()->commit();
foreach ($activitatsCobrar as [$a, $passada]) {
    $r = [];
    $rebuts = calcula_activitat(fila('SELECT * FROM activitats WHERE id = ?', [$a]), $r);
    crea_rebuts($passada ? $rebuts : array_slice($rebuts, 0, (int)(count($rebuts) * .6)), min($AVUI, (string)valor('SELECT date(data, \'-7 days\') FROM activitats WHERE id = ?', [$a])));
    enllaca_rebuts_activitat($a);
}

// ---------- Estat dels rebuts: els d'abans d'este mes, cobrats; uns pocs retornats o pendents; remeses mensuals ----------
db()->beginTransaction();
$MES = substr($AVUI, 0, 7);
q("UPDATE rebuts SET estat = 'pagat', data_pagament = min(date(data_emissio, '+' || (id % 20 + 2) || ' days'), ?) WHERE data_emissio < ?", [$AVUI, "$MES-01"]);
foreach (q("SELECT id FROM rebuts WHERE data_emissio < ? AND forma_pagament = 'domiciliacio' ORDER BY id", ["$MES-01"])->fetchAll(PDO::FETCH_COLUMN) as $k => $r) {
    if ($k % 131 === 40) {
        q("UPDATE rebuts SET estat = 'retornat', data_pagament = NULL, observacions = ? WHERE id = ?", [$tr('Retornat pel banc', 'Devuelto por el banco'), $r]);
    }
}
foreach (q("SELECT id FROM rebuts WHERE data_emissio BETWEEN ? AND ? AND forma_pagament = 'efectiu' ORDER BY id", [date('Y-m-01', strtotime("$MES-01 -5 months")), date('Y-m-d', strtotime("$MES-01 -1 day"))])->fetchAll(PDO::FETCH_COLUMN) as $k => $r) {
    if ($k % 11 === 5) {
        q("UPDATE rebuts SET estat = 'per_cobrar', data_pagament = NULL WHERE id = ?", [$r]);
    }
}
q("UPDATE rebuts SET forma_pagament = 'transferencia' WHERE forma_pagament = 'efectiu' AND id % 9 = 0");
foreach (q("SELECT DISTINCT substr(data_emissio, 1, 7) FROM rebuts WHERE forma_pagament = 'domiciliacio' ORDER BY 1")->fetchAll(PDO::FETCH_COLUMN) as $mes) {
    $preparada = $mes === $MES;
    q('INSERT INTO remeses(referencia, data_creacio, data_cobrament, estat) VALUES(?,?,?,?)', ['tmp-' . $mes, $mes . '-06 10:00:00', $preparada ? $despres(8) : $mes . '-10', $preparada ? 'preparada' : 'cobrada']);
    $rid = $id();
    q('UPDATE remeses SET referencia = ? WHERE id = ?', [sprintf('REM-%s06-%03d', str_replace('-', '', $mes), $rid), $rid]);
    q("UPDATE rebuts SET remesa_id = ? WHERE forma_pagament = 'domiciliacio' AND substr(data_emissio, 1, 7) = ?", [$rid, $mes]);
}
q("UPDATE mandats SET ultim_us = (SELECT MAX(r.data_emissio) FROM rebuts r WHERE r.mandat_id = mandats.id AND r.data_emissio < ?)", ["$MES-01"]);

// ---------- Junta, reunions, documents i comunicats ----------
$juntaAdults = array_values(array_filter(array_keys($socis), fn($p) => $edat($p) >= 30 && $edat($p) <= 70));
shuffle($juntaAdults);
$vocal = fn(string $ca, string $es) => $tr('Vocal ', 'Vocal ') . $tr($ca, $es);
$vocals = [
    'club_esportiu' => [['de futbol base', 'de fútbol base'], ['de material', 'de material'], ['del primer equip', 'del primer equipo']],
    'falla' => [['de festejos', 'de festejos'], ['d\'infantils', 'de infantiles'], ['de loteria', 'de lotería'], ['de casal', 'de casal']],
    'gaiata' => [['de festejos', 'de festejos'], ['d\'infantils', 'de infantiles'], ['de la gaiata', 'de la gaiata']],
    'foguera' => [['de festejos', 'de festejos'], ['d\'infantils', 'de infantiles'], ['de racó', 'de racó']],
    'comparsa' => [['d\'esquadres', 'de escuadras'], ['de vestuari', 'de vestuario'], ['de música', 'de música']],
    'ampa' => [['de menjador', 'de comedor'], ['d\'extraescolars', 'de extraescolares'], ['de festes', 'de fiestas']],
][$tipus] ?? [['d\'activitats', 'de actividades'], ['de comunicació', 'de comunicación'], ['de material', 'de material']];
$elegida = ($C - 2) . '-06-14';
foreach (array_merge([['presidencia', null], ['vicepresidencia', null], ['secretaria', null], ['tresoreria', null]], array_map(fn($v) => ['vocal', $vocal($v[0], $v[1])], $vocals)) as $k => [$c, $desc]) {
    q('INSERT INTO carrecs(persona_id, carrec, descripcio, data_inici) VALUES(?,?,?,?)', [$juntaAdults[$k], $c, $desc, $elegida]);
}
foreach ([['presidencia', null], ['secretaria', null], ['tresoreria', null]] as $k => [$c, $desc]) {
    q('INSERT INTO carrecs(persona_id, carrec, descripcio, data_inici, data_fi) VALUES(?,?,?,?,?)', [$juntaAdults[12 + $k], $c, $desc, ($C - 6) . '-06-12', $elegida]);
}
$nomP = fn(int $p) => $persones[$p][0] . ' ' . $persones[$p][1];
if ($configura || (string)config('certificat_signant') === '') {
    desa_config('certificat_signant', $nomP($juntaAdults[2]));
    desa_config('certificat_president', $nomP($juntaAdults[0]));
}
$acta = $tr("Lectura i aprovació, si escau, de l'acta anterior\n", "Lectura y aprobación, si procede, del acta anterior\n");
$precs = $tr('Precs i preguntes', 'Ruegos y preguntas');
$ordres = $ES
    ? ["Calendario de actividades\nEstado de cuentas\n", "Cuotas del próximo ejercicio\nLotería de Navidad\n", "Preparación de las fiestas\nCompras de material\n", "Subvenciones\nComunicación con los socios\n"]
    : ["Calendari d'activitats\nEstat de comptes\n", "Quotes del pròxim exercici\nLoteria de Nadal\n", "Preparació de les festes\nCompres de material\n", "Subvencions\nComunicació amb els socis\n"];
$acords = $ES
    ? ['Aprobar el calendario del trimestre.', 'Mantener las cuotas del ejercicio.', 'Comprar el material que falta.', 'Convocar la asamblea general.']
    : ['Aprovar el calendari del trimestre.', 'Mantindre les quotes de l\'exercici.', 'Comprar el material que falta.', 'Convocar l\'assemblea general.'];
$local = $tr('Local social', 'Local social');
for ($k = 10; $k >= 0; $k--) {
    $d = date('Y-m-d', strtotime('thursday', strtotime("$AVUI -" . (30 + $k * 49) . ' days')));
    q("INSERT INTO reunions(tipus, data, hora, lloc, data_convocatoria, ordre_del_dia, assistents, desenvolupament, acords, hora_fi, estat) VALUES('junta',?,?,?,?,?,?,?,?,?, 'celebrada')", [
        $d, '20:30', $local, date('Y-m-d', strtotime("$d -7 days")), $acta . $ordres[$k % 4] . $precs, $tr('Assistixen ', 'Asisten ') . $rnd(5, 8) . $tr(' membres de la junta.', ' miembros de la junta.'),
        $tr('Es tracten els punts de l\'ordre del dia i s\'obri un torn de paraula.', 'Se tratan los puntos del orden del día y se abre un turno de palabra.'),
        $tr("Aprovar l'acta anterior.\n", "Aprobar el acta anterior.\n") . $acords[$k % 4], '22:10']);
}
$anyAssemblea = (int)date('n', strtotime($AVUI)) >= 4 ? $C : $C - 1;
foreach ([($anyAssemblea - 1) . '-03-26', $anyAssemblea . '-03-25'] as $d) {
    q("INSERT INTO reunions(tipus, data, hora, hora_segona, lloc, data_convocatoria, ordre_del_dia, assistents, desenvolupament, acords, hora_fi, estat) VALUES('assemblea_ordinaria',?,?,?,?,?,?,?,?,?,?, 'celebrada')", [
        $d, '19:30', '20:00', $local, date('Y-m-d', strtotime("$d -15 days")),
        $acta . $tr("Memòria d'activitats\nEstat de comptes de l'exercici anterior\nPressupost de l'exercici\n", "Memoria de actividades\nEstado de cuentas del ejercicio anterior\nPresupuesto del ejercicio\n") . $precs,
        $rnd(40, 90) . $tr(' persones sòcies presents i ', ' personas socias presentes y ') . $rnd(5, 20) . $tr(' representades.', ' representadas.'),
        $tr('La tresoreria presenta els comptes i el pressupost.', 'La tesorería presenta las cuentas y el presupuesto.'),
        $tr("Aprovar l'acta anterior.\nAprovar els comptes.\nAprovar el pressupost.", "Aprobar el acta anterior.\nAprobar las cuentas.\nAprobar el presupuesto."), '21:30']);
}
q("INSERT INTO reunions(tipus, data, hora, lloc, data_convocatoria, ordre_del_dia, estat) VALUES('junta', ?, '20:30', ?, ?, ?, 'convocada')", [$despres(6), $local, $abans(1), $acta . $ordres[0] . $precs]);
foreach ([
    [$tr('Estatuts de l\'entitat', 'Estatutos de la entidad'), 'estatuts', '2011-04-20', null], [$tr('Reglament de règim intern', 'Reglamento de régimen interno'), 'reglament', ($C - 6) . '-03-15', null],
    [$tr('Inscripció al Registre d\'Associacions', 'Inscripción en el Registro de Asociaciones'), 'registre', '1996-06-10', null],
    [$tr('Assegurança de responsabilitat civil', 'Seguro de responsabilidad civil'), 'asseguranca', $abans(341), $despres(24)],
    [$tr('Conveni amb l\'ajuntament', 'Convenio con el ayuntamiento'), 'conveni', "$C-01-15", "$C-12-31"],
    [$tr('Certificat d\'estar al corrent amb Hisenda', 'Certificado de estar al corriente con Hacienda'), 'fiscal', $abans(160), $despres(20)],
    [$tr('Model 182 (donatius) ', 'Modelo 182 (donativos) ') . ($C - 2), 'fiscal', ($C - 1) . '-01-28', null],
] as [$t, $tp, $d, $cad]) {
    q('INSERT INTO documents(titol, tipus, data_document, data_caducitat) VALUES(?,?,?,?)', [$t, $tp, $d, $cad]);
    $doc = $id();
    $text = "$t\n\n" . $tr('Document de prova.', 'Documento de prueba.');
    q('INSERT INTO document_fitxers(document_id, contingut) VALUES(?,?)', [$doc, $text]);
    q("UPDATE documents SET fitxer_nom = ?, fitxer_tipus = 'text/plain', fitxer_mida = ? WHERE id = ?", [strtolower(preg_replace('/\W+/', '-', iconv('UTF-8', 'ASCII//TRANSLIT', $t))) . '.txt', strlen($text), $doc]);
}
$nSocis = count($socis);
foreach ([[300, $tr('Socis i sòcies', 'Socios y socias'), $nSocis, $tr('Calendari de l\'exercici', 'Calendario del ejercicio')],
             [220, $tr('Socis i sòcies', 'Socios y socias'), $nSocis, $tr('Convocatòria de l\'assemblea general', 'Convocatoria de la asamblea general')],
             [150, $tr('Socis i sòcies', 'Socios y socias'), $nSocis, $tr('Loteria de Nadal: talonaris', 'Lotería de Navidad: talonarios')],
             [60, $tr('Socis i sòcies', 'Socios y socias'), $nSocis, $tr('Quotes del nou exercici', 'Cuotas del nuevo ejercicio')],
             [25, $tr('Socis i sòcies', 'Socios y socias'), $nSocis, $tr('Inscripcions obertes', 'Inscripciones abiertas')],
             [4, $tr('Socis i sòcies', 'Socios y socias'), $nSocis, $tr('Recordatori de la pròxima activitat', 'Recordatorio de la próxima actividad')]] as [$d, $dest, $nd, $as]) {
    q('INSERT INTO comunicats(data, destinataris, n_destinataris, assumpte, usuari) VALUES(?,?,?,?,?)', [$abans($d), $dest, $nd, $as, 'admin']);
}
foreach ($agr as $g => $a) {
    if ($g < 3) {
        q('INSERT INTO comunicats(data, destinataris, n_destinataris, assumpte, usuari) VALUES(?,?,?,?,?)', [$abans(10 + $g * 17), (string)valor('SELECT nom FROM agrupacions WHERE id = ?', [$a]),
            count($membresDe[$a] ?? []), $tr('Horari i convocatòria', 'Horario y convocatoria'), 'admin']);
    }
}

// ---------- Llibre de caixa ----------
$escala = max(1, $nSocis / 200);
foreach ([$C - 1, $C] as $any) {
    $movs = [];
    for ($m = 1; $m <= ($any === $C ? (int)date('n', strtotime($AVUI)) : 12); $m++) {
        $movs[] = [sprintf('%d-%02d-05', $any, $m), 'despesa', 'banc', 'subministraments', $tr('Llum i aigua del local', 'Luz y agua del local'), $rnd(90, 320)];
        $movs[] = [sprintf('%d-%02d-12', $any, $m), 'despesa', 'banc', 'comissions', $tr('Comissions bancàries', 'Comisiones bancarias'), $rnd(6, 30)];
        if ($prob(.6)) {
            $movs[] = [sprintf('%d-%02d-%02d', $any, $m, $rnd(1, 27)), 'despesa', 'caixa', 'material',
                $tria($ES ? ['Material de oficina', 'Material para las actividades', 'Reparaciones del local', 'Fotocopias'] : ['Material d\'oficina', 'Material per a les activitats', 'Reparacions del local', 'Fotocòpies']), $rnd(20, 240)];
        }
        if ($prob(.35)) {
            $movs[] = [sprintf('%d-%02d-%02d', $any, $m, $rnd(1, 27)), 'ingres', 'caixa', 'donatius', $tr('Donatius', 'Donativos'), $rnd(30, 400)];
        }
    }
    $movs[] = ["$any-02-15", 'despesa', 'banc', 'federacio', $tr('Quota anual de la federació', 'Cuota anual de la federación'), (int)(180 * $escala)];
    $movs[] = ["$any-03-01", 'despesa', 'banc', 'assegurances', $tr('Assegurança de responsabilitat civil', 'Seguro de responsabilidad civil'), (int)(280 * $escala)];
    if ($festa) {
        $movs[] = ["$any-01-20", 'despesa', 'banc', 'material', $tr('Primer pagament a l\'artista', 'Primer pago al artista'), (int)(6000 * $escala)];
        $movs[] = ["$any-02-25", 'despesa', 'banc', 'actuacions', $tr('Banda de música per a les festes', 'Banda de música para las fiestas'), (int)(2800 * $escala)];
        $movs[] = ["$any-03-10", 'despesa', 'banc', 'material', $tr('Pagament final a l\'artista', 'Pago final al artista'), (int)(9000 * $escala)];
        $movs[] = ["$any-03-12", 'despesa', 'banc', 'material', $tr('Il·luminació i pirotècnia', 'Iluminación y pirotecnia'), (int)(3500 * $escala)];
    }
    if ($tipus === 'club_esportiu') {
        $movs[] = ["$any-09-20", 'despesa', 'banc', 'federacio', $tr('Fitxes i arbitratges', 'Fichas y arbitrajes'), (int)(5200 * $escala)];
        $movs[] = ["$any-10-15", 'ingres', 'banc', 'altres_moviments', $tr('Patrocini de la temporada', 'Patrocinio de la temporada'), 3000];
    }
    $movs[] = ["$any-06-30", 'ingres', 'banc', 'altres_moviments', $tr('Patrocini de comerços del poble', 'Patrocinio de comercios del pueblo'), $rnd(600, 2400)];
    foreach ($movs as $mv) {
        if ($mv[0] <= $AVUI) {
            q('INSERT INTO moviments(data, tipus, compte, categoria, concepte, import) VALUES(?,?,?,?,?,?)', $mv);
        }
    }
}
$proveidors = ['subministraments' => [$tr('Companyia Elèctrica del Mediterrani', 'Compañía Eléctrica del Mediterráneo'), 'A46000001'],
    'material' => [$tr('Subministraments La Plana', 'Suministros La Plana'), 'B12000011'], 'federacio' => [$tr('Federació', 'Federación'), 'G46000012'],
    'assegurances' => [$tr('Assegurances La Plana', 'Seguros La Plana'), 'A12000003'], 'actuacions' => [$tr('Unió Musical de Benicompàs', 'Unión Musical de Benicompàs'), 'G12000014']];
foreach (q("SELECT id, categoria FROM moviments WHERE tipus = 'despesa' ORDER BY id")->fetchAll() as $k => $m) {
    if (isset($proveidors[$m['categoria']])) {
        q('UPDATE moviments SET proveidor = ?, nif = ?, justificant = ? WHERE id = ?', [$proveidors[$m['categoria']][0], $proveidors[$m['categoria']][1], sprintf('F-%04d', 100 + $k), $m['id']]);
    }
}
desa_config('caixa_saldo_' . ($C - 1) . '_banc', (string)(int)(6200 * $escala));
desa_config('caixa_saldo_' . ($C - 1) . '_caixa', '380');
desa_config('caixa_saldo_' . $C . '_banc', (string)round(7450.6 * $escala, 2));
desa_config('caixa_saldo_' . $C . '_caixa', '415.30');

// ---------- Subvencions ----------
if ($te('subvencions')) {
    $org = fn(int $ordre) => (int)valor('SELECT id FROM organismes WHERE ordre = ?', [$ordre]);
    $subvencio = function (array $c, int $fets, array $cobraments) use ($id, $abans): int {
        q('INSERT INTO subvencions(' . implode(', ', array_keys($c)) . ') VALUES(' . implode(', ', array_fill(0, count($c), '?')) . ')', array_values($c));
        $sid = $id();
        q('INSERT INTO subvencio_requisits(subvencio_id, requisit_id) SELECT ?, requisit_id FROM organisme_requisits WHERE organisme_id = ?', [$sid, $c['organisme_id']]);
        foreach (array_slice(q('SELECT requisit_id FROM subvencio_requisits WHERE subvencio_id = ? ORDER BY requisit_id', [$sid])->fetchAll(PDO::FETCH_COLUMN), 0, $fets) as $r) {
            q('UPDATE subvencio_requisits SET fet = 1, data_fet = ? WHERE subvencio_id = ? AND requisit_id = ?', [$c['data_justificacio'] ?? $abans(7), $sid, $r]);
        }
        foreach ($cobraments as [$d, $import, $concepte]) {
            q("INSERT INTO subvencio_cobraments(subvencio_id, data, import, compte, concepte) VALUES(?, ?, ?, 'banc', ?)", [$sid, $d, $import, $concepte]);
        }
        return $sid;
    };
    foreach ([$C - 1, $C] as $any) {
        $tancat = $any === $C - 1;
        $imp = (int)(2500 * $escala);
        $subvencio(['nom' => $tr('Conveni amb l\'ajuntament', 'Convenio con el ayuntamiento'), 'organisme_id' => $org(30), 'exercici' => $any, 'expedient' => "CONV-$any-21",
            'objecte' => $tr('Activitats de l\'any', 'Actividades del año'), 'estat' => $tancat ? 'tancada' : 'concedida', 'data_resolucio' => "$any-02-10",
            'data_limit_justificacio' => $tancat ? "$any-12-15" : $despres(30), 'data_justificacio' => $tancat ? "$any-12-05" : null, 'import_concedit' => $imp, 'import_justificar' => $imp],
            $tancat ? 4 : 2, array_filter([["$any-06-30", $imp / 2, $tr('Bestreta', 'Anticipo')], $tancat ? ["$any-12-28", $imp / 2, $tr('Pagament final', 'Pago final')] : null], fn($x) => $x && $x[0] <= $AVUI));
    }
    $subvencio(['nom' => $tr('Ajudes a entitats', 'Ayudas a entidades'), 'organisme_id' => $org(20), 'exercici' => $C, 'expedient' => "SUBV/$C/0388",
        'objecte' => $tr('Equipament i activitats', 'Equipamiento y actividades'), 'estat' => 'sollicitada', 'data_sollicitud' => $abans(70),
        'import_pressupost' => (int)(6000 * $escala), 'import_sollicitat' => (int)(3000 * $escala)], 3, []);
    $subvencio(['nom' => $tr('Ajudes de la Generalitat', 'Ayudas de la Generalitat'), 'organisme_id' => $org(10), 'exercici' => $C + 1,
        'objecte' => $tr('Programa d\'activitats', 'Programa de actividades'), 'estat' => 'prevista', 'data_limit_sollicitud' => $despres(14),
        'import_pressupost' => (int)(8000 * $escala), 'import_sollicitat' => (int)(4000 * $escala)], 0, []);
}
db()->commit();
