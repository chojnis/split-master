<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Transaction;
use App\Entity\GroupMembership;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use App\Service\TransactionService;
use App\Service\GroupMembershipService;
use App\Entity\User;
use ApiPlatform\Metadata\DeleteOperationInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;
use App\Entity\Group;
use App\Dto\TransactionRequest;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;

final class TransactionProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor,
        #[Autowire(service: 'api_platform.doctrine.orm.state.remove_processor')]
        private ProcessorInterface $removeProcessor,
        private Security $security,
        private GroupMembershipService $groupMembershipService,
        private TransactionService $transactionService,
        private EntityManagerInterface $entityManager
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $this->logger->info('TransactionProcessor: process method called');

        $user = $this->security->getUser();
        if (!$user) {
            throw new AccessDeniedException('User not authenticated.');
        }
    
        // Obsługa DELETE
        if ($operation instanceof DeleteOperationInterface) {
            $this->validateDeleteOperation($data, $user);
            return $this->removeProcessor->process($data, $operation, $uriVariables, $context);
        }
    
        // Obsługa POST (z groupId w URI)
        if ($data instanceof TransactionRequest && $operation instanceof Post) {
            $group = $this->entityManager->getRepository(Group::class)->find($uriVariables['groupId']);
            if (!$group) {
                throw new InvalidArgumentException('Group not found.');
            }
    
            $transaction = $this->transactionService->createOrUpdateTransactionFromRequest(
                $data,
                $group
            );
        }
        // Obsługa PATCH (bez groupId w URI)
        elseif ($data instanceof TransactionRequest && $operation instanceof Patch) {
            $existingTransaction = $this->entityManager->getRepository(Transaction::class)->find($uriVariables['id']);
            if (!$existingTransaction) {
                throw new InvalidArgumentException('Transaction not found.');
            }
    
            $transaction = $this->transactionService->createOrUpdateTransactionFromRequest(
                $data,
                $existingTransaction->getGroup(),
                $existingTransaction
            );
        }
        else {
            $transaction = $data;
        }
    
        $this->validatePersistOperation($transaction, $user);
        return $this->persistProcessor->process($transaction, $operation, $uriVariables, $context);
    }

    private function validatePersistOperation(Transaction $transaction, User $user): void
    {
        if($transaction->getId() !== null) {
            $this->validateUpdateOperation($transaction, $user);
        }

        $payer = $transaction->getPayer();
        if (!$this->groupMembershipService->isUserMemberOfGroup($payer, $transaction->getGroup())) {
            throw new AccessDeniedException('Payer must be a member of the group.');
        }

        foreach ($transaction->getPayees() as $payee) {
            if (!$this->groupMembershipService->isUserMemberOfGroup($payee, $transaction->getGroup())) {
                throw new AccessDeniedException('All payees must be members of the group.');
            }
        }
    }

    private function validateDeleteOperation(Transaction $transaction, User $user): void
    {
        if (!$this->groupMembershipService->isUserMemberOfGroup($user, $transaction->getGroup())) {
            throw new AccessDeniedException('You are not a member of the group.');
        }
    }

    private function validateUpdateOperation(Transaction $transaction, User $user): void
    {
        $unitOfWork = $this->entityManager->getUnitOfWork();
        $originalData = $unitOfWork->getOriginalEntityData($transaction);
        $originalTransaction = new Transaction();
        $originalTransaction->setGroup($originalData['group']);
        $originalTransaction->setPayer($originalData['payer']);

        if (!$this->groupMembershipService->isUserMemberOfGroup($user, $originalTransaction->getGroup())) {
            throw new AccessDeniedException('You are not a member of the group.');
        }
    }
}