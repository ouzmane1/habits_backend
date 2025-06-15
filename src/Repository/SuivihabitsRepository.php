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

//    /**
//     * @return Suivihabits[] Returns an array of Suivihabits objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Suivihabits
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
    public function findCheckedDatesByUserAndHabit(Users $user, Habits $habit): array
    {
        $results = $this->createQueryBuilder('s')
            ->select('s.date')
            ->where('s.habit = :habit')
            ->andWhere('s.user = :user')
            ->setParameter('habit', $habit)
            ->setParameter('user', $user)
            ->orderBy('s.date', 'DESC')
            ->getQuery()
            ->getResult();

        // $results est un tableau de tableau ['date' => DateTime], on transforme en tableau simple de DateTime
        return array_map(fn($r) => $r['date'], $results);
    }
}
