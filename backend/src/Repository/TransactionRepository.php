<?php

namespace App\Repository;

use App\Entity\Transaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Transaction>
 */
class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    //    /**
    //     * @return Transaction[] Returns an array of Transaction objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Transaction
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function getTotalPaidByUserInGroup(User $user, Group $group): array
    {
        $result = $this->createQueryBuilder('t')
            ->select('IDENTITY(t.currency) as currencyId, SUM(t.amount) as total, SUM(t.amount * t.exchangeRate) as convertedTotal')
            ->where('t.payer = :user')
            ->andWhere('t.group = :group')
            ->groupBy('t.currency')
            ->setParameter('user', $user)
            ->setParameter('group', $group)
            ->getQuery()
            ->getResult();

        return array_column($result, 'total', 'currencyId');
    }

    public function getTotalOwedByUserInGroup(User $user, Group $group): array
    {
        $result = $this->createQueryBuilder('t')
            ->select('IDENTITY(t.currency) as currencyId, SUM(t.amount / SIZE(t.payees)) as total, SUM(t.amount * t.exchangeRate / SIZE(t.payees)) as convertedTotal')
            ->where(':user MEMBER OF t.payees')
            ->andWhere('t.group = :group')
            ->groupBy('t.currency')
            ->setParameter('user', $user)
            ->setParameter('group', $group)
            ->getQuery()
            ->getResult();

        return array_column($result, 'total', 'currencyId');
    }
}
