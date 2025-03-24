<?php

namespace App\Service;

use App\Entity\Transaction;
use App\Entity\GroupMembership;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Psr\Log\LoggerInterface;
use App\Entity\User;
use ApiPlatform\Metadata\Operation;

class TransactionService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    public function isUserPayerOrOwner(Transaction $transaction, User $user): bool
    {
        $group = $transaction->getGroup();
        $isOwner = $group->getOwner() === $user;
        $isPayer = $transaction->getPayer() === $user;

        return $isOwner || $isPayer;
    }
}
