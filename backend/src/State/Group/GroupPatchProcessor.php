<?php

namespace App\State\Group;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\State\ProviderInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use App\Repository\GroupMembershipRepository;
use App\Entity\Group;

/**
 * 
 * Processes PATCH requests for Group resources by implementing the ProcessorInterface.
 * This final class handles updating group data based on incoming patch operations.
 * 
 */
final class GroupPatchProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor, 
        #[Autowire(service: 'api_platform.doctrine.orm.state.item_provider')]
        private ProviderInterface $itemProvider,
        private GroupMembershipRepository $groupMembershipRepository
    ) {}

    public function process($data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if (!$data instanceof Group) {
            throw new \InvalidArgumentException('Invalid data type. Expected Group.');
        }

        if($user = $data->getOwner()) {
            if(!$this->groupMembershipRepository->isUserMemberOfGroup($user, $data)) {
                throw new \InvalidArgumentException('New owner must be a member of group.');
            }
        }

        $previousData = $context['previous_data'] ?? null;
        if(!$previousData instanceof Group) {
            throw new \InvalidArgumentException('Previous data not found.');
        }

        $data->setCurrency($previousData->getCurrency());

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}