<?php

namespace App\Security\Voter;

use App\Entity\GroupMembership;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Bundle\SecurityBundle\Security;
use Psr\Log\LoggerInterface;

class GroupMembershipVoter extends Voter
{
    const CREATE = 'CREATE';
    const VIEW = 'VIEW';
    const EDIT = 'EDIT';
    const DELETE = 'DELETE';

    public function __construct(private Security $security, private LoggerInterface $logger) {}

    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, [self::CREATE, self::VIEW, self::EDIT, self::DELETE]) && $subject instanceof GroupMembership;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        /** @var GroupMembership $groupMembership */
        $groupMembership = $subject;

        switch ($attribute) {
            case self::CREATE:
                return $subject->getGroup()->getOwner() === $user;
            case self::VIEW:
            case self::DELETE:
                return $groupMembership->getUser() === $user || $groupMembership->getGroup()->getOwner() === $user;
            case self::EDIT:
                return $groupMembership->getUser() === $user;
        }

        return false;
    }
}