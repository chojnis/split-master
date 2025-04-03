<?php

namespace App\Service;

use App\Entity\Group;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use App\Entity\User;
use App\Entity\GroupMembership;
use App\Dto\Group\CreateGroupRequest;
use App\Entity\Currency;
use Symfony\Bundle\SecurityBundle\Security;
use App\Service\GroupMembershipService;

class GroupService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private GroupMembershipService $groupMembershipService,
        private Security $security
    ) {}

    public function transferOwnership(Group $group, User $newOwner): void
    {
        if ($group->getOwner() === $newOwner) {
            throw new \InvalidArgumentException('User is already the owner of this group.');
        }
        
        $groupMembership = $this->groupMembershipService->ensureMembership($newOwner, $group);
        if($groupMembership->getStatus() !== GroupMembership::STATUS_ACCEPTED) {
            throw new \InvalidArgumentException('New owner must be an accepted member.');
        }

        $group->setOwner($newOwner);
        $this->entityManager->flush();
    }

    public function handleOwnerLeavingGroup(Group $group, User $user): void
    {
        if ($group->getOwner() !== $user) {
            return;
        }

        $groupMembers = $this->groupMembershipService->getGroupMembers($group);
        // if (count($groupMembers) === 1) {
        //     $this->entityManager->remove($group);
        //     $this->entityManager->flush();
        //     return;
        // }

        foreach ($groupMembers as $member) {
            if ($member->getUser() !== $user) {
                $this->transferOwnership($group, $member->getUser());
                break;
            }
        }
    }

    // public function createGroupFromRequest(CreateGroupRequest $createGroupRequest): Group
    // {
    //     $group = new Group();
    //     $group->setGroupName($createGroupRequest->groupName);
    //     $group->setDescription($createGroupRequest->description);
        
    //     $currency = $this->entityManager->getReference(Currency::class, $createGroupRequest->currencyId);
    //     $group->setCurrency($currency);
        
    //     $group->setOwner($this->security->getUser());
        
    //     $this->entityManager->persist($group);
    //     $this->entityManager->flush();
        
    //     return $group;
    // }
}