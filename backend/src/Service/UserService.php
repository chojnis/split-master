<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Dto\User\RegisterUserDto;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Group;

final class UserService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $userPasswordHasher,
        private GroupService $groupService,
    ) {}

    public function getUserByEmail(string $email): ?User
    {
        return $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => $email]);
    }

    public function getOwnedGroups(User $user): array
    {
        return $this->entityManager
            ->getRepository(Group::class)
            ->findBy(['owner' => $user]);
    }

    public function softDeleteUser(User $user): void
    {
        if($user->isDeleted()) {
            throw new \InvalidArgumentException('User is already deleted.');
        }

        foreach ($user->getOwnedGroups() as $group) {
            $newOwner = null;
            foreach($group->getGroupMemberships() as $membership) {
                if ($membership->getUser() !== $newOwner) {
                    $newOwner = $membership->getUser();
                    break;
                }
            }
            
            if ($newOwner) {
                $this->groupService->transferOwnership($group, $newOwner);
            } else {
                $this->entityManager->remove($group);
            }
        }

        $user->setDeletedAt(new \DateTime());
        $user->setEmail($user->getEmail() . '_deleted_' . time());

        foreach ($user->getGroupMemberships() as $membership) {
            $this->entityManager->remove($membership);
        }
        
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
