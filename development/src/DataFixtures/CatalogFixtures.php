<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Catalog\Exercise;
use App\Entity\Catalog\MuscleGroup;
use App\Enum\EquipmentType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CatalogFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $grupos = $this->crearGruposMusculares($manager);
        $this->crearEjercicios($manager, $grupos);
        $manager->flush();
    }

    /** @return array<string, MuscleGroup> */
    private function crearGruposMusculares(ObjectManager $manager): array
    {
        $datos = [
            'pecho'         => 'Pecho',
            'espalda'       => 'Espalda',
            'hombros'       => 'Hombros',
            'biceps'        => 'Bíceps',
            'triceps'       => 'Tríceps',
            'cuadriceps'    => 'Cuádriceps',
            'isquiosurales' => 'Isquiosurales',
            'aductores'     => 'Aductores',
            'gluteos'       => 'Glúteos',
        ];

        $grupos = [];
        foreach ($datos as $slug => $nombre) {
            $grupo = (new MuscleGroup())->setName($nombre)->setSlug($slug);
            $manager->persist($grupo);
            $grupos[$slug] = $grupo;
            $this->addReference('muscle-' . $slug, $grupo);
        }

        return $grupos;
    }

    /** @param array<string, MuscleGroup> $g */
    private function crearEjercicios(ObjectManager $manager, array $g): void
    {
        $ejercicios = [
            [
                'ref'        => 'press-banca-plano',
                'nombre'     => 'Press banca plano',
                'equipo'     => EquipmentType::BARBELL,
                'primarios'  => [$g['pecho']],
                'secundarios' => [$g['triceps'], $g['hombros']],
            ],
            [
                'ref'        => 'remo-gironda-neutro',
                'nombre'     => 'Remo Gironda agarre neutro',
                'equipo'     => EquipmentType::CABLE,
                'primarios'  => [$g['espalda']],
                'secundarios' => [$g['biceps']],
            ],
            [
                'ref'        => 'jalon',
                'nombre'     => 'Jalón',
                'equipo'     => EquipmentType::CABLE,
                'primarios'  => [$g['espalda']],
                'secundarios' => [$g['biceps']],
            ],
            [
                'ref'        => 'extension-triceps-v',
                'nombre'     => 'Extensión tríceps en V',
                'equipo'     => EquipmentType::CABLE,
                'primarios'  => [$g['triceps']],
                'secundarios' => [],
            ],
            [
                'ref'        => 'aductor-cerrar',
                'nombre'     => 'Aductor (cerrar)',
                'equipo'     => EquipmentType::MACHINE,
                'primarios'  => [$g['aductores']],
                'secundarios' => [],
            ],
            [
                'ref'        => 'prensa-discos',
                'nombre'     => 'Prensa discos',
                'equipo'     => EquipmentType::MACHINE,
                'primarios'  => [$g['cuadriceps']],
                'secundarios' => [$g['gluteos'], $g['isquiosurales']],
            ],
            [
                'ref'        => 'femoral-tumbado',
                'nombre'     => 'Femoral tumbado',
                'equipo'     => EquipmentType::MACHINE,
                'primarios'  => [$g['isquiosurales']],
                'secundarios' => [$g['gluteos']],
            ],
            [
                'ref'        => 'peso-muerto-convencional',
                'nombre'     => 'Peso muerto convencional',
                'equipo'     => EquipmentType::BARBELL,
                'primarios'  => [$g['isquiosurales']],
                'secundarios' => [$g['gluteos'], $g['espalda']],
            ],
            [
                'ref'        => 'elevaciones-laterales-pie',
                'nombre'     => 'Elevaciones laterales de pie con mancuernas',
                'equipo'     => EquipmentType::DUMBBELL,
                'primarios'  => [$g['hombros']],
                'secundarios' => [],
            ],
            [
                'ref'        => 'curl-barra-ez',
                'nombre'     => 'Curl estricto barra EZ',
                'equipo'     => EquipmentType::BARBELL,
                'primarios'  => [$g['biceps']],
                'secundarios' => [],
            ],
            [
                'ref'        => 'remo-mancuerna',
                'nombre'     => 'Remo mancuerna',
                'equipo'     => EquipmentType::DUMBBELL,
                'primarios'  => [$g['espalda']],
                'secundarios' => [$g['biceps']],
            ],
            [
                'ref'        => 'press-banca-multipower-30',
                'nombre'     => 'Press banca multipower 30° inclinado',
                'equipo'     => EquipmentType::SMITH,
                'primarios'  => [$g['pecho']],
                'secundarios' => [$g['hombros'], $g['triceps']],
            ],
            [
                'ref'        => 'skull-crash-mancuernas',
                'nombre'     => 'Skull crash mancuernas',
                'equipo'     => EquipmentType::DUMBBELL,
                'primarios'  => [$g['triceps']],
                'secundarios' => [],
            ],
            [
                'ref'        => 'remo-torax-apoyado',
                'nombre'     => 'Remo tórax apoyado (máquina)',
                'equipo'     => EquipmentType::MACHINE,
                'primarios'  => [$g['espalda']],
                'secundarios' => [$g['biceps']],
            ],
        ];

        foreach ($ejercicios as $datos) {
            $ejercicio = (new Exercise())
                ->setName($datos['nombre'])
                ->setEquipment($datos['equipo']);

            foreach ($datos['primarios'] as $musculo) {
                $ejercicio->addPrimaryMuscle($musculo);
            }
            foreach ($datos['secundarios'] as $musculo) {
                $ejercicio->addSecondaryMuscle($musculo);
            }

            $manager->persist($ejercicio);
            $this->addReference('exercise-' . $datos['ref'], $ejercicio);
        }
    }
}
