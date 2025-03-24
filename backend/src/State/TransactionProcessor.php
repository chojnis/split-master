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
use Psr\Log\LoggerInterface;
use App\Service\GroupMembershipService;
use App\Entity\User;
use ApiPlatform\Metadata\DeleteOperationInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;
use App\Entity\Group;

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
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        /** @var Transaction $transaction */
        $transaction = $data;

        $user = $this->security->getUser();
        if (!$user) {
            throw new AccessDeniedException('User not authenticated');
        }

        if($uriVariables['groupId']){
            $group = $this->entityManager->getRepository(Group::class)->find($uriVariables['groupId']);
            if (!$group) {
                throw new InvalidArgumentException('Group not found');
            }
            $transaction->setGroup($group);
        }

        if (!$this->groupMembershipService->isUserMemberOfGroup($user, $transaction->getGroup())) {
            throw new AccessDeniedException('User is not a member of the group');
        }

        if($operation instanceof DeleteOperationInterface) {
            $this->validateDeleteOperation($transaction, $user);
            return $this->removeProcessor->process($transaction, $operation, $uriVariables, $context);
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
            throw new AccessDeniedException('Payer must be a member of the group');
        }

        foreach ($transaction->getPayees() as $payee) {
            if (!$this->groupMembershipService->isUserMemberOfGroup($payee, $transaction->getGroup())) {
                throw new AccessDeniedException('All payees must be members of the group');
            }
        }
    }

    private function validateDeleteOperation(Transaction $transaction, User $user): void
    {
        if (!$this->transactionService->isUserPayerOrOwner($transaction, $user)) {
            throw new AccessDeniedException('Only the payer or group owner can delete the transaction');
        }
    }

    private function validateUpdateOperation(Transaction $transaction, User $user): void
    {
        $unitOfWork = $this->entityManager->getUnitOfWork();
        $originalData = $unitOfWork->getOriginalEntityData($transaction);
        $originalTransaction = new Transaction();
        $originalTransaction->setGroup($originalData['group']);
        $originalTransaction->setPayer($originalData['payer']);

        if (!$this->transactionService->isUserPayerOrOwner($originalTransaction, $user)) {
            throw new AccessDeniedException('Only the payer or group owner can update the transaction');
        }
    }
}