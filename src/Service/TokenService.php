<?php

namespace App\Service;

use App\Entity\RefreshToken;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

class TokenService
{
    public function __construct(private EntityManagerInterface $em) {}

    public function createRefreshToken($user): string
    {
        // $repo = $this->em->getRepository(RefreshToken::class);
        // $oldTokens = $repo->findBy(['user_id' => $user]);
        // foreach ($oldTokens as $token) {
        //     $this->em->remove($token);
        // }
        // $this->em->flush();

        $refresh = new RefreshToken();
        $refresh->setToken(Uuid::v4()->toRfc4122());
        $refresh->setUserId($user);
        $refresh->setCreatedAt(new \DateTimeImmutable());
        $refresh->setExpiresAt((new \DateTimeImmutable())->modify('+7 days'));
        $refresh->setRevoked(false);

        $this->em->persist($refresh);
        $this->em->flush();

        return $refresh->getToken();
    }
}
