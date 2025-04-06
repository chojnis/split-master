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
use App\Service\TransactionService;
use App\Repository\GroupMembershipRepository;


class TransactionPatchProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private Security $security,
        private GroupMembershipRepository $groupMembershipRepository,
        private TransactionService $transactionService
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Transaction
    {
        $user = $this->security->getUser();
        if($user === null) {
            throw new \AccessDeniedException('User not authenticated.');
        }

        if(!$data instanceof TransactionRequest) {
            throw new \InvalidArgumentException('Invalid data type. Expected TransactionRequest.');
        }

        $previousData = $context['previous_data'] ?? null;
        if (!$previousData instanceof Transaction) {
            throw new \InvalidArgumentException('Previous data not found.');
        }

        if(!$this->groupMembershipRepository->isUserMemberOfGroup($user, $previousData->getGroup())) {
            throw new \AccessDeniedException('User is not a member of the group.');
        }

        $transaction = $this->entityManager->getRepository(Transaction::class)->find($uriVariables['id']);
        if($transaction === null) {
            throw new \InvalidArgumentException('Transaction not found.');
        }

        return $this->transactionService->editTransaction($data, $transaction);
    }
}
