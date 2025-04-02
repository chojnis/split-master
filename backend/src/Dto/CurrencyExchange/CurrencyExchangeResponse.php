<?php

namespace App\Dto\CurrencyExchange;

use Symfony\Component\Serializer\Annotation\Groups;

class CurrencyExchangeResponse
{
    public function __construct(
        #[Groups(['currency_exchange:read'])]
        public string $fromCurrency,
        
        #[Groups(['currency_exchange:read'])]
        public string $toCurrency,
        
        #[Groups(['currency_exchange:read'])]
        public float $rate,
        
        #[Groups(['currency_exchange:read'])]
        public \DateTime $date
    ) {}
}
