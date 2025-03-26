<?php

namespace App\Service;

use App\Entity\RefreshToken;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class RefreshTokenService
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function generateRefreshToken(User $user): RefreshToken
    {
        $refreshToken = new RefreshToken();
        $refreshToken->setUser($user);

        $maxAttempts = 5;
        $attempt = 0;

        do {
            if ($attempt++ >= $maxAttempts) {
                throw new \Exception('Could not generate a unique refresh token');
            }
            $token = bin2hex(random_bytes(64));
        } while ($this->refreshTokenExists($token));
    
        $refreshToken->setRefreshToken($token);
        $refreshToken->setValid(new \DateTime('+1 month'));

        $this->entityManager->persist($refreshToken);
        $this->entityManager->flush();

        return $refreshToken;
    }

    public function revokeRefreshToken(RefreshToken $refreshToken): void
    {
        $refreshToken->setValid(new \DateTime());
        $this->entityManager->flush();
    }

    public function isRefreshTokenValid(RefreshToken $refreshToken): bool
    {
        return $refreshToken->getValid() > new \DateTime();
    }

    public function refreshTokenExists(string $refreshToken): bool
    {
        return (bool) $this->entityManager
            ->getRepository(RefreshToken::class)
            ->findOneBy(['refreshToken' => $refreshToken]);
    }

    public function getUserFromRefreshToken(RefreshToken $refreshToken): ?User
    {
        return $refreshToken->getUser();
    }

    public function getRefreshToken(string $refreshToken): ?RefreshToken
    {
        return $this->entityManager
            ->getRepository(RefreshToken::class)
            ->findOneBy(['refreshToken' => $refreshToken]);
    }
}