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
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use App\Repository\GroupMembershipRepository;

class TransactionService
{
    public function __construct(
        private TransactionRepository $transactionRepository,
        private CurrencyRepository $currencyRepository,
        private UserRepository $userRepository,
        private CurrencyExchangeService $currencyExchangeService,
        private EntityManagerInterface $entityManager,
        private GroupMembershipRepository $groupMembershipRepository,
        private Security $security,
        private LoggerInterface $logger,
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

        if (!$this->groupMembershipRepository->isUserMemberOfGroup($payer, $group)) {
            throw new \InvalidArgumentException('Payer is not a member of the group.');
        }

        foreach ($payees as $payee) {
            if (!$this->groupMembershipRepository->isUserMemberOfGroup($payee, $group)) {
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
        $this->entityManager->persist($transaction);

        $creditEntry = new TransactionEntry();
        $creditEntry
            // ->setTransaction($transaction)
            ->setUser($payer)
            ->setAmount($totalAmount)
            ->setType(TransactionEntry::TYPE_CREDIT);
        // $this->entityManager->persist($creditEntry);
        $transaction->addEntry($creditEntry);

        foreach($payees as $index => $payee) {
            $debitEntry = new TransactionEntry();
            $debitEntry
                // ->setTransaction($transaction)
                ->setUser($payee)
                ->setAmount($debitAmounts[$index])
                ->setType(TransactionEntry::TYPE_DEBIT);
            // $this->entityManager->persist($debitEntry);
            $transaction->addEntry($debitEntry);
        }

        $this->entityManager->flush();

        return $transaction;
    }

    public function editTransaction(TransactionRequest $request, Transaction $transaction): Transaction
    {
        $user = $this->security->getUser();
        if (!$user) {
            throw new AccessDeniedException('User not authenticated.');
        }

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

        $this->addTransactionHistory(
            $transaction,
            $user,
            'currency',
            $transaction->getCurrency()->getCode(),
            $currency->getCode()
        );
        $transaction->setCurrency($currency);

        if($request->exchangeRate === null && $transaction->getGroup()->getCurrency() !== $currency) {
            $exchangeRate = $this->currencyExchangeService->getExchangeRate(
                $currency->getCode(),
                $transaction->getGroup()->getCurrency()->getCode(),
                new \DateTime()
            );

        } elseif($request->exchangeRate !== null) {
            $exchangeRate = $request->exchangeRate;
        } else {
            $exchangeRate = $transaction->getExchangeRate();
        }

        $this->addTransactionHistory(
            $transaction,
            $user,
            'exchangeRate',
            $transaction->getExchangeRate(),
            $exchangeRate
        );
        $transaction->setExchangeRate($exchangeRate);

        $this->addTransactionHistory(
            $transaction,
            $user,
            'name',
            $transaction->getName(),
            $request->name
        );
        $transaction->setName($request->name);

        if($request->transactionDate === null) {
            $transactionDate = new \DateTime();
        } else {
            $transactionDate = $request->transactionDate;
        }
        $this->addTransactionHistory(
            $transaction,
            $user,
            'transactionDate',
            $transaction->getTransactionDate()->format('Y-m-d H:i:s'),
            $transactionDate->format('Y-m-d H:i:s')
        );
        $transaction->setTransactionDate($transactionDate);

        list($oldPayer, $oldPayees, $oldAmount) = $this->getTransactionDetails($transaction);

        $toUpdateAmount = false;
        if ($oldPayer !== $payer) {
            $this->addTransactionHistory(
                $transaction,
                $user,
                'payer',
                $payer->getId(),
                $request->payerId
            );
            $toUpdateAmount = true;
        }

        if ($oldAmount !== $request->amount) {
            $this->addTransactionHistory(
                $transaction,
                $user,
                'amount',
                $oldAmount,
                $request->amount
            );
            $toUpdateAmount = true;
        }

        $oldPayeeIds = array_map(fn($user) => $user->getId(), $oldPayees);
        $newPayeeIds = array_map(fn($user) => $user->getId(), $payees);
        if (count($oldPayeeIds) !== count($newPayeeIds) || array_diff($oldPayeeIds, $newPayeeIds)) {
            $this->addTransactionHistory(
                $transaction,
                $user,
                'payees',
                implode(',', array_map(fn($payee) => $payee->getCustomUsername() ?? $payee->getEmail(), $oldPayees)),
                implode(',', array_map(fn($payee) => $payee->getCustomUsername() ?? $payee->getEmail(), $payees))
            );
            $toUpdateAmount = true;
        }

        if (!$toUpdateAmount) {
            return $transaction;
        }

        // remove existing entries
        foreach ($transaction->getEntries() as $entry) {
            $transaction->removeEntry($entry);
            $this->entityManager->remove($entry);
        }

        // add new entries
        $totalAmount = $request->amount;
        $debitAmounts = splitAmount($totalAmount, count($payees));

        // add credit entry
        $creditEntry = new TransactionEntry();
        $creditEntry
            ->setUser($payer)
            ->setAmount($totalAmount)
            ->setType(TransactionEntry::TYPE_CREDIT);
        $transaction->addEntry($creditEntry);
        
        // add debit entries
        foreach ($payees as $index => $payee) {
            $debitEntry = new TransactionEntry();
            $debitEntry
                ->setUser($payee)
                ->setAmount($debitAmounts[$index])
                ->setType(TransactionEntry::TYPE_DEBIT);
            $transaction->addEntry($debitEntry);
        }

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

    public function addTransactionHistory(Transaction $transaction, User $changedBy, string $field, $oldValue, $newValue): void
    {
        if($oldValue === $newValue) {
            return;
        }

        $history = (new TransactionHistory())
            ->setTransaction($transaction)
            ->setChangedBy($changedBy)
            ->setField($field)
            ->setOldValue((string) $oldValue)
            ->setNewValue((string) $newValue)
            ->setChangedAt(new \DateTime());

        $this->entityManager->persist($history);
        // $this->entityManager->flush();
    }
}

function splitAmount(float $totalAmount, int $numberOfPayees): array
{
    if ($numberOfPayees <= 0) {
        throw new \InvalidArgumentException('Number of payees must be greater than zero.');
    }

    $totalAmountInCents = round($totalAmount * 100);
    $amountPerPayee = floor($totalAmountInCents / $numberOfPayees);
    $remainder = $totalAmountInCents % $numberOfPayees;

    $amounts = array_fill(0, $numberOfPayees, $amountPerPayee);

    // randomze the distribution of the remainder
    if ($remainder > 0) {
        $randomKeys = array_rand($amounts, $remainder);
        foreach ((array)$randomKeys as $key) {
            $amounts[$key]++;
        }
    }

    // return $amounts;
    return array_map(fn($amount) => $amount / 100, $amounts);
}
