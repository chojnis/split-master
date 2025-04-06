<?php

namespace App\Repository;

use App\Entity\GroupMembership;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Group;
use App\Entity\User;

/**
 * @extends ServiceEntityRepository<Group>
 */
class GroupMembershipRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GroupMembership::class);
    }

    public function save(GroupMembership $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(GroupMembership $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function isUserMemberOfGroup(User $user, Group $group): ?GroupMembership
    {
       return $this->findOneBy([
            'user' => $user,
            'group' => $group,
            'status' => GroupMembership::STATUS_ACCEPTED,
        ]);
    }

    public function getUserGroupInvites(User $user): iterable
    {
        return $this->createQueryBuilder('g')
            ->where('g.user = :user')
            ->andWhere('g.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', GroupMembership::STATUS_PENDING)
            ->getQuery()
            ->getResult();
    }

    public function getGroupMembers(Group $group): iterable
    {
        return $this->createQueryBuilder('gm')
            ->where('gm.group = :group')
            ->andWhere('gm.status = :status')
            ->setParameter('group', $group)
            ->setParameter('status', GroupMembership::STATUS_ACCEPTED)
            ->getQuery()
            ->getResult();
    }

    public function getGroupMembership(User $user, Group $group): ?GroupMembership
    {
        return $this->findOneBy([
            'user' => $user,
            'group' => $group
        ]);
    }

    public function areUsersMembersOfSameGroup()
    {
        $qb = $this->createQueryBuilder('gm1')
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
