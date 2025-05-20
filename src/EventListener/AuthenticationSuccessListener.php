<?php

namespace App\EventListener;

use App\Service\TokenService;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;

class AuthenticationSuccessListener
{
    public function __construct(private TokenService $tokenService) {}

    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event): void
    {
        $user = $event->getUser();
        if (!$user) return;

        $refreshToken = $this->tokenService->createRefreshToken($user);

        $data = $event->getData();
        $data['refresh_token'] = $refreshToken;
        $event->setData($data);
    }
}
