<?php

namespace App\State\GroupMembership;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\GroupMembership;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use ApiPlatform\Metadata\DeleteOperationInterface;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Get;
use App\Entity\User;
use App\Repository\GroupMembershipRepository;
use App\Repository\GroupRepository;
use App\Repository\UserRepository;

class GroupMembershipDeleteAdminProvider implements ProviderInterface
{
    public function __construct(
        private GroupMembershipRepository $groupMembershipRepository,
        private GroupRepository $groupRepository,
        private UserRepository $userRepository
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): GroupMembership|null
    {
        $group = $this->groupRepository->find($uriVariables['groupId']);
        $user = $this->userRepository->find($uriVariables['userId']);
        
        if (!$group || !$user) {
            return null;
        }

        return $this->groupMembershipRepository->getGroupMembership($user, $group);
    }
}