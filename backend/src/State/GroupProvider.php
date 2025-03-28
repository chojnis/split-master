<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Group;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use ApiPlatform\Metadata\GetCollection;
use App\Service\GroupMembershipService;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class GroupProvider implements ProviderInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.item_provider')]
        private ProviderInterface $itemProvider,
        private GroupMembershipService $groupMembershipService,
        private EntityManagerInterface $entityManager,
        private Security $security
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): iterable|Group|null
    {
        $user = $this->security->getUser();
        if (!$user) {
            return [];
        }

        if($operation instanceof GetCollection) {
            return $this->groupMembershipService->getUserGroups($user);
        }

        $groupId = $uriVariables['id'];
        $group = $this->entityManager->getRepository(Group::class)->find($groupId);
        if($group && !$this->groupMembershipService->isUserMemberOfGroup($user, $group)) {
            throw new AccessDeniedException('You are not a member of this group.');
        }

        return $this->itemProvider->provide($operation, $uriVariables, $context);
    }
}