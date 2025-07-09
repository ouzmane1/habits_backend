<?php

namespace App\EventListener;

use App\Service\TokenService;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use App\Service\LogMongodb;
use Doctrine\ODM\MongoDB\DocumentManager as MongoDocumentManager;

class AuthenticationSuccessListener
{
    public function __construct(
        private TokenService $tokenService,
        private LogMongodb $logMongodb,
        private MongoDocumentManager $mongoDm) {}

    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event): void
    {
        $user = $event->getUser();
        if (!$user) return;

        $refreshToken = $this->tokenService->createRefreshToken($user);

        $data = $event->getData();
        $data['refresh_token'] = $refreshToken;
        $event->setData($data);

        // Logging de la connexion réussie
        $email = $user->getUserIdentifier();
        $this->logMongodb->logUserAction(
            $this->mongoDm,
            $email,
            'Connexion',
            true
        );
    }
}
