<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\GroupMembership;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use ApiPlatform\Metadata\GetCollection;
use App\Service\GroupMembershipService;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use ApiPlatform\Metadata\DeleteOperationInterface;
use Psr\Log\LoggerInterface;
use App\Entity\User;
use App\Entity\Group;

class GroupMembershipProvider implements ProviderInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.item_provider')]
        private ProviderInterface $itemProvider,
        private GroupMembershipService $groupMembershipService,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
        private Security $security,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): iterable|GroupMembership|null
    {
        $user = $this->security->getUser();
        if (!$user) {
            throw new \InvalidArgumentException('User not found');
        }

        if($operation instanceof DeleteOperationInterface) {
            $groupId = $uriVariables['groupId'];
            $userId = $uriVariables['userId'];

            if($userId) {
                $user = $this->entityManager->getRepository(User::class)->find($userId);
                if (!$user) {
                    throw new \InvalidArgumentException('User not found');
                }
            }

            $group = $this->entityManager->getRepository(Group::class)->find($groupId);
            if (!$group) {
                throw new \InvalidArgumentException('Group not found');
            }

            $groupMembership = $this->groupMembershipService->getGroupMembership($user, $group);
            if (!$groupMembership) {
                throw new \InvalidArgumentException('Group membership not found');
            }

            return $groupMembership;
        }

        if($operation instanceof GetCollection) {
            if($operation->getName() === 'get_group_members') {
                $groupId = $uriVariables['groupId'];
                $group = $this->entityManager->getRepository(Group::class)->find($groupId);
                if (!$group) {
                    throw new \InvalidArgumentException('Group not found');
                }
                if (!$this->groupMembershipService->getGroupMembership($user, $group)) {
                    throw new AccessDeniedException();
                }
                return $this->groupMembershipService->getGroupMembers($group);
            }
            return $this->groupMembershipService->getUserGroupInvites($user);
        }

        $groupMembershipId = $uriVariables['id'];
        $groupMembership = $this->entityManager->getRepository(GroupMembership::class)->find($groupMembershipId);
        if($groupMembership->getUser() !== $user) {
            throw new AccessDeniedException();
        }

        return $this->itemProvider->provide($operation, $uriVariables, $context);
    }
}