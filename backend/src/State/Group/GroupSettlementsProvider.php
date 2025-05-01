<?php

namespace App\State\Group;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Group;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use App\Service\GroupService;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use App\Repository\GroupMembershipRepository;
use App\Dto\Group\GroupSettlementResponse;

/**
 *
 * This class implements the ProviderInterface and is responsible for providing
 * access to settlement data within the context of a group. It handles fetching,
 * processing, and possibly manipulating settlement information for groups.
 *
 */
class GroupSettlementsProvider implements ProviderInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.item_provider')]
        private ProviderInterface $itemProvider,
        private GroupMembershipRepository $groupMembershipRepository,
        private GroupService $groupService,
        private Security $security
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): iterable|null
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

        return $this->groupService->calculateSettlements($group);
    }
}