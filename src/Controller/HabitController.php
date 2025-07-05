<?php

namespace App\Controller;

use App\Entity\Habits;
use App\Entity\Suivihabits;
use App\Entity\SuiviObjective;
use App\Entity\Users;
use App\Enum\FrequenceType;
use App\Repository\HabitsRepository;
use App\Repository\ObjectivesRepository;
use App\Repository\SuivihabitsRepository;
use App\Repository\SuiviObjectiveRepository;
use App\Service\BadgeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class HabitController extends AbstractController
{
    #[Route('/api/create/habits', name: 'api_habit_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return $this->json(['error' => 'Données JSON invalides.'], Response::HTTP_BAD_REQUEST);
        }

        $habit = new Habits();

        $habit->setTitle($data['title'] ?? '');
        $habit->setDescription($data['description'] ?? '');

        // Gestion de la fréquence
        if (isset($data['frequence'])) {
            try {
                $frequenceEnum = FrequenceType::from($data['frequence']);
                $habit->setFrequence($frequenceEnum);
            } catch (\ValueError $e) {
                // mauvaise valeur = laisser null → déclenchera la contrainte @Assert\NotNull
            }
        }

        $habit->setStatut('en cours');
        $habit->setUsersId($this->getUser());

        // Verrification des erreurs de validation
        $errors = $validator->validate($habit);

        if (count($errors) > 0) {
            $errorMessages = [];

            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }

            return $this->json(['errors' => $errorMessages], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $em->persist($habit);
        $em->flush();

        return $this->json([
            'message' => 'Habitude créée avec succès',
            'habit' => [
                'id' => $habit->getId(),
                'title' => $habit->getTitle(),
                'frequence' => $habit->getFrequence()->value,
            ]
        ], Response::HTTP_CREATED);
    }

    #[Route('/api/habit/{id}/log-day', name: 'api_habit_log', methods: ['POST'])]
    public function logDay(int $id, Request $request, EntityManagerInterface $em, HabitsRepository $habitRepo, BadgeService $badgeService): JsonResponse
    {
        $habit = $habitRepo->find($id);
        if (!$habit) {
            return $this->json(['error' => 'habitude introuvable'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        $date = new \DateTime($data['date'] ?? 'now');

        $log = new Suivihabits();
        $log->setHabitsId($habit);
        $log->setDate($date);
        $log->setFinish(true); // Marquer comme complété

        $em->persist($log);
        $em->flush();

        $user = $this->getUser();
        $badgeService->checkPremierPas($user);

        $badgeService->checkAndAward7DayStreak($user, $habit);

        return $this->json([
            'message' => 'Jour marqué comme complété',
            'date' => $log->getDate()->format('Y-m-d'),
        ]);
    }

    #[Route('/api/habits/{id}', name: 'api_habit_update', methods: ['PUT'])]
    public function update(int $id, Request $request, EntityManagerInterface $em, HabitsRepository $repo, ValidatorInterface $validator): JsonResponse
    {
        $habit = $repo->find($id);

        if (!$habit || $habit->getUsersId() !== $this->getUser()) {
            return $this->json(['error' => 'Habitude introuvable ou accès non autorisé'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        $habit->setTitle($data['title'] ?? $habit->getTitle());
        $habit->setDescription($data['description'] ?? $habit->getDescription());

        try {
            if (isset($data['frequence'])) {
                $habit->setFrequence(FrequenceType::from($data['frequence']));
            }
        } catch (\ValueError $e) {
            return $this->json(['error' => 'Fréquence invalide'], Response::HTTP_BAD_REQUEST);
        }

        $errors = $validator->validate($habit);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $em->flush();

        return $this->json(['message' => 'Habitude mise à jour avec succès']);
    }

    #[Route('/api/habits/{id}', name: 'api_habit_delete', methods: ['DELETE'])]
    public function delete(int $id, EntityManagerInterface $em, HabitsRepository $repo): JsonResponse
    {
        $habit = $repo->find($id);

        if (!$habit || $habit->getUsersId() !== $this->getUser()) {
            return $this->json(['error' => 'Habitude introuvable ou accès non autorisé'], Response::HTTP_NOT_FOUND);
        }

        $em->remove($habit);
        $em->flush();

        return $this->json(['message' => 'Habitude supprimée avec succès']);
    }

    #[Route('/api/habits', name: 'api_habit_list', methods: ['GET'])]
    public function list(HabitsRepository $repo): JsonResponse
    {
        $user = $this->getUser();
        $habits = $repo->findBy(['users_id' => $user]);

        $data = [];
        foreach ($habits as $habit) {
            $data[] = [
                'id' => $habit->getId(),
                'title' => $habit->getTitle(),
                'description' => $habit->getDescription(),
                'frequence' => $habit->getFrequence()->value,
                'statut' => $habit->getStatut(),
            ];
        }

        return $this->json($data);
    }

    #[Route('/api/habits/{id}', name: 'api_habit_show', methods: ['GET'])]
    public function show(int $id, HabitsRepository $repo): JsonResponse
    {
        $habit = $repo->find($id);

        if (!$habit || $habit->getUsersId() !== $this->getUser()) {
            return $this->json(['error' => 'Habitude introuvable ou accès non autorisé'], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'id' => $habit->getId(),
            'title' => $habit->getTitle(),
            'description' => $habit->getDescription(),
            'frequence' => $habit->getFrequence()->value,
            'statut' => $habit->getStatut(),
        ]);
    }

    #[Route('/api/today/tasks', name: 'api_today_tasks', methods: ['GET'])]
    public function getTodayTasks(
        HabitsRepository $habitsRepo,
        ObjectivesRepository $objectivesRepo,
        SuivihabitsRepository $suiviHabitudeRepo,
        SuiviObjectiveRepository $suiviObjectiveRepo
    ): JsonResponse {
        $user = $this->getUser();
        $today = new \DateTimeImmutable();
        $dayOfWeek = $today->format('N'); // 1 (lundi) à 7 (dimanche)
        $dayOfMonth = $today->format('j'); // 1 à 31

        $tasks = [];

        //  HABITUDES
        $habits = $habitsRepo->findBy(['users_id' => $user, 'statut' => 'en cours']);
        foreach ($habits as $habit) {
            $freq = $habit->getFrequence()->value;

            if (
                $freq === 'quotidien' ||
                ($freq === 'hebdomadaire' && $dayOfWeek == 1) || 
                ($freq === 'mensuel' && $dayOfMonth == 1)        
            ) {
                // Vérifie s’il y a un enregistrement dans SuiviHabits
                $alreadyDone = $suiviHabitudeRepo->findOneBy([
                    'habits_id' => $habit,
                    'date' => $today
                ]);

                $streak = $this->calculateStreakHabit($habit, $suiviHabitudeRepo, $today);
                $tasks[] = [
                    'id' => $habit->getId(),
                    'title' => $habit->getTitle(),
                    'type' => 'habit',
                    'frequence' => $habit->getFrequence()->value,
                    'date' => $today->format('Y-m-d'),
                    'streak' => $streak,
                    'completed' => $alreadyDone ? true : false,
                ];
            }
        }

        //  OBJECTIFS
        $objectives = $objectivesRepo->findBy(['users_id' => $user, 'statut' => 'en cours']);
        foreach ($objectives as $objective) {
            // Vérifie s’il y a un enregistrement dans SuiviObjective
            $alreadyDone = $suiviObjectiveRepo->findOneBy([
                'objective' => $objective,
                'date' => $today
            ]);
            $streak = $this->calculateStreakObjective($objective, $suiviObjectiveRepo, $today);
            $tasks[] = [
                'id' => $objective->getId(),
                'title' => $objective->getTitre(),
                'type' => 'objective',
                'date' => $today->format('Y-m-d'),
                'streak' => $streak,
                'completed' => $alreadyDone ? true : false,
            ];
        }

        return $this->json($tasks);
    }

    #[Route('/api/tasks/{type}/{id}/log-day', name: 'api_task_log', methods: ['POST'])]
    public function logTaskDay(
        string $type,
        int $id,
        Request $request,
        EntityManagerInterface $em,
        HabitsRepository $habitsRepo,
        ObjectivesRepository $objectivesRepo,
        BadgeService $badgeService
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $date = new \DateTime($data['date'] ?? 'now');
        $user = $this->getUser();

        if ($type === 'habit') {
            $habit = $habitsRepo->find($id);
            if (!$habit) {
                return $this->json(['error' => 'Habitude introuvable'], Response::HTTP_NOT_FOUND);
            }

            $log = new Suivihabits();
            $log->setHabitsId($habit);
            $log->setDate($date);
            $log->setFinish(true);

            $em->persist($log);
            $em->flush();

            $badgeService->checkPremierPas($user);
            $badgeService->checkAndAward7DayStreak($user, $habit);

            return $this->json([
                'message' => 'Habitude marquée comme complétée',
                'date' => $log->getDate()->format('Y-m-d'),
            ]);
        }

        if ($type === 'objective') {
            $objective = $objectivesRepo->find($id);
            if (!$objective) {
                return $this->json(['error' => 'Objectif introuvable'], Response::HTTP_NOT_FOUND);
            }

            $log = new SuiviObjective();
            $log->setObjective($objective);
            $log->setDate($date);

            $em->persist($log);
            $em->flush();

            return $this->json([
                'message' => 'Objectif marqué comme complété',
                'date' => $log->getDate()->format('Y-m-d'),
            ]);
        }

        return $this->json(['error' => 'Type de tâche inconnu'], Response::HTTP_BAD_REQUEST);
    }

    #[Route('/api/tasks/{type}/{id}/log-day', name: 'api_task_log_delete', methods: ['DELETE'])]
    public function deleteTaskLogDay(
        string $type,
        int $id,
        Request $request,
        EntityManagerInterface $em,
        HabitsRepository $habitsRepo,
        ObjectivesRepository $objectivesRepo,
        SuivihabitsRepository $suiviHabitsRepo,
        SuiviObjectiveRepository $suiviObjectivesRepo
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $date = new \DateTime($data['date'] ?? 'now');
        $user = $this->getUser();

        if ($type === 'habit') {
            $habit = $habitsRepo->find($id);
            if (!$habit || $habit->getUsersId() !== $user) {
                return $this->json(['error' => 'Habitude introuvable ou non autorisée'], Response::HTTP_NOT_FOUND);
            }

            $log = $suiviHabitsRepo->findOneBy([
                'habits_id' => $habit,
                'date' => $date,
            ]);

            if (!$log) {
                return $this->json(['message' => 'Aucun suivi trouvé pour cette habitude à cette date.'], Response::HTTP_NOT_FOUND);
            }

            $em->remove($log);
            $em->flush();

            return $this->json(['message' => 'Suivi de l\'habitude supprimé.']);
        }

        if ($type === 'objective') {
            $objective = $objectivesRepo->find($id);
            if (!$objective || $objective->getUsersId() !== $user) {
                return $this->json(['error' => 'Objectif introuvable ou non autorisé'], Response::HTTP_NOT_FOUND);
            }

            $log = $suiviObjectivesRepo->findOneBy([
                'objective' => $objective,
                'date' => $date,
            ]);

            if (!$log) {
                return $this->json(['message' => 'Aucun suivi trouvé pour cet objectif à cette date.'], Response::HTTP_NOT_FOUND);
            }

            $em->remove($log);
            $em->flush();

            return $this->json(['message' => 'Suivi de l\'objectif supprimé.']);
        }

        return $this->json(['error' => 'Type de tâche inconnu'], Response::HTTP_BAD_REQUEST);
    }

    #[Route('/api/user/completed-tasks', name: 'api_user_completed_tasks', methods: ['GET'])]
    public function getCompletedTasks(
        SuivihabitsRepository $suivihabitsRepo,
        SuiviObjectiveRepository $suiviObjectiveRepo
    ): JsonResponse {
        $user = $this->getUser();

        $habits = $user->getHabits();
        $habitIds = array_map(fn($h) => $h->getId(), $habits->toArray());

        $objectives = $user->getObjectives();
        $objectiveIds = array_map(fn($o) => $o->getId(), $objectives->toArray());

        $habitLogs = $suivihabitsRepo->findByUserHabits($habitIds);
        $objectiveLogs = $suiviObjectiveRepo->findByUserObjectives($objectiveIds);

        $completed = [];

        foreach ($habitLogs as $log) {
            $completed[] = [
                'date' => $log->getDate()->format('Y-m-d'),
                'type' => 'habit',
            ];
        }

        foreach ($objectiveLogs as $log) {
            $completed[] = [
                'date' => $log->getDate()->format('Y-m-d'),
                'type' => 'objective',
            ];
        }

        return $this->json($completed);
    }



    private function calculateStreakHabit($habit, SuivihabitsRepository $repo, \DateTimeImmutable $today): int
    {
        $streak = 0;
        $current = $today;

        while (true) {
            $log = $repo->findOneBy([
                'habits_id' => $habit,
                'date' => $current,
            ]);

            if ($log) {
                $streak++;
                $current = $current->modify('-1 day');
            } else {
                break;
            }
        }

        return $streak;
    }

    private function calculateStreakObjective($objective, SuiviObjectiveRepository $repo, \DateTimeImmutable $today): int
    {
        $streak = 0;
        $current = $today;

        while (true) {
            $log = $repo->findOneBy([
                'objective' => $objective,
                'date' => $current,
            ]);

            if ($log) {
                $streak++;
                $current = $current->modify('-1 day');
            } else {
                break;
            }
        }

        return $streak;
    }

}
