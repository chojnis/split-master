<?php

namespace App\Entity;

use App\Repository\CurrencyExchangeRepository;
use Doctrine\ORM\Mapping as ORM;
use DateTime;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use App\State\CurrencyExchangeProvider;
use App\Entity\Currency;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use ApiPlatform\Metadata\Link;

#[ApiResource(
    security: "is_granted('ROLE_USER')",
    normalizationContext: ['groups' => ['currency_exchange:read']]
)]
#[Get(
    uriTemplate: '/currency-exchange/{fromCurrencyId}/{toCurrencyId}',
    openapi: new Operation(
        parameters: [
            new Parameter(
                name: 'fromCurrencyId',
                in: 'path',
                description: 'ID of the source currency.',
                required: true,
                schema: ['type' => 'integer']
            ),
            new Parameter(
                name: 'toCurrencyId',
                in: 'path',
                description: 'ID of the target currency.',
                required: true,
                schema: ['type' => 'integer']
            ),
            new Parameter(
                name: 'date',
                in: 'query',
                description: 'Date in format YYYY-MM-DD. If not provided, current date is used.',
                required: false,
                schema: ['type' => 'string']
            ),
        ],
    ),
    provider: CurrencyExchangeProvider::class
)]

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
    #[Groups(['currency_exchange:read'])]
    private ?string $fromCurrency = null;

    #[ORM\Column(length: 3)]
    #[Groups(['currency_exchange:read'])]
    private ?string $toCurrency = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 6)]
    #[Groups(['currency_exchange:read'])]
    private ?float $rate = null;

    #[ORM\Column(type: 'date')]
    #[Groups(['currency_exchange:read'])]
    private ?\DateTimeInterface $date = null;

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

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
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