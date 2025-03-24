<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\UserRepository;
use App\State\UserPasswordHasher;
use Doctrine\DBAL\Types\Types;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use App\Controller\User\RegisterUser;
use App\State\UserRegisterProcessor;
use App\Dto\User\RegisterUserDto;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\RequestBody;
use ApiPlatform\OpenApi\Model\Response;
use App\Entity\GroupMembership;
use App\Entity\Transaction;
use App\Entity\Group;
use App\State\UserProvider;

#[ApiResource(
    security: "is_granted('ROLE_USER')",
    normalizationContext: ['groups' => ['user:read']],
    denormalizationContext: ['groups' => ['user:create', 'user:update']]
)]
#[GetCollection(
    uriTemplate: '/groups/{groupId}/users',
    uriVariables: [
        'groupId' => [
            'from_class' => Group::class,
            'from_property' => 'id',
            'to_property' => 'group'
        ],
    ],
    provider: UserProvider::class
)]
#[Get(
    security: "is_granted('ROLE_USER') and object == user",
)]
#[Post(
    name: 'register',
    uriTemplate: '/register',
    processor: UserRegisterProcessor::class,
    validationContext: ['groups' =>
        ['Default', 'user:create']
    ],
)]
#[Patch(
    security: "is_granted('ROLE_USER') and object == user",
    securityMessage: "You can only edit your own account.",
    processor: UserPasswordHasher::class
)]
#[Delete(
    security: "is_granted('ROLE_USER') and object == user",
    securityMessage: "You can only delete your own account."
)]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[UniqueEntity('email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[Groups(['user:read'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Email]
    #[Groups(['user:read', 'user:create', 'user:update'])]
    private ?string $email = null;

    #[ORM\Column]
    private ?string $password = null;

    #[Assert\NotBlank(groups: ['user:create'])]
    #[Groups(['user:create', 'user:update'])]
    private ?string $plainPassword = null;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column(type: Types::STRING, length: 255, unique: true, nullable: true)]
    #[Groups(['user:read', 'user:create', 'user:update'])]
    private ?string $username = null;

    // #[ORM\ManyToMany(targetEntity: Group::class, inversedBy: 'users')]
    // #[ORM\JoinTable(name: 'user_groups')]
    // private Collection $groups;

    #[ORM\OneToMany(targetEntity: GroupMembership::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $groupMemberships;

    // #[ORM\OneToMany(mappedBy: 'user', targetEntity: Transaction::class)]
    // private Collection $transactions;

    #[ORM\OneToMany(mappedBy: 'payer', targetEntity: Transaction::class)]
    private Collection $transactionsAsPayer;

    #[ORM\ManyToMany(mappedBy: 'payees', targetEntity: Transaction::class)]
    private Collection $transactionsAsPayee;


    public function __construct()
    {
        // $this->groups = new ArrayCollection();
        $this->groupMemberships = new ArrayCollection();
        // $this->transactions = new ArrayCollection();
        $this->transactionsAsPayer = new ArrayCollection();
        $this->transactionsAsPayee = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    // public function getUsername(): ?string
    // {
    //     return $this->username;
    // }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    // public function getGroups(): Collection
    // {
    //     return $this->groups;
    // }

    // public function addGroup(Group $group): self
    // {
    //     if (!$this->groups->contains($group)) {
    //         $this->groups->add($group);
    //         $group->addUser($this);
    //     }

    //     return $this;
    // }

    // public function removeGroup(Group $group): self
    // {
    //     if ($this->groups->removeElement($group)) {
    //         $group->removeUser($this);
    //     }

    //     return $this;
    // }

    // public function isMemberOf(Group $group): bool
    // {
    //     return $this->groups->contains($group);
    // }

    public function getGroupMemberships(): Collection
    {
        return $this->groupMemberships;
    }

    public function addGroupMembership(GroupMembership $groupMembership): self
    {
        if (!$this->groupMemberships->contains($groupMembership)) {
            $this->groupMemberships->add($groupMembership);
            $groupMembership->setUser($this);
        }

        return $this;
    }

    public function removeGroupMembership(GroupMembership $groupMembership): self
    {
        if ($this->groupMemberships->removeElement($groupMembership)) {
            if ($groupMembership->getUser() === $this) {
                $groupMembership->setUser(null);
            }
        }
        return $this;
    }
}
