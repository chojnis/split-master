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
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use Symfony\Component\DependencyInjection\Attribute\Autowire;


class UserProvider implements ProviderInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.item_provider')]
        private ProviderInterface $itemProvider,
        private EntityManagerInterface $entityManager, 
        private Security $security,
        private GroupMembershipService $groupMembershipService
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): iterable|User|null
    {
        $user = $this->security->getUser();
        if (!$user) {
            return new AccessDeniedException('User not authenticated.');
        }

        if ($operation instanceof GetCollection) {
            $group = $this->entityManager->getRepository(Group::class)->find($uriVariables['groupId']);
            if (!$group) {
                throw new InvalidArgumentException('Group not found.');
            }

            if(!$this->groupMembershipService->isUserMemberOfGroup($user, $group)) {
                throw new AccessDeniedException('You are not a member of this group.');
            }

            return $this->groupMembershipService->getGroupUsers($group);
        } 
        // else if ($operation instanceof Patch || $operation instanceof Delete) {
        //     return $user;
        // }

        return $this->itemProvider->provide($operation, $uriVariables, $context);
    }
}