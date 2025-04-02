<?php

namespace App\Repository;

use App\Entity\CurrencyExchange;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Currency>
 */
class CurrencyExchangeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CurrencyExchange::class);
    }

    public function findLatestExchangeRatesForCurrency(string $currency): array
    {
        return $this->createQueryBuilder('c')
            ->select('c.fromCurrency, MAX(c.date) as latestDate, c.rate')
            ->andWhere('c.toCurrency = :currency')
            ->setParameter('currency', $currency)
            ->groupBy('c.fromCurrency')
            ->orderBy('latestDate', 'DESC')
            ->getQuery()
            ->getResult();
    }
    
    public function findLatestExchangeRateForPair(string $fromCurrency, string $toCurrency): ?CurrencyExchange
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.fromCurrency = :fromCurrency AND c.toCurrency = :toCurrency')
            ->setParameter('fromCurrency', $fromCurrency)
            ->setParameter('toCurrency', $toCurrency)
            ->orderBy('c.date', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}