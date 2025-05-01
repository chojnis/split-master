<?php

namespace App\State\GroupMembership;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use App\Repository\GroupMembershipRepository;
use App\Repository\GroupRepository;
use ApiPlatform\Metadata\GetCollection;

/**
 * 
 * Provider for managing group membership data and operations.
 * Implements the ProviderInterface to ensure standardized data access methods.
 * 
 */
class GroupMembershipProvider implements ProviderInterface
{
    public function __construct(
        private GroupMembershipRepository $groupMembershipRepository,
        private GroupRepository $groupRepository,
        private Security $security
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): iterable|null
    {
        if(!$operation instanceof GetCollection) {
            return null;
        }

        $user = $this->security->getUser();
        if (!$user) {
            throw new \AccesDeniedException('User not authenticated.');
        }

        $group = $this->groupRepository->find($uriVariables['groupId']);
        if (!$group) {
            throw new \InvalidArgumentException('Group not found.');
        }

        if ($group->getOwner() !== $user) {
            throw new \AccessDeniedException('You are not the owner of this group.');
        }

        return $this->groupMembershipRepository->getGroupMemberships($group);
    }
}