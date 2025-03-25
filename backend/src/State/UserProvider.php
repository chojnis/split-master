<?php

namespace App\State;

use App\Entity\User;
use App\Entity\Group;
use Doctrine\ORM\EntityManagerInterface;
use ApiPlatform\State\ProviderInterface;
use ApiPlatform\Metadata\Operation;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;
use App\Service\GroupMembershipService;

class UserProvider implements ProviderInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager, 
        private Security $security,
        private GroupMembershipService $groupMembershipService
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): iterable|null
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
            throw new AccessDeniedException('You are not a member of this group');
        }

        return $this->groupMembershipService->getGroupUsers($group);
    }
}