<?php

namespace App\Controller;


use App\Entity\RefreshToken;
use App\Entity\Users;
use App\Repository\RefreshTokenRepository;
use App\Repository\SuivihabitsRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use App\Service\TokenService;
use App\Service\LogMongodb;
use Doctrine\ODM\MongoDB\DocumentManager as MongoDocumentManager;

class AuthController extends AbstractController
{
    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        JWTTokenManagerInterface $jwtManager,
        TokenService $tokenService,
        LogMongodb $logMongodb,
        MongoDocumentManager $mongoDm
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $email = $data['email'] ?? null;
        $plainPassword = $data['password'] ?? null;
        $name = $data['name'] ?? null;

        $user = new Users();
        $user->setName($name);
        $user->setEmail($email);
        $user->setPassword($plainPassword);
        if ($this->isGranted('ROLE_ADMIN') && isset($data['roles'])) {
        $user->setRoles($data['roles']);
        } else {
            $user->setRoles([Users::ROLE_USER]);
        }

        // Validation des propriétés 
        $propertyErrors = $validator->validate($user, null, ['Default']);

        // Si aucune erreur sur les propriétés, alors on vérifie l’unicité
        if (count($propertyErrors) === 0) {
            $uniqueErrors = $validator->validate($user, null, ['Default', 'UniqueEntity']);
        } else {
            $uniqueErrors = [];
        }

        // Sinon Fusionne les erreurs
        $allErrors = array_merge(iterator_to_array($propertyErrors), iterator_to_array($uniqueErrors));

        if (count($allErrors) > 0) {
            $errorMessages = [];
            foreach ($allErrors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            // Log de l’échec d’inscription
            $logMongodb->logUserAction(
                $mongoDm,
                $email ?? 'inconnu',
                'Inscription',
                false
            );
            return new JsonResponse(['errors' => $errorMessages], 400);
        }

        $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
        $em->persist($user);
        $em->flush();

        // Log de la réussite d’inscription
        $logMongodb->logUserAction(
            $mongoDm,
            $email,
            'Inscription',
            true
        );
        // Génére le JWT pour le nouvel utilisateur
        $token = $jwtManager->create($user);
        // Génére le refresh token
        $refreshToken = $tokenService->createRefreshToken($user);

        return new JsonResponse([
        'token' => $token,
        'refresh_token' => $refreshToken,
        'roles' => $user->getRoles(),
        'message' => 'Inscription réussie et authentification automatique.'
        ]);

        return new JsonResponse(['message' => 'Inscription réussie !'], 201);
    }

    #[Route('/api/token/refresh', name: 'api_token_refresh', methods: ['POST'])]
    public function refresh(
        Request $request,
        RefreshTokenRepository $refreshTokenRepository,
        JWTTokenManagerInterface $jwtManager
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $refreshToken = $data['refresh_token'] ?? null;

        if (!$refreshToken) {
            return new JsonResponse(['message' => 'Missing refresh token'], 400);
        }

        $tokenEntity = $refreshTokenRepository->findValidToken($refreshToken);


        if (!$tokenEntity) {
            return new JsonResponse(['message' => 'Invalid refresh token'], 401);
        }

        $user = $tokenEntity->getUserId();
        $newJwt = $jwtManager->create($user);

        return new JsonResponse([
            'token' => $newJwt,
        ]);
    }
    #[Route('/api/logout', name: 'api_logout', methods: ['POST'])]
    public function logout(
        Request $request,
        RefreshTokenRepository $repo,
        LogMongodb $logMongodb,
        MongoDocumentManager $mongoDm
    ): JsonResponse {
        $refreshToken = $request->get('refresh_token');
        $token = $repo->findOneBy(['token' => $refreshToken]);
        $user = $this->getUser();
        $email = $user ? $user->getUserIdentifier() : 'unknown';

        if ($token) {
            $token->setRevoked(true);
            $repo->save($token, true);
        }

        // Log de la tentative de déconnexion
        $logMongodb->logUserAction(
            $mongoDm,
            $email,
            'Déconnexion',
            true
        );

        return new JsonResponse(['message' => 'Invalid refresh token'], 400);
    }

    #[Route('/api/me', name: 'api_me', methods: ['GET'])]
    public function me(
        SuivihabitsRepository $suiviHabitsRepo,
    ): JsonResponse {
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        // Compter les habitudes complétées (liées à l'utilisateur via habits)
        $completedHabits = $suiviHabitsRepo->createQueryBuilder('s')
            ->join('s.habits_id', 'h')
            ->where('s.finish = true')
            ->andWhere('h.users_id = :user')
            ->setParameter('user', $user)
            ->select('COUNT(s.id)')
            ->getQuery()
            ->getSingleScalarResult();

        // Nombre de badges associés à l'utilisateur (relation ManyToMany)
        $badgesCount = count($user->getBadges());

        return new JsonResponse([
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
            'completed_habits' => (int) $completedHabits,
            'badges_count' => $badgesCount,
        ]);
    }

    #[Route('/api/me/update', name: 'api_me_update', methods: ['PUT'])]
    public function updateMe(
        Request $request,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ): JsonResponse {
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        $data = json_decode($request->getContent(), true);

        $email = $data['email'] ?? null;
        $name = $data['name'] ?? null;

        if ($email) {
            $user->setEmail($email);
        }

        if ($name) {
            $user->setName($name);
        }

        // Valider les propriétés modifiées
        $errors = $validator->validate($user);

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }

            return $this->json(['errors' => $errorMessages], 400);
        }

        $em->persist($user);
        $em->flush();

        return $this->json([
            'message' => 'Informations mises à jour avec succès.',
            'user' => [
                'name' => $user->getName(),
                'email' => $user->getEmail(),
            ]
        ]);
    }


}

