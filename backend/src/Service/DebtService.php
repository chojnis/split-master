<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Group;
use App\Entity\Transaction;
use App\Repository\TransactionRepository;
use App\Entity\Currency;
use App\Service\CurrencyExchangeService;
use App\Service\GroupMembershipService;
use Psr\Log\LoggerInterface;

class DebtService
{
    public function __construct(
        private TransactionRepository $transactionRepository,
        private CurrencyExchangeService $currencyExchange,
        private LoggerInterface $logger,
        private GroupMembershipService $groupMembershipService,
    ) {}

    public function getDetailedDebtsForGroup(Group $group): array
    {
        // $members = $group->getGroupMemberships()->map(fn($m) => $m->getUser());
        $members = $this->groupMembershipService->getGroupUsers($group);
        // $userMap = array_flip(array_map(fn(User $u) => $u->getId(), $members->toArray()));
        $userMap = [];
        foreach ($members as $user) {
            if ($user instanceof User) {
                $userMap[$user->getId()] = $user;
            }
        }
        $debtsMatrix = array_fill_keys(array_keys($userMap), array_fill_keys(array_keys($userMap), 0.0));
        
        foreach ($this->transactionRepository->findBy(['group' => $group]) as $transaction) {
            $this->processTransaction($transaction, $userMap, $debtsMatrix, $group->getCurrency());
        }
        
        return $this->normalizeDebts($debtsMatrix, $userMap, $group->getCurrency());
    }

    public function getOptimizedPaymentsForGroup(Group $group): array 
    {
        $detailedDebts = $this->getDetailedDebtsForGroup($group);
        
        return $this->minimizeTransactions($detailedDebts);
    }

    private function processTransaction(
        Transaction $transaction,
        array $userMap,
        array &$debtsMatrix,
        Currency $targetCurrency
    ): void {
        $payerId = $transaction->getPayer()->getId();
        $totalPayees = count($transaction->getPayees());
        $transactionCurrency = $transaction->getCurrency();
        
        foreach ($transaction->getPayees() as $payee) {
            $payeeId = $payee->getId();
            if ($payeeId != $payerId && isset($userMap[$payeeId])) {
                
                if ($transactionCurrency->getId() !== $targetCurrency->getId()) {
                    $share = $transaction->getConvertedAmount();
                } else {
                    $share = $transaction->getAmount();
                }

                $share /= $totalPayees;
                
                $debtsMatrix[$payeeId][$payerId] += $share;
            }
        }
    }

    private function normalizeDebts(array $matrix, array $userMap, Currency $targetCurrency): array
    {
        $normalized = [];

        foreach ($matrix as $debtorId => $creditors) {
            foreach ($creditors as $creditorId => $amount) {
                if ($amount > 0 && $debtorId != $creditorId) {
                    $normalized[] = [
                        'debtor' => $userMap[$debtorId],
                        'creditor' => $userMap[$creditorId],
                        'amount' => round($amount, 2),
                        'currency' => $targetCurrency,
                    ];
                }
            }
        }
        
        return $normalized;
    }

    private function getRelatedTransactions(int $debtorId, int $creditorId, Group $group): array
    {
        return $this->transactionRepository->findTransactionsBetweenUsersInGroup(
            $debtorId,
            $creditorId,
            $group->getId()
        );
    }

    private function minimizeTransactions(array $debts): array
    {
        $transactions = [];

        // log debts
        $this->logger->info('Debts before minimization:', $debts);
        
        foreach ($debts as $pair1 => $amount1) {
            list($debtor1, $creditor1) = explode('-', $pair1);
            
            foreach ($debts as $pair2 => $amount2) {
                list($debtor2, $creditor2) = explode('-', $pair2);
                
                if ($creditor1 == $debtor2 && $debtor1 == $creditor2) {
                    $reduction = min($amount1, $amount2);
                    $debts[$pair1] -= $reduction;
                    $debts[$pair2] -= $reduction;
                }
            }
        }
        
        foreach ($debts as $pair => $amount) {
            if ($amount > 0) {
                list($debtorId, $creditorId) = explode('-', $pair);
                $transactions[] = [
                    'from' => $debtorId,
                    'to' => $creditorId,
                    'amount' => round($amount, 2)
                ];
            }
        }
        
        return $transactions;
    }
}