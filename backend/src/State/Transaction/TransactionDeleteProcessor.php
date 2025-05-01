<?php

namespace App\State\Transaction;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Exception\AccessDeniedException;
use Symfony\Bundle\SecurityBundle\Security;
use App\Repository\GroupMembershipRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * 
 * This class is responsible for processing the deletion of transactions.
 * It implements the ProcessorInterface, which requires the implementation of processing methods
 * to handle transaction deletion operations.
 * 
 */
class TransactionDeleteProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.remove_processor')]
        private ProcessorInterface $removeProcessor,
        private GroupMembershipRepository $groupMembershipRepository,
        private Security $security
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $user = $this->security->getUser();
        if($user === null) {
            throw new \AccessDeniedException('User not authenticated.');
        }

        if (!$this->groupMembershipRepository->isUserMemberOfGroup($user, $data->getGroup())) {
            throw new AccessDeniedException('You are not a member of the group.');
        }

        // TODO: add soft delete and history
        return $this->removeProcessor->process($data, $operation, $uriVariables, $context);
    }
}
