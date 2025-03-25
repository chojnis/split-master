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

    public function isUserMemberOfGroup(User $user, Group $group): ?GroupMembership
    {
       return $this->entityManager->getRepository(GroupMembership::class)
            ->findOneBy([
                'user' => $user,
                'group' => $group,
                'status' => GroupMembership::STATUS_ACCEPTED,
            ]);
    }

    public function getUserGroups(User $user): iterable
    {
        return $this->entityManager->getRepository(Group::class)
            ->createQueryBuilder('g')
            ->innerJoin('g.groupMemberships', 'gm', 'WITH', 'gm.user = :user AND gm.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', GroupMembership::STATUS_ACCEPTED)
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
            ->setParameter('status', GroupMembership::STATUS_PENDING)
            ->getQuery()
            ->getResult();
    }

    public function getGroupMembers(Group $group): iterable
    {
        return $this->entityManager->getRepository(GroupMembership::class)
        ->createQueryBuilder('gm')
        ->where('gm.group = :group')
        ->andWhere('gm.status = :status')
        ->setParameter('group', $group)
        ->setParameter('status', GroupMembership::STATUS_ACCEPTED)
        ->getQuery()
        ->getResult();
    }

    public function getGroupUsers(Group $group): iterable
    {
        return $this->entityManager->getRepository(User::class)
            ->createQueryBuilder('u')
            ->innerJoin('u.groupMemberships', 'gm', 'WITH', 'gm.group = :group AND gm.status = :status')
            ->setParameter('group', $group)
            ->setParameter('status', GroupMembership::STATUS_ACCEPTED)
            ->getQuery()
            ->getResult();
    }

    public function getGroupMembership(User $user, Group $group): ?GroupMembership
    {
        return $this->entityManager->getRepository(GroupMembership::class)
            ->findOneBy([
                'user' => $user,
                'group' => $group
            ]);
    }

    public function ensureMembership(User $user, Group $group): GroupMembership
    {
        $existingMembership = $this->getGroupMembership($user, $group);
        if($existingMembership) {
            return $existingMembership;
        }
        
        $membership = new GroupMembership();
        $membership->setUser($user);
        $membership->setGroup($group);
        $membership->setStatus(GroupMembership::STATUS_ACCEPTED);

        $this->entityManager->persist($membership);
        $this->entityManager->flush();

        return $membership;
    }

    public function areUsersMembersOfSameGroup(User $user1, User $user2): bool
    {
        if($user1 === $user2) {
            return true;
        }

        $qb = $this->entityManager->getRepository(GroupMembership::class)
        ->createQueryBuilder('gm1')
        ->select('COUNT(gm1)')
        ->innerJoin('gm1.group', 'g')
        ->innerJoin(
            GroupMembership::class, 
            'gm2', 
            'WITH', 
            'gm2.group = g AND gm2.user = :user2 AND gm2.status = :status'
        )
        ->where('gm1.user = :user1')
        ->andWhere('gm1.status = :status')
        ->setParameter('user1', $user1)
        ->setParameter('user2', $user2)
        ->setParameter('status', GroupMembership::STATUS_ACCEPTED);

        return $qb->getQuery()->getSingleScalarResult() > 0;
    }
}
