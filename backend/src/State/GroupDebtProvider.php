<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Symfony\Bundle\SecurityBundle\Security;
use App\Service\DebtService;
use App\Repository\GroupRepository;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Entity\Group;
use App\Repository\GroupMembershipRepository;

class GroupDebtProvider implements ProviderInterface
{
    public function __construct(
        private GroupRepository $groupRepository,
        private DebtService $debtService,
        private GroupMembershipRepository $groupMembershipRepository,
        private Security $security
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): iterable|null
    {
        $user = $this->security->getUser();
        if (!$user) {
            throw new AccessDeniedException('User not authenticated.');
        }

        $groupId = $uriVariables['id'] ?? null;

        if ($groupId === null) {
            throw new NotFoundHttpException('Group ID not provided');
        }

        $group = $this->groupRepository->find($groupId);
        
        if (!$group instanceof Group) {
            throw new NotFoundHttpException('Group not found');
        }

        if (!$this->groupMembershipRepository->isUserMemberOfGroup($user, $group)) {
            throw new AccessDeniedException('You are not a member of this group.');
        }

        // return $this->debtService->getDetailedDebtsForGroup($group);
        return $this->debtService->getOptimizedDebtsForGroup($group);

    }
}