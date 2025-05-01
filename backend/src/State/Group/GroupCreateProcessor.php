<?php

namespace App\State\Group;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Group;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use ApiPlatform\Exception\AccessDeniedException;

/**
 *
 * This processor handles the logic for creating new groups in the system. It implements
 * the ProcessorInterface to standardize the processing flow.
 *
 */
final class GroupCreateProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor,
        private Security $security
    ) {}

    public function process($data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $user = $this->security->getUser();
        if (!$user) {
            throw new \AccessDeniedException('User not authenticated.');
        }

        if (!$data instanceof Group) {
            throw new \InvalidArgumentException('Invalid data type. Expected Group.');
        }

        $data->setOwner($user);

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}