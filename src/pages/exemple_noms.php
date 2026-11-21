<?php
// Noms, cognoms i carrers inventats per a les dades d'exemple (les xicotetes i les de l'entitat gran)
// Copyright (C) 2026 José Vicente Sanjuan
// SPDX-License-Identifier: AGPL-3.0-or-later

/** ['h' => noms d'home, 'd' => noms de dona, 'cognoms' => cognoms, 'carrers' => carrers en l'idioma de la instal·lació] */
function noms_exemple(bool $es): array
{
    $nomsH = ['Pau', 'Vicent', 'Joan', 'Josep', 'Pere', 'Jordi', 'Àlex', 'Marc', 'Hèctor', 'Andreu', 'Toni', 'Xavier', 'Salvador', 'Rafael', 'Enric', 'Lluís', 'Miquel',
        'Ferran', 'Biel', 'Nil', 'Arnau', 'Pol', 'Iker', 'Hugo', 'Leo', 'Martí', 'Adrià', 'Òscar', 'Carles', 'David', 'Sergi', 'Raül', 'Gerard', 'Ximo', 'Batiste',
        'Quique', 'Manel', 'Àngel', 'Ricard', 'Roger', 'Eloi', 'Aleix', 'Bernat', 'Guillem', 'Jaume', 'Nacho', 'Daniel', 'Mateu', 'Lucas', 'Izan', 'Bruno'];
    $nomsD = ['Maria', 'Laia', 'Júlia', 'Marta', 'Anna', 'Clara', 'Paula', 'Carla', 'Neus', 'Amparo', 'Empar', 'Rosa', 'Teresa', 'Vicenta', 'Mireia', 'Aitana', 'Noa',
        'Lucía', 'Martina', 'Sara', 'Irene', 'Elena', 'Alba', 'Aina', 'Nerea', 'Carmen', 'Pilar', 'Isabel', 'Sílvia', 'Núria', 'Montse', 'Eva', 'Lorena', 'Inma',
        'Cristina', 'Raquel', 'Patrícia', 'Ainhoa', 'Valèria', 'Èlia', 'Maialen', 'Berta', 'Gemma', 'Lidia', 'Andrea', 'Sofia', 'Vega', 'Ona', 'Abril', 'Mar', 'Lola'];
    $cognomsL = ['Garcia', 'Martínez', 'Ferrer', 'Sanchis', 'Soler', 'Navarro', 'Peris', 'Vidal', 'Climent', 'Monfort', 'Ribes', 'Pastor', 'Bosch', 'Llorens', 'Gil',
        'Domènech', 'Moliner', 'Tormo', 'Castelló', 'Planells', 'Esteve', 'Mompó', 'Bellver', 'Gimeno', 'Roig', 'Segarra', 'Albiol', 'Arnau', 'Cabedo', 'Beltran',
        'Vicent', 'Marqués', 'Cervera', 'Puig', 'Salvador', 'Folch', 'Fabregat', 'Agost', 'Pitarch', 'Carda', 'Nebot', 'Bort', 'Museros', 'Alcaide', 'Huguet',
        'Badenes', 'Escrig', 'Font', 'Grau', 'Ivars', 'Juan', 'López', 'Mas', 'Orts', 'Pérez', 'Querol', 'Ramos', 'Sorribes', 'Torres', 'Usó', 'Vilar', 'Sales',
        'Mezquita', 'Porcar', 'Ripollés', 'Safont', 'Tena', 'Aparici', 'Bou', 'Castell', 'Fuster', 'Guinot', 'Lluch', 'Martí', 'Ortells', 'Ros'];
    $carrers = $es
        ? ['Calle Mayor', 'Calle de San Vicente', 'Plaza de la Música', 'Avenida del País Valenciano', 'Calle del Mar', 'Calle de los Músicos', 'Calle Nueva',
            'Calle de la Iglesia', 'Calle del Sol', 'Avenida de la Constitución', 'Calle de las Eras', 'Calle del Arrabal', 'Calle de San José', 'Calle de la Fuente']
        : ['Carrer Major', 'Carrer de Sant Vicent', 'Plaça de la Música', 'Avinguda del País Valencià', 'Carrer de la Mar', 'Carrer dels Músics', 'Carrer Nou',
            'Carrer de l\'Església', 'Carrer del Sol', 'Avinguda de la Constitució', 'Carrer de les Eres', 'Carrer del Raval', 'Carrer de Sant Josep', 'Carrer de la Font'];

    return ['h' => $nomsH, 'd' => $nomsD, 'cognoms' => $cognomsL, 'carrers' => $carrers];
}
