<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Group;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Exception\AccessDeniedException;
use App\Service\GroupMembershipService;
use App\Dto\Group\CreateGroupRequest;
use App\Service\GroupService;

final class GroupProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor, 
        private GroupService $groupService,
        private Security $security
    ) {}

    public function process($data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $user = $this->security->getUser();
        if (!$user) {
            throw new \AccessDeniedException('User not authenticated.');
        }

        if ($data instanceof CreateGroupRequest && $operation instanceof Post) {
            return $this->groupService->createGroupFromRequest($data);
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}