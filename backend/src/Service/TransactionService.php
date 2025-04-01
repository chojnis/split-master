<?php

namespace App\Service;

use App\Entity\Transaction;
use App\Entity\GroupMembership;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use App\Entity\User;
use ApiPlatform\Metadata\Operation;
use App\Dto\TransactionRequest;
use App\Entity\Currency;
use App\Entity\Group;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;
use App\Repository\TransactionRepository;
use App\Repository\CurrencyRepository;
use App\Repository\UserRepository;
use App\Service\CurrencyExchangeService;

class TransactionService
{
    public function __construct(
        private TransactionRepository $transactionRepository,
        private CurrencyRepository $currencyRepository,
        private UserRepository $userRepository,
        private CurrencyExchangeService $currencyExchangeService,
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
        $this->entityManager->beginTransaction();
        
        try {
            $transaction = $existingTransaction ?? new Transaction();
            
            if (!$existingTransaction) {
                $transaction->setGroup($group);
            }

            $this->updateTransactionFromRequest($transaction, $request);
            
            $this->entityManager->persist($transaction);
            $this->entityManager->flush();
            $this->entityManager->commit();
            
            return $transaction;
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }

    private function updateTransactionFromRequest(Transaction $transaction, TransactionRequest $request): void
    {
        $currency = $this->currencyRepository->find($request->currencyId);
        if (!$currency) {
            throw new InvalidArgumentException('Currency not found.');
        }

        $payer = $this->userRepository->find($request->payerId);
        if (!$payer) {
            throw new InvalidArgumentException('Payer not found.');
        }

        $payees = $this->userRepository->findBy(['id' => $request->payeesIds]);
        if (count($payees) !== count($request->payeesIds)) {
            throw new InvalidArgumentException('One or more payees not found.');
        }

        // if no exchange rate, get it from currency exchange service
        if ($request->exchangeRate === null) {
            $exchangeRate = $this->currencyExchangeService->getExchangeRate(
                $currency->getCode(),
                $transaction->getGroup()->getCurrency()->getCode(),
                new \DateTime()
            );
        } else {
            $exchangeRate = $request->exchangeRate;
        }

        $transaction
            ->setName($request->name)
            ->setAmount($request->amount)
            ->setCurrency($currency)
            ->setPayer($payer)
            ->setExchangeRate($exchangeRate);

        foreach ($transaction->getPayees() as $payee) {
            $transaction->removePayee($payee);
        }

        foreach ($payees as $payee) {
            $transaction->addPayee($payee);
        }
    }
}
