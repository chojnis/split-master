<?php

namespace App\State\GroupMembership;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\GroupMembership;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use ApiPlatform\Metadata\GetCollection;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use ApiPlatform\Metadata\DeleteOperationInterface;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Get;
use App\Entity\User;
use App\Entity\Group;
use App\Repository\GroupMembershipRepository;
use App\Repository\GroupRepository;
use App\Repository\UserRepository;

class GroupMembershipDeleteProvider implements ProviderInterface
{
    public function __construct(
        private GroupMembershipRepository $groupMembershipRepository,
        private GroupRepository $groupRepository,
        private Security $security,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): GroupMembership|null
    {
        $user = $this->security->getUser();
        $group = $this->groupRepository->find($uriVariables['groupId']);
        if (!$group || !$user) {
            return null;
        }

        return $this->groupMembershipRepository->getGroupMembership($user, $group);
    }
}