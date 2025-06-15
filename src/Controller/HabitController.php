<?php

namespace App\Controller;

use App\Entity\Habits;
use App\Entity\Suivihabits;
use App\Entity\Users;
use App\Enum\FrequenceType;
use App\Repository\HabitsRepository;
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




}
