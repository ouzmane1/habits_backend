<?php

namespace App\Controller;


use App\Entity\RefreshToken;
use App\Entity\Users;
use App\Repository\RefreshTokenRepository;
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

class AuthController extends AbstractController
{
    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        JWTTokenManagerInterface $jwtManager,
        TokenService $tokenService
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $email = $data['email'] ?? null;
        $plainPassword = $data['password'] ?? null;
        $name = $data['name'] ?? null;

        $user = new Users();
        $user->setName($name);
        $user->setEmail($email);
        $user->setPassword($plainPassword);
        $user->setRoles(['ROLE_USER']);

        // Validation des propriétés (NotBlank, Length, Regex, etc.)
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
            return new JsonResponse(['errors' => $errorMessages], 400);
        }

        $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
        $em->persist($user);
        $em->flush();

        // Générer le JWT pour le nouvel utilisateur
        $token = $jwtManager->create($user);
        // Générer le refresh token
        $refreshToken = $tokenService->createRefreshToken($user);

        return new JsonResponse([
        'token' => $token,
        'refresh_token' => $refreshToken,
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
    public function logout(Request $request, RefreshTokenRepository $repo): JsonResponse
    {
        $refreshToken = $request->get('refresh_token');
        $token = $repo->findOneBy(['token' => $refreshToken]);
        if ($token) {
            $token->setRevoked(true);
            $repo->save($token, true);
        }

        return new JsonResponse(['message' => 'Logged out']);
    }

    #[Route('/api/me', name: 'api_me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        return new JsonResponse([
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'role' => $user->getRoles(),
        ]);
    }
}

