<?php

namespace App\Service;

use App\Entity\RefreshToken;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\RefreshTokenRepository;

/**
 * 
 * This class handles operations related to refresh tokens including generation,
 * validation, and revocation of refresh tokens for authentication purposes.
 * 
 */
class RefreshTokenService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RefreshTokenRepository $refreshTokenRepository
    ) {}

    public function generateRefreshToken(User $user): RefreshToken
    {
        $refreshToken = new RefreshToken();
        $refreshToken->setUser($user);

        $maxAttempts = 5;
        $attempt = 0;

        do {
            if ($attempt++ >= $maxAttempts) {
                throw new \InvalidArgumentException('Could not generate a unique refresh token.');
            }
            $token = bin2hex(random_bytes(64));
        } while ($this->refreshTokenRepository->findOneByToken($token));
    
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
}