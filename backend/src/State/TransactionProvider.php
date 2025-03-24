<?php

namespace App\State;

use App\Entity\Transaction;
use App\Entity\Group;
use App\Entity\GroupMembership;
use Doctrine\ORM\EntityManagerInterface;
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
        private LoggerInterface $logger
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): iterable
    {
        $user = $this->security->getUser();
        if (!$user) {
            return [];
        }
    
        $queryBuilder = $this->entityManager->getRepository(Transaction::class)
            ->createQueryBuilder('t')
            ->innerJoin('t.group', 'g')
            ->innerJoin('g.groupMemberships', 'gm', 'WITH', 'gm.user = :user AND gm.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', 'accepted');
    
        // Filtruj po grupie (groupId)
        if (isset($uriVariables['groupId'])) {
            $queryBuilder
                ->andWhere('g.id = :groupId')
                ->setParameter('groupId', $uriVariables['groupId']);
        } else {
            $queryBuilder
                ->leftJoin('t.payees', 'u')
                ->orWhere('t.payer = :user')
                ->orWhere('u.id = :user');
        }
    
        if (isset($context['filters']['payer'])) {
            $queryBuilder
                ->andWhere('t.payer = :payer')
                ->setParameter('payer', $context['filters']['payer']);
        }
    
        if (isset($context['filters']['payees'])) {
            $userIds = explode(',', $context['filters']['payees']);
            $queryBuilder
                ->leftJoin('t.payees', 'u')
                ->andWhere('u.id IN (:payeesUserIds)')
                ->setParameter('payeesUserIds', $userIds);
        }
    
        return $queryBuilder->getQuery()->getResult();
    }
}