<?php

namespace App\Dto\Transaction;

use Symfony\Component\Serializer\Annotation\Groups;
use App\Entity\Currency;
use App\Entity\User;
use Doctrine\Common\Collections\Collection;

class TransactionResponse
{
    public function __construct(
        #[Groups(['transaction:read'])]
        public int $id,
        
        #[Groups(['transaction:read'])]
        public string $name,
        
        #[Groups(['transaction:read'])]
        public float $amount,

        #[Groups(['transaction:read'])]
        public Currency $currency,

        #[Groups(['transaction:read'])]
        public float $exchangeRate,

        #[Groups(['transaction:read'])]
        public User $payer,

        #[Groups(['transaction:read'])]
        public array $payees,

        #[Groups(['transaction:read'])]
        public Collection $entries,

        #[Groups(['transaction:read'])]
        public \DateTime $transactionDate
    ) {}
}