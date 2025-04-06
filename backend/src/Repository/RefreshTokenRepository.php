<?php

namespace App\Repository;

use App\Entity\RefreshToken;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RefreshToken>
 */
class RefreshTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RefreshToken::class);
    }

    public function findOneByToken(string $refreshToken): ?RefreshToken
    {
        return $this->findOneBy(['refreshToken' => $refreshToken]);
    }

    public function findValidOneByToken(string $refreshToken): ?RefreshToken
    {
        return $this->createQueryBuilder('r')
            ->where('r.refreshToken = :token')
            ->andWhere('r.valid > :now')
            ->setParameter('token', $refreshToken)
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getOneOrNullResult();
    }
}
