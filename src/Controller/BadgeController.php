<?php

namespace App\Controller;

use App\Entity\Badges;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class BadgeController extends AbstractController
{

    #[Route('/api/badges/user', name: 'api_user_badges', methods: ['GET'])]
    public function userBadges(): JsonResponse
    {
        $user = $this->getUser();
        $badges = $user->getBadges();

        $data = array_map(function (Badges $badge) {
            return [
                'title' => $badge->getTitle(),
                'description' => $badge->getDescription(),
                'icon' => $badge->getIcon(),
                'code' => $badge->getCode()
            ];
        }, $badges->toArray());

        return $this->json($data);
    }

    #[Route('/api/create/badges', name: 'api_badges_create', methods: ['POST'])]
    public function createBadge(Request $request, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $badge = new Badges();
        $badge->setTitle($data['title'] ?? '');
        $badge->setDescription($data['description'] ?? '');
        $badge->setCode($data['code'] ?? '');
        $badge->setIcon($data['icon'] ?? '');

        $errors = $validator->validate($badge);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }

            return $this->json(['errors' => $errorMessages], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $em->persist($badge);
        $em->flush();

        return $this->json([
            'message' => 'Badge créé avec succès',
            'badge' => [
                'id' => $badge->getId(),
                'title' => $badge->getTitle(),
                'description' => $badge->getDescription(),
                'code' => $badge->getCode(),
                'icon' => $badge->getIcon(),
            ]
        ], Response::HTTP_CREATED);
    }

    #[Route('/api/badges', name: 'api_badge_list', methods: ['GET'])]
    public function listBadges(EntityManagerInterface $em): JsonResponse
    {
        $badges = $em->getRepository(Badges::class)->findAll();
        $data = [];

        foreach ($badges as $badge) {
            $data[] = [
                'id' => $badge->getId(),
                'title' => $badge->getTitle(),
                'description' => $badge->getDescription(),
                'code' => $badge->getCode(),
                'icon' => $badge->getIcon(),
            ];
        }

        return $this->json($data);
    }

    #[Route('/api/badges/{id}', name: 'api_badge_update', methods: ['PUT'])]
    public function updateBadge(int $id, Request $request, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
    {
        $badge = $em->getRepository(Badges::class)->find($id);

        if (!$badge) {
            return $this->json(['error' => 'Badge introuvable'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        $badge->setTitle($data['title'] ?? $badge->getTitle());
        $badge->setDescription($data['description'] ?? $badge->getDescription());
        $badge->setCode($data['code'] ?? $badge->getCode());
        $badge->setIcon($data['icon'] ?? $badge->getIcon());

        $errors = $validator->validate($badge);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $em->flush();

        return $this->json(['message' => 'Badge mis à jour avec succès']);
    }

    #[Route('/api/badges/{id}', name: 'api_badge_delete', methods: ['DELETE'])]
    public function deleteBadge(int $id, EntityManagerInterface $em): JsonResponse
    {
        $badge = $em->getRepository(Badges::class)->find($id);

        if (!$badge) {
            return $this->json(['error' => 'Badge introuvable'], Response::HTTP_NOT_FOUND);
        }

        $em->remove($badge);
        $em->flush();

        return $this->json(['message' => 'Badge supprimé avec succès']);
    }

}
