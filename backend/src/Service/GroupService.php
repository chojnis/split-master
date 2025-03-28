<?php

namespace App\Service;

use App\Entity\Group;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use App\Entity\User;
use App\Entity\GroupMembership;

class GroupService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private GroupMembershipService $groupMembershipService
    ) {}

    public function transferOwnership(Group $group, User $newOwner): Group
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
}