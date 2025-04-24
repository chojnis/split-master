<?php

namespace App\Service;

use App\Entity\Group;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use App\Entity\User;
use App\Entity\GroupMembership;
use App\Dto\Group\CreateGroupRequest;
use App\Entity\Currency;
use App\Entity\TransactionEntry;
use App\Repository\GroupMembershipRepository;
use App\Repository\UserRepository;
use App\Dto\Group\GroupSettlementResponse;


class GroupService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private GroupMembershipRepository $groupMembershipRepository,
        private UserRepository $userRepository
    ) {}

    public function removeUserFromGroup(User $user, Group $group): void
    {
        $groupMembership = $this->groupMembershipRepository->getGroupMembership($user, $group);
        if (!$groupMembership) {
            return;
        }

        $newOwner = null;
        if($group->getOwner() === $user) {
            foreach($this->groupMembershipRepository->getGroupMembers($group) as $member) {
                if ($member->getUser() !== $newOwner) {
                    $newOwner = $member->getUser();
                    break;
                }
            }
            
            if ($newOwner === null) {
                $this->entityManager->remove($group);
                $this->entityManager->flush();
                return;
            }
        }

        $settlements = $this->calculateSettlements($group);
        foreach ($settlements as $settlement) {
            if ($settlement['from'] === $user || $settlement['to'] === $user) {
                throw new AccessDeniedException('Użytkownik ma niezrealizowane rozliczenia.');
            }
        }

        if($newOwner) {
            $group->setOwner($newOwner);
        }

        $this->entityManager->remove($groupMembership);
        $this->entityManager->flush();
    }

    public function calculateSettlements(Group $group)
    {
        $balances = $this->getBalances($group);

        $debtors = [];
        $creditors = [];

        foreach ($balances as $userId => $balance) {
            if ($balance < 0) {
                $debtors[$userId] = -$balance;
            } elseif ($balance > 0) {
                $creditors[$userId] = $balance;
            }
        }

        $settlements = [];
        $currency = $group->getCurrency();

        foreach ($debtors as $debtorId => $debtAmount) {
            foreach ($creditors as $creditorId => &$creditAmount) {
                if ($debtAmount === 0) break;

                $amountToPay = min($debtAmount, $creditAmount);

                $settlements[] = new GroupSettlementResponse(
                    from: $this->userRepository->find($debtorId),
                    to: $this->userRepository->find($creditorId),
                    amount: $amountToPay,
                    currency: $currency
                );

                $debtAmount -= $amountToPay;
                $creditAmount -= $amountToPay;
            }
        }

        return $settlements;
    }

    public function getBalances(Group $group): array
    {
        $balances = [];
        foreach ($group->getTransactions() as $transaction) {
            foreach ($transaction->getEntries() as $entry) {
                $userId = $entry->getUser()->getId();
                if (!isset($balances[$userId])) {
                    $balances[$userId] = 0;
                }
                $balances[$userId] += $entry->getType() === TransactionEntry::TYPE_CREDIT
                    ? $entry->getAmount()
                    : -$entry->getAmount();
            }
        }

        foreach ($balances as $userId => $balance) {
            $balances[$userId] = round($balance, 2);
        }
        
        return $balances;
    }

    public function ensureMembership(User $user, Group $group): GroupMembership
    {
        $existingMembership = $this->groupMembershipRepository->getGroupMembership($user, $group);
        if($existingMembership) {
            return $existingMembership;
        }
        
        $membership = new GroupMembership();
        $membership->setUser($user);
        $membership->setGroup($group);
        $membership->setStatus(GroupMembership::STATUS_ACCEPTED);

        $this->entityManager->persist($membership);
        $this->entityManager->flush();

        return $membership;
    }

    public function inviteUser(User $user, Group $group): GroupMembership
    {
        $groupMembership = $this->groupMembershipRepository->getGroupMembership($user, $group);
        if($groupMembership && $groupMembership->getStatus() === GroupMembership::STATUS_ACCEPTED) {
            throw new \InvalidArgumentException('Użytkownik jest już członkiem tej grupy.');
        }

        if($groupMembership && $groupMembership->getStatus() === GroupMembership::STATUS_PENDING) {
            throw new \InvalidArgumentException('Użytkownik został już zaproszony do tej grupy.');
        }

        $membership = new GroupMembership();
        $membership->setUser($user);
        $membership->setGroup($group);
        $membership->setStatus(GroupMembership::STATUS_PENDING);

        $this->entityManager->persist($membership);
        $this->entityManager->flush();

        return $membership;
    }
}