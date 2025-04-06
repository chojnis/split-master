<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Repository\UserRepository;
use App\Service\GroupService;

final class UserService
{
    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $userPasswordHasher,
        private GroupService $groupService,
    ) {}

    public function softDeleteUser(User $user): void
    {
        if($user->isDeleted()) {
            return;
        }

        foreach ($user->getGroupMemberships() as $membership) {
            $this->groupService->removeUserFromGroup($user, $membership->getGroup());
        }

        $user->setDeletedAt(new \DateTime());
        $user->setEmail($user->getEmail() . '_deleted_' . time());
        
        $user->save($user, true);
    }
}
