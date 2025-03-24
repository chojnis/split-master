<?php

namespace App\Service;

use App\Entity\GroupMembership;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use App\Entity\User;
use App\Entity\Group;

class GroupMembershipService
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    public function isUserMemberOfGroup(User $user, Group $group): bool
    {
        $membership = $this->entityManager->getRepository(GroupMembership::class)
            ->findOneBy([
                'user' => $user,
                'group' => $group,
                'status' => 'accepted',
            ]);

        return $membership !== null;
    }

    public function getUserGroups(User $user): iterable
    {
        return $this->entityManager->getRepository(Group::class)
            ->createQueryBuilder('g')
            ->innerJoin('g.groupMemberships', 'gm', 'WITH', 'gm.user = :user AND gm.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', 'accepted')
            ->getQuery()
            ->getResult();
    }

    public function getUserGroupInvites(User $user): iterable
    {
        return $this->entityManager->getRepository(GroupMembership::class)
            ->createQueryBuilder('g')
            ->where('g.user = :user')
            ->andWhere('g.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', 'pending')
            ->getQuery()
            ->getResult();
    }
}
