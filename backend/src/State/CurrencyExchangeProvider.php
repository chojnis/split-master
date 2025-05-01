<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Service\CurrencyExchangeService;
use App\Repository\CurrencyExchangeRepository;
use App\Repository\CurrencyRepository;
use App\Entity\CurrencyExchange;

/**
 * 
 * This class implements the ProviderInterface to provide currency exchange functionality.
 * It is responsible for handling currency conversion operations and exchange rate retrieval.
 * 
 */
class CurrencyExchangeProvider implements ProviderInterface
{
    public function __construct(
        private CurrencyExchangeService $currencyExchangeService,
        private CurrencyExchangeRepository $currencyExchangeRepository,
        private CurrencyRepository $currencyRepository
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): CurrencyExchange|null
    {
        $fromCurrencyId = $uriVariables['fromCurrencyId'] ?? null;
        $toCurrencyId = $uriVariables['toCurrencyId'] ?? null;
        $date = $context['filters']['date'] ?? null;

        if ($date && strtotime($date) !== false) {
            $date = new \DateTime($date);
        } else {
            $date = null;
        }

        if (!$fromCurrencyId || !$toCurrencyId) {
            throw new \InvalidArgumentException('Invalid currency ID provided');
        }

        $fromCurrency = $this->currencyRepository->find($fromCurrencyId);
        $toCurrency = $this->currencyRepository->find($toCurrencyId);

        if (!$fromCurrency || !$toCurrency) {
            throw new \InvalidArgumentException('Invalid currency ID provided');
        }

        return $this->currencyExchangeService->getExchangeRateObject($fromCurrency->getCode(), $toCurrency->getCode(), $date);
    }
}
