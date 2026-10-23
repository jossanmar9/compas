<?php
// Vocabulari segons el tipus d'entitat: els textos base són els d'una societat musical; ací es canvien els noms
// (socis → fallers i falleres, alumnat → esportistes, agrupacions → equips…) per a la resta de tipus.
// Cada tipus tria uns perfils (escola, grups, socis, material i roba) i les plantilles fan els textos.
// Copyright (C) 2026 José Vicente Sanjuan
// SPDX-License-Identifier: AGPL-3.0-or-later

/** Textos que canvien per al tipus d'entitat i l'idioma (buit per a una societat musical) */
function vocabulari(string $tipus, string $l): array
{
    $info = TIPUS_ENTITAT[$tipus] ?? null;
    if (!$info || $tipus === 'societat_musical') {
        return [];
    }
    $v = voc_general($l);
    foreach (['escola' => 'voc_escola', 'grups' => 'voc_grups', 'socis' => 'voc_socis', 'material' => 'voc_material', 'roba' => 'voc_roba'] as $clau => $fn) {
        $perfil = $info['vocabulari'][$clau] ?? null;
        if ($perfil) {
            $v = array_merge($v, $fn($perfil, $l));
        }
    }
    $nom = $info['nom_exemple'][$l] ?? null;
    if ($nom) {
        $v['setup.exemple_nom'] = $nom;
    }
    return $v;
}

/** Aplica les ranures {…} d'un perfil a unes plantilles */
function voc_plantilles(array $plantilles, array $r): array
{
    $claus = [];
    foreach ($r as $k => $x) {
        $claus['{' . $k . '}'] = $x;
    }
    return array_map(fn($p) => strtr($p, $claus), $plantilles);
}

/** Textos que no són d'una societat musical, per a tots els altres tipus */
function voc_general(string $l): array
{
    return $l === 'es' ? [
        'llist.cert_nota' => 'Los certificados se imprimen desde las fichas (botón «Certificado»), y la hoja de consentimiento desde la ficha de la persona.',
        'inf.memoria_text' => 'Datos para la memoria y las subvenciones: personas por edad, socios, grupos…',
        'consent.text' => 'que se hagan fotografías y vídeos en que aparezca durante las actividades, actos y salidas de la entidad, y que se publiquen en la página web, las redes sociales, la revista, los carteles y otros medios de comunicación propios de la entidad, siempre sin finalidad comercial.',
        'economia.nota_factures' => 'Compàs hace recibos, no facturas. Si la entidad tiene que hacer facturas, las tiene que hacer con un programa de facturación que cumpla la normativa.',
        'ajuda.carrec_descripcio' => 'Opcional. Por ejemplo, «Vocal de actividades» o «Vocal de patrimonio».',
        'ajuda.document_persona' => 'Para los contratos y otros documentos de una persona (monitores, personal…).',
        'opt.personal' => 'Personal (monitores, entrenadores…)',
        'ajuda.subvencio_nom' => 'Por ejemplo: «Ayudas a entidades» o «Convenio con el ayuntamiento».',
        'ajuda.subvencio_objecte' => 'Las actividades del año, un acto, la compra de material…',
        'ajuda.tarifa_nom' => 'Cómo aparecerá en el recibo. Por ejemplo: «Cuota ordinaria» o «Cuota infantil».',
        'setup.exemple_ajuda' => 'Personas, cuotas y actividades de prueba para conocer el programa. Se pueden borrar después o volver a empezar eliminando la carpeta de datos.',
    ] : [
        'llist.cert_nota' => 'Els certificats s\'imprimeixen des de les fitxes (botó «Certificat»), i el full de consentiment des de la fitxa de la persona.',
        'inf.memoria_text' => 'Dades per a la memòria i les subvencions: persones per edat, socis, grups…',
        'consent.text' => 'que es facen fotografies i vídeos en què aparega durant les activitats, actes i eixides de l\'entitat, i que es publiquen en la pàgina web, les xarxes socials, la revista, els cartells i altres mitjans de comunicació propis de l\'entitat, sempre sense finalitat comercial.',
        'economia.nota_factures' => 'Compàs fa rebuts, no factures. Si l\'entitat ha de fer factures, les ha de fer amb un programa de facturació que complisca la normativa.',
        'ajuda.carrec_descripcio' => 'Opcional. Per exemple, «Vocal d\'activitats» o «Vocal de patrimoni».',
        'ajuda.document_persona' => 'Per als contractes i altres documents d\'una persona (monitors, personal…).',
        'opt.personal' => 'Personal (monitors, entrenadors…)',
        'ajuda.subvencio_nom' => 'Per exemple: «Ajudes a entitats» o «Conveni amb l\'ajuntament».',
        'ajuda.subvencio_objecte' => 'Les activitats de l\'any, un acte, la compra de material…',
        'ajuda.tarifa_nom' => 'Com apareixerà al rebut. Per exemple: «Quota ordinària» o «Quota infantil».',
        'setup.exemple_ajuda' => 'Persones, quotes i activitats de prova per a conéixer el programa. Es poden esborrar després o tornar a començar eliminant la carpeta de dades.',
    ];
}

/* ---------- Escola: escola esportiva, activitats extraescolars, tallers… ---------- */

function voc_escola(string $perfil, string $l): array
{
    $p = [
        'ca' => [
            'esportiva' => ['Escola' => 'Escola esportiva', 'escola_lc' => 'l\'escola esportiva', 'Alumnes' => 'Esportistes', 'Alumne' => 'Esportista', 'alumnes_lc' => 'esportistes',
                'alumne_lc' => 'esportista', 'tot_alumnat' => 'Tots els esportistes', 'dels_alumnes' => 'dels esportistes', 'Professors' => 'Entrenadors', 'Professor' => 'Entrenador/a',
                'professors_lc' => 'entrenadors', 'tot_professorat' => 'Tots els entrenadors', 'Assignatures' => 'Esports', 'Assignatura' => 'Esport', 'les_assignatures' => 'els esports',
                'assignatura_lc' => 'esport', 'Cursos' => 'Temporades', 'Curs' => 'Temporada', 'curs_lc' => 'temporada', 'el_curs' => 'la temporada', 'del_curs' => 'de la temporada',
                'al_curs' => 'a la temporada', 'Matricules' => 'Inscripcions', 'Matricula' => 'Inscripció', 'inscrits' => 'Inscrits', 'Aules' => 'Instal·lacions', 'Aula' => 'Instal·lació',
                'Horari' => 'Horari d\'entrenaments', 'Grups' => 'Grups d\'entrenament', 'Grup' => 'Grup d\'entrenament', 'Classes' => 'Entrenaments', 'classes_lc' => 'entrenaments'],
            'extraescolars' => ['Escola' => 'Activitats extraescolars', 'escola_lc' => 'les activitats extraescolars', 'Alumnes' => 'Alumnat', 'Alumne' => 'Alumne', 'alumnes_lc' => 'alumnes',
                'alumne_lc' => 'alumne', 'tot_alumnat' => 'Tot l\'alumnat', 'dels_alumnes' => 'de l\'alumnat', 'Professors' => 'Monitors', 'Professor' => 'Monitor/a', 'professors_lc' => 'monitors',
                'tot_professorat' => 'Tots els monitors', 'Assignatures' => 'Activitats', 'Assignatura' => 'Activitat', 'les_assignatures' => 'les activitats', 'assignatura_lc' => 'activitat',
                'Cursos' => 'Cursos escolars', 'Curs' => 'Curs escolar', 'curs_lc' => 'curs', 'el_curs' => 'el curs', 'del_curs' => 'del curs', 'al_curs' => 'al curs',
                'Matricules' => 'Inscripcions', 'Matricula' => 'Inscripció', 'inscrits' => 'Inscrits', 'Aules' => 'Espais', 'Aula' => 'Espai', 'Horari' => 'Horari', 'Grups' => 'Grups', 'Grup' => 'Grup', 'Classes' => 'Activitats', 'classes_lc' => 'activitats'],
            'tallers' => ['Escola' => 'Tallers i cursos', 'escola_lc' => 'els tallers i cursos', 'Alumnes' => 'Participants', 'Alumne' => 'Participant', 'alumnes_lc' => 'participants',
                'alumne_lc' => 'participant', 'tot_alumnat' => 'Tots els participants', 'dels_alumnes' => 'dels participants', 'Professors' => 'Monitors', 'Professor' => 'Monitor/a',
                'professors_lc' => 'monitors', 'tot_professorat' => 'Tots els monitors', 'Assignatures' => 'Tallers', 'Assignatura' => 'Taller', 'les_assignatures' => 'els tallers',
                'assignatura_lc' => 'taller', 'Cursos' => 'Temporades', 'Curs' => 'Temporada', 'curs_lc' => 'temporada', 'el_curs' => 'la temporada', 'del_curs' => 'de la temporada',
                'al_curs' => 'a la temporada', 'Matricules' => 'Inscripcions', 'Matricula' => 'Inscripció', 'inscrits' => 'Inscrits', 'Aules' => 'Espais', 'Aula' => 'Espai',
                'Horari' => 'Horari', 'Grups' => 'Grups', 'Grup' => 'Grup', 'Classes' => 'Sessions', 'classes_lc' => 'sessions'],
            'colla' => ['Escola' => 'Escola de la colla', 'escola_lc' => 'l\'escola de la colla', 'Alumnes' => 'Alumnat', 'Alumne' => 'Alumne', 'alumnes_lc' => 'alumnes', 'alumne_lc' => 'alumne',
                'tot_alumnat' => 'Tot l\'alumnat', 'dels_alumnes' => 'de l\'alumnat', 'Professors' => 'Professorat', 'Professor' => 'Professor', 'professors_lc' => 'professors',
                'tot_professorat' => 'Tot el professorat', 'Assignatures' => 'Assignatures', 'Assignatura' => 'Assignatura', 'les_assignatures' => 'les assignatures', 'assignatura_lc' => 'assignatura',
                'Cursos' => 'Cursos', 'Curs' => 'Curs', 'curs_lc' => 'curs', 'el_curs' => 'el curs', 'del_curs' => 'del curs', 'al_curs' => 'al curs', 'Matricules' => 'Matrícules',
                'Matricula' => 'Matrícula', 'inscrits' => 'Matriculats', 'Aules' => 'Aules', 'Aula' => 'Aula', 'Horari' => 'Horari', 'Grups' => 'Grups', 'Grup' => 'Grup', 'Classes' => 'Classes', 'classes_lc' => 'classes'],
        ],
        'es' => [
            'esportiva' => ['Escola' => 'Escuela deportiva', 'escola_lc' => 'la escuela deportiva', 'Alumnes' => 'Deportistas', 'Alumne' => 'Deportista', 'alumnes_lc' => 'deportistas',
                'alumne_lc' => 'deportista', 'tot_alumnat' => 'Todos los deportistas', 'dels_alumnes' => 'de los deportistas', 'Professors' => 'Entrenadores', 'Professor' => 'Entrenador/a',
                'professors_lc' => 'entrenadores', 'tot_professorat' => 'Todos los entrenadores', 'Assignatures' => 'Deportes', 'Assignatura' => 'Deporte', 'les_assignatures' => 'los deportes',
                'assignatura_lc' => 'deporte', 'Cursos' => 'Temporadas', 'Curs' => 'Temporada', 'curs_lc' => 'temporada', 'el_curs' => 'la temporada', 'del_curs' => 'de la temporada',
                'al_curs' => 'a la temporada', 'Matricules' => 'Inscripciones', 'Matricula' => 'Inscripción', 'inscrits' => 'Inscritos', 'Aules' => 'Instalaciones', 'Aula' => 'Instalación',
                'Horari' => 'Horario de entrenamientos', 'Grups' => 'Grupos de entrenamiento', 'Grup' => 'Grupo de entrenamiento', 'Classes' => 'Entrenamientos', 'classes_lc' => 'entrenamientos'],
            'extraescolars' => ['Escola' => 'Actividades extraescolares', 'escola_lc' => 'las actividades extraescolares', 'Alumnes' => 'Alumnado', 'Alumne' => 'Alumno', 'alumnes_lc' => 'alumnos',
                'alumne_lc' => 'alumno', 'tot_alumnat' => 'Todo el alumnado', 'dels_alumnes' => 'del alumnado', 'Professors' => 'Monitores', 'Professor' => 'Monitor/a', 'professors_lc' => 'monitores',
                'tot_professorat' => 'Todos los monitores', 'Assignatures' => 'Actividades', 'Assignatura' => 'Actividad', 'les_assignatures' => 'las actividades', 'assignatura_lc' => 'actividad',
                'Cursos' => 'Cursos escolares', 'Curs' => 'Curso escolar', 'curs_lc' => 'curso', 'el_curs' => 'el curso', 'del_curs' => 'del curso', 'al_curs' => 'al curso',
                'Matricules' => 'Inscripciones', 'Matricula' => 'Inscripción', 'inscrits' => 'Inscritos', 'Aules' => 'Espacios', 'Aula' => 'Espacio', 'Horari' => 'Horario', 'Grups' => 'Grupos', 'Grup' => 'Grupo', 'Classes' => 'Actividades', 'classes_lc' => 'actividades'],
            'tallers' => ['Escola' => 'Talleres y cursos', 'escola_lc' => 'los talleres y cursos', 'Alumnes' => 'Participantes', 'Alumne' => 'Participante', 'alumnes_lc' => 'participantes',
                'alumne_lc' => 'participante', 'tot_alumnat' => 'Todos los participantes', 'dels_alumnes' => 'de los participantes', 'Professors' => 'Monitores', 'Professor' => 'Monitor/a',
                'professors_lc' => 'monitores', 'tot_professorat' => 'Todos los monitores', 'Assignatures' => 'Talleres', 'Assignatura' => 'Taller', 'les_assignatures' => 'los talleres',
                'assignatura_lc' => 'taller', 'Cursos' => 'Temporadas', 'Curs' => 'Temporada', 'curs_lc' => 'temporada', 'el_curs' => 'la temporada', 'del_curs' => 'de la temporada',
                'al_curs' => 'a la temporada', 'Matricules' => 'Inscripciones', 'Matricula' => 'Inscripción', 'inscrits' => 'Inscritos', 'Aules' => 'Espacios', 'Aula' => 'Espacio',
                'Horari' => 'Horario', 'Grups' => 'Grupos', 'Grup' => 'Grupo', 'Classes' => 'Sesiones', 'classes_lc' => 'sesiones'],
            'colla' => ['Escola' => 'Escuela de la colla', 'escola_lc' => 'la escuela de la colla', 'Alumnes' => 'Alumnado', 'Alumne' => 'Alumno', 'alumnes_lc' => 'alumnos', 'alumne_lc' => 'alumno',
                'tot_alumnat' => 'Todo el alumnado', 'dels_alumnes' => 'del alumnado', 'Professors' => 'Profesorado', 'Professor' => 'Profesor', 'professors_lc' => 'profesores',
                'tot_professorat' => 'Todo el profesorado', 'Assignatures' => 'Asignaturas', 'Assignatura' => 'Asignatura', 'les_assignatures' => 'las asignaturas', 'assignatura_lc' => 'asignatura',
                'Cursos' => 'Cursos', 'Curs' => 'Curso', 'curs_lc' => 'curso', 'el_curs' => 'el curso', 'del_curs' => 'del curso', 'al_curs' => 'al curso', 'Matricules' => 'Matrículas',
                'Matricula' => 'Matrícula', 'inscrits' => 'Matriculados', 'Aules' => 'Aulas', 'Aula' => 'Aula', 'Horari' => 'Horario', 'Grups' => 'Grupos', 'Grup' => 'Grupo', 'Classes' => 'Clases', 'classes_lc' => 'clases'],
        ],
    ][$l][$perfil] ?? null;
    if (!$p) {
        return [];
    }
    $t = $l === 'es' ? [
        'menu.escola' => '{Escola}', 'modul.escola' => '{Escola}', 'genera.escola' => '{Escola}', 'mem.escola' => '{Escola}', 'opt.escola' => '{Escola}', 'rgpd.act.escola' => '{Escola}',
        'modul_ajuda.escola' => '{Alumnes}, {professors_lc}, inscripciones, grupos y horario.',
        'rebut.concepte_escola' => '{Escola} — %s', 'rebut.concepte_matricula' => '{Matricula} — {curs_lc} %s',
        'ent.alumnes' => '{Alumnes}', 'ent1.alumnes' => '{Alumne}', 'camp.alumne_id' => '{Alumne}', 'mem.alumnes' => '{Alumnes}',
        'ent.professors' => '{Professors}', 'ent1.professors' => '{Professor}', 'camp.professor_id' => '{Professor}', 'com.professorat' => '{Professors}',
        'filtre.tot_professorat' => '{tot_professorat}', 'mem.professors' => '{Professors} con grupos', 'rgpd.act.professorat' => '{Professors} y personal',
        'rgpd.professorat.interessats' => '{Professors}, dirección y personal.',
        'inici.sense_certificat' => '{Professors} en activo sin el certificado de delitos de naturaleza sexual: %d. Es obligatorio para trabajar con menores.',
        'avis_cfg.certificat_delictes' => '{Professors} sin el certificado de delitos sexuales',
        'ent.cursos' => '{Cursos}', 'ent1.cursos' => '{Curs}', 'camp.curs_id' => '{Curs}', 'camp.actual' => '{Curs} actual', 'resum.titol' => '{Curs} %s',
        'inici.sense_curs' => 'Todavía no hay ningún{a} {curs_lc}.', 'avis_cfg.curs_actual' => '{Escola}: no hay ningún{a} {curs_lc} actual',
        'escola.sense_grups' => 'No hay grupos activos en {el_curs} actual.', 'mem.subtitol' => 'Edades calculadas a %s (inicio {del_curs}).',
        'ent.assignatures' => '{Assignatures}', 'ent1.assignatures' => '{Assignatura}', 'camp.assignatura_id' => '{Assignatura}', 'mem.per_assignatura' => '{Alumnes} por {assignatura_lc}',
        'cert.assignatures' => 'en {les_assignatures} siguientes:',
        'ent.matricules' => '{Matricules}', 'ent1.matricules' => '{Matricula}', 'camp.matricula_id' => '{Matricula}', 'opt.matricula' => '{Matricula}', 'per.matricula' => '{Matricula} %s',
        'inici.matriculats' => '{inscrits} %s', 'ent.aules' => '{Aules}', 'ent1.aules' => '{Aula}', 'camp.aula_id' => '{Aula}', 'menu.horari' => '{Horari}', 'ent.horaris' => '{Horari}',
        'ent.grups' => '{Grups}', 'ent1.grups' => '{Grup}', 'camp.grup_id' => '{Grup}',
        'mem.alumnes_edat' => '{Alumnes} por edad (total: %d)', 'mem.per_poblacio' => '{Alumnes} por población', 'llist.tot_alumnat' => '{tot_alumnat}',
        'inf.alumnes_grup' => '{Alumnes} de un grupo', 'inf.alumnes_grup_text' => 'Con la edad, los tutores y sus teléfonos. Para los {professors_lc}.',
        'etiq.qui_tutors' => 'Familias {dels_alumnes} (tutores que reciben comunicaciones)', 'com.families' => 'Familias {dels_alumnes} (tutores y {alumnes_lc} mayores de edad)',
        'economia.config_descomptes' => 'Descuentos de {escola_lc}', 'filtre.imatge_pendent' => '{Alumnes} sin consentimiento de imagen firmado',
        'cert.matricula_actiu' => 'está inscrito/a en {escola_lc} de esta entidad en {el_curs} %s', 'cert.matricula_baixa' => 'ha estado inscrito/a en {escola_lc} de esta entidad en {el_curs} %s',
        'genera.escola_ajuda' => 'Un recibo por familia, con la inscripción y las cuotas de cada {alumne_lc} y los descuentos.',
        'passa.boto' => 'Pasar {al_curs} siguiente', 'passa.titol' => 'Pasar de {curs_lc}', 'audit.passa_curs' => '{Curs} nuevo',
        'imp.que_alumnes' => 'personas, la ficha de {alumne_lc} y, si los hay, el padre, la madre o el tutor/a', 'imp.res_rols.alumnes' => 'Fichas de {alumne_lc} nuevas',
        'imp.av_ja_alumne' => 'Ya está en {escola_lc}: solo se completarán los datos.',
        'inici.classes_hui' => '{Classes} de hoy (%s)', 'inici.sense_classes' => 'Hoy no hay {classes_lc}.',
    ] : [
        'menu.escola' => '{Escola}', 'modul.escola' => '{Escola}', 'genera.escola' => '{Escola}', 'mem.escola' => '{Escola}', 'opt.escola' => '{Escola}', 'rgpd.act.escola' => '{Escola}',
        'modul_ajuda.escola' => '{Alumnes}, {professors_lc}, inscripcions, grups i horari.',
        'rebut.concepte_escola' => '{Escola} — %s', 'rebut.concepte_matricula' => '{Matricula} — {curs_lc} %s',
        'ent.alumnes' => '{Alumnes}', 'ent1.alumnes' => '{Alumne}', 'camp.alumne_id' => '{Alumne}', 'mem.alumnes' => '{Alumnes}',
        'ent.professors' => '{Professors}', 'ent1.professors' => '{Professor}', 'camp.professor_id' => '{Professor}', 'com.professorat' => '{Professors}',
        'filtre.tot_professorat' => '{tot_professorat}', 'mem.professors' => '{Professors} amb grups', 'rgpd.act.professorat' => '{Professors} i personal',
        'rgpd.professorat.interessats' => '{Professors}, direcció i personal.',
        'inici.sense_certificat' => '{Professors} en actiu sense el certificat de delictes de naturalesa sexual: %d. És obligatori per a treballar amb menors.',
        'avis_cfg.certificat_delictes' => '{Professors} sense el certificat de delictes sexuals',
        'ent.cursos' => '{Cursos}', 'ent1.cursos' => '{Curs}', 'camp.curs_id' => '{Curs}', 'camp.actual' => '{Curs} actual', 'resum.titol' => '{Curs} %s',
        'inici.sense_curs' => 'Encara no hi ha cap {curs_lc}.', 'avis_cfg.curs_actual' => '{Escola}: no hi ha cap {curs_lc} actual',
        'escola.sense_grups' => 'No hi ha grups actius en {el_curs} actual.', 'mem.subtitol' => 'Edats calculades a %s (inici {del_curs}).',
        'ent.assignatures' => '{Assignatures}', 'ent1.assignatures' => '{Assignatura}', 'camp.assignatura_id' => '{Assignatura}', 'mem.per_assignatura' => '{Alumnes} per {assignatura_lc}',
        'cert.assignatures' => 'en {les_assignatures} següents:',
        'ent.matricules' => '{Matricules}', 'ent1.matricules' => '{Matricula}', 'camp.matricula_id' => '{Matricula}', 'opt.matricula' => '{Matricula}', 'per.matricula' => '{Matricula} %s',
        'inici.matriculats' => '{inscrits} %s', 'ent.aules' => '{Aules}', 'ent1.aules' => '{Aula}', 'camp.aula_id' => '{Aula}', 'menu.horari' => '{Horari}', 'ent.horaris' => '{Horari}',
        'ent.grups' => '{Grups}', 'ent1.grups' => '{Grup}', 'camp.grup_id' => '{Grup}',
        'mem.alumnes_edat' => '{Alumnes} per edat (total: %d)', 'mem.per_poblacio' => '{Alumnes} per població', 'llist.tot_alumnat' => '{tot_alumnat}',
        'inf.alumnes_grup' => '{Alumnes} d\'un grup', 'inf.alumnes_grup_text' => 'Amb l\'edat, els tutors i els seus telèfons. Per als {professors_lc}.',
        'etiq.qui_tutors' => 'Famílies {dels_alumnes} (tutors que reben comunicacions)', 'com.families' => 'Famílies {dels_alumnes} (tutors i {alumnes_lc} majors d\'edat)',
        'economia.config_descomptes' => 'Descomptes de {escola_lc}', 'filtre.imatge_pendent' => '{Alumnes} sense consentiment d\'imatge signat',
        'cert.matricula_actiu' => 'està inscrit/a en {escola_lc} d\'esta entitat en {el_curs} %s', 'cert.matricula_baixa' => 'ha estat inscrit/a en {escola_lc} d\'esta entitat en {el_curs} %s',
        'genera.escola_ajuda' => 'Un rebut per família, amb la inscripció i les quotes de cada {alumne_lc} i els descomptes.',
        'passa.boto' => 'Passa {al_curs} següent', 'passa.titol' => 'Passar de {curs_lc}', 'audit.passa_curs' => '{Curs} nou',
        'imp.que_alumnes' => 'persones, la fitxa de {alumne_lc} i, si n\'hi ha, el pare, la mare o el tutor/a', 'imp.res_rols.alumnes' => 'Fitxes de {alumne_lc} noves',
        'imp.av_ja_alumne' => 'Ja està en {escola_lc}: només es completaran les dades.',
        'inici.classes_hui' => '{Classes} de hui (%s)', 'inici.sense_classes' => 'Hui no hi ha {classes_lc}.',
    ];
    // En castellà, «ningún curso» però «ninguna temporada»
    $p['a'] = $l === 'es' && in_array($p['curs_lc'], ['temporada'], true) ? 'a' : '';
    return voc_plantilles($t, $p);
}

/* ---------- Grups (agrupacions): equips, seccions, esquadres, comissions… ---------- */

function voc_grups(string $perfil, string $l): array
{
    $p = [
        'ca' => [
            'equips' => ['Grups' => 'Equips', 'Grup' => 'Equip', 'grup_lc' => 'equip', 'un_grup' => 'un equip', 'del_grup' => 'de l\'equip', 'els_grups' => 'els equips', 'dels_grups' => 'dels equips',
                'Membres' => 'Jugadors i membres', 'Membres_curt' => 'Jugadors', 'membres_lc' => 'jugadors', 'Membre' => 'Jugador/a', 'Funcio' => 'Posició o dorsal', 'funcio_lc' => 'posició',
                'sense_funcio' => 'Sense posició indicada', 'la_funcio' => 'la posició', 'Director' => 'Entrenador/a principal', 'Assistent' => 'Segon/a entrenador/a', 'Responsable' => 'Delegat/da',
                'ajuda' => 'Equips, jugadors, entrenadors i delegats.'],
            'seccions' => ['Grups' => 'Seccions', 'Grup' => 'Secció', 'grup_lc' => 'secció', 'un_grup' => 'una secció', 'del_grup' => 'de la secció', 'els_grups' => 'les seccions',
                'dels_grups' => 'de les seccions', 'Membres' => 'Membres de les seccions', 'Membres_curt' => 'Membres', 'membres_lc' => 'membres', 'Membre' => 'Membre',
                'Funcio' => 'Funció (instrument, figura…)', 'funcio_lc' => 'funció', 'sense_funcio' => 'Sense funció indicada', 'la_funcio' => 'la funció', 'Director' => 'Cap de secció',
                'Assistent' => 'Sotscap', 'Responsable' => 'Responsable', 'ajuda' => 'Les seccions de l\'entitat i els seus membres.'],
            'grups' => ['Grups' => 'Grups i seccions', 'Grup' => 'Grup', 'grup_lc' => 'grup', 'un_grup' => 'un grup', 'del_grup' => 'del grup', 'els_grups' => 'els grups', 'dels_grups' => 'dels grups',
                'Membres' => 'Membres dels grups', 'Membres_curt' => 'Membres', 'membres_lc' => 'membres', 'Membre' => 'Membre', 'Funcio' => 'Funció o paper', 'funcio_lc' => 'funció',
                'sense_funcio' => 'Sense funció indicada', 'la_funcio' => 'la funció', 'Director' => 'Director/a', 'Assistent' => 'Director/a adjunt/a', 'Responsable' => 'Responsable',
                'ajuda' => 'Teatre, dansa, cor… i els seus membres.'],
            'comissions' => ['Grups' => 'Comissions', 'Grup' => 'Comissió', 'grup_lc' => 'comissió', 'un_grup' => 'una comissió', 'del_grup' => 'de la comissió', 'els_grups' => 'les comissions',
                'dels_grups' => 'de les comissions', 'Membres' => 'Membres de les comissions', 'Membres_curt' => 'Membres', 'membres_lc' => 'membres', 'Membre' => 'Membre', 'Funcio' => 'Tasca',
                'funcio_lc' => 'tasca', 'sense_funcio' => 'Sense tasca indicada', 'la_funcio' => 'la tasca', 'Director' => 'Coordinador/a', 'Assistent' => 'Coordinador/a adjunt/a',
                'Responsable' => 'Responsable', 'ajuda' => 'Les comissions de treball i qui en forma part.'],
            'esquadres' => ['Grups' => 'Esquadres', 'Grup' => 'Esquadra', 'grup_lc' => 'esquadra', 'un_grup' => 'una esquadra', 'del_grup' => 'de l\'esquadra', 'els_grups' => 'les esquadres',
                'dels_grups' => 'de les esquadres', 'Membres' => 'Membres de les esquadres', 'Membres_curt' => 'Membres', 'membres_lc' => 'membres', 'Membre' => 'Membre',
                'Funcio' => 'Lloc a l\'esquadra', 'funcio_lc' => 'lloc', 'sense_funcio' => 'Sense lloc indicat', 'la_funcio' => 'el lloc', 'Director' => 'Cap d\'esquadra',
                'Assistent' => 'Segon/a cap', 'Responsable' => 'Responsable', 'ajuda' => 'Les esquadres de la comparsa i qui desfila en cadascuna.'],
        ],
        'es' => [
            'equips' => ['Grups' => 'Equipos', 'Grup' => 'Equipo', 'grup_lc' => 'equipo', 'un_grup' => 'un equipo', 'del_grup' => 'del equipo', 'els_grups' => 'los equipos', 'dels_grups' => 'de los equipos',
                'Membres' => 'Jugadores y miembros', 'Membres_curt' => 'Jugadores', 'membres_lc' => 'jugadores', 'Membre' => 'Jugador/a', 'Funcio' => 'Posición o dorsal', 'funcio_lc' => 'posición',
                'sense_funcio' => 'Sin posición indicada', 'la_funcio' => 'la posición', 'Director' => 'Entrenador/a principal', 'Assistent' => 'Segundo/a entrenador/a', 'Responsable' => 'Delegado/a',
                'ajuda' => 'Equipos, jugadores, entrenadores y delegados.'],
            'seccions' => ['Grups' => 'Secciones', 'Grup' => 'Sección', 'grup_lc' => 'sección', 'un_grup' => 'una sección', 'del_grup' => 'de la sección', 'els_grups' => 'las secciones',
                'dels_grups' => 'de las secciones', 'Membres' => 'Miembros de las secciones', 'Membres_curt' => 'Miembros', 'membres_lc' => 'miembros', 'Membre' => 'Miembro',
                'Funcio' => 'Función (instrumento, figura…)', 'funcio_lc' => 'función', 'sense_funcio' => 'Sin función indicada', 'la_funcio' => 'la función', 'Director' => 'Jefe/a de sección',
                'Assistent' => 'Subjefe/a', 'Responsable' => 'Responsable', 'ajuda' => 'Las secciones de la entidad y sus miembros.'],
            'grups' => ['Grups' => 'Grupos y secciones', 'Grup' => 'Grupo', 'grup_lc' => 'grupo', 'un_grup' => 'un grupo', 'del_grup' => 'del grupo', 'els_grups' => 'los grupos', 'dels_grups' => 'de los grupos',
                'Membres' => 'Miembros de los grupos', 'Membres_curt' => 'Miembros', 'membres_lc' => 'miembros', 'Membre' => 'Miembro', 'Funcio' => 'Función o papel', 'funcio_lc' => 'función',
                'sense_funcio' => 'Sin función indicada', 'la_funcio' => 'la función', 'Director' => 'Director/a', 'Assistent' => 'Director/a adjunto/a', 'Responsable' => 'Responsable',
                'ajuda' => 'Teatro, danza, coro… y sus miembros.'],
            'comissions' => ['Grups' => 'Comisiones', 'Grup' => 'Comisión', 'grup_lc' => 'comisión', 'un_grup' => 'una comisión', 'del_grup' => 'de la comisión', 'els_grups' => 'las comisiones',
                'dels_grups' => 'de las comisiones', 'Membres' => 'Miembros de las comisiones', 'Membres_curt' => 'Miembros', 'membres_lc' => 'miembros', 'Membre' => 'Miembro', 'Funcio' => 'Tarea',
                'funcio_lc' => 'tarea', 'sense_funcio' => 'Sin tarea indicada', 'la_funcio' => 'la tarea', 'Director' => 'Coordinador/a', 'Assistent' => 'Coordinador/a adjunto/a',
                'Responsable' => 'Responsable', 'ajuda' => 'Las comisiones de trabajo y quién forma parte de ellas.'],
            'esquadres' => ['Grups' => 'Escuadras', 'Grup' => 'Escuadra', 'grup_lc' => 'escuadra', 'un_grup' => 'una escuadra', 'del_grup' => 'de la escuadra', 'els_grups' => 'las escuadras',
                'dels_grups' => 'de las escuadras', 'Membres' => 'Miembros de las escuadras', 'Membres_curt' => 'Miembros', 'membres_lc' => 'miembros', 'Membre' => 'Miembro',
                'Funcio' => 'Puesto en la escuadra', 'funcio_lc' => 'puesto', 'sense_funcio' => 'Sin puesto indicado', 'la_funcio' => 'el puesto', 'Director' => 'Cabo de escuadra',
                'Assistent' => 'Segundo/a cabo', 'Responsable' => 'Responsable', 'ajuda' => 'Las escuadras de la comparsa y quién desfila en cada una.'],
        ],
    ][$l][$perfil] ?? null;
    if (!$p) {
        return [];
    }
    $t = $l === 'es' ? [
        'menu.agrupacions' => '{Grups}', 'modul.agrupacions' => '{Grups}', 'ent.agrupacions' => '{Grups}', 'ent1.agrupacions' => '{Grup}', 'camp.agrupacio_id' => '{Grup}',
        'camp.agrupacions' => '{Grups}', 'mem.agrupacions' => '{Grups}', 'modul_ajuda.agrupacions' => '{ajuda}', 'ent.membres' => '{Membres}', 'ent1.membres' => '{Membre}',
        'inici.musics' => '{Membres} en activo', 'camp.instrument_veu' => '{Funcio}', 'opt.director_titular' => '{Director}', 'opt.director_assistent' => '{Assistent}',
        'opt.responsable' => '{Responsable}', 'llist.music' => '{Membre}', 'llist.n_musics' => '%d {membres_lc}', 'llist.sense_instrument' => '{sense_funcio}',
        'llist.tria_agrupacio' => 'Elige {un_grup}', 'inf.musics' => '{Membres_curt} por {funcio_lc}', 'inf.musics_text' => 'Los {membres_lc} {del_grup} agrupados por {funcio_lc}.',
        'inf.antiguitat_text' => 'Socios y {membres_lc} que cumplen 25 o 50 años (o los que quieras).', 'antig.musics_fita' => '{Membres_curt} que cumplen %d años en %d',
        'antig.tots_musics' => 'Todos los {membres_lc} por antigüedad', 'mem.musics' => '{Membres_curt}', 'etiq.qui_musics' => '{Membres_curt} {dels_grups}',
        'antig.nota' => 'La antigüedad se cuenta desde la fecha de alta, sin descontar los periodos de baja. Para los {membres_lc}, desde la primera alta en cualquier {grup_lc}.',
        'cert.amb_instrument' => ', con {la_funcio} %s', 'cert.membre_actiu' => 'es miembro {del_grup} «%s» de esta entidad desde el %s%s.',
        'cert.membre_baixa' => 'ha sido miembro {del_grup} «%s» de esta entidad desde el %s hasta el %s%s.',
        'rgpd.act.agrupacions' => '{Grups}', 'rgpd.agrupacions.finalitat' => 'Organizar {els_grups}, sus miembros y la función de cada uno.',
        'rgpd.agrupacions.interessats' => '{Membres}.', 'rgpd.agrupacions.dades' => 'Identificación y contacto, {grup_lc}, {funcio_lc} y fechas.',
        'rep.veure_agrupacio' => 'Obras de {un_grup}', 'rep.sense_agrupacio' => 'Sin {grup_lc}',
    ] : [
        'menu.agrupacions' => '{Grups}', 'modul.agrupacions' => '{Grups}', 'ent.agrupacions' => '{Grups}', 'ent1.agrupacions' => '{Grup}', 'camp.agrupacio_id' => '{Grup}',
        'camp.agrupacions' => '{Grups}', 'mem.agrupacions' => '{Grups}', 'modul_ajuda.agrupacions' => '{ajuda}', 'ent.membres' => '{Membres}', 'ent1.membres' => '{Membre}',
        'inici.musics' => '{Membres} en actiu', 'camp.instrument_veu' => '{Funcio}', 'opt.director_titular' => '{Director}', 'opt.director_assistent' => '{Assistent}',
        'opt.responsable' => '{Responsable}', 'llist.music' => '{Membre}', 'llist.n_musics' => '%d {membres_lc}', 'llist.sense_instrument' => '{sense_funcio}',
        'llist.tria_agrupacio' => 'Tria {un_grup}', 'inf.musics' => '{Membres_curt} per {funcio_lc}', 'inf.musics_text' => 'Els {membres_lc} {del_grup} agrupats per {funcio_lc}.',
        'inf.antiguitat_text' => 'Socis i {membres_lc} que complixen 25 o 50 anys (o els que vulgues).', 'antig.musics_fita' => '{Membres_curt} que fan %d anys en %d',
        'antig.tots_musics' => 'Tots els {membres_lc} per antiguitat', 'mem.musics' => '{Membres_curt}', 'etiq.qui_musics' => '{Membres_curt} {dels_grups}',
        'antig.nota' => 'L\'antiguitat es compta des de la data d\'alta, sense descomptar els períodes de baixa. Per als {membres_lc}, des de la primera alta en qualsevol {grup_lc}.',
        'cert.amb_instrument' => ', amb {la_funcio} %s', 'cert.membre_actiu' => 'és membre {del_grup} «%s» d\'esta entitat des del %s%s.',
        'cert.membre_baixa' => 'ha sigut membre {del_grup} «%s» d\'esta entitat des del %s fins al %s%s.',
        'rgpd.act.agrupacions' => '{Grups}', 'rgpd.agrupacions.finalitat' => 'Organitzar {els_grups}, els seus membres i la funció de cadascú.',
        'rgpd.agrupacions.interessats' => '{Membres}.', 'rgpd.agrupacions.dades' => 'Identificació i contacte, {grup_lc}, {funcio_lc} i dates.',
        'rep.veure_agrupacio' => 'Obres de {un_grup}', 'rep.sense_agrupacio' => 'Sense {grup_lc}',
    ];
    return voc_plantilles($t, $p);
}

/* ---------- Socis: fallers i falleres, foguerers, comparsistes, famílies associades… ---------- */

function voc_socis(string $perfil, string $l): array
{
    $p = [
        'ca' => [
            'fallers' => ['Socis' => 'Fallers i falleres', 'Soci' => 'Faller/a', 'soci_lc' => 'faller/a', 'de_soci' => 'de faller/a', 'dels_socis' => 'dels fallers i falleres',
                'tots_socis' => 'tots els fallers i falleres', 'Tots_socis' => 'Tots els fallers i falleres'],
            'foguerers' => ['Socis' => 'Foguerers i foguereres', 'Soci' => 'Foguerer/a', 'soci_lc' => 'foguerer/a', 'de_soci' => 'de foguerer/a', 'dels_socis' => 'dels foguerers i foguereres',
                'tots_socis' => 'tots els foguerers i foguereres', 'Tots_socis' => 'Tots els foguerers i foguereres'],
            'comissio' => ['Socis' => 'Membres de la comissió', 'Soci' => 'Membre de la comissió', 'soci_lc' => 'membre de la comissió', 'de_soci' => 'de membre', 'dels_socis' => 'dels membres',
                'tots_socis' => 'tots els membres de la comissió', 'Tots_socis' => 'Tots els membres'],
            'comparsistes' => ['Socis' => 'Comparsistes', 'Soci' => 'Comparsista', 'soci_lc' => 'comparsista', 'de_soci' => 'de comparsista', 'dels_socis' => 'dels comparsistes',
                'tots_socis' => 'tots els comparsistes', 'Tots_socis' => 'Tots els comparsistes'],
            'membres' => ['Socis' => 'Membres de la colla', 'Soci' => 'Membre de la colla', 'soci_lc' => 'membre', 'de_soci' => 'de membre', 'dels_socis' => 'dels membres',
                'tots_socis' => 'tots els membres de la colla', 'Tots_socis' => 'Tots els membres'],
            'families' => ['Socis' => 'Famílies associades', 'Soci' => 'Família associada', 'soci_lc' => 'família associada', 'de_soci' => 'de família associada',
                'dels_socis' => 'de les famílies associades', 'tots_socis' => 'totes les famílies associades', 'Tots_socis' => 'Totes les famílies associades'],
        ],
        'es' => [
            'fallers' => ['Socis' => 'Falleros y falleras', 'Soci' => 'Fallero/a', 'soci_lc' => 'fallero/a', 'de_soci' => 'de fallero/a', 'dels_socis' => 'de los falleros y falleras',
                'tots_socis' => 'todos los falleros y falleras', 'Tots_socis' => 'Todos los falleros y falleras'],
            'foguerers' => ['Socis' => 'Foguerers y foguereras', 'Soci' => 'Foguerer/a', 'soci_lc' => 'foguerer/a', 'de_soci' => 'de foguerer/a', 'dels_socis' => 'de los foguerers y foguereras',
                'tots_socis' => 'todos los foguerers y foguereras', 'Tots_socis' => 'Todos los foguerers y foguereras'],
            'comissio' => ['Socis' => 'Miembros de la comisión', 'Soci' => 'Miembro de la comisión', 'soci_lc' => 'miembro de la comisión', 'de_soci' => 'de miembro', 'dels_socis' => 'de los miembros',
                'tots_socis' => 'todos los miembros de la comisión', 'Tots_socis' => 'Todos los miembros'],
            'comparsistes' => ['Socis' => 'Comparsistas', 'Soci' => 'Comparsista', 'soci_lc' => 'comparsista', 'de_soci' => 'de comparsista', 'dels_socis' => 'de los comparsistas',
                'tots_socis' => 'todos los comparsistas', 'Tots_socis' => 'Todos los comparsistas'],
            'membres' => ['Socis' => 'Miembros de la colla', 'Soci' => 'Miembro de la colla', 'soci_lc' => 'miembro', 'de_soci' => 'de miembro', 'dels_socis' => 'de los miembros',
                'tots_socis' => 'todos los miembros de la colla', 'Tots_socis' => 'Todos los miembros'],
            'families' => ['Socis' => 'Familias asociadas', 'Soci' => 'Familia asociada', 'soci_lc' => 'familia asociada', 'de_soci' => 'de familia asociada',
                'dels_socis' => 'de las familias asociadas', 'tots_socis' => 'todas las familias asociadas', 'Tots_socis' => 'Todas las familias asociadas'],
        ],
    ][$l][$perfil] ?? null;
    if (!$p) {
        return [];
    }
    $t = $l === 'es' ? [
        'ent.socis' => '{Socis}', 'ent1.socis' => '{Soci}', 'camp.numero' => 'Número {de_soci}', 'imp.camp.numero' => 'Número {de_soci}', 'inici.socis_actius' => '{Socis} de alta',
        'inf.socis' => '{Socis} por número', 'llist.socis_titol' => '{Socis} por número', 'inf.socis_text' => 'Listado {dels_socis} ordenado por número, con DNI y contacto.',
        'antig.socis_fita' => '{Socis} que cumplen %d años en %d', 'antig.tots_socis' => '{Tots_socis} por antigüedad', 'mem.socis' => '{Socis} por tipo',
        'mem.socis_moviment' => 'Altas y bajas {dels_socis} en %d', 'etiq.qui_socis' => '{Socis} de alta', 'com.socis' => '{Socis}', 'rgpd.act.socis' => 'Gestión {dels_socis}',
        'rgpd.socis.interessats' => '{Socis}.', 'ent.tipus_socis' => 'Tipos {de_soci}', 'ent1.tipus_socis' => 'Tipo {de_soci}', 'camp.tipus_soci' => 'Tipo {de_soci}',
        'imp.camp.tipus_soci' => 'Tipo {de_soci}', 'alta.tipus' => 'Tipo {de_soci}', 'alta.boto' => 'Hoja de alta {de_soci}', 'alta.titol' => 'Solicitud de alta {de_soci}',
        'camp.full_alta_intro' => 'Hoja de alta {de_soci}: texto de introducción', 'camp.full_alta_peu' => 'Hoja de alta {de_soci}: texto legal del pie',
        'cert.soci_actiu' => 'es {soci_lc} de esta entidad desde el %s%s.', 'cert.soci_actiu_sense' => 'es {soci_lc} de esta entidad%s.',
        'cert.soci_baixa' => 'ha sido {soci_lc} de esta entidad desde el %s hasta el %s%s.', 'cert.tipus_soci' => ', en la modalidad %s',
        'opt.quota_soci' => 'Cuota {de_soci}', 'opt.quotes' => 'Cuotas {dels_socis}', 'genera.quotes' => 'Cuotas {dels_socis}',
        'genera.quotes_ajuda' => 'Un recibo por {soci_lc} de alta en el periodo, con la tarifa de su tipo.', 'genera.sense_tarifes_quota' => 'No hay ninguna tarifa de cuota {de_soci}.',
        'rebut.concepte_quota' => 'Cuota {de_soci} — %s', 'rebut.num_soci' => '{soci_lc} n.º %s', 'desc.soci' => '{soci_lc} %s', 'camp.descompte_soci' => 'Descuento por ser {soci_lc} (%)',
        'imp.que_socis' => 'personas y su ficha {de_soci} (número, tipo, alta)', 'imp.res_rols.socis' => 'Fichas {de_soci} nuevas', 'imp.av_ja_soci' => 'Ya es {soci_lc}: solo se completarán los datos personales.',
        'imp.av_numero' => 'El número {de_soci} %s ya está usado.', 'imp.av_tipus' => 'Tipo {de_soci} «%s» desconocido: se pondrá «%s».',
        'err.tipus_soci_en_us' => 'Este tipo {de_soci} se usa en fichas o tarifas: no se puede eliminar. Si ya no se utiliza, dalo de baja.',
        'ajuda.tipus_soci' => 'Los tipos {de_soci} los define la entidad, según sus estatutos (botón «Tipos {de_soci}» del listado).',
        'reunio.conv_socis' => 'Se ruega la asistencia de {tots_socis}. Quienes no puedan asistir pueden delegar el voto por escrito, si lo permiten los estatutos.',
        'rgpd.socis.dades' => 'Nombre y apellidos, DNI, fecha de nacimiento, dirección, teléfono, correo, número y tipo, fechas de alta y baja.',
        'ajuda.mandat_persona' => 'Quién paga: el/la {soci_lc} o el padre, la madre o el tutor/a.',
    ] : [
        'ent.socis' => '{Socis}', 'ent1.socis' => '{Soci}', 'camp.numero' => 'Número {de_soci}', 'imp.camp.numero' => 'Número {de_soci}', 'inici.socis_actius' => '{Socis} d\'alta',
        'inf.socis' => '{Socis} per número', 'llist.socis_titol' => '{Socis} per número', 'inf.socis_text' => 'Llistat {dels_socis} ordenat per número, amb DNI i contacte.',
        'antig.socis_fita' => '{Socis} que fan %d anys en %d', 'antig.tots_socis' => '{Tots_socis} per antiguitat', 'mem.socis' => '{Socis} per tipus',
        'mem.socis_moviment' => 'Altes i baixes {dels_socis} en %d', 'etiq.qui_socis' => '{Socis} d\'alta', 'com.socis' => '{Socis}', 'rgpd.act.socis' => 'Gestió {dels_socis}',
        'rgpd.socis.interessats' => '{Socis}.', 'ent.tipus_socis' => 'Tipus {de_soci}', 'ent1.tipus_socis' => 'Tipus {de_soci}', 'camp.tipus_soci' => 'Tipus {de_soci}',
        'imp.camp.tipus_soci' => 'Tipus {de_soci}', 'alta.tipus' => 'Tipus {de_soci}', 'alta.boto' => 'Full d\'alta {de_soci}', 'alta.titol' => 'Sol·licitud d\'alta {de_soci}',
        'camp.full_alta_intro' => 'Full d\'alta {de_soci}: text d\'introducció', 'camp.full_alta_peu' => 'Full d\'alta {de_soci}: text legal del peu',
        'cert.soci_actiu' => 'és {soci_lc} d\'esta entitat des del %s%s.', 'cert.soci_actiu_sense' => 'és {soci_lc} d\'esta entitat%s.',
        'cert.soci_baixa' => 'ha sigut {soci_lc} d\'esta entitat des del %s fins al %s%s.', 'cert.tipus_soci' => ', en la modalitat %s',
        'opt.quota_soci' => 'Quota {de_soci}', 'opt.quotes' => 'Quotes {dels_socis}', 'genera.quotes' => 'Quotes {dels_socis}',
        'genera.quotes_ajuda' => 'Un rebut per {soci_lc} d\'alta en el període, amb la tarifa del seu tipus.', 'genera.sense_tarifes_quota' => 'No hi ha cap tarifa de quota {de_soci}.',
        'rebut.concepte_quota' => 'Quota {de_soci} — %s', 'rebut.num_soci' => '{soci_lc} núm. %s', 'desc.soci' => '{soci_lc} %s', 'camp.descompte_soci' => 'Descompte per ser {soci_lc} (%)',
        'imp.que_socis' => 'persones i la seua fitxa {de_soci} (número, tipus, alta)', 'imp.res_rols.socis' => 'Fitxes {de_soci} noves', 'imp.av_ja_soci' => 'Ja és {soci_lc}: només es completaran les dades personals.',
        'imp.av_numero' => 'El número {de_soci} %s ja està usat.', 'imp.av_tipus' => 'Tipus {de_soci} «%s» desconegut: es posarà «%s».',
        'err.tipus_soci_en_us' => 'Este tipus {de_soci} es fa servir en fitxes o tarifes: no es pot eliminar. Si ja no s\'utilitza, posa\'l de baixa.',
        'ajuda.tipus_soci' => 'Els tipus {de_soci} els defineix l\'entitat, segons els seus estatuts (botó «Tipus {de_soci}» del llistat).',
        'reunio.conv_socis' => 'Es prega l\'assistència de {tots_socis}. Els qui no hi puguen assistir poden delegar el vot per escrit, si ho permeten els estatuts.',
        'rgpd.socis.dades' => 'Nom i cognoms, DNI, data de naixement, adreça, telèfon, correu, número i tipus, dates d\'alta i baixa.',
        'ajuda.mandat_persona' => 'Qui paga: el/la {soci_lc} o el pare, la mare o el tutor/a.',
    ];
    return voc_plantilles($t, $p);
}

/* ---------- Material (instruments) ---------- */

function voc_material(string $perfil, string $l): array
{
    $nom = ['ca' => ['material' => 'Material', 'esportiu' => 'Material esportiu'], 'es' => ['material' => 'Material', 'esportiu' => 'Material deportivo']][$l][$perfil] ?? 'Material';
    return $l === 'es' ? [
        'menu.instruments' => $nom, 'modul.instruments' => $nom, 'ent1.instruments' => 'Artículo del inventario', 'camp.instrument_id' => 'Material',
        'modul_ajuda.instruments' => 'Inventario, mantenimientos y préstamos de material.', 'inici.cedits' => 'Material prestado',
        'err.instrument_cedit' => 'Este material ya está prestado. Registra primero la devolución.', 'avis_cfg.cessions_vencudes' => 'Material prestado que se tendría que haber devuelto',
        'rgpd.act.instruments' => 'Préstamo de material', 'rgpd.instruments.finalitat' => 'Controlar el material prestado y su devolución.',
        'rgpd.instruments.interessats' => 'Personas a quienes se presta material.', 'rgpd.instruments.dades' => 'Nombre, material, fechas de entrega y devolución, estado y fianza.',
        'rgpd.instruments.conservacio' => 'Mientras dure el préstamo y, después, para el historial del material.', 'opt.instruments' => 'Material y reparaciones',
        'camp.tipus_instrument' => 'Tipo de material', 'opt.instrument' => 'Material',
    ] : [
        'menu.instruments' => $nom, 'modul.instruments' => $nom, 'ent1.instruments' => 'Article de l\'inventari', 'camp.instrument_id' => 'Material',
        'modul_ajuda.instruments' => 'Inventari, manteniments i préstecs de material.', 'inici.cedits' => 'Material deixat',
        'err.instrument_cedit' => 'Este material ja està deixat. Registra primer la devolució.', 'avis_cfg.cessions_vencudes' => 'Material deixat que s\'havia d\'haver tornat',
        'rgpd.act.instruments' => 'Préstec de material', 'rgpd.instruments.finalitat' => 'Controlar el material deixat i la seua devolució.',
        'rgpd.instruments.interessats' => 'Persones a qui es deixa material.', 'rgpd.instruments.dades' => 'Nom, material, dates d\'entrega i devolució, estat i fiança.',
        'rgpd.instruments.conservacio' => 'Mentre dure el préstec i, després, per a l\'historial del material.', 'opt.instruments' => 'Material i reparacions',
        'camp.tipus_instrument' => 'Tipus de material', 'opt.instrument' => 'Material',
    ];
}

/* ---------- Roba (uniformes): equipacions, vestits, roba i marxandatge ---------- */

function voc_roba(string $perfil, string $l): array
{
    $p = [
        'ca' => [
            'equipacions' => ['Roba' => 'Equipacions', 'article' => 'Article d\'equipació', 'articles' => 'Articles d\'equipació', 'de_roba' => 'd\'equipació', 'Material' => 'Material i equipacions',
                'exemple' => 'Per exemple: «Equipació de joc», «Xandall» o «Motxilla».', 'ajuda' => 'Equipacions, xandalls, motxilles… per talla: compres, vendes i préstecs.',
                'prestec' => '(per exemple, l\'equipació de joc)'],
            'vestits' => ['Roba' => 'Vestits i roba', 'article' => 'Article de roba', 'articles' => 'Articles de roba', 'de_roba' => 'de roba', 'Material' => 'Material i vestits',
                'exemple' => 'Per exemple: «Vestit de l\'esquadra», «Samarreta» o «Gorra».', 'ajuda' => 'Vestits, samarretes, gorres… per talla: compres, vendes i préstecs.',
                'prestec' => '(per exemple, un vestit de l\'esquadra)'],
            'roba' => ['Roba' => 'Roba i marxandatge', 'article' => 'Article de roba', 'articles' => 'Articles de roba', 'de_roba' => 'de roba', 'Material' => 'Material i roba',
                'exemple' => 'Per exemple: «Samarreta de l\'entitat», «Dessuadora» o «Motxilla».', 'ajuda' => 'Samarretes, dessuadores, motxilles… per talla: compres, vendes i préstecs.',
                'prestec' => '(per exemple, una peça per a un acte)'],
        ],
        'es' => [
            'equipacions' => ['Roba' => 'Equipaciones', 'article' => 'Artículo de equipación', 'articles' => 'Artículos de equipación', 'de_roba' => 'de equipación', 'Material' => 'Material y equipaciones',
                'exemple' => 'Por ejemplo: «Equipación de juego», «Chándal» o «Mochila».', 'ajuda' => 'Equipaciones, chándales, mochilas… por talla: compras, ventas y préstamos.',
                'prestec' => '(por ejemplo, la equipación de juego)'],
            'vestits' => ['Roba' => 'Trajes y ropa', 'article' => 'Artículo de ropa', 'articles' => 'Artículos de ropa', 'de_roba' => 'de ropa', 'Material' => 'Material y trajes',
                'exemple' => 'Por ejemplo: «Traje de la escuadra», «Camiseta» o «Gorra».', 'ajuda' => 'Trajes, camisetas, gorras… por talla: compras, ventas y préstamos.',
                'prestec' => '(por ejemplo, un traje de la escuadra)'],
            'roba' => ['Roba' => 'Ropa y merchandising', 'article' => 'Artículo de ropa', 'articles' => 'Artículos de ropa', 'de_roba' => 'de ropa', 'Material' => 'Material y ropa',
                'exemple' => 'Por ejemplo: «Camiseta de la entidad», «Sudadera» o «Mochila».', 'ajuda' => 'Camisetas, sudaderas, mochilas… por talla: compras, ventas y préstamos.',
                'prestec' => '(por ejemplo, una prenda para un acto)'],
        ],
    ][$l][$perfil] ?? null;
    if (!$p) {
        return [];
    }
    $t = $l === 'es' ? [
        'menu.uniformes' => '{Roba}', 'modul.uniformes' => '{Roba}', 'menu.material' => '{Material}', 'ent.uniformes' => '{articles}', 'ent1.uniformes' => '{article}',
        'ent.uniforme_compres' => 'Compras {de_roba}', 'ent1.uniforme_compres' => 'Compra {de_roba}', 'ent.uniforme_lliuraments' => 'Entregas {de_roba}',
        'ent1.uniforme_lliuraments' => 'Entrega {de_roba}', 'ajuda.uniforme_nom' => '{exemple}', 'modul_ajuda.uniformes' => '{ajuda}', 'rgpd.act.uniformes' => '{Roba}',
        'rgpd.uniformes.finalitat' => 'Entregar, vender o prestar ropa y material de la entidad y controlar las devoluciones.', 'rgpd.uniformes.interessats' => 'Personas que reciben ropa de la entidad.',
        'ajuda.lliurament_tipus' => 'Venta: la persona se la queda y la paga. Préstamo: la tiene que devolver {prestec}. Gratuito: se la queda sin pagar.',
        'unif.cap_article' => 'Todavía no hay ningún artículo. Empieza añadiendo los que tiene la entidad, con sus tallas.',
    ] : [
        'menu.uniformes' => '{Roba}', 'modul.uniformes' => '{Roba}', 'menu.material' => '{Material}', 'ent.uniformes' => '{articles}', 'ent1.uniformes' => '{article}',
        'ent.uniforme_compres' => 'Compres {de_roba}', 'ent1.uniforme_compres' => 'Compra {de_roba}', 'ent.uniforme_lliuraments' => 'Lliuraments {de_roba}',
        'ent1.uniforme_lliuraments' => 'Lliurament {de_roba}', 'ajuda.uniforme_nom' => '{exemple}', 'modul_ajuda.uniformes' => '{ajuda}', 'rgpd.act.uniformes' => '{Roba}',
        'rgpd.uniformes.finalitat' => 'Lliurar, vendre o deixar roba i material de l\'entitat i controlar-ne les devolucions.', 'rgpd.uniformes.interessats' => 'Persones que reben roba de l\'entitat.',
        'ajuda.lliurament_tipus' => 'Venda: la persona se la queda i la paga. Préstec: l\'ha de tornar {prestec}. Gratuït: se la queda sense pagar.',
        'unif.cap_article' => 'Encara no hi ha cap article. Comença afegint els que té l\'entitat, amb les seues talles.',
    ];
    return voc_plantilles($t, $p);
}
