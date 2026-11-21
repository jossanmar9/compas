<?php
// Dades d'exemple d'una entitat gran (centenars de persones i milers de rebuts), per a provar Compàs amb un volum realista.
// La societat musical està en src/exemple/societat_musical.php; la resta de tipus, en src/exemple/associacio.php.
// Copyright (C) 2026 José Vicente Sanjuan
// SPDX-License-Identifier: AGPL-3.0-or-later

/**
 * Omple una instal·lació nova (sense persones) amb una entitat gran del tipus configurat.
 * $avui: la data de «hui» de les dades (per defecte, la d'avui; les captures de la web en fan servir una de fixa).
 * $configura: posa també el nom, el CIF i l'adreça de l'entitat inventada (només per a les captures).
 * Sempre ix el mateix per a una mateixa data: la llavor de l'atzar és fixa.
 */
function carrega_exemple_gran(?string $avui = null, bool $configura = false): void
{
    if ((int)valor('SELECT COUNT(*) FROM persones') > 0) {
        throw new RuntimeException('Les dades d\'exemple grans només es poden carregar en una instal·lació buida.');
    }
    @set_time_limit(0);
    mt_srand(2026);
    $AVUI = $avui ?? date('Y-m-d');
    $tipus = tipus_entitat();
    // S'executa dins d'esta funció: el fitxer veu $AVUI, $configura i $tipus
    require SRC_DIR . '/exemple/' . ($tipus === 'societat_musical' ? 'societat_musical' : 'associacio') . '.php';
}
