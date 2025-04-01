<?php

namespace App\Entity;

use App\Repository\CurrencyExchangeRepository;
use Doctrine\ORM\Mapping as ORM;
use DateTime;

#[ORM\Entity(repositoryClass: CurrencyExchangeRepository::class)]
#[ORM\Table(name: 'currency_exchange')]
#[ORM\Index(columns: ['from_currency', 'to_currency', 'date'], name: 'currency_exchange_idx')]
class CurrencyExchange
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 3)]
    private ?string $fromCurrency = null;

    #[ORM\Column(length: 3)]
    private ?string $toCurrency = null;

    #[ORM\Column(type: 'date')]
    private ?DateTime $date = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 6)]
    private ?float $rate = null;

    public function __construct()
    {
        $this->date = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFromCurrency(): ?string
    {
        return $this->fromCurrency;
    }

    public function setFromCurrency(string $fromCurrency): self
    {
        $this->fromCurrency = $fromCurrency;
        return $this;
    }

    public function getToCurrency(): ?string
    {
        return $this->toCurrency;
    }

    public function setToCurrency(string $toCurrency): self
    {
        $this->toCurrency = $toCurrency;
        return $this;
    }

    public function getDate(): ?DateTime
    {
        return $this->date;
    }

    public function setDate(DateTime $date): self
    {
        $this->date = $date;
        return $this;
    }

    public function getRate(): ?float
    {
        return $this->rate;
    }

    public function setRate(float $rate): self
    {
        $this->rate = $rate;
        return $this;
    }
}