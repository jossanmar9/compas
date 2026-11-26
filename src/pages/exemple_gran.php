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
/**
 * Loteria de l'entitat gran: cada persona s'endú un talonari, dos o uns paperets solts (amb els números seguits), a la caixa se'n
 * venen uns quants, i els talonaris fets són els repartits més uns quants de reserva. Els dècims quadren amb el que juguen els paperets.
 * Del sorteig passat està tot liquidat i pagat a l'administració; del d'enguany, només una part.
 */
function exemple_entregues_loteria(int $lo, array $persones, int $any, bool $passat, float $preu, float $juga, callable $rnd, callable $prob, callable $tria, callable $abans, string $avui): void
{
    $forma = fn(string $codi) => valor('SELECT id FROM formes_pagament WHERE codi = ?', [$codi]);
    $formes = [$forma('efectiu'), $forma('efectiu'), $forma('efectiu'), $forma('bizum'), $forma('bizum'), $forma('transferencia')];
    $seg = 1;
    $data = fn() => date('Y-m-d', min(strtotime("$any-10-" . sprintf('%02d', $rnd(1, 20))), strtotime($avui)));
    $entrega = function (?int $p, int $n) use ($lo, $any, $passat, $preu, $rnd, $prob, $tria, $abans, $formes, $data, &$seg): void {
        $quan = $data();
        $liquidada = $passat || $prob(.18);
        $tor = $liquidada ? ($p === null ? 0 : $rnd(0, (int)floor($n * .4))) : null;
        $pagat = $liquidada ? ($n - $tor) * $preu - ($prob(.06) ? $preu * $rnd(1, 3) : 0) : null;
        q('INSERT INTO loteria_entregues(loteria_id, persona_id, data_entrega, participacions, des_de, fins_a, tornades, import_pagat, forma_pagament_id, data_pagament) VALUES(?,?,?,?,?,?,?,?,?,?)',
            [$lo, $p, $quan, $n, $seg, $seg + $n - 1, $tor, $pagat, $liquidada ? $tria($formes) : null,
                $liquidada ? ($passat ? "$any-12-" . sprintf('%02d', $rnd(1, 19)) : max($quan, $abans(1))) : null]);
        $seg += $n;
    };
    foreach ($persones as $p) {
        $entrega((int)$p, $tria([50, 50, 50, 50, 100, 100, 25, 20, 10]));
    }
    $entrega(null, 30); // venuts a la caixa de l'entitat
    $talonaris = (int)ceil(($seg - 1) / 50) + 6;
    $decims = (int)round($talonaris * 50 * $juga / 20);
    q('UPDATE loteries SET talonaris = ?, decims = ?, preu_decim = 20, pagat_administracio = ?, data_pagament_administracio = ?, forma_pagament_administracio = ? WHERE id = ?',
        [$talonaris, $decims, $passat ? $decims * 20 : null, $passat ? "$any-12-20" : null, $passat ? valor("SELECT id FROM formes_pagament WHERE codi = 'transferencia'") : null, $lo]);
}

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
