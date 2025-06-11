<?php

namespace App\Controller;

use App\Entity\Habits;
use App\Enum\FrequenceType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class HabitController extends AbstractController
{
    #[Route('create/habits', name: 'api_habit_create', methods: ['POST'])]
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
}
