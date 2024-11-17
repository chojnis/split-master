<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: 'App\Repository\TransactionRepository')]
#[ApiResource(
    normalizationContext: ['groups' => ['transaction:read']],
    denormalizationContext: ['groups' => ['transaction:write']],
)]
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

    #[ORM\ManyToOne(targetEntity: 'App\Entity\User')]
    #[ORM\JoinColumn(name: 'payer_id', referencedColumnName: 'id', nullable: false)]
    #[Groups(['transaction:read', 'transaction:write'])]
    #[Assert\NotNull(message: 'A payer must be assigned to the transaction.')]
    private User $payer;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Group')]
    #[ORM\JoinColumn(name: 'group_id', referencedColumnName: 'id', nullable: false)]
    #[Groups(['transaction:read', 'transaction:write'])]
    #[Assert\NotNull(message: 'A group must be associated with the transaction.')]
    private Group $group;

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

    public function setCreatedAt(\DateTime $created_at): self
    {
        $this->created_at = $created_at;
        return $this;
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
