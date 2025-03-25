<?php

namespace App\State;

use App\Entity\User;
use App\Entity\Group;
use Doctrine\ORM\EntityManagerInterface;
use ApiPlatform\State\ProviderInterface;
use ApiPlatform\Metadata\Operation;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;
use App\Service\GroupMembershipService;

class UserProvider implements ProviderInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager, 
        private Security $security,
        private GroupMembershipService $groupMembershipService,
        private LoggerInterface $logger
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): iterable
    {
        $user = $this->security->getUser();
        if (!$user) {
            return [];
        }

        $group = $this->entityManager->getRepository(Group::class)->find($uriVariables['groupId']);
        if (!$group) {
            throw new InvalidArgumentException('Group not found');
        }

        if(!$this->groupMembershipService->isUserMemberOfGroup($user, $group)) {
            throw new AccessDeniedException('You are not a member of the group');
        }

        $queryBuilder = $this->entityManager->getRepository(User::class)
            ->createQueryBuilder('u')
            ->innerJoin('u.groupMemberships', 'gm', 'WITH', 'gm.group = :group AND gm.status = :status')
            ->setParameter('group', $group)
            ->setParameter('status', 'accepted');
    
        return $queryBuilder->getQuery()->getResult();
    }
}