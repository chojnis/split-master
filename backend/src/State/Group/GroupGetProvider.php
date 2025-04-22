<?php

namespace App\State\Group;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Group;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use App\Repository\GroupMembershipRepository;

class GroupGetProvider implements ProviderInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.item_provider')]
        private ProviderInterface $itemProvider,
        private GroupMembershipRepository $groupMembershipRepository,
        private Security $security
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Group|null
    {
        $user = $this->security->getUser();
        if (!$user) {
            return new AccessDeniedException('User not authenticated.');
        }

        $group = $this->itemProvider->provide($operation, $uriVariables, $context);

        if(!$group instanceof Group) {
            return null;
        }

        if(!$this->groupMembershipRepository->isUserMemberOfGroup($user, $group)) {
            throw new AccessDeniedException('You are not a member of this group.');
        }

        return $group;
    }
}