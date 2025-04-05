<?php

namespace App\State\Transaction;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Transaction;
use App\Dto\TransactionRequest;
use App\Entity\TransactionEntry;
use Doctrine\ORM\EntityManagerInterface;
use ApiPlatform\Exception\InvalidArgumentException;
use App\Entity\TransactionHistory;
use ApiPlatform\Exception\AccessDeniedException;
use App\Entity\Group;
use Symfony\Bundle\SecurityBundle\Security;
use App\Service\GroupMembershipService;
use App\Service\TransactionService;
use Psr\Log\LoggerInterface;


class TransactionPostProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private Security $security,
        private GroupMembershipService $groupMembershipService,
        private TransactionService $transactionService,
        private LoggerInterface $logger
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        $user = $this->security->getUser();
        if($user === null) {
            throw new \AccessDeniedException('User not authenticated.');
        }

        if(!$data instanceof TransactionRequest) {
            throw new \InvalidArgumentException('Invalid data type. Expected TransactionRequest.');
        }

        $groupId = $uriVariables['groupId'] ?? null;
        $group = $this->entityManager->getRepository(Group::class)->find($groupId);
        if($group === null) {
            throw new \InvalidArgumentException('Group not found.');
        }

        if(!$this->groupMembershipService->isUserMemberOfGroup($user, $group)) {
            throw new \AccessDeniedException('User is not a member of the group.');
        }

        $this->transactionService->createTransaction($data, $group);
    }
}
