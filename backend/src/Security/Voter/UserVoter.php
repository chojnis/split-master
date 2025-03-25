<?php

namespace App\Security\Voter;

use App\Entity\GroupMembership;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Bundle\SecurityBundle\Security;
use Psr\Log\LoggerInterface;
use App\Service\GroupMembershipService;

class UserVoter extends Voter
{
    const VIEW = 'VIEW';

    public function __construct(private Security $security, private GroupMembershipService $groupMembershipService, private LoggerInterface $logger) {}

    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, [self::VIEW]) && $subject instanceof User;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        switch ($attribute) {
            case self::VIEW:
                return $this->groupMembershipService->areUsersMembersOfSameGroup($user, $subject);
        }

        return false;
    }
}