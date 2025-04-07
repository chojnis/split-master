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
use App\Dto\CurrencyExchange\CurrencyExchangeResponse;
use Psr\Log\LoggerInterface;

class CurrencyExchangeProvider implements ProviderInterface
{
    public function __construct(
        private CurrencyExchangeService $currencyExchangeService,
        private CurrencyExchangeRepository $currencyExchangeRepository,
        private CurrencyRepository $currencyRepository,
        private LoggerInterface $logger
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if($operation instanceof Get) {
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

            list($date, $rate) = $this->currencyExchangeService->getExchangeRate($fromCurrency->getCode(), $toCurrency->getCode(), $date);

            return new CurrencyExchangeResponse(
                fromCurrency: $fromCurrency->getCode(),
                toCurrency: $toCurrency->getCode(),
                rate: $rate,
                date: $date
            );
        } elseif ($operation instanceof GetCollection) {
            $toCurrencyId = $uriVariables['toCurrencyId'] ?? null;

            if (!$toCurrencyId) {
                throw new \InvalidArgumentException('Invalid currency ID provided');
            }

            $toCurrency = $this->currencyRepository->find($toCurrencyId);
            if (!$toCurrency) {
                throw new \InvalidArgumentException('Invalid currency ID provided');
            }

            $rates = $this->currencyExchangeRepository->findLatestExchangeRatesForCurrency($toCurrency->getCode());

            return array_map(fn($rate) => new CurrencyExchangeResponse(
                fromCurrency: $rate['fromCurrency'],
                toCurrency: $toCurrency->getCode(),
                rate: $rate['rate'],
                date: new \DateTime($rate['latestDate'])
            ), $rates);

        } else {
            throw new \InvalidArgumentException('Unsupported operation type');
        }
    }
}
