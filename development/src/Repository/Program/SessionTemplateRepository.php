<?php

declare(strict_types=1);

namespace App\Repository\Program;

use App\Entity\Program\Mesocycle;
use App\Entity\Program\SessionTemplate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SessionTemplate>
 */
class SessionTemplateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SessionTemplate::class);
    }

    /**
     * Carga una sesión con todos sus bloques, ejercicios planificados y series.
     * Es la query más pesada del módulo de programación, pero se ejecuta solo
     * una vez al precargar la sesión del día (SessionPreloader).
     *
     * Usa múltiples LEFT JOIN FETCH para evitar el problema N+1 en colecciones anidadas.
     * Doctrine ejecuta esto como una sola query SQL con múltiples JOINs.
     */
    public function findOneWithFullPlan(string $id): ?SessionTemplate
    {
        return $this->createQueryBuilder('st')
            ->leftJoin('st.exerciseBlocks', 'eb')
            ->leftJoin('eb.plannedExercises', 'pe')
            ->leftJoin('pe.plannedSets', 'ps')
            ->leftJoin('pe.exercise', 'ex')
            ->leftJoin('eb.weeklyMetabolicPlans', 'wmp')
            ->leftJoin('eb.weeklyCardioPlans', 'wcp')
            ->addSelect('eb', 'pe', 'ps', 'ex', 'wmp', 'wcp')
            ->where('st.id = :id')
            ->setParameter('id', $id)
            ->orderBy('eb.sortOrder', 'ASC')
            ->addOrderBy('pe.sortOrder', 'ASC')
            ->addOrderBy('ps.sortOrder', 'ASC')
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Devuelve las plantillas de sesión de un mesociclo, ordenadas.
     *
     * @return SessionTemplate[]
     */
    public function findByMesocycle(Mesocycle $mesocycle): array
    {
        return $this->createQueryBuilder('st')
            ->where('st.mesocycle = :mesocycle')
            ->setParameter('mesocycle', $mesocycle)
            ->orderBy('st.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
