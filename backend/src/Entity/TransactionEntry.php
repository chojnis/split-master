<?php

namespace App\Entity;

use App\Entity\Transaction;
use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class TransactionEntry
{
    public const TYPE_CREDIT = 'CREDIT';
    public const TYPE_DEBIT = 'DEBIT';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[ORM\Groups(['transaction:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Transaction::class, inversedBy: 'entries')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Transaction $transaction = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[ORM\Groups(['transaction:read'])]
    private ?float $amount = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[ORM\Groups(['transaction:read'])]
    private ?User $user = null;

    // #[ORM\Column(type: 'string', enumType: TransactionEntryType::class)]
    #[ORM\Column(type: 'string')]
    #[ORM\Groups(['transaction:read'])]
    private ?string $type = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTransaction(): ?Transaction
    {
        return $this->transaction;
    }

    public function setTransaction(Transaction $transaction): self
    {
        $this->transaction = $transaction;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }
}

enum TransactionEntryType: string
{
    case CREDIT = TransactionEntry::TYPE_CREDIT;
    case DEBIT = TransactionEntry::TYPE_DEBIT;
}
