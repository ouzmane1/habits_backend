<?php

namespace App\Repository;

use App\Entity\Habits;
use App\Entity\Suivihabits;
use App\Entity\Users;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Suivihabits>
 */
class SuivihabitsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Suivihabits::class);
    }

    public function findCheckedDatesByUserAndHabit(Habits $habit): array
    {
        $results = $this->createQueryBuilder('s')
            ->select('s.date')
            ->where('s.habits_id = :habit')
            ->setParameter('habit', $habit)
            ->orderBy('s.date', 'DESC')
            ->getQuery()
            ->getResult();

        // $results est un tableau de tableau ['date' => DateTime], on transforme en tableau simple de DateTime
        return array_map(fn($r) => $r['date'], $results);
    }
    public function findByUserHabits(array $habitIds): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.habits_id IN (:ids)')
            ->setParameter('ids', $habitIds)
            ->getQuery()
            ->getResult();
    }
}
