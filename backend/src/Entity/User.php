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
use ApiPlatform\Metadata\Link;
use App\Dto\UserUpdateDto;
use App\State\UserUpdateProcessor;
use App\State\UserDeleteProcessor;

#[ApiResource(
    normalizationContext: ['groups' => ['user:read']],
    denormalizationContext: ['groups' => ['user:create', 'user:update']]
)]
#[Get(
    security: "is_granted('ROLE_USER') and is_granted('VIEW', object)"
)]
#[GetCollection(
    security: "is_granted('ROLE_USER')",
    uriTemplate: '/groups/{groupId}/members',
    uriVariables: [
        'groupId' => new Link(
            fromClass: Group::class,
            fromProperty: 'groupMemberships'
        ),
    ],
    provider: UserProvider::class
)]
#[Post(
    name: 'register',
    uriTemplate: '/register',
    processor: UserPasswordHasher::class,
    validationContext: ['groups' => ['user:create']],
)]
#[Patch(
    uriTemplate: '/user',
    security: "is_granted('ROLE_USER')",
    processor: UserUpdateProcessor::class,
    provider: UserProvider::class,
    validationContext: ['groups' => ['Default', 'user:update']],
    denormalizationContext: ['groups' => ['user:update']],
    input: UserUpdateDto::class,
)]
#[Delete(
    uriTemplate: '/user',
    security: "is_granted('ROLE_USER')",
    provider: UserProvider::class,
    processor: UserDeleteProcessor::class,
)]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[UniqueEntity('email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[Groups(['user:read', 'group_membership:members', 'transaction:read', 'group:read'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(
        message: 'Email nie może być pusty.',
        groups: ['user:create']
    )]
    #[Assert\Email(
        message: 'Niepoprawny adres email.',
        groups: ['user:create']
    )]
    #[Groups(['user:read', 'user:create', 'group_membership:members', 'transaction:read', 'group:read'])]
    private ?string $email = null;

    #[ORM\Column]
    private ?string $password = null;

    #[Assert\NotBlank(
        message: 'Hasło nie może być puste.'
    )]
    #[Groups(['user:create', 'user:update'])]
    private ?string $plainPassword = null;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column(type: Types::STRING, length: 255, unique: true, nullable: true)]
    #[Groups(['user:read', 'user:create', 'user:update', 'group_membership:members', 'transaction:read', 'group:read'])]
    private ?string $username = null;

    #[ORM\OneToMany(targetEntity: GroupMembership::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $groupMemberships;

    #[ORM\OneToMany(mappedBy: 'payer', targetEntity: Transaction::class)]
    private Collection $transactionsAsPayer;

    #[ORM\ManyToMany(mappedBy: 'payees', targetEntity: Transaction::class)]
    private Collection $transactionsAsPayee;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $deletedAt = null;

    public function __construct()
    {
        $this->groupMemberships = new ArrayCollection();
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

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

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

    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
    }
}
