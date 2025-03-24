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

class GroupMembershipProvider implements ProviderInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.item_provider')]
        private ProviderInterface $itemProvider,
        private GroupMembershipService $groupMembershipService,
        private EntityManagerInterface $entityManager,
        private Security $security,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): iterable|GroupMembership|null
    {
        $user = $this->security->getUser();
        if (!$user) {
            return [];
        }

        if($operation instanceof GetCollection) {
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