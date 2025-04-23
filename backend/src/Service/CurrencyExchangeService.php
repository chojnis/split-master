<?php

namespace App\Service;

use App\Repository\CurrencyExchangeRepository;
use Doctrine\ORM\EntityManagerInterface;
use DateTime;
use Exception;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Entity\CurrencyExchange;

class CurrencyExchangeService
{
    public function __construct(
        private HttpClientInterface $client,
        private CurrencyExchangeRepository $currencyExchangeRepository,
        private EntityManagerInterface $entityManager
    ) {}

    /**
     * Get exchange rate from source currency to target currency
     * 
     * @param string $sourceCurrency Source currency code (e.g. 'USD')
     * @param string $targetCurrency Target currency code (e.g. 'EUR')
     * @return CurrencyExchange
     * @throws Exception If exchange rate cannot be obtained
     */
    public function getExchangeRate(string $sourceCurrency, string $targetCurrency, ?\DateTimeInterface $date = null): CurrencyExchange
    {
        // If same currency, rate is 1.0
        if ($sourceCurrency === $targetCurrency) {
            return (new CurrencyExchange())
                ->setFromCurrency($sourceCurrency)
                ->setToCurrency($targetCurrency)
                ->setRate(1.0)
                ->setDate(new \DateTime());
        }
        
        $date = $date ?? new \DateTime();
        $date->setTime(0, 0, 0);
        
        // Check if we have the rate in database
        $exchangeRate = $this->currencyExchangeRepository->findOneBy([
            'fromCurrency' => $sourceCurrency,
            'toCurrency' => $targetCurrency,
            'date' => $date,
        ]);

        if ($exchangeRate !== null) {
            return $exchangeRate;
        }
        
        try{
            $rates = $this->fetchExchangeRateFromApi($sourceCurrency, $date);

            $this->saveExchangeRatesBatch($sourceCurrency, $rates, $date);

            if (!isset($rates[strtolower($targetCurrency)])) {
                throw new Exception('Exchange rate not found in API response');
            }
            
            $rate = $rates[strtolower($targetCurrency)];
        } catch (Exception $e) {
            // If API call fails, try to get the latest rate from the database
            $rate = $this->currencyExchangeRepository->findOneBy([
                'fromCurrency' => $sourceCurrency,
                'toCurrency' => $targetCurrency,
            ], ['date' => 'DESC']);

            if(!$rate) {
                throw new Exception('Exchange rate not found in database and API call failed: ' . $e->getMessage());
            }

            return $rate;
        }
        
        return (new CurrencyExchange())
            ->setFromCurrency($sourceCurrency)
            ->setToCurrency($targetCurrency)
            ->setRate($rate)
            ->setDate($date);
    }

    /**
     * Fetches the exchange rate from an API
     *
     * @param string $baseCurrency The base currency code to fetch the exchange rate for
     * @param \DateTimeInterface|null $date Optional date for historical exchange rates. If null, current rate is fetched.
     * @return array ['currency_code' => 'exchange_rate']
     * @throws \Exception If there is an error fetching the exchange rate
     */
    private function fetchExchangeRateFromApi(string $baseCurrency, ?\DateTimeInterface $date = null): array
    {
        $dateStr = $date ? $date->format('Y-m-d') : (new DateTime())->format('Y-m-d');
        $version = 'v1';
        $baseCurrency = strtolower($baseCurrency);
        $endpoint = "currencies/{$baseCurrency}.json";

        $urls = [
            "https://cdn.jsdelivr.net/npm/@fawazahmed0/currency-api@{$dateStr}/{$version}/{$endpoint}",
            "https://{$dateStr}.currency-api.pages.dev/{$version}/{$endpoint}",
        ];
                
        foreach ($urls as $url) {
            $response = $this->client->request('GET', $url);
            if ($response->getStatusCode() === 200) {
                return $response->toArray()[$baseCurrency] ?? [];
            }
        }

        throw new \RuntimeException("Failed to fetch exchange rates from all sources.");
    }

    /**
     * Save exchange rate to database
     */
    private function saveExchangeRate(
        string $sourceCurrency,
        string $targetCurrency,
        float $rate,
        DateTime $date
    ): void {
        $exchangeRate = new CurrencyExchange();
        $exchangeRate->setFromCurrency($sourceCurrency);
        $exchangeRate->setToCurrency($targetCurrency);
        $exchangeRate->setRate($rate);
        $exchangeRate->setDate($date);
        
        $this->entityManager->persist($exchangeRate);
        $this->entityManager->flush();
    }

    /**
     * Save multiple exchange rates to database
     * 
     * @param string $sourceCurrency Source/base currency code
     * @param array $rates Array of [targetCurrency => rate]
     * @param DateTime $date Date of the exchange rate
     */
    private function saveExchangeRatesBatch(string $sourceCurrency, array $rates, DateTime $date): void
    {
        foreach ($rates as $targetCurrency => $rate) {
            if (
                $sourceCurrency === $targetCurrency 
                || !is_numeric($rate) 
                || strlen($targetCurrency) > 3
                || strlen((string)floor($rate)) > 4
            ) {
                continue;
            }

            $rate = round($rate, 4);

            $exchangeRate = new CurrencyExchange();
            $exchangeRate->setFromCurrency(strtoupper($sourceCurrency));
            $exchangeRate->setToCurrency(strtoupper($targetCurrency));
            $exchangeRate->setRate($rate);
            $exchangeRate->setDate($date);

            $this->entityManager->persist($exchangeRate);
        }

        $this->entityManager->flush();
    }
}