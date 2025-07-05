<?php

namespace App\Repository;

use App\Entity\SuiviObjective;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SuiviObjective>
 */
class SuiviObjectiveRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SuiviObjective::class);
    }

   public function findByUserObjectives(array $objectiveIds): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.objective IN (:ids)')
            ->setParameter('ids', $objectiveIds)
            ->getQuery()
            ->getResult();
    }
}
