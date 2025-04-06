<?php

namespace App\State;

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

class GroupMembershipProvider implements ProviderInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.item_provider')]
        private ProviderInterface $itemProvider,
        private EntityManagerInterface $entityManager,
        private GroupMembershipRepository $groupMembershipRepository,
        private Security $security,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): iterable|GroupMembership|null
    {
        $user = $this->security->getUser();
        if (!$user) {
            throw new \InvalidArgumentException('User not found.');
        }

        if($operation instanceof Post) {
            return new GroupMembership();
        }

        if($operation instanceof DeleteOperationInterface) {
            $groupId = $uriVariables['groupId'];

            if(isset($uriVariables['userId'])) {
                $userId = $uriVariables['userId'];
                $user = $this->entityManager->getRepository(User::class)->find($userId);
                if (!$user) {
                    throw new \InvalidArgumentException('User not found.');
                }
            }

            $group = $this->entityManager->getRepository(Group::class)->find($groupId);
            if (!$group) {
                throw new \InvalidArgumentException('Group not found.');
            }

            $groupMembership = $this->groupMembershipRepository->getGroupMembership($user, $group);
            if (!$groupMembership) {
                throw new \InvalidArgumentException('Group membership not found.');
            }

            return $groupMembership;
        }

        if($operation instanceof GetCollection) {
            if($operation->getName() === 'get_group_members') {
                $groupId = $uriVariables['groupId'];
                $group = $this->entityManager->getRepository(Group::class)->find($groupId);
                if (!$group) {
                    throw new \InvalidArgumentException('Group not found.');
                }
                if (!$this->groupMembershipRepository->isUserMemberOfGroup($user, $group)) {
                    throw new AccessDeniedException();
                }
                return $this->groupMembershipRepository->getGroupMembers($group);
            }
            return $this->groupMembershipRepository->getUserGroupInvites($user);
        }

        // if($operation instanceof Get) {
        //     if(!isset($uriVariables['groupId']) || !isset($uriVariables['userId'])) {
        //         throw new \InvalidArgumentException('Group or user not found');
        //     }

        //     $groupId = $uriVariables['groupId'];
        //     $userId = $uriVariables['userId'];

        //     $group = $this->entityManager->getRepository(Group::class)->find($groupId);
        //     if (!$group) {
        //         throw new \InvalidArgumentException('Group not found');
        //     }

        //     if(!$this->groupMembershipRepository->isUserMemberOfGroup($user, $group)) {
        //         throw new AccessDeniedException();
        //     }

        //     $user = $this->entityManager->getRepository(User::class)->find($userId);
        //     if (!$user) {
        //         throw new \InvalidArgumentException('User not found');
        //     }

        //     $groupMembership = $this->groupMembershipRepository->getGroupMembership($user, $group);
        //     if (!$groupMembership || $groupMembership->getStatus() !== GroupMembership::STATUS_ACCEPTED) {
        //         throw new \InvalidArgumentException('User is not a member of this group');
        //     }

        //     return $groupMembership;
        // }

        return $this->itemProvider->provide($operation, $uriVariables, $context);
    }
}