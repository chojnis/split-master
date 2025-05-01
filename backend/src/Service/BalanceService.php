<?php

namespace App\Service;

use App\Repository\TransactionRepository;

/**
 * 
 * The BalanceService handles operations related to user balances,
 * including calculations, updates, and balance-related business logic.
 * This may involve tracking debts, credits, and financial transactions
 * between users or within groups.
 * 
 */
class BalanceService
{
    public function __construct(
        private TransactionRepository $transactionRepository
    ) {}

    public function getUserBalanceForGroup(
        User $user, 
        Group $group
    ): float {
        $balance = 0.0;
        
        $paidAmount = $this->transactionRepository->getTotalPaidByUserInGroup($user, $group);
        $owedAmount = $this->transactionRepository->getTotalOwedByUserInGroup($user, $group);
        
        foreach ($paidAmount as $currencyId => $amounts) {
            $totalAmount = $amounts['total'];
            $totalConvertedAmount = $amounts['convertedTotal'];

            if ($currencyId != $group->getCurrency()->getId()) {
                $amount = $totalConvertedAmount;
            } else {
                $amount = $totalAmount;
            }

            $balance -= $amount;
        }
        
        foreach ($owedAmount as $currencyId => $amount) {
            $totalAmount = $amounts['total'];
            $totalConvertedAmount = $amounts['convertedTotal'];

            if ($currencyId != $group->getCurrency()->getId()) {
                $amount = $totalConvertedAmount;
            } else {
                $amount = $totalAmount;
            }

            $balance += $amount;
        }
        
        return $balance;
    }
}