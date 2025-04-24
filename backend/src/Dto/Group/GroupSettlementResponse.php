<?php

namespace App\Dto\Group;

use Symfony\Component\Serializer\Annotation\Groups;
use App\Entity\Currency;
use App\Entity\User;

class GroupSettlementResponse
{
    public function __construct(
        #[Groups(['settlement:read'])]
        public User $from,
        
        #[Groups(['settlement:read'])]
        public User $to,
        
        #[Groups(['settlement:read'])]
        public float $amount,

        #[Groups(['settlement:read'])]
        public Currency $currency,
    ) {}
}