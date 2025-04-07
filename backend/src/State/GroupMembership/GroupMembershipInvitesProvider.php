<?php

namespace App\State\GroupMembership;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use ApiPlatform\Metadata\GetCollection;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use App\Repository\GroupMembershipRepository;
use App\Repository\GroupRepository;
use Symfony\Bundle\SecurityBundle\Security;

class GroupMembershipInvitesProvider implements ProviderInterface
{
    public function __construct(
        private GroupMembershipRepository $groupMembershipRepository,
        private Security $security,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): iterable
    {
        $user = $this->security->getUser();
        if (!$user) {
            throw new \AccessDeniedException('User not authenticated.');
        }

        return $this->groupMembershipRepository->getUserGroupInvites($user);
    }
}