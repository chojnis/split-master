<?php

namespace App\Repository;

use App\Entity\Transaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;
use Doctrine\Common\Collections\Criteria;

/**
 * @extends ServiceEntityRepository<Transaction>
 */
class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    // public function getUserGroups(User $user, int $page = 1, int $itemsPerPage = 30): DoctrinePaginator
    // {
    //     return new DoctrinePaginator(
    //         $this->createQueryBuilder('g')
    //             ->innerJoin('g.groupMemberships', 'gm', 'WITH', 'gm.user = :user AND gm.status = :status')
    //             ->setParameter('user', $user)
    //             ->setParameter('status', GroupMembership::STATUS_ACCEPTED)
    //             ->addCriteria(
    //                 Criteria::create()
    //                     ->setFirstResult(($page - 1) * $itemsPerPage)
    //                     ->setMaxResults($itemsPerPage)
    //             )
    //     );
    // }

    public function getGroupTransactions($group, int $page = 1, int $itemsPerPage = 30): DoctrinePaginator
    {
        return new DoctrinePaginator(
            $this->createQueryBuilder('t')
                ->where('t.group = :group')
                ->setParameter('group', $group)
                ->addCriteria(
                    Criteria::create()
                        ->setFirstResult(($page - 1) * $itemsPerPage)
                        ->setMaxResults($itemsPerPage)
                )
        );
    }
}
