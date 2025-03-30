<?php

namespace App\Service;

use App\Entity\Transaction;
use App\Entity\GroupMembership;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Psr\Log\LoggerInterface;
use App\Entity\User;
use ApiPlatform\Metadata\Operation;
use App\Dto\TransactionRequest;
use App\Entity\Currency;
use App\Entity\Group;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;

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

    public function createOrUpdateTransactionFromRequest(
        TransactionRequest $request,
        Group $group,
        ?Transaction $existingTransaction = null
    ): Transaction {
        // Walidacja i pobranie waluty
        $currency = $this->entityManager->getRepository(Currency::class)->find($request->currencyId);
        if (!$currency) {
            throw new InvalidArgumentException('Currency not found.');
        }

        // Walidacja i pobranie płatnika
        $payer = $this->entityManager->getRepository(User::class)->find($request->payerId);
        if (!$payer) {
            throw new InvalidArgumentException('Payer not found.');
        }

        // Walidacja i pobranie odbiorców
        $payees = $this->entityManager->getRepository(User::class)->findBy(['id' => $request->payeesIds]);
        if (count($payees) !== count($request->payeesIds)) {
            throw new InvalidArgumentException('One or more payees not found.');
        }

        // Utwórz nową lub zaktualizuj istniejącą transakcję
        $transaction = $existingTransaction ?? new Transaction();
        
        if (!$existingTransaction) {
            $transaction->setGroup($group);
        }

        // Ustawienie właściwości transakcji
        $transaction->setName($request->name);
        $transaction->setAmount($request->amount);
        $transaction->setCurrency($currency);
        $transaction->setPayer($payer);

        // Czyszczenie i dodanie nowych odbiorców (dla PATCH)
        if ($existingTransaction) {
            foreach ($transaction->getPayees() as $existingPayee) {
                $transaction->removePayee($existingPayee);
            }
        }

        foreach ($payees as $payee) {
            $transaction->addPayee($payee);
        }

        return $transaction;
    }
}
