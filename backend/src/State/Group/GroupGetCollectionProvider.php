<?php

namespace App\State\Group;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Symfony\Bundle\SecurityBundle\Security;
use App\Repository\GroupRepository;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\Doctrine\Orm\Paginator;

/**
 * 
 * This class implements the ProviderInterface and handles the logic
 * for fetching multiple Group records from the data source.
 * 
 */
class GroupGetCollectionProvider implements ProviderInterface
{
    public function __construct(
        private readonly GroupRepository $groupRepository,
        private readonly Security $security,
        private readonly Pagination $pagination,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): iterable
    {
        $user = $this->security->getUser();
        if (!$user) {
            throw new AccessDeniedException('User not authenticated.');
        }

        [$page, , $limit] = $this->pagination->getPagination($operation, $context);

        return new Paginator(
            $this->groupRepository->getUserGroups($user, $page, $limit)
        );
    }
}