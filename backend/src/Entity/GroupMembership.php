<?php
// src/Entity/GroupMembership.php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Exception\InvalidStatusChangeException;
use Symfony\Component\Serializer\Annotation\Groups;
use App\State\GroupMembershipProvider;
use App\Repository\GroupMembershipRepository;
use App\State\GroupMembershipProcessor;
use ApiPlatform\Metadata\Link;
use App\Entity\Group;
use App\Entity\User;

#[ApiResource(
    security: "is_granted('ROLE_USER')", 
    normalizationContext: ['groups' => ['group_membership:read']]
)]
#[GetCollection(
    name: 'get_group_invites',
    provider: GroupMembershipProvider::class, 
    uriTemplate: '/invites'
)]
#[GetCollection(
    name: 'get_group_members',
    uriTemplate: '/groups/{groupId}/members',
    uriVariables: [
        'groupId' => [
            'from_class' => Group::class, 
            'from_property' => 'id', 
            'to_property' => 'group'
        ]
    ],
    processor: GroupMembershipProvider::class,
    normalizationContext: ['groups' => ['group_membership:members']]
)]
#[Post(
    denormalizationContext: ['groups' => ['group_membership:create']], 
    uriTemplate: '/groups/{groupId}/members', 
    uriVariables: [
        'groupId' => [
            'from_class' => Group::class, 
            'from_property' => 'id', 
            'to_property' => 'group'
        ]
    ],
    processor: GroupMembershipProcessor::class
)]
#[Patch(
    security: "is_granted('ROLE_USER') and is_granted('EDIT', object)", 
    denormalizationContext: ['groups' => ['group_membership:patch']],
    uriTemplate: '/invites/{id}',
    processor: GroupMembershipProcessor::class
)]
#[Delete(
    security: "is_granted('ROLE_USER') and is_granted('DELETE', object)", 
    uriTemplate: '/groups/{groupId}/members/{userId}',
    uriVariables: [
        'groupId' => new Link(
            fromClass: Group::class, 
            fromProperty: 'groupMemberships'
        ),
        'userId' => new Link(
            fromClass: User::class, 
            fromProperty: 'groupMemberships'
        )
    ],
    name: 'delete_group_membership_admin',
    provider: GroupMembershipProvider::class,
    processor: GroupMembershipProcessor::class
)]
#[Delete(
    security: "is_granted('ROLE_USER') and is_granted('DELETE', object)", 
    uriTemplate: '/groups/{groupId}/membership',
    uriVariables: [
        'groupId' => new Link(
            fromClass: Group::class, 
            fromProperty: 'groupMemberships'
        )
    ],
    name: 'delete_group_membership_user',
    provider: GroupMembershipProvider::class,
    processor: GroupMembershipProcessor::class
)]
#[ORM\Entity(repositoryClass: GroupMembershipRepository::class)]
#[ORM\UniqueConstraint(name: 'user_group_unique', columns: ['user_id', 'group_id'])]
#[ORM\Table(name: 'group_membership')]
class GroupMembership
{
    const STATUS_PENDING = 'pending';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REJECTED = 'rejected';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'groupMemberships')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['group_membership:read', 'group_membership:create', 'group_membership:members', 'group:read'])]
    private User $user;

    #[ORM\ManyToOne(targetEntity: Group::class, inversedBy: 'groupMemberships')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups(['group_membership:read'])]
    private Group $group;

    #[ORM\Column(type: 'string', length: 20)]
    #[Assert\Choice(choices: [self::STATUS_PENDING, self::STATUS_ACCEPTED, self::STATUS_REJECTED])]
    #[Groups(['group_membership:read', 'group_membership:patch'])]
    private string $status = self::STATUS_PENDING;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
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

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
}