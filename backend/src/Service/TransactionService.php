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
        private Security $security
    ) {}

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

        $exchangeRate = $request->exchangeRate;

        $transactionDate = $request->transactionDate;
        if ($transactionDate === null) {
            $transactionDate = new \DateTime();
        }

        $transaction = new Transaction();
        $transaction
            ->setName($request->name)
            ->setOriginalAmount($request->amount)
            ->setCurrency($currency)
            ->setExchangeRate($exchangeRate)
            ->setGroup($group)
            ->setTransactionDate($transactionDate);

        $this->entityManager->persist($transaction);

        $this->createTransactionEntries($transaction, $payer, $payees, $request->amount);

        $this->entityManager->flush();

        return $transaction;
    }

    public function editTransaction(TransactionRequest $request, Transaction $transaction): Transaction
    {
        $user = $this->security->getUser();
        if (!$user) {
            throw new AccessDeniedException('User not authenticated.');
        }

        list($oldPayer, $oldPayees, $oldAmount) = $this->getTransactionDetails($transaction);
        if(!$this->groupMembershipRepository->isUserMemberOfGroup($oldPayer, $transaction->getGroup())) {
            throw new \InvalidArgumentException('Nie można edytować transakcji z nieobecnymi członkami.');
        }

        foreach($oldPayees as $payee) {
            if (!$this->groupMembershipRepository->isUserMemberOfGroup($payee, $transaction->getGroup())) {
                throw new \InvalidArgumentException('Nie można edytować transakcji z nieobecnymi członkami.');
            }
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

        $toUpdateAmount = false;

        if($transaction->getCurrency() !== $currency) {
            $this->addTransactionHistory(
                $transaction,
                $user,
                'currency',
                $transaction->getCurrency()->getCode(),
                $currency->getCode()
            );
            $transaction->setCurrency($currency);
            $toUpdateAmount = true;
        }

        if($transaction->getExchangeRate() !== $request->exchangeRate) {
            $this->addTransactionHistory(
                $transaction,
                $user,
                'exchangeRate',
                $transaction->getExchangeRate(),
                $request->exchangeRate
            );
            $transaction->setExchangeRate($request->exchangeRate);
            $toUpdateAmount = true;
        }

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

        // if ($oldAmount !== $request->amount) {
        if ($transaction->getOriginalAmount() !== $request->amount) {
            $this->addTransactionHistory(
                $transaction,
                $user,
                'amount',
                $oldAmount,
                $request->amount
            );
            $transaction->setOriginalAmount($request->amount);
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

        if ($toUpdateAmount) {
            $this->createTransactionEntries($transaction, $payer, $payees, $request->amount);
        }

        $this->entityManager->flush();
        return $transaction;
    }

    private function createTransactionEntries(Transaction $transaction, User $payer, array $payees, float $totalAmount): void
    {
        foreach ($transaction->getEntries() as $entry) {
            $transaction->removeEntry($entry);
            $this->entityManager->remove($entry);
        }

        $exchangeRate = $transaction->getExchangeRate();
        if($exchangeRate === null) {
            $exchangeRateObject = $this->currencyExchangeService->getExchangeRateObject(
                $transaction->getCurrency()->getCode(),
                $transaction->getGroup()->getCurrency()->getCode(),
                $transaction->getTransactionDate()
            );
            $exchangeRate = $exchangeRateObject->getRate();
        }

        $totalAmount = $totalAmount * $exchangeRate;
        $debitAmounts = splitAmount($totalAmount, count($payees));

        $creditEntry = new TransactionEntry();
        $creditEntry
            ->setUser($payer)
            ->setAmount($totalAmount)
            ->setType(TransactionEntry::TYPE_CREDIT);
        $transaction->addEntry($creditEntry);

        foreach ($payees as $index => $payee) {
            $debitEntry = new TransactionEntry();
            $debitEntry
                ->setUser($payee)
                ->setAmount($debitAmounts[$index])
                ->setType(TransactionEntry::TYPE_DEBIT);
            $transaction->addEntry($debitEntry);
        }
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

    // randomize distribution of the remainder
    if ($remainder > 0) {
        $randomKeys = array_rand($amounts, $remainder);
        foreach ((array)$randomKeys as $key) {
            $amounts[$key]++;
        }
    }

    return array_map(fn($amount) => $amount / 100, $amounts);
}
