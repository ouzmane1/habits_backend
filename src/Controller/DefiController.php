<?php

namespace App\Controller;

use App\Entity\Defi;
use App\Entity\DefiProgress;
use App\Entity\DefiUsers;
use App\Repository\DefiProgressRepository;
use App\Repository\DefiRepository;
use App\Service\DefiService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DefiController extends AbstractController
{
    #[Route('/create/defi', name: 'api_defi_create', methods: ['POST'])]
    public function createDefi(Request $request, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $defi = new Defi();
        $defi->setTitle($data['title']);
        $defi->setDescription($data['description']);
        $defi->setDateStart(new \DateTime($data['date_start']));
        $defi->setDateEnd(new \DateTime($data['date_end']));
        $defi->setCreateBy($this->getUser()->getUserIdentifier());

        // Validation des données
        $errors = $validator->validate($defi);

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }

            return $this->json([
                'errors' => $errorMessages
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $em->persist($defi);
        $em->flush();

        return $this->json(['message' => 'Défi créé avec succès.']);
    }

    #[Route('/defi', name: 'api_defi_list', methods: ['GET'])]
    public function listDefis(DefiRepository $defiRepo): JsonResponse
    {
        $defis = $defiRepo->findAll();
        $data = [];

        foreach ($defis as $defi) {
            $data[] = [
                'id' => $defi->getId(),
                'title' => $defi->getTitle(),
                'description' => $defi->getDescription(),
                'date_start' => $defi->getDateStart()->format('Y-m-d'),
                'date_end' => $defi->getDateEnd()->format('Y-m-d'),
                'created_by' => $defi->getCreateBy(),
            ];
        }

        return $this->json($data);
    }

    #[Route('/defi/{id}', name: 'api_defi_update', methods: ['PUT'])]
    public function updateDefi(int $id, Request $request, DefiRepository $defiRepo, EntityManagerInterface $em): JsonResponse
    {
        $defi = $defiRepo->find($id);
        if (!$defi) {
            return $this->json(['error' => 'Défi introuvable'], 404);
        }

        $data = json_decode($request->getContent(), true);

        $defi->setTitle($data['title'] ?? $defi->getTitle());
        $defi->setDescription($data['description'] ?? $defi->getDescription());

        if (isset($data['date_start'])) {
            $defi->setDateStart(new \DateTime($data['date_start']));
        }
        if (isset($data['date_end'])) {
            $defi->setDateEnd(new \DateTime($data['date_end']));
        }
        $defi->setCreateBy($this->getUser()->getUserIdentifier());

        $em->persist($defi);
        $em->flush();

        return $this->json(['message' => 'Défi mis à jour avec succès.']);
    }

    #[Route('/defi/{id}', name: 'api_defi_delete', methods: ['DELETE'])]
    public function deleteDefi(int $id, DefiRepository $defiRepo, EntityManagerInterface $em): JsonResponse
    {
        $defi = $defiRepo->find($id);
        if (!$defi) {
            return $this->json(['error' => 'Défi introuvable'], 404);
        }

        $em->remove($defi);
        $em->flush();

        return $this->json(['message' => 'Défi supprimé avec succès.']);
    }

    #[Route('/defi/{id}', name: 'api_defi_get', methods: ['GET'])]
    public function getDefi(int $id, DefiRepository $defiRepo): JsonResponse
    {
        $defi = $defiRepo->find($id);
        if (!$defi) {
            return $this->json(['error' => 'Défi introuvable'], 404);
        }

        return $this->json([
            'id' => $defi->getId(),
            'title' => $defi->getTitle(),
            'description' => $defi->getDescription(),
            'date_start' => $defi->getDateStart()->format('Y-m-d'),
            'date_end' => $defi->getDateEnd()->format('Y-m-d'),
            'created_by' => $defi->getCreateBy(),
        ]);
    }

    #[Route('/defi/{id}/join', name: 'api_defi_join', methods: ['POST'])]
    public function joinDefi(int $id, DefiRepository $defiRepo, EntityManagerInterface $em): JsonResponse
    {
        $defi = $defiRepo->find($id);
        if (!$defi) {
            return $this->json(['error' => 'Défi introuvable'], 404);
        }

        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Utilisateur non authentifié'], 401);
        }

        // Vérifie si l'utilisateur est déjà inscrit
        $defiUserRepo = $em->getRepository(DefiUsers::class);
        $existingDefiUser = $defiUserRepo->findOneBy(['users_id' => $user, 'defi_id' => $defi]);

        if ($existingDefiUser) {
            return $this->json(['message' => 'Vous êtes déjà inscrit à ce défi.']);
        }

        // Crée une nouvelle inscription
        $defiUser = new DefiUsers();
        $defiUser->setUsersId($user);
        $defiUser->setDefiId($defi);
        $defiUser->setDateInscription(new \DateTime());
        $defiUser->setPoint(0);
        $defiUser->setRanking(0);

        $em->persist($defiUser);
        $em->flush();

        return $this->json(['message' => 'Inscription au défi réussie.']);
    }


    #[Route('/defi/{id}/log-day', name: 'api_defi_log', methods: ['POST'])]
    public function logDefiDay(int $id, Request $request, DefiRepository $defiRepo, EntityManagerInterface $em, DefiService $defiService): JsonResponse
    {
        $defi = $defiRepo->find($id);
        if (!$defi) {
            return $this->json(['error' => 'Défi introuvable'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $date = new \DateTime($data['date'] ?? 'now');
        $user = $this->getUser();

        // Vérifie si la progression est déjà enregistrée
        $progressRepo = $em->getRepository(DefiProgress::class);
        $existing = $progressRepo->findOneBy([
            'user' => $user,
            'defi' => $defi,
            'date' => $date
        ]);

        if (!$existing) {
            $progress = new DefiProgress();
            $progress->setUserId($user);
            $progress->setDefi($defi);
            $progress->setDate($date);
            $progress->setFinish(true);

            $em->persist($progress);
            $em->flush();
        }

        // Met à jour les points et le classement
        $defiService->updateDefiPoints($user, $defi);
        $defiService->updateDefiRankings($defi);

        return $this->json(['message' => 'Jour marqué comme complété.']);
    }

    //voir les points et le rang d’un utilisateur dans un défi
    #[Route('/defi/{id}/stats', name: 'api_defi_user_stats', methods: ['GET'])]
    public function getUserDefiStats(
        int $id,
        DefiRepository $defiRepo,
        EntityManagerInterface $em
    ): JsonResponse {
        $user = $this->getUser();
        $defi = $defiRepo->find($id);

        if (!$defi) {
            return $this->json(['error' => 'Défi introuvable.'], 404);
        }

        $defiUser = $em->getRepository(DefiUsers::class)->findOneBy([
            'users_id' => $user,
            'defi_id' => $defi
        ]);

        if (!$defiUser) {
            return $this->json(['error' => 'Utilisateur non inscrit à ce défi.'], 403);
        }

        return $this->json([
            'defi' => $defi->getTitle(),
            'points' => $defiUser->getPoints(),
            'rank' => $defiUser->getRank(),
            'participationDate' => $defiUser->getParticipationDate()->format('Y-m-d'),
        ]);
    }

    //le classement complet d’un défi avec tous les participants
    #[Route('/defi/{id}/classement', name: 'api_defi_classement', methods: ['GET'])]
    public function getDefiClassement(
        int $id,
        DefiRepository $defiRepo,
        EntityManagerInterface $em
    ): JsonResponse {
        $defi = $defiRepo->find($id);

        if (!$defi) {
            return $this->json(['error' => 'Défi introuvable.'], 404);
        }

        $classement = $em->getRepository(DefiUsers::class)->createQueryBuilder('du')
            ->where('du.defi_id = :defi')
            ->setParameter('defi', $defi)
            ->orderBy('du.points', 'DESC')
            ->getQuery()
            ->getResult();

        $data = [];
        foreach ($classement as $index => $defiUser) {
            $data[] = [
                'position' => $index + 1,
                'username' => $defiUser->getUsersId()->getUsername(), // change si tu utilises un autre champ
                'points' => $defiUser->getPoints(),
                'rank' => $defiUser->getRank(),
            ];
        }

        return $this->json([
            'defi' => $defi->getTitle(),
            'classement' => $data,
        ]);
    }



}
