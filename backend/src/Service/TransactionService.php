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
use App\Entity\TransactionEntry;
use App\Entity\TransactionHistory;
use App\Service\GroupMembershipService;

class TransactionService
{
    public function __construct(
        private TransactionRepository $transactionRepository,
        private GroupMembershipService $groupMembershipService,
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

    public function createTransaction(TransactionRequest $request, Group $group): Transaction
    {
        $payer = $this->userRepository->find($request->payerId);
        if (!$payer) {
            throw new \InvalidArgumentException('Payer not found.');
        }

        $payees = $this->userRepository->findBy(['id' => $request->payeesIds]);
        if (count($payees) !== count($request->payeesIds)) {
            throw new \InvalidArgumentException('One or more payees not found.');
        }

        $currency = $this->currencyRepository->find($request->currencyId);
        if (!$currency) {
            throw new \InvalidArgumentException('Currency not found.');
        }

        if (!$this->groupMembershipService->isUserMemberOfGroup($payer, $group)) {
            throw new \InvalidArgumentException('Payer is not a member of the group.');
        }

        foreach ($payees as $payee) {
            if (!$this->groupMembershipService->isUserMemberOfGroup($payee, $group)) {
                throw new \InvalidArgumentException('Not all payees are members of the group.');
            }
        }

        if ($request->exchangeRate === null) {
            $exchangeRate = $this->currencyExchangeService->getExchangeRate(
                $currency->getCode(),
                $group->getCurrency()->getCode(),
                new \DateTime()
            );
        } else {
            $exchangeRate = $request->exchangeRate;
        }

        if($request->transactionDate === null) {
            $transactionDate = new \DateTime();
        } else {
            $transactionDate = $request->transactionDate;
        }

        $transaction = new Transaction();
        $transaction
            ->setName($request->name)
            ->setCurrency($currency)
            ->setExchangeRate($exchangeRate)
            ->setGroup($group)
            ->setTransactionDate($transactionDate);

        $totalAmount = $request->amount;
        $debitAmounts = splitAmount($totalAmount, count($payees));

        $creditEntry = new TransactionEntry();
        $creditEntry
            ->setTransaction($transaction)
            ->setUser($payer)
            ->setAmount($totalAmount)
            ->setType(TransactionEntry::TYPE_CREDIT);
        $this->entityManager->persist($creditEntry);

        foreach($payees as $index => $payee) {
            $debitEntry = new TransactionEntry();
            $debitEntry
                ->setTransaction($transaction)
                ->setUser($payee)
                ->setAmount($debitAmounts[$index])
                ->setType(TransactionEntry::TYPE_DEBIT);
            $this->entityManager->persist($debitEntry);
        }

        $this->entityManager->persist($transaction);
        $this->entityManager->flush();

        return $transaction;
    }

    public function getTransactionPayer(Transaction $transaction): User
    {
        foreach($transaction->getEntries() as $entry) {
            if ($entry->getType() === TransactionEntry::TYPE_CREDIT) {
                return $entry->getUser();
            }
        }
    }

    public function getTransactionPayees(Transaction $transaction): array
    {
        $payees = [];
        foreach($transaction->getEntries() as $entry) {
            if ($entry->getType() === TransactionEntry::TYPE_DEBIT) {
                $payees[] = $entry->getUser();
            }
        }
        return $payees;
    }
    
    public function getTransactionAmount(Transaction $transaction): float
    {
        foreach($transaction->getEntries() as $entry) {
            if ($entry->getType() === TransactionEntry::TYPE_CREDIT) {
                return $entry->getAmount();
            }
        }
    }

    public function getTransactionDetails(Transaction $transaction): array
    {
        foreach($transaction->getEntries() as $entry) {
            if ($entry->getType() === TransactionEntry::TYPE_DEBIT) {
                $payees[] = $entry->getUser();
            } elseif ($entry->getType() === TransactionEntry::TYPE_CREDIT) {
                $payer = $entry->getUser();
                $amount = $entry->getAmount();
            }
        }

        if (!isset($payer) || !isset($payees) || !isset($amount)) {
            throw new \InvalidArgumentException('Transaction data is incomplete.');
        }

        return [
            $payer,
            $payees,
            $amount
        ];
    }
}

function splitAmount(float $totalAmount, int $numberOfPayees): array
{
    if ($numberOfPayees <= 0) {
        throw new \InvalidArgumentException('Number of payees must be greater than zero.');
    }

    $amountPerPayee = floor($totalAmount / $numberOfPayees);
    $remainder = $totalAmount % $numberOfPayees;

    $amounts = array_fill(0, $numberOfPayees, $amountPerPayee);

    // for ($i = 0; $i < $remainder; $i++) {
    //     $amounts[$i]++;
    // }

    // randomze the distribution of the remainder
    $randomKeys = array_rand($amounts, $remainder);
    foreach ($randomKeys as $key) {
        $amounts[$key]++;
    }

    return $amounts;
}
