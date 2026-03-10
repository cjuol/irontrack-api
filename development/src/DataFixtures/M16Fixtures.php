<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Catalog\Exercise;
use App\Entity\Program\ExerciseBlock;
use App\Entity\Program\Mesocycle;
use App\Entity\Program\PlannedExercise;
use App\Entity\Program\PlannedSet;
use App\Entity\Program\SessionTemplate;
use App\Entity\Program\WeeklyCardioPlan;
use App\Entity\Program\WeeklyMetabolicPlan;
use App\Entity\User;
use App\Enum\BlockType;
use App\Enum\CardioFormat;
use App\Enum\MetabolicFormat;
use App\Enum\SessionType;
use App\Enum\SetType;
use App\Enum\WeightModifier;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class M16Fixtures extends Fixture implements DependentFixtureInterface
{
    private ObjectManager $manager;

    public function getDependencies(): array
    {
        return [CatalogFixtures::class, UserFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        $this->manager = $manager;

        /** @var User $user */
        $user = $this->getReference('user-cristobal', User::class);

        $mesociclo = $this->crearMesociclo($user);
        $this->crearSesion1($mesociclo);
        $this->crearSesion2($mesociclo);
        $this->crearSesion3($mesociclo);
        $this->crearSesion4($mesociclo);

        $manager->flush();
    }

    private function crearMesociclo(User $user): Mesocycle
    {
        $objetivo = 'Preparación específica para Hyrox. Objetivo principal: rendimiento híbrido combinando '
            . 'resistencia a la fuerza y capacidad cardiovascular de forma simultánea. '
            . '4 sesiones semanales: Híbrida, Mixta, Estructural y Resistencia.';

        $mesociclo = (new Mesocycle())
            ->setUser($user)
            ->setName('Mesociclo 16 — Hyrox')
            ->setStartDate(new \DateTimeImmutable('2026-01-26'))
            ->setEndDate(new \DateTimeImmutable('2026-03-01'))
            ->setNumWeeks(5)
            ->setStepGoalTrainingDay(10000)
            ->setStepGoalRestDay(12000)
            ->setObjective($objetivo);

        $this->manager->persist($mesociclo);
        return $mesociclo;
    }

    // --- Sesión 1: Híbrida (Fuerza + Metabólico A) ---

    private function crearSesion1(Mesocycle $mesociclo): void
    {
        $sesion = $this->crearSesionTemplate(
            $mesociclo,
            'Sesión 1 — Híbrida',
            SessionType::HYBRID,
            1,
            'Bloque de fuerza seguido de bloque metabólico tipo Hyrox (Metabólico A).'
        );

        $this->crearBloqueS1Fuerza($sesion);
        $this->crearBloqueS1Metabolico($sesion);
    }

    private function crearBloqueS1Fuerza(SessionTemplate $sesion): void
    {
        $bloque = $this->crearBloque($sesion, 'Fuerza', BlockType::STRENGTH, 1);

        /** @var Exercise $pressBanca */
        $pressBanca = $this->getReference('exercise-press-banca-plano', Exercise::class);
        $ej = $this->crearEjercicio($bloque, $pressBanca, 1);
        $this->addSerie($ej, 1, SetType::TOP_SET, 6, 7, 0, false, 120, null);
        $this->addSerie($ej, 2, SetType::NORMAL, 5, 6, null, true, 120, WeightModifier::KEEP);
        $this->addSerie($ej, 3, SetType::NORMAL, 8, 10, 0, false, 120, WeightModifier::DECREASE);

        /** @var Exercise $remoGironda */
        $remoGironda = $this->getReference('exercise-remo-gironda-neutro', Exercise::class);
        $ej = $this->crearEjercicio($bloque, $remoGironda, 2);
        $this->addSerie($ej, 1, SetType::NORMAL, 8, 10, 1, false, 90, null);
        $this->addSerie($ej, 2, SetType::NORMAL, 8, 10, 1, false, 90, null);
        $this->addSerie($ej, 3, SetType::DESCENDING, 8, 10, null, true, 120, null);

        /** @var Exercise $jalon */
        $jalon = $this->getReference('exercise-jalon', Exercise::class);
        $ej = $this->crearEjercicio($bloque, $jalon, 3);
        $this->addSerie($ej, 1, SetType::NORMAL, 8, 10, 1, false, 90, null);
        $this->addSerie($ej, 2, SetType::NORMAL, 8, 10, 1, false, 90, null);
        $this->addSerie($ej, 3, SetType::DESCENDING, 8, 10, null, true, 120, null);

        /** @var Exercise $extTriceps */
        $extTriceps = $this->getReference('exercise-extension-triceps-v', Exercise::class);
        $ej = $this->crearEjercicio($bloque, $extTriceps, 4);
        $this->addSerie($ej, 1, SetType::AMRAP, 10, null, 0, false, 90, null);
        $this->addSerie($ej, 2, SetType::AMRAP, 10, null, 0, false, 90, null);
        // Triple drop: 6+6+10 reps (repsMin=inicio, repsMax=último drop)
        $this->addSerie($ej, 3, SetType::DESCENDING, 6, 10, null, true, 120, null);
    }

    private function crearBloqueS1Metabolico(SessionTemplate $sesion): void
    {
        $bloque = $this->crearBloque($sesion, 'Metabólico A', BlockType::METABOLIC, 2);

        $planes = [
            [
                'semana'      => 1,
                'formato'     => MetabolicFormat::AMRAP,
                'duracion'    => 8,
                'rondas'      => null,
                'descanso'    => null,
                'descripcion' => 'AMRAP 8 minutos: máximas rondas sin descansar.',
                'ejercicios'  => [
                    ['name' => 'Push press con mancuernas', 'reps' => 8],
                    ['name' => 'Burpees (sin salto, solo bajar y subir)', 'reps' => 8],
                ],
            ],
            [
                'semana'      => 2,
                'formato'     => MetabolicFormat::EMOM,
                'duracion'    => 10,
                'rondas'      => null,
                'descanso'    => null,
                'descripcion' => 'EMOM 10 min. El resto del minuto es descanso.',
                'ejercicios'  => [
                    ['name' => 'Zancadas con mancuernas', 'reps' => '10-12', 'notes' => 'Minutos pares'],
                    ['name' => 'Push press con mancuernas', 'reps' => '10-12', 'notes' => 'Minutos impares'],
                ],
            ],
            [
                'semana'      => 3,
                'formato'     => MetabolicFormat::ROUNDS_FOR_TIME,
                'duracion'    => null,
                'rondas'      => 5,
                'descanso'    => 90,
                'descripcion' => '5 rondas totales, descanso 1:30 min por ronda. Cada ronda al menor tiempo posible.',
                'ejercicios'  => [
                    ['name' => 'Sentadilla Goblet pesada', 'reps' => 15],
                    ['name' => 'Burpees con salto largo', 'reps' => 10],
                    ['name' => 'Kettlebell swing pesadas', 'reps' => 15],
                ],
            ],
            [
                'semana'      => 4,
                'formato'     => MetabolicFormat::POWER_INTERVALS,
                'duracion'    => null,
                'rondas'      => 4,
                'descanso'    => null,
                'descripcion' => 'Repetir 4 veces sin descanso entre intervalos.',
                'ejercicios'  => [
                    ['name' => 'Burpees', 'reps' => '30s máxima velocidad + 30s descanso'],
                    ['name' => 'Wall balls', 'reps' => '30s + 30s descanso'],
                ],
            ],
            [
                'semana'      => 5,
                'formato'     => MetabolicFormat::TEST,
                'duracion'    => null,
                'rondas'      => 1,
                'descanso'    => null,
                'descripcion' => 'Test: realizarlo en el menor tiempo posible. Anotar resultado.',
                'ejercicios'  => [
                    ['name' => 'Push press con mancuernas', 'reps' => 50],
                    ['name' => 'Burpees con salto largo', 'reps' => 50],
                ],
            ],
        ];

        foreach ($planes as $datos) {
            $plan = (new WeeklyMetabolicPlan())
                ->setExerciseBlock($bloque)
                ->setWeekNumber($datos['semana'])
                ->setFormatType($datos['formato'])
                ->setDurationMinutes($datos['duracion'])
                ->setTotalRounds($datos['rondas'])
                ->setRestBetweenRoundsSeconds($datos['descanso'])
                ->setDescription($datos['descripcion'])
                ->setExercises($datos['ejercicios']);
            $this->manager->persist($plan);
        }
    }

    // --- Sesión 2: Mixta (Fuerza pierna + Cardio 1) ---

    private function crearSesion2(Mesocycle $mesociclo): void
    {
        $sesion = $this->crearSesionTemplate(
            $mesociclo,
            'Sesión 2 — Mixta',
            SessionType::MIXED,
            2,
            'Bloque de fuerza de tren inferior seguido de cardio progresivo en cinta (Cardio 1).'
        );

        $this->crearBloqueS2Fuerza($sesion);
        $this->crearBloqueS2Cardio($sesion);
    }

    private function crearBloqueS2Fuerza(SessionTemplate $sesion): void
    {
        $bloque = $this->crearBloque($sesion, 'Fuerza', BlockType::STRENGTH, 1);

        /** @var Exercise $aductor */
        $aductor = $this->getReference('exercise-aductor-cerrar', Exercise::class);
        $ej = $this->crearEjercicio($bloque, $aductor, 1);
        $this->addSerie($ej, 1, SetType::TOP_SET, 9, 9, 0, false, 100, null);
        $this->addSerie($ej, 2, SetType::BACK_OFF, 11, 14, null, true, 100, WeightModifier::DECREASE);

        /** @var Exercise $prensa */
        $prensa = $this->getReference('exercise-prensa-discos', Exercise::class);
        $ej = $this->crearEjercicio($bloque, $prensa, 2, 'Con el asiento tumbado y los pies colocados muy abajo en la base, buscando énfasis de rodilla.');
        $this->addSerie($ej, 1, SetType::AMRAP, 6, null, 0, false, 150, null);
        $this->addSerie($ej, 2, SetType::NORMAL, 7, 9, 0, false, 150, WeightModifier::KEEP);
        $this->addSerie($ej, 3, SetType::NORMAL, 11, 13, null, true, 150, WeightModifier::DECREASE);

        /** @var Exercise $femoral */
        $femoral = $this->getReference('exercise-femoral-tumbado', Exercise::class);
        $ej = $this->crearEjercicio($bloque, $femoral, 3);
        $this->addSerie($ej, 1, SetType::AMRAP, 6, null, 0, false, 150, null);
        $this->addSerie($ej, 2, SetType::NORMAL, 7, 9, 0, false, 150, WeightModifier::KEEP);
        $this->addSerie($ej, 3, SetType::SET_COUNTDOWN, 1, 5, null, true, 150, WeightModifier::DECREASE);

        /** @var Exercise $pesoMuerto */
        $pesoMuerto = $this->getReference('exercise-peso-muerto-convencional', Exercise::class);
        $ej = $this->crearEjercicio($bloque, $pesoMuerto, 4);
        $this->addSerie($ej, 1, SetType::AMRAP, 3, null, 0, false, 200, null);
        $this->addSerie($ej, 2, SetType::BACK_OFF, 7, 8, 0, false, 200, WeightModifier::DECREASE);
    }

    private function crearBloqueS2Cardio(SessionTemplate $sesion): void
    {
        $bloque = $this->crearBloque($sesion, 'Cardio 1', BlockType::CARDIO, 2);

        $planes = [
            [
                'semana'      => 1,
                'formato'     => CardioFormat::WALK,
                'duracion'    => 20,
                'descripcion' => 'Caminata en cinta: 10% inclinación a 5,5 km/h.',
                'intervalos'  => [
                    ['work_duration' => 1200, 'work_speed' => 5.5, 'work_incline' => 10, 'rounds' => 1],
                ],
            ],
            [
                'semana'      => 2,
                'formato'     => CardioFormat::WALK,
                'duracion'    => 25,
                'descripcion' => 'Caminata en cinta: 12% inclinación a 5,5 km/h.',
                'intervalos'  => [
                    ['work_duration' => 1500, 'work_speed' => 5.5, 'work_incline' => 12, 'rounds' => 1],
                ],
            ],
            [
                'semana'      => 3,
                'formato'     => CardioFormat::INTERVALS,
                'duracion'    => 20,
                'descripcion' => '4 rondas: 4 min andar inclinado (10% + 5,5 km/h) + 1 min trote suave (7 km/h sin inclinación).',
                'intervalos'  => [
                    ['work_duration' => 240, 'work_speed' => 5.5, 'work_incline' => 10, 'rest_duration' => 60, 'rest_speed' => 7.0, 'rest_incline' => 0, 'rounds' => 4],
                ],
            ],
            [
                'semana'      => 4,
                'formato'     => CardioFormat::INTERVALS,
                'duracion'    => 25,
                'descripcion' => '5 rondas: 3 min andar inclinado + 2 min trote suave (7 km/h sin inclinación).',
                'intervalos'  => [
                    ['work_duration' => 180, 'work_speed' => 5.5, 'work_incline' => 10, 'rest_duration' => 120, 'rest_speed' => 7.0, 'rest_incline' => 0, 'rounds' => 5],
                ],
            ],
            [
                'semana'      => 5,
                'formato'     => CardioFormat::CONTINUOUS,
                'duracion'    => 20,
                'descripcion' => 'Trote continuo a 7 km/h. Máximo 1 pausa de 5 min si hay fatiga excesiva en el minuto 10.',
                'intervalos'  => [
                    ['work_duration' => 1200, 'work_speed' => 7.0, 'work_incline' => 0, 'rounds' => 1],
                ],
            ],
        ];

        foreach ($planes as $datos) {
            $plan = (new WeeklyCardioPlan())
                ->setExerciseBlock($bloque)
                ->setWeekNumber($datos['semana'])
                ->setFormatType($datos['formato'])
                ->setDurationMinutes($datos['duracion'])
                ->setDescription($datos['descripcion'])
                ->setIntervals($datos['intervalos']);
            $this->manager->persist($plan);
        }
    }

    // --- Sesión 3: Estructural (solo fuerza) ---

    private function crearSesion3(Mesocycle $mesociclo): void
    {
        $sesion = $this->crearSesionTemplate(
            $mesociclo,
            'Sesión 3 — Estructural',
            SessionType::STRENGTH,
            3,
            'Sesión exclusiva de fuerza. Mantenemos la base muscular sin interferencia aeróbica.'
        );

        $bloque = $this->crearBloque($sesion, 'Fuerza', BlockType::STRENGTH, 1);

        /** @var Exercise $elevLaterales */
        $elevLaterales = $this->getReference('exercise-elevaciones-laterales-pie', Exercise::class);
        $ej = $this->crearEjercicio($bloque, $elevLaterales, 1, 'Codos completamente extendidos, hombros descendidos con las mancuernas lo más alejadas del cuerpo.');
        $this->addSerie($ej, 1, SetType::NORMAL, 14, 15, 1, false, 90, null);
        $this->addSerie($ej, 2, SetType::NORMAL, 13, 15, 0, false, 90, WeightModifier::KEEP);
        $this->addSerie($ej, 3, SetType::DESCENDING, 8, 12, null, true, 120, WeightModifier::INCREASE);

        /** @var Exercise $curlEz */
        $curlEz = $this->getReference('exercise-curl-barra-ez', Exercise::class);
        $ej = $this->crearEjercicio($bloque, $curlEz, 2, 'Espalda completamente apoyada en la pared.');
        $this->addSerie($ej, 1, SetType::NORMAL, 7, 9, 2, false, 90, null);
        $this->addSerie($ej, 2, SetType::NORMAL, 7, 9, 1, false, 90, WeightModifier::KEEP);
        $this->addSerie($ej, 3, SetType::NORMAL, 7, 9, 0, false, 120, WeightModifier::KEEP);

        /** @var Exercise $remoMancuerna */
        $remoMancuerna = $this->getReference('exercise-remo-mancuerna', Exercise::class);
        $ej = $this->crearEjercicio($bloque, $remoMancuerna, 3, 'Dos pies en suelo, máximo recorrido. Llevar la mancuerna hasta la cadera.');
        // Descanso: 30s por brazo, 75s de serie completa
        $this->addSerie($ej, 1, SetType::NORMAL, 7, 10, null, true, 75, null);
        $this->addSerie($ej, 2, SetType::NORMAL, 7, 10, null, true, 75, null);

        /** @var Exercise $pressMultipower */
        $pressMultipower = $this->getReference('exercise-press-banca-multipower-30', Exercise::class);
        $ej = $this->crearEjercicio($bloque, $pressMultipower, 4);
        $this->addSerie($ej, 1, SetType::TOP_SET, 8, 9, 0, false, 120, null);
        $this->addSerie($ej, 2, SetType::NORMAL, 7, 9, null, true, 120, WeightModifier::KEEP);
        $this->addSerie($ej, 3, SetType::DESCENDING, 6, 12, null, true, 120, null);

        /** @var Exercise $skullCrash */
        $skullCrash = $this->getReference('exercise-skull-crash-mancuernas', Exercise::class);
        $ej = $this->crearEjercicio($bloque, $skullCrash, 5, 'Banco a 15° de inclinación.');
        $this->addSerie($ej, 1, SetType::NORMAL, 10, 12, 1, false, 90, null);
        $this->addSerie($ej, 2, SetType::NORMAL, 10, 12, 1, false, 90, WeightModifier::KEEP);
        $this->addSerie($ej, 3, SetType::SET_COUNTDOWN, 1, 5, null, true, 120, WeightModifier::DECREASE);

        /** @var Exercise $remoTorax */
        $remoTorax = $this->getReference('exercise-remo-torax-apoyado', Exercise::class);
        $ej = $this->crearEjercicio($bloque, $remoTorax, 6, 'Codos a la altura del hombro.');
        $this->addSerie($ej, 1, SetType::NORMAL, 12, 14, 1, false, 90, null);
        $this->addSerie($ej, 2, SetType::NORMAL, 12, 14, null, true, 90, null);
    }

    // --- Sesión 4: Resistencia (Metabólico B + Cardio 2) ---

    private function crearSesion4(Mesocycle $mesociclo): void
    {
        $sesion = $this->crearSesionTemplate(
            $mesociclo,
            'Sesión 4 — Resistencia',
            SessionType::RESISTANCE,
            4,
            'Mayor demanda cardiovascular. Simulacro de fatiga tipo Hyrox. Metabólico B + Cardio 2.'
        );

        $this->crearBloqueS4Metabolico($sesion);
        $this->crearBloqueS4Cardio($sesion);
    }

    private function crearBloqueS4Metabolico(SessionTemplate $sesion): void
    {
        $bloque = $this->crearBloque($sesion, 'Metabólico B', BlockType::METABOLIC, 1);

        $planes = [
            [
                'semana'      => 1,
                'formato'     => MetabolicFormat::ROUNDS_FOR_TIME,
                'duracion'    => null,
                'rondas'      => 3,
                'descanso'    => 60,
                'descripcion' => '3 rondas.',
                'ejercicios'  => [
                    ['name' => 'Remo en cuerda de escalada inclinado al máximo', 'reps' => 20],
                    ['name' => 'Paseo de granjero con mancuernas pesadas', 'reps' => '30s'],
                ],
            ],
            [
                'semana'      => 2,
                'formato'     => MetabolicFormat::ROUNDS_FOR_TIME,
                'duracion'    => null,
                'rondas'      => 6,
                'descanso'    => null,
                'descripcion' => 'Escalera descendente: 10-8-6-4-2 reps. Isométrica: 30-30-20-20-10-10 segundos.',
                'ejercicios'  => [
                    ['name' => 'Sentadilla isométrica con disco apoyado en pared', 'reps' => '10-8-6-4-2 (30-30-20-20-10-10s)'],
                    ['name' => 'Subidas al cajón con mancuernas (alternando piernas)', 'reps' => '10-8-6-4-2'],
                ],
            ],
            [
                'semana'      => 3,
                'formato'     => MetabolicFormat::ROUNDS_FOR_TIME,
                'duracion'    => null,
                'rondas'      => 4,
                'descanso'    => null,
                'descripcion' => '4 rondas.',
                'ejercicios'  => [
                    ['name' => 'Remo aire', 'reps' => '200m'],
                    ['name' => 'Zancadas profundas con mancuernas', 'reps' => '20m'],
                    ['name' => 'Flexiones', 'reps' => 20],
                ],
            ],
            [
                'semana'      => 4,
                'formato'     => MetabolicFormat::ROUNDS_FOR_TIME,
                'duracion'    => null,
                'rondas'      => 1,
                'descanso'    => null,
                'descripcion' => 'Half Hyrox: realizarlo en el menor tiempo posible.',
                'ejercicios'  => [
                    ['name' => 'Remo aire', 'reps' => '500m'],
                    ['name' => 'Swing con kettlebell', 'reps' => 50],
                    ['name' => 'Burpees con salto', 'reps' => 10],
                ],
            ],
            [
                'semana'      => 5,
                'formato'     => MetabolicFormat::ROUNDS_FOR_TIME,
                'duracion'    => null,
                'rondas'      => null,
                'descanso'    => null,
                // Cada ronda se intercala con 500m de carrera en cinta
                'descripcion' => 'Intercalar cada ronda con 500m de carrera en cinta.',
                'ejercicios'  => [
                    ['name' => 'Paseo de granjero con mancuernas', 'reps' => '40m'],
                    ['name' => 'Zancadas con mancuernas', 'reps' => 20],
                ],
            ],
        ];

        foreach ($planes as $datos) {
            $plan = (new WeeklyMetabolicPlan())
                ->setExerciseBlock($bloque)
                ->setWeekNumber($datos['semana'])
                ->setFormatType($datos['formato'])
                ->setDurationMinutes($datos['duracion'])
                ->setTotalRounds($datos['rondas'])
                ->setRestBetweenRoundsSeconds($datos['descanso'])
                ->setDescription($datos['descripcion'])
                ->setExercises($datos['ejercicios']);
            $this->manager->persist($plan);
        }
    }

    private function crearBloqueS4Cardio(SessionTemplate $sesion): void
    {
        $bloque = $this->crearBloque($sesion, 'Cardio 2', BlockType::CARDIO, 2);

        $planes = [
            [
                'semana'      => 1,
                'formato'     => CardioFormat::INTERVALS,
                'duracion'    => 12,
                'descripcion' => '3 rondas: 3 min remo a intensidad media/alta + 1 min carrera a 7 km/h.',
                'intervalos'  => [
                    ['work_duration' => 180, 'work_type' => 'remo', 'rest_duration' => 60, 'rest_speed' => 7.0, 'rest_type' => 'carrera', 'rounds' => 3],
                ],
            ],
            [
                'semana'      => 2,
                'formato'     => CardioFormat::INTERVALS,
                'duracion'    => 16,
                'descripcion' => '4 rondas: 3 min remo a intensidad media/alta + 1 min carrera a 7 km/h.',
                'intervalos'  => [
                    ['work_duration' => 180, 'work_type' => 'remo', 'rest_duration' => 60, 'rest_speed' => 7.0, 'rest_type' => 'carrera', 'rounds' => 4],
                ],
            ],
            [
                'semana'      => 3,
                'formato'     => CardioFormat::INTERVALS,
                'duracion'    => 30,
                'descripcion' => 'Test de intervalos 30 min totales: 2 min andar (5,5 km/h) + 1 min carrera (7 km/h).',
                'intervalos'  => [
                    ['work_duration' => 120, 'work_speed' => 5.5, 'work_type' => 'andar', 'rest_duration' => 60, 'rest_speed' => 7.0, 'rest_type' => 'carrera'],
                ],
            ],
            [
                'semana'      => 4,
                'formato'     => CardioFormat::CONTINUOUS,
                'duracion'    => 35,
                'descripcion' => '35 min de cardio continuo en bicicleta o remo al 70% de intensidad.',
                'intervalos'  => [],
            ],
            [
                'semana'      => 5,
                'formato'     => CardioFormat::SIMULATION,
                'duracion'    => null,
                'descripcion' => 'Simulacro de carrera: 4×500m de carrera que se alterna con el metabólico (paseo granjero + zancadas).',
                'intervalos'  => [
                    ['work_duration' => null, 'work_type' => 'carrera', 'work_distance' => 500, 'rounds' => 4],
                ],
            ],
        ];

        foreach ($planes as $datos) {
            $plan = (new WeeklyCardioPlan())
                ->setExerciseBlock($bloque)
                ->setWeekNumber($datos['semana'])
                ->setFormatType($datos['formato'])
                ->setDurationMinutes($datos['duracion'])
                ->setDescription($datos['descripcion'])
                ->setIntervals($datos['intervalos']);
            $this->manager->persist($plan);
        }
    }

    // --- Helpers ---

    private function crearSesionTemplate(
        Mesocycle $mesociclo,
        string $nombre,
        SessionType $tipo,
        int $orden,
        ?string $notas = null,
    ): SessionTemplate {
        $sesion = (new SessionTemplate())
            ->setMesocycle($mesociclo)
            ->setName($nombre)
            ->setType($tipo)
            ->setSortOrder($orden)
            ->setNotes($notas);

        $mesociclo->addSessionTemplate($sesion);
        $this->manager->persist($sesion);
        return $sesion;
    }

    private function crearBloque(
        SessionTemplate $sesion,
        string $nombre,
        BlockType $tipo,
        int $orden,
    ): ExerciseBlock {
        $bloque = (new ExerciseBlock())
            ->setName($nombre)
            ->setType($tipo)
            ->setSortOrder($orden);

        $sesion->addExerciseBlock($bloque);
        $this->manager->persist($bloque);
        return $bloque;
    }

    private function crearEjercicio(
        ExerciseBlock $bloque,
        Exercise $ejercicio,
        int $orden,
        ?string $notas = null,
    ): PlannedExercise {
        $planned = (new PlannedExercise())
            ->setExercise($ejercicio)
            ->setSortOrder($orden)
            ->setNotes($notas);

        $bloque->addPlannedExercise($planned);
        $this->manager->persist($planned);
        return $planned;
    }

    private function addSerie(
        PlannedExercise $ejercicio,
        int $orden,
        SetType $tipo,
        ?int $repsMin,
        ?int $repsMax,
        ?int $rir,
        bool $hastaFallo,
        ?int $descansoSegundos,
        ?WeightModifier $modificadorPeso,
        ?string $notas = null,
    ): void {
        $serie = (new PlannedSet())
            ->setSortOrder($orden)
            ->setSetType($tipo)
            ->setRepsMin($repsMin)
            ->setRepsMax($repsMax)
            ->setRestSeconds($descansoSegundos)
            ->setWeightModifier($modificadorPeso)
            ->setNotes($notas);

        // setRirToFailure(true) pone rir=null internamente
        if ($hastaFallo) {
            $serie->setRirToFailure(true);
        } else {
            $serie->setRir($rir);
        }

        $ejercicio->addPlannedSet($serie);
        $this->manager->persist($serie);
    }
}
