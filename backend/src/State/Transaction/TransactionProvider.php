<?php

namespace App\State;

use App\Entity\Transaction;
use App\Entity\Group;
use App\Entity\GroupMembership;
use App\Service\GroupMembershipService;
use Doctrine\ORM\EntityManagerInterface;
use ApiPlatform\State\ProviderInterface;
use ApiPlatform\Metadata\Operation;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Get;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use App\Dto\Transaction\TransactionResponse;
use App\Service\TransactionService;
use Psr\Log\LoggerInterface;
use App\Repository\GroupMembershipRepository;



class TransactionProvider implements ProviderInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.item_provider')]
        private ProviderInterface $itemProvider,
        #[Autowire(service: 'api_platform.doctrine.orm.state.collection_provider')]
        private ProviderInterface $collectionProvider,
        private EntityManagerInterface $entityManager, 
        private Security $security,
        private GroupMembershipService $groupMembershipService,
        private TransactionService $transactionService,
        private GroupMembershipRepository $groupMembershipRepository,
        private LoggerInterface $logger,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): iterable|TransactionResponse|Transaction|null
    {
        // log entrance
        $this->logger->info('TransactionProvider: provide method called');

        $user = $this->security->getUser();
        if (!$user) {
            throw new \AccessDeniedException('User not authenticated.');
        }

        if($operation instanceof Get) {
            $transaction = $this->itemProvider->provide($operation, $uriVariables, $context);
            if (!$transaction) {
                return null;
            }

            if(!$this->groupMembershipRepository->isUserMemberOfGroup($user, $transaction->getGroup())) {
                throw new AccessDeniedException('You are not a member of this group.');
            }

            list($payer, $payees, $amount) = $this->transactionService->getTransactionDetails($transaction);
            
            return new TransactionResponse(
                id: $transaction->getId(),
                name: $transaction->getName(),
                amount: $amount,
                currency: $transaction->getCurrency(),
                exchangeRate: $transaction->getExchangeRate(),
                payer: $payer,
                payees: $payees,
                entries: $transaction->getEntries(),
                transactionDate: $transaction->getTransactionDate(),
            );
        }

        // return $this->itemProvider->provide($operation, $uriVariables, $context);
        return null;
    }
}