<?php

namespace App\Service;
use App\Entity\Badge;
use App\Entity\Badges;
use App\Entity\Habits;
use App\Entity\Suivihabits;
use App\Entity\Users;
use App\Repository\SuivihabitsRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\console_log;

class BadgeService
{
    private EntityManagerInterface $em;
    private SuivihabitsRepository $suiviHabitRepository;

    public function __construct(EntityManagerInterface $em, SuivihabitsRepository $suiviHabitRepository)
    {
        $this->em = $em;
        $this->suiviHabitRepository = $suiviHabitRepository;
    }
    private array $badgeCodes = [
        'first_habit',
        'weekend_warrior',
        '7_day_streak',
        'month_master',
        'daily_five',
        'legend_100',
    ];

    /**
     * Attribue un badge à un utilisateur si il ne l'a pas déjà.
     */
    public function awardBadge(Users $user, string $badgeCode): bool
    {
        // Vérifie si l'utilisateur a déjà ce badge
        foreach ($user->getBadges() as $badge) {
            if ($badge->getCode() === $badgeCode) {
                return false; // badge déjà attribué
            }
        }

        $badge = $this->em->getRepository(Badges::class)->findOneBy(['code' => $badgeCode]);
        if (!$badge) {
            throw new \Exception("Badge non trouvé : $badgeCode");
        }

        $user->addBadge($badge);
        $this->em->persist($user);
        $this->em->flush();

        return true;
    }
    /**
     * Calcule le nombre de jours consécutifs où l'utilisateur a coché l'habitude.
     * Commence par aujourd'hui et compte les jours précédents jusqu'à ce qu'il y ait une interruption.
     */
    public function getConsecutiveDays(Users $user, Habits $habit): int
    {
        $checkedDates = $this->suiviHabitRepository->findCheckedDatesByUserAndHabit($user, $habit);

        if (empty($checkedDates)) {
            return 0;
        }

        $count = 0;
        $prevDate = null;

        $today = new \DateTimeImmutable('today');

        foreach ($checkedDates as $date) {
            // Convertit en DateTimeImmutable pour manipuler les dates facilement
            $date = \DateTimeImmutable::createFromMutable($date);

            if ($count === 0) {
                // Le premier jour doit être aujourd'hui pour commencer la série
                if ($date != $today) {
                    break;
                }
                $count++;
                $prevDate = $date;
                continue;
            }

            // Vérifie que la date actuelle est exactement la veille du précédent jour compté
            $expectedDate = $prevDate->sub(new \DateInterval('P1D'));

            if ($date == $expectedDate) {
                $count++;
                $prevDate = $date;
            } else {
                break;
            }
        }

        return $count;
    }

    /**
     * Vérifie si l'utilisateur a une série de 7 jours consécutifs pour l'habitude donnée.
     * Si oui, attribue le badge correspondant.
     */
    public function checkAndAward7DayStreak(Users $user, Habits $habit): bool
    {
        $consecutiveDays = $this->getConsecutiveDays($user, $habit);
        error_log("Consecutive days : $consecutiveDays");
        if ($consecutiveDays >= 7) {
            return $this->awardBadge($user, '7_day_streak');
        }

        return false;
    }

    public function checkPremierPas(Users $user): void
    {
        $badgeRepo = $this->em->getRepository(Badges::class);
        $badgeCode = 'first_habit'; // Le code unique de ton badge "Premier Pas"
        $badge = $badgeRepo->findOneBy(['code' => $badgeCode]);

        if (!$badge) {
            // Si le badge n'existe pas en base, on arrête la fonction
            return;
        }

        // Si l'utilisateur a déjà ce badge, on ne fait rien
        if ($user->getBadges()->contains($badge)) {
            return;
        }

        // Vérifie si l'utilisateur a complété au moins une habitude
        $habitsCompletedCount = $this->suiviHabitRepository->countHabitsCompletedByUser($user);

        if ($habitsCompletedCount > 0) {
            // Attribue le badge
            $this->awardBadge($user, $badgeCode);
        }
    }

}
