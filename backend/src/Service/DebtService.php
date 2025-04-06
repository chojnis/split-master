<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Group;
use App\Entity\Transaction;
use App\Repository\TransactionRepository;
use App\Entity\Currency;
use App\Service\CurrencyExchangeService;

class DebtService
{
    public function __construct(
        private TransactionRepository $transactionRepository,
        private CurrencyExchangeService $currencyExchange
    ) {}

    private function getDebtsMatrixForGroup(Group $group): array
    {
        $transactions = $this->transactionRepository->findBy(['group' => $group]);

        // iterate over transactions and get all user ids
        foreach ($transactions as $transaction) {
            $allUserIds[$transaction->getPayer()->getId()] = true;
            foreach ($transaction->getPayees() as $payee) {
                $allUserIds[$payee->getId()] = true;
            }
        }

        // initialize debts matrix with all user ids
        $debtsMatrix = array_fill_keys(array_keys($allUserIds), array_fill_keys(array_keys($allUserIds), 0.0));
        
        // iterate over transactions again to calculate debts
        foreach ($transactions as $transaction) {
            // $this->processTransaction($transaction, $debtsMatrix);
            $payerId = $transaction->getPayer()->getId();
            $totalPayees = count($transaction->getPayees());
            $transactionCurrency = $transaction->getCurrency();
            $targetCurrency = $transaction->getGroup()->getCurrency();
            
            foreach ($transaction->getPayees() as $payee) {
                $payeeId = $payee->getId();
                if ($payeeId === $payerId) {
                    continue;
                }
                    
                if ($transactionCurrency->getId() !== $targetCurrency->getId()) {
                    $share = $transaction->getConvertedAmount();
                } else {
                    $share = $transaction->getAmount();
                }

                $share /= $totalPayees;
                
                $debtsMatrix[$payeeId][$payerId] += $share;
            }
        }

        return $debtsMatrix;
    }

    public function getOptimizedDebtsForGroup(Group $group, bool $simple = false): array
    {
        $debtsMatrix = $this->getDebtsMatrixForGroup($group);
        
        if ($simple) {
            $transactions = $this->optimizeSimple($debtsMatrix);
        } else {
            $transactions = $this->optimizeAdvanced($debtsMatrix);
        }
        
        return $this->normalizeTransactions($transactions, $group);
    }

    private function optimizeSimple(array $matrix): array
    {
        $transactions = [];
        
        // Cancel out mutual debts
        foreach ($matrix as $debtorId => $creditors) {
            foreach ($creditors as $creditorId => $amount) {
                if ($amount > 0 && isset($matrix[$creditorId][$debtorId])) {
                    $reverseAmount = $matrix[$creditorId][$debtorId];
                    $reduction = min($amount, $reverseAmount);
                    $matrix[$debtorId][$creditorId] -= $reduction;
                    $matrix[$creditorId][$debtorId] -= $reduction;
                }
            }
        }
        
        // Collect remaining debts
        foreach ($matrix as $debtorId => $creditors) {
            foreach ($creditors as $creditorId => $amount) {
                if ($amount > 0.001) { // Small threshold for floating point
                    $transactions[] = [
                        'from' => $debtorId,
                        'to' => $creditorId,
                        'amount' => round($amount, 2)
                    ];
                }
            }
        }
        
        return $transactions;
    }

    private function optimizeAdvanced(array $matrix): array
    {
        $netBalances = [];
        foreach ($matrix as $debtorId => $creditors) {
            foreach ($creditors as $creditorId => $amount) {
                $netBalances[$debtorId] = ($netBalances[$debtorId] ?? 0) - $amount;
                $netBalances[$creditorId] = ($netBalances[$creditorId] ?? 0) + $amount;
            }
        }
        
        // Separate debtors and creditors
        $debtors = array_filter($netBalances, fn($b) => $b < -0.001);
        $creditors = array_filter($netBalances, fn($b) => $b > 0.001);
        
        // Sort by absolute amount (descending)
        arsort($debtors);
        arsort($creditors);
        
        // Greedy settlement
        $transactions = [];
        $debtorIds = array_keys($debtors);
        $creditorIds = array_keys($creditors);
        
        for ($d = 0, $c = 0; $d < count($debtorIds) && $c < count($creditorIds); ) {
            $debtorId = $debtorIds[$d];
            $creditorId = $creditorIds[$c];
            $amount = min(-$debtors[$debtorId], $creditors[$creditorId]);
            
            $transactions[] = [
                'from' => $debtorId,
                'to' => $creditorId,
                'amount' => round($amount, 2)
            ];
            
            $debtors[$debtorId] += $amount;
            $creditors[$creditorId] -= $amount;
            
            if (abs($debtors[$debtorId]) < 0.001) $d++;
            if (abs($creditors[$creditorId]) < 0.001) $c++;
        }
        
        return $transactions;
    }

    private function normalizeTransactions(array $transactions, Group $group): array
    {
        $users = $this->groupMembershipService->getGroupUsers($group);
        $userMap = [];
        foreach ($users as $user) {
            $userMap[$user->getId()] = $user;
        }

        foreach ($transactions as &$transaction) {
            $fromUser = $userMap[$transaction['from']] ?? ["id" => $transaction['from'], "username" => "Niedostępny użytkownik " . $transaction['from']];
            $toUser = $userMap[$transaction['to']] ?? ["id" => $transaction['to'], "username" => "Niedostępny użytkownik " . $transaction['to']];
            $amount = $transaction['amount'];
            
            $transaction['from'] = $fromUser;
            $transaction['to'] = $toUser;
            $transaction['amount'] = $amount;
            $transaction['currency'] = $group->getCurrency();
        }
        
        return $transactions;
    }

}