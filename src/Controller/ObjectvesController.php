<?php

namespace App\Controller;

use App\Entity\Objectives;
use App\Entity\SuiviObjective;
use App\Repository\ObjectivesRepository;
use App\Repository\SuiviObjectiveRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ObjectvesController extends AbstractController
{
    #[Route('create/objective', name: 'api_objective_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return $this->json(['error' => 'Données JSON invalides.'], Response::HTTP_BAD_REQUEST);
        }

        $objectif = new Objectives();

        $objectif->setTitre($data['titre'] ?? '');
        $objectif->setDescription($data['description'] ?? '');
        $objectif->setDateStart(new \DateTime($data['date_start'] ?? 'now'));
        $objectif->setDateEnd(new \DateTime($data['date_end'] ?? 'now'));
        $objectif->setStatut('en cours');
        $objectif->setUsersId($this->getUser());

        $errors = $validator->validate($objectif);

        if (count($errors) > 0) {
            $errorMessages = [];

            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }

            return $this->json(['errors' => $errorMessages], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $em->persist($objectif);
        $em->flush();

        return $this->json([
            'message' => 'Objectif créé avec succès',
            'objectif' => [
                'id' => $objectif->getId(),
                'titre' => $objectif->getTitre(),
                'date_start' => $objectif->getDateStart()->format('Y-m-d'),
                'date_end' => $objectif->getDateEnd()->format('Y-m-d'),
            ]
        ], Response::HTTP_CREATED);
    }

    #[Route('/objective/{id}/log-day', name: 'api_objective_log', methods: ['POST'])]
    public function logDay(int $id, Request $request, EntityManagerInterface $em, ObjectivesRepository $objectiveRepo): JsonResponse
    {
        $objective = $objectiveRepo->find($id);
        if (!$objective) {
            return $this->json(['error' => 'Objective introuvable'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        $date = new \DateTime($data['date'] ?? 'now');

        $log = new SuiviObjective();
        $log->setObjective($objective);
        $log->setDate($date);

        $em->persist($log);
        $em->flush();

        return $this->json([
            'message' => 'Jour marqué comme complété',
            'date' => $log->getDate()->format('Y-m-d'),
        ]);
    }

    #[Route('/objective/{id}/progress', name: 'api_objective_progress', methods: ['GET'])]
    public function progress(int $id, ObjectivesRepository $objectiveRepo, SuiviObjectiveRepository $logRepo): JsonResponse
    {
        $objective = $objectiveRepo->find($id);
        if (!$objective) {
            return $this->json(['error' => 'Objective introuvable'], Response::HTTP_NOT_FOUND);
        }

        $start = $objective->getDateStart();
        $end = $objective->getDateEnd();
        $totalDays = $start->diff($end)->days + 1;

        $completedDays = count($logRepo->findBy(['objective' => $objective]));

        $progress = ($totalDays > 0) ? round(($completedDays / $totalDays) * 100, 2) : 0;

        return $this->json([
            'total_days' => $totalDays,
            'completed_days' => $completedDays,
            'progress' => $progress . '%'
        ]);
    }

    #[Route('/objectives/{id}', name: 'api_objective_update', methods: ['PUT'])]
    public function update(int $id, Request $request, EntityManagerInterface $em, ValidatorInterface $validator, ObjectivesRepository $repo): JsonResponse
    {
        $objective = $repo->find($id);

        if (!$objective) {
            return $this->json(['error' => 'Objectif introuvable'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        $objective->setTitre($data['titre'] ?? $objective->getTitre());
        $objective->setDescription($data['description'] ?? $objective->getDescription());
        $objective->setDateStart(new \DateTime($data['date_start'] ?? $objective->getDateStart()->format('Y-m-d')));
        $objective->setDateEnd(new \DateTime($data['date_end'] ?? $objective->getDateEnd()->format('Y-m-d')));

        $errors = $validator->validate($objective);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $em->flush();

        return $this->json(['message' => 'Objectif mis à jour avec succès']);
    }

    #[Route('/objectives/{id}', name: 'api_objective_delete', methods: ['DELETE'])]
    public function delete(int $id, EntityManagerInterface $em, ObjectivesRepository $repo): JsonResponse
    {
        $objective = $repo->find($id);

        if (!$objective) {
            return $this->json(['error' => 'Objectif introuvable'], Response::HTTP_NOT_FOUND);
        }

        $em->remove($objective);
        $em->flush();

        return $this->json(['message' => 'Objectif supprimé avec succès']);
    }

    #[Route('/objectives', name: 'api_objective_list', methods: ['GET'])]
    public function list(ObjectivesRepository $repo): JsonResponse
    {
        $user = $this->getUser();
        $objectives = $repo->findBy(['users_id' => $user]);

        $data = [];
        foreach ($objectives as $obj) {
            $data[] = [
                'id' => $obj->getId(),
                'titre' => $obj->getTitre(),
                'description' => $obj->getDescription(),
                'date_start' => $obj->getDateStart()->format('Y-m-d'),
                'date_end' => $obj->getDateEnd()->format('Y-m-d'),
                'progres' => $obj->getProgres(),
                'statut' => $obj->getStatut(),
            ];
        }

        return $this->json($data);
    }

    #[Route('/objectives/{id}', name: 'api_objective_show', methods: ['GET'])]
    public function show(int $id, ObjectivesRepository $repo): JsonResponse
    {
        $objective = $repo->find($id);

        if (!$objective || $objective->getUsersId() !== $this->getUser()) {
            return $this->json(['error' => 'Objectif introuvable ou accès non autorisé'], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'id' => $objective->getId(),
            'titre' => $objective->getTitre(),
            'description' => $objective->getDescription(),
            'date_start' => $objective->getDateStart()->format('Y-m-d'),
            'date_end' => $objective->getDateEnd()->format('Y-m-d'),
            'progres' => $objective->getProgres(),
            'statut' => $objective->getStatut(),
        ]);
    }
    



}
