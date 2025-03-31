<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\GroupMembership;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Exception\InvalidArgumentException;
use ApiPlatform\Exception\AccessDeniedException;
use App\Entity\Group;
use Doctrine\ORM\EntityManagerInterface;
use App\Exception\InvalidStatusChangeException;
use ApiPlatform\Metadata\Patch;
use App\Service\GroupMembershipService;
use ApiPlatform\Metadata\DeleteOperationInterface;
use Psr\Log\LoggerInterface;
use App\Service\GroupService;
use App\Service\UserService;
use App\Dto\GroupMembershipInviteDto;

final class GroupMembershipProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor, 
        #[Autowire(service: 'api_platform.doctrine.orm.state.remove_processor')]
        private ProcessorInterface $deleteProcessor,
        private EntityManagerInterface $entityManager,
        private GroupMembershipService $groupMembershipService,
        private UserService $userService,
        private GroupService $groupService,
        private Security $security
    ) {}

    public function process($data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $user = $this->security->getUser();
        if (!$user) {
            throw new \AccesDeniedException('User not authenticated.');
        }

        if($operation instanceof DeleteOperationInterface) {
            $group = $data->getGroup();

            $this->groupService->handleOwnerLeavingGroup($group, $user);
            
            return $this->deleteProcessor->process($data, $operation, $uriVariables, $context);
        }

        if ($data instanceof GroupMembershipInviteDto && $operation instanceof Post) {
            $groupId = $uriVariables['groupId'];
            $group = $this->entityManager->getRepository(Group::class)->find($groupId);
            if (!$group) {
                throw new \InvalidArgumentException('Group not found.');
            }

            if($group->getOwner() !== $user) {
                throw new \InvalidArgumentException('You are not the owner of this group.');
            }

            $email = $data->getEmail();
            $invitedUser = $this->userService->getUserByEmail($email);
            if (!$invitedUser) {
                throw new \InvalidArgumentException('User not found.');
            }

            return $this->groupMembershipService->inviteUser($invitedUser, $group);       
        }elseif ($data instanceof GroupMembership && $operation instanceof Patch) {
            $status = $data->getStatus();
            $originalData = $this->entityManager->getUnitOfWork()->getOriginalEntityData($data);

            if($originalData['status'] !== GroupMembership::STATUS_PENDING) {
                throw new InvalidStatusChangeException();
            }
        }

        $data->setUpdatedAt(new \DateTime());
        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);

    }
}