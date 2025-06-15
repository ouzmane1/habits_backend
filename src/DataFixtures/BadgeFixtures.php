<?php

namespace App\DataFixtures;

use App\Entity\Badges;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class BadgeFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $badges = [
            [
                'title' => 'Premier Pas',
                'description' => 'Compléter sa première habitude',
                'icon' => ['fas', 'bullseye'],
                'code' => 'first_habit'
            ],
            [
                'title' => 'Guerrier du Week-end',
                'description' => 'Maintenir ses habitudes pendant un week-end complet',
                'icon' => ['fas', 'trophy'],
                'code' => 'weekend_warrior'
            ],
            [
                'title' => 'Flamme Ardente',
                'description' => 'Maintenir une série de 7 jours consécutifs',
                'icon' => ['fas', 'fire'],
                'code' => '7_day_streak'
            ],
            [
                'title' => 'Maître du Mois',
                'description' => 'Compléter toutes ses habitudes pendant 30 jours',
                'icon' => ['fas', 'calendar'],
                'code' => 'month_master'
            ],
            [
                'title' => 'Étoile Montante',
                'description' => 'Compléter 5 habitudes en une journée',
                'icon' => ['fas', 'star'],
                'code' => 'daily_five'
            ],
            [
                'title' => 'Légende Vivante',
                'description' => 'Maintenir une série de 100 jours consécutifs',
                'icon' => ['fas', 'trophy'],
                'code' => 'legend_100'
            ],
        ];

        foreach ($badges as $b) {
            $badge = new Badges();
            $badge->setTitle($b['title']);
            $badge->setDescription($b['description']);
            $badge->setIcon(implode(' ', $b['icon']));
            $badge->setCode($b['code']);
            $manager->persist($badge);
        }

        $manager->flush();
    }
}
