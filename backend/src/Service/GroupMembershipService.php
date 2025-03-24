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

    /**
     * @deprecated Use getGroupMembership instead
     */
    public function isUserMemberOfGroup(User $user, Group $group): false|GroupMembership
    {
        $membership = $this->entityManager->getRepository(GroupMembership::class)
            ->findOneBy([
                'user' => $user,
                'group' => $group,
                'status' => GroupMembership::STATUS_ACCEPTED,
            ]);

        if(!$membership) {
            return false;
        }

        return $membership;
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

    public function getGroupMembership(User $user, Group $group): ?GroupMembership
    {
        return $this->entityManager->getRepository(GroupMembership::class)
            ->findOneBy([
                'user' => $user,
                'group' => $group,
                'status' => GroupMembership::STATUS_ACCEPTED
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
}
