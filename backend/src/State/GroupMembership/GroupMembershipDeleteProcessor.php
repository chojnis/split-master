<?php

namespace App\State\GroupMembership;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Symfony\Bundle\SecurityBundle\Security;
use ApiPlatform\Exception\InvalidArgumentException;
use ApiPlatform\Exception\AccessDeniedException;
use App\Service\GroupService;
use App\Repository\UserRepository;
use App\Repository\GroupRepository;
use App\Entity\User;

/**
 *
 * This processor implements the ProcessorInterface and 
 * processes requests to remove users from groups.
 * 
 */
final class GroupMembershipDeleteProcessor implements ProcessorInterface
{
    public function __construct(
        private GroupService $groupService,
        private GroupRepository $groupRepository,
        private UserRepository $userRepository,
        private Security $security
    ) {}

    public function process($data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $group = $data->getGroup();
        $user = $data->getUser();

        return $this->groupService->removeUserFromGroup($user, $group);
    }
}