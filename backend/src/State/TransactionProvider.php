<?php

namespace App\State;

use App\Entity\Transaction;
use App\Entity\Group;
use App\Entity\GroupMembership;
use App\Service\GroupMembershipService;
use Doctrine\ORM\EntityManagerInterface;
use ApiPlatform\State\ProviderInterface;
use ApiPlatform\Metadata\Operation;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Get;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class TransactionProvider implements ProviderInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.item_provider')]
        private ProviderInterface $itemProvider,
        private EntityManagerInterface $entityManager, 
        private Security $security,
        private GroupMembershipService $groupMembershipService,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): iterable|Transaction|null
    {
        $user = $this->security->getUser();
        if (!$user) {
            return [];
        }

        if($operation instanceof Post) {
            $transaction = new Transaction();
            if (isset($uriVariables['groupId'])) {
                $group = $this->entityManager->getRepository(Group::class)
                    ->find($uriVariables['groupId']);
                
                if (!$group) {
                    throw new \InvalidArgumentException('Group not found');
                }
                
                $transaction->setGroup($group);
            }

            return $transaction;
        }

        if($operation instanceof Get) {
            $transaction = $this->itemProvider->provide($operation, $uriVariables, $context);
            if (!$transaction) {
                return null;
            }

            if(!$this->groupMembershipService->isUserMemberOfGroup($user, $transaction->getGroup())) {
                throw new AccessDeniedException('You are not a member of this group.');
            }
            
            return $transaction;
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