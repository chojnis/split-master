<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\GroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\State\GroupProvider;
use App\Entity\GroupMembership;
use App\Entity\Transaction;
use App\Entity\User;
use Symfony\Component\Serializer\Annotation\Groups;
use App\State\GroupProcessor;
use App\State\GroupDebtProvider;
use App\Dto\Group\GroupDebtResponse;
use ApiPlatform\Metadata\Link;

#[ApiResource(security: "is_granted('ROLE_USER')", normalizationContext: ['groups' => ['group:read']], denormalizationContext: ['groups' => ['group:write']])]
#[GetCollection(provider: GroupProvider::class)]
#[Get(provider: GroupProvider::class)]
#[Post(processor: GroupProcessor::class)]
#[Patch(security: "is_granted('ROLE_USER') and object.getOwner() == user")]
#[Delete(security: "is_granted('ROLE_USER') and object.getOwner() == user")]

#[Get(
    uriTemplate: '/groups/{id}/debts',
    // uriVariables: [
    //     'id' => new Link(
    //         fromClass: Group::class
    //     ),
    // ],
    provider: GroupDebtProvider::class,
    output: GroupDebtResponse::class,
    normalizationContext: ['groups' => ['debt:read']],
)]

#[ORM\Entity(repositoryClass: GroupRepository::class)]
#[ORM\Table(name: '`group`')]
class Group
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(groups: ['group:read', 'transaction:read'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 100)]
    #[Assert\NotBlank(message: 'Nazwa grupy nie może być pusta.')]
    #[Assert\Length(
        max: 100,
        maxMessage: 'Nazwa grupy nie może przekroczyć ilości znaków: {{ limit }}.'
    )]
    #[Groups(groups: ['group:read', 'group:write', 'transaction:read', 'group_membership:invites'])]
    private string $groupName;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Opis nie może przekroczyć ilości znaków: {{ limit }}.'
    )]
    #[Groups(groups: ['group:read', 'group:write', 'transaction:read', 'group_membership:invites'])]
    private ?string $description = null;

    #[ORM\OneToMany(targetEntity: GroupMembership::class, mappedBy: 'group', cascade: ["persist"])]
    // #[Groups(groups: ['group:read'])]
    private Collection $groupMemberships;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'owner_id', referencedColumnName: 'id', nullable: false)]
    #[Groups(groups: ['group:read'])]
    private User $owner;

    #[ORM\OneToMany(targetEntity: Transaction::class, mappedBy: 'group')]
    private Collection $transactions;

    #[ORM\ManyToOne(targetEntity: Currency::class)]
    #[ORM\JoinColumn(name: 'currency_id', referencedColumnName: 'id', nullable: false)]
    #[Groups(groups: ['group:read', 'group:write'])]
    private Currency $currency;

    public function __construct()
    {
        $this->groupMemberships = new ArrayCollection();
        $this->transactions = new ArrayCollection();
    }

    public function getGroupMemberships(): Collection
    {
        return $this->groupMemberships;
    }

    public function addGroupMembership(GroupMembership $groupMembership): self
    {
        if (!$this->groupMemberships->contains($groupMembership)) {
            $this->groupMemberships[] = $groupMembership;
            $groupMembership->setGroup($this);
        }
        return $this;
    }

    public function removeGroupMembership(GroupMembership $groupMembership): self
    {
        if ($this->groupMemberships->removeElement($groupMembership)) {
            if ($groupMembership->getGroup() === $this) {
                $groupMembership->setGroup(null);
            }
        }
        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            $user->removeGroup($this);
        }

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGroupName(): ?string
    {
        return $this->groupName;
    }

    public function setGroupName(string $groupName): self
    {
        $this->groupName = $groupName;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getOwner(): User
    {
        return $this->owner;
    }

    public function setOwner(User $owner): self
    {
        $this->owner = $owner;
        return $this;
    }

    public function isMember(User $user): bool
    {
        return $this->users->contains($user);
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
}
