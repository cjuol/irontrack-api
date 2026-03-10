<?php

declare(strict_types=1);

namespace App\Repository\Program;

use App\Entity\Program\Mesocycle;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Mesocycle>
 */
class MesocycleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Mesocycle::class);
    }

    /**
     * Devuelve todos los mesociclos del usuario ordenados del más reciente al más antiguo.
     *
     * @return Mesocycle[]
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.user = :user')
            ->setParameter('user', $user)
            ->orderBy('m.startDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Devuelve el mesociclo activo del usuario en una fecha dada.
     * Un mesociclo está activo si startDate <= fecha <= endDate.
     *
     * Usado por TrainingDayService para asignar el stepGoal correcto.
     */
    public function findActiveForUser(User $user, \DateTimeImmutable $date): ?Mesocycle
    {
        return $this->createQueryBuilder('m')
            ->where('m.user = :user')
            ->andWhere('m.startDate <= :date')
            ->andWhere('m.endDate >= :date')
            ->setParameter('user', $user)
            ->setParameter('date', $date)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Carga un mesociclo con todas sus sesiones y bloques en la mínima cantidad
     * de queries posible (JOIN FETCH en cascada).
     *
     * Usado por el endpoint GET /api/v1/mesocycles/{id}
     */
    public function findOneWithFullStructure(string $id, User $user): ?Mesocycle
    {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.sessionTemplates', 'st')
            ->leftJoin('st.exerciseBlocks', 'eb')
            ->addSelect('st', 'eb')
            ->where('m.id = :id')
            ->andWhere('m.user = :user')
            ->setParameter('id', $id)
            ->setParameter('user', $user)
            ->orderBy('st.sortOrder', 'ASC')
            ->addOrderBy('eb.sortOrder', 'ASC')
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Devuelve el mesociclo más reciente del usuario.
     * Útil como fallback cuando no hay mesociclo activo en la fecha dada.
     */
    public function findLatestForUser(User $user): ?Mesocycle
    {
        return $this->createQueryBuilder('m')
            ->where('m.user = :user')
            ->setParameter('user', $user)
            ->orderBy('m.startDate', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
