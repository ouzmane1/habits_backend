<?php

namespace App\Service;

use App\Entity\Defi;
use App\Entity\DefiProgress;
use App\Entity\DefiUsers;
use App\Entity\Users;
use App\Repository\DefiUsersRepository;
use App\Repository\SuivihabitsRepository;
use Doctrine\ORM\EntityManagerInterface;

class DefiService
{
    private EntityManagerInterface $em;
    private DefiUsersRepository $defiUsersRepo;
    private SuivihabitsRepository $suiviHabitRepo;

    public function __construct(
        EntityManagerInterface $em,
        DefiUsersRepository $defiUsersRepo,
        SuivihabitsRepository $suiviHabitRepo
    ) {
        $this->em = $em;
        $this->defiUsersRepo = $defiUsersRepo;
        $this->suiviHabitRepo = $suiviHabitRepo;
    }

   public function updateDefiPoints(Users $user, Defi $defi): void
    {
        $defiUser = $this->em->getRepository(DefiUsers::class)->findOneBy([
            'users_id' => $user,
            'defi_id' => $defi,
        ]);

        if (!$defiUser) return;

        $points = $this->em->getRepository(DefiProgress::class)->count([
            'user' => $user,
            'defi' => $defi,
            'done' => true,
        ]);

        $defiUser->setPoints($points);
        $this->em->persist($defiUser);
        $this->em->flush();
    }

    public function updateDefiRankings(Defi $defi): void
    {
        $defiUsers = $this->em->getRepository(DefiUsers::class)->findBy([
            'defi_id' => $defi,
        ]);

        usort($defiUsers, fn($a, $b) => $b->getPoints() <=> $a->getPoints());

        foreach ($defiUsers as $index => $defiUser) {
            $defiUser->setRank($index + 1);
            $this->em->persist($defiUser);
        }

        $this->em->flush();
    }



}
