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
        private EntityManagerInterface $entityManager,
    ) {}
    
    /**
     * Get exchange rate from source currency to target currency
     * 
     * @param string $sourceCurrency Source currency code (e.g. 'USD')
     * @param string $targetCurrency Target currency code (e.g. 'EUR')
     * @return float Exchange rate value
     * @throws Exception If exchange rate cannot be obtained
     */
    public function getExchangeRate(string $sourceCurrency, string $targetCurrency): float
    {
        // If same currency, rate is 1.0
        if ($sourceCurrency === $targetCurrency) {
            return 1.0;
        }
        
        $date = $date ?? new DateTime();
        $date->setTime(0, 0, 0);
        
        // Check if we have the rate in database
        $exchangeRate = $this->currencyExchangeRepository->findOneBy([
            'fromCurrency' => $sourceCurrency,
            'toCurrency' => $targetCurrency,
            'date' => $date,
        ]);

        if ($exchangeRate !== null) {
            return $exchangeRate->getRate();
        }
        
        // We don't have the rate - fetch from API
        $rate = $this->fetchExchangeRateFromApi($sourceCurrency, $targetCurrency);
        
        // Save the result to database
        $this->saveExchangeRate($sourceCurrency, $targetCurrency, $rate, $date);
        
        return $rate;
    }
    
    /**
     * Convert amount from source currency to target currency
     * 
     * @param float $amount Amount to convert
     * @param string $sourceCurrency Source currency code
     * @param string $targetCurrency Target currency code
     * @param DateTime|null $date Date for conversion (defaults to today)
     * @return float Converted amount
     * @throws Exception If exchange rate cannot be obtained
     */
    public function convert(
        float $amount,
        string $sourceCurrency,
        string $targetCurrency,
        ?DateTime $date = null
    ): float {
        $rate = $this->getExchangeRate($sourceCurrency, $targetCurrency, $date);
        return $amount * $rate;
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
     * Set exchange rate manually (useful for testing or when having external data)
     * 
     * @param string $sourceCurrency Source currency code
     * @param string $targetCurrency Target currency code
     * @param float $rate Exchange rate value
     * @param DateTime|null $date Date, defaults to today
     */
    public function setExchangeRate(
        string $sourceCurrency,
        string $targetCurrency,
        float $rate,
        ?DateTime $date = null
    ): void {
        $date = $date ?? new DateTime();
        $date->setTime(0, 0, 0);
        
        $existingRate = $this->currencyExchangeRepository->findOneBy([
            'fromCurrency' => $sourceCurrency,
            'toCurrency' => $targetCurrency,
            'date' => $date,
        ]);
        
        if ($existingRate !== null) {
            $existingRate->setRate($rate);
        } else {
            $this->saveExchangeRate($sourceCurrency, $targetCurrency, $rate, $date);
        }
        
        $this->entityManager->flush();
    }
}