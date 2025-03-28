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

#[ApiResource(security: "is_granted('ROLE_USER')", normalizationContext: ['groups' => ['group:read']], denormalizationContext: ['groups' => ['group:write']])]
#[GetCollection(provider: GroupProvider::class)]
#[Get(provider: GroupProvider::class)]
#[Post(processor: GroupProcessor::class)]
#[Patch(security: "is_granted('ROLE_USER') and object.getOwner() == user")]
#[Delete(security: "is_granted('ROLE_USER') and object.getOwner() == user")]
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
    #[Groups(groups: ['group:read', 'group:write', 'transaction:read'])]
    private string $groupName;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Opis nie może przekroczyć ilości znaków: {{ limit }}.'
    )]
    #[Groups(groups: ['group:read', 'group:write', 'transaction:read'])]
    private ?string $description = null;

    #[ORM\OneToMany(targetEntity: GroupMembership::class, mappedBy: 'group', cascade: ["persist"])]
    // #[Groups(groups: ['group:read'])]
    private Collection $groupMemberships;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'owner_id', referencedColumnName: 'id', nullable: false)]
    // #[Groups(groups: ['group:read'])]
    private User $owner;

    #[ORM\OneToMany(targetEntity: Transaction::class, mappedBy: 'group')]
    private Collection $transactions;

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

    public function setGroupName(string $groupName): static
    {
        $this->groupName = $groupName;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getOwner(): User
    {
        return $this->owner;
    }

    public function setOwner(User $owner): static
    {
        $this->owner = $owner;
        return $this;
    }

    public function isMember(User $user): bool
    {
        return $this->users->contains($user);
    }
}
