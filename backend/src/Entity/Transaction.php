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

#[ApiResource(
    security: "is_granted('ROLE_USER')",
    normalizationContext: ['groups' => ['transaction:read']],
    denormalizationContext: ['groups' => ['transaction:write']]
)]
#[Get(provider: TransactionProvider::class)]
#[GetCollection(
    uriTemplate: '/transactions',
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
    name: 'create',
    processor: TransactionProcessor::class,
    uriTemplate: '/groups/{groupId}/transactions',
    uriVariables: [
        'groupId' => [
            'from_class' => Group::class,
            'from_property' => 'id',
            'to_property' => 'group'
        ],
    ],
)]
#[Patch(
    name: 'patch',
    processor: TransactionProcessor::class
)]
#[Delete(
    name: 'delete',
    processor: TransactionProcessor::class
)]
#[ApiFilter(SearchFilter::class, properties: [
    'group.id' => 'exact',
    'payer.id' => 'exact',
    'payees.id' => 'exact',
])]
#[ORM\Entity(repositoryClass: 'App\Repository\TransactionRepository')]
#[ORM\Table(name: 'transaction')]
class Transaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['transaction:read'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['transaction:read', 'transaction:write'])]
    #[Assert\NotBlank(message: 'Transaction name cannot be blank.')]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Transaction name cannot exceed {{ limit }} characters.'
    )]
    private string $name;

    #[ORM\Column(type: 'decimal', scale: 2)]
    #[Groups(['transaction:read', 'transaction:write'])]
    #[Assert\NotBlank(message: 'Amount cannot be blank.')]
    #[Assert\Positive(message: 'Amount must be greater than 0.')]
    #[Assert\LessThanOrEqual(
        value: 999999.99,
        message: 'Amount cannot exceed {{ compared_value }}.'
    )]
    private float $amount;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Currency')]
    #[ORM\JoinColumn(name: 'currency_id', referencedColumnName: 'id', nullable: false)]
    #[Groups(['transaction:read', 'transaction:write'])]
    #[Assert\NotNull(message: 'Currency must be provided.')]
    private Currency $currency;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['transaction:read'])]
    #[Assert\NotBlank(message: 'Created date cannot be blank.')]
    #[Assert\Type(
        type: \DateTime::class,
        message: 'The value {{ value }} is not a valid datetime.'
    )]
    private \DateTime $created_at;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\User', inversedBy: 'transactionsAsPayer')]
    #[ORM\JoinColumn(name: 'payer_id', referencedColumnName: 'id', nullable: false)]
    #[Groups(['transaction:read', 'transaction:write'])]
    #[Assert\NotNull(message: 'A payer must be assigned to the transaction.')]
    private User $payer;

    #[ORM\ManyToMany(targetEntity: 'App\Entity\User', inversedBy: 'transactionsAsPayee')]
    #[ORM\JoinTable(name: 'transaction_payees')]
    #[Groups(['transaction:read', 'transaction:write'])]
    #[Assert\Count(
        min: 1,
        minMessage: 'At least one user must be associated with the transaction.'
    )]
    private Collection $payees;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Group')]
    #[ORM\JoinColumn(name: 'group_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[Groups(['transaction:read'])]
    #[Assert\NotNull(message: 'A group must be associated with the transaction.')]
    private Group $group;

    public function __construct()
    {
        $this->payees = new ArrayCollection();
        $this->created_at = new \DateTime();
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

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;
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
        return $this->created_at;
    }

    public function getPayer(): User
    {
        return $this->payer;
    }

    public function setPayer(User $payer): self
    {
        $this->payer = $payer;
        return $this;
    }

    public function getPayees(): Collection
    {
        return $this->payees;
    }

    public function addPayee(User $payee): self
    {
        if (!$this->payees->contains($payee)) {
            $this->payees->add($payee);
        }
        return $this;
    }

    public function removePayee(User $payee): self
    {
        $this->payees->removeElement($payee);
        return $this;
    }

    public function isAssociatedWith(User $user): bool
    {
        return $this->payer === $user || $this->payees->contains($user);
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
}
