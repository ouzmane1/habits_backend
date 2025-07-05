<?php

namespace App\Repository;

use App\Entity\Defi;
use App\Entity\DefiProgress;
use App\Entity\Users;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DefiProgress>
 */
class DefiProgressRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DefiProgress::class);
    }
    public function countCompletedDays(Users $user, Defi $defi): int
    {
        return $this->createQueryBuilder('dp')
            ->select('COUNT(DISTINCT dp.date)')
            ->andWhere('dp.user_id = :user')
            ->andWhere('dp.defi = :defi')
            ->andWhere('dp.finish = true')
            ->setParameter('user', $user)
            ->setParameter('defi', $defi)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
