<?php

namespace App\Service;

use App\Repository\CurrencyExchangeRepository;
use Doctrine\ORM\EntityManagerInterface;
use DateTime;
use Exception;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Entity\CurrencyExchange;
use Psr\Log\LoggerInterface;

class CurrencyExchangeService
{
    public function __construct(
        private HttpClientInterface $client,
        private CurrencyExchangeRepository $currencyExchangeRepository,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger
    ) {}
    
    /**
     * Get exchange rate from source currency to target currency
     * 
     * @param string $sourceCurrency Source currency code (e.g. 'USD')
     * @param string $targetCurrency Target currency code (e.g. 'EUR')
     * @return array [date, rate]
     * @throws Exception If exchange rate cannot be obtained
     */
    public function getExchangeRate(string $sourceCurrency, string $targetCurrency, ?\DateTimeInterface $date = null): array
    {
        // If same currency, rate is 1.0
        if ($sourceCurrency === $targetCurrency) {
            return [$date, 1.0];
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
            return [$date, $exchangeRate->getRate()];
        }
        
        // $rate = $this->fetchExchangeRateFromApi($sourceCurrency, $targetCurrency);

        try{
            $rates = $this->fetchExchangeRateFromApi_v2($sourceCurrency, $date);

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

            return [$rate->getDate(), $rate->getRate()];
        }
        
        return [$date, $rate];
    }
    
    /**
     * Fetches exchange rate from external API
     * 
     * @param string $sourceCurrency Source currency code
     * @param string $targetCurrency Target currency code
     * @return float Exchange rate
     * @throws Exception If API call fails
     */
    private function fetchExchangeRateFromApi(string $sourceCurrency, string $targetCurrency): float
    {
        $apiKey = $_ENV['EXCHANGE_RATE_API_KEY'] ?? null;
        $apiUrl = 'https://v6.exchangerate-api.com/v6/';
        
        if (!$apiKey) {
            throw new Exception('Exchange rate API key not configured');
        }
        
        $endpoint = $apiUrl . $apiKey . '/pair/' . $sourceCurrency . '/' . $targetCurrency;
        
        try {
            $response = $this->client->request('GET', $endpoint);
            
            if ($response->getStatusCode() !== 200) {
                throw new Exception('API request failed with status code: ' . $response->getStatusCode());
            }
            
            $data = $response->toArray();
            
            if ($data['result'] !== 'success') {
                throw new Exception('API error: ' . ($data['error-type'] ?? 'unknown error'));
            }
            
            return (float) $data['conversion_rate'];
            
        } catch (\Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface $e) {
            throw new Exception('API connection failed: ' . $e->getMessage());
        } catch (\Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface $e) {
            throw new Exception('Failed to decode API response: ' . $e->getMessage());
        } catch (\Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface $e) {
            throw new Exception('API client error: ' . $e->getMessage());
        } catch (\Exception $e) {
            throw new Exception('Failed to get exchange rate: ' . $e->getMessage());
        }
    }

    /**
     * Fetches the exchange rate from an API (version 2).
     *
     * @param string $baseCurrency The base currency code to fetch the exchange rate for
     * @param \DateTimeInterface|null $date Optional date for historical exchange rates. If null, current rate is fetched.
     * @return array ['currency_code' => 'exchange_rate']
     * @throws \Exception If there is an error fetching the exchange rate
     */
    private function fetchExchangeRateFromApi_v2(string $baseCurrency, ?\DateTimeInterface $date = null): array
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