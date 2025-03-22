<?php

namespace App\State;

use App\Entity\Transaction;
use App\Entity\Group;
use Doctrine\ORM\EntityManagerInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use ApiPlatform\State\ProviderInterface;
use ApiPlatform\Metadata\Operation;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class TransactionProvider implements ProviderInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager, 
        private Security $security,
        // add logger
        private LoggerInterface $logger
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): iterable
    {
        $this->logger->info("TRANSACTION PROVIDER START");

        $user = $this->security->getUser();
        if (!$user) {
            return [];
        }

        $queryBuilder = $this->entityManager->getRepository(Transaction::class)
            ->createQueryBuilder('t')
            // ->where('t.user = :user OR g.owner = :user OR g.users = :user')
            ->where('1 = 1');

        if (isset($uriVariables['groupId'])) {
            $groupId = $uriVariables['groupId'];
            $group = $this->entityManager->getRepository(Group::class)->find($groupId);
            if (!$group) {
                throw new AccessDeniedException('Group not found');
            }

            if (!$group->isMember($user)) {
                throw new AccessDeniedException('User is not associated with the group');
            }

            if(isset($context['filters']['payees'])) {
                $userIds = explode(',', $context['filters']['payees']);
                $queryBuilder
                    ->leftJoin('t.payees', 'u')
                    ->andWhere(
                        $queryBuilder->expr()->orX(
                            't.payer IN (:payerUserIds)',
                            'u.id IN (:payeesUserIds)'
                        )
                    )
                    ->setParameter('payerUserIds', $userIds)
                    ->setParameter('payeesUserIds', $userIds);
            }

            $queryBuilder
                ->innerJoin('t.group', 'g')
                ->andWhere('g.id = :groupId')
                ->setParameter('groupId', $uriVariables['groupId']);
        } else {
            $queryBuilder
                ->andWhere('t.payer = :user OR :user MEMBER OF t.payees')
                ->setParameter('user', $user);
        }

        return $queryBuilder->getQuery()->getResult();
    }
}
