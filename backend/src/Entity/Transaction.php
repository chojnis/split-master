<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Repository\TransactionRepository;
use App\State\TransactionProvider;
use App\State\TransactionProcessor;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\OpenApi\Model\Parameter;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\Metadata\Link;
use App\Dto\TransactionRequest;
use App\Entity\Group;
use App\Entity\Currency;
use App\Entity\User;
use App\Entity\TransactionEntry;
use App\State\Transaction\TransactionPostProcessor;
use App\Dto\Transaction\TransactionResponse;

#[ApiResource(
    security: "is_granted('ROLE_USER')",
    denormalizationContext: ['groups' => ['transaction:write']],
    normalizationContext: ['groups' => ['transaction:read']],
)]
#[Get(
    provider: TransactionProvider::class,
    output: TransactionResponse::class,
)]
#[GetCollection(
    uriTemplate: '/groups/{groupId}/transactions',
    uriVariables: [
        'groupId' => [
            'from_class' => Group::class,
            'from_property' => 'id',
            'to_property' => 'group'
        ],
    ],
    provider: TransactionProvider::class,
    openapi: new Operation(
        parameters: [
            new Parameter(
                name: 'payees',
                in: 'query',
                schema: ['type' => 'string'],
                description: 'Filter transactions by payees IDs.'
            ),
            new Parameter(
                name: 'payer',
                in: 'query',
                schema: ['type' => 'string'],
                description: 'Filter transactions by payer ID.'
            )
        ]
    ),
)]
#[Post(
    uriTemplate: '/groups/{groupId}/transactions',
    uriVariables: [
        'groupId' => new Link(
            fromClass: Group::class,
            fromProperty: 'transactions'
        ),
    ],
    // name: 'create',
    // provider: TransactionProvider::class,
    // processor: TransactionProcessor::class,
    provider: TransactionProvider::class,
    processor: TransactionPostProcessor::class,
    input: TransactionRequest::class,
    output: TransactionResponse::class
)]
#[Patch(
    name: 'patch',
    processor: TransactionProcessor::class,
    input: TransactionRequest::class,
)]
#[Delete(
    name: 'delete',
    processor: TransactionProcessor::class
)]
#[ORM\Entity(repositoryClass: TransactionRepository::class)]
#[ORM\Table(name: 'transaction')]
class Transaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['transaction:read'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['transaction:read'])]
    private ?string $name = null;

    #[ORM\ManyToOne(targetEntity: Currency::class)]
    #[ORM\JoinColumn(name: 'currency_id', referencedColumnName: 'id', nullable: false)]
    #[Groups(['transaction:read'])]
    private ?Currency $currency = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 6)]
    #[Groups(['transaction:read'])]
    private ?float $exchangeRate = null;

    #[ORM\ManyToOne(targetEntity: Group::class, inversedBy: 'transactions')]
    #[ORM\JoinColumn(name: 'group_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Group $group = null;

    #[ORM\OneToMany(mappedBy: 'transaction', targetEntity: TransactionEntry::class, cascade: ['persist', 'remove'])]
    // #[Groups(['transaction:read'])]
    private Collection $entries;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['transaction:read'])]
    private ?\DateTime $transactionDate = null;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['transaction:read'])]
    private ?\DateTime $createdAt = null;

    public function __construct()
    {
        $this->entries = new ArrayCollection();
        $this->transactionDate = new \DateTime();
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    public function setCurrency(Currency $currency): self
    {
        $this->currency = $currency;
        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function getTransactionDate(): \DateTime
    {
        return $this->transactionDate;
    }

    public function setTransactionDate(\DateTime $transactionDate): self
    {
        $this->transactionDate = $transactionDate;
        return $this;
    }


    public function getGroup(): Group
    {
        return $this->group;
    }

    public function setGroup(Group $group): self
    {
        $this->group = $group;
        return $this;
    }

    public function getExchangeRate(): float
    {
        return $this->exchangeRate;
    }

    public function setExchangeRate(float $exchangeRate): self
    {
        $this->exchangeRate = $exchangeRate;
        return $this;
    }

    public function getEntries(): Collection
    {
        return $this->entries;
    }
}

