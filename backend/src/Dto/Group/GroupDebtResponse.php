<?php

namespace App\Dto\Group;

use Symfony\Component\Serializer\Annotation\Groups;

class GroupDebtResponse
{
    public function __construct(
        #[Groups(['debt:read'])]
        public int $debtorId,
        
        // #[Groups(['debt:read'])]
        // public string $debtorName,
        
        #[Groups(['debt:read'])]
        public int $creditorId,
        
        // #[Groups(['debt:read'])]
        // public string $creditorName,
        
        #[Groups(['debt:read'])]
        public float $amount
    ) {}
}