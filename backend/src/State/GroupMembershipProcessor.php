<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\GroupMembership;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Exception\InvalidArgumentException;
use App\Entity\Group;
use Doctrine\ORM\EntityManagerInterface;
use App\Exception\InvalidStatusChangeException;
use ApiPlatform\Metadata\Patch;
use App\Service\GroupMembershipService;
use ApiPlatform\Metadata\DeleteOperationInterface;
use Psr\Log\LoggerInterface;
use App\Service\GroupService;

final class GroupMembershipProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor, 
        #[Autowire(service: 'api_platform.doctrine.orm.state.remove_processor')]
        private ProcessorInterface $deleteProcessor,
        private EntityManagerInterface $entityManager,
        private GroupMembershipService $groupMembershipService,
        private GroupService $groupService,
        private Security $security
    ) {}

    public function process($data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $user = $this->security->getUser();
        if (!$user) {
            throw new \InvalidArgumentException('User not found');
        }

        if($operation instanceof DeleteOperationInterface) {
            $group = $data->getGroup();
            if($group->getOwner() === $user) {
                $groupMembers = $this->groupMembershipService->getGroupMembers($group);
                if(count($groupMembers) < 2) {
                    throw new \InvalidArgumentException('You cannot delete the group owner');
                }

                foreach($groupMembers as $groupMember) {
                    if($groupMember->getUser() !== $user) {
                        $groupService->transferOwnership($group, $groupMember->getUser());
                        break;
                    }
                }
            }
            return $this->deleteProcessor->process($data, $operation, $uriVariables, $context);
        }

        if ($data instanceof GroupMembership && $operation instanceof Post) {
            $groupId = $uriVariables['groupId'];

            $group = $this->entityManager->getRepository(Group::class)->find($groupId);

            if (!$group) {
                throw new \InvalidArgumentException('Group not found');
            }

            if($group->getOwner() !== $user) {
                throw new \InvalidArgumentException('You are not the owner of this group');
            }

            $groupMembership = $this->groupMembershipService->getGroupMembership($data->getUser(), $group);
            if($groupMembership && $groupMembership->getStatus() === GroupMembership::STATUS_ACCEPTED) {
                throw new \InvalidArgumentException('User is already a member of this group');
            }

            if($groupMembership && $groupMembership->getStatus() === GroupMembership::STATUS_PENDING) {
                throw new \InvalidArgumentException('User has already been invited to this group');
            }

            $data->setGroup($group);
            $data->setStatus(GroupMembership::STATUS_PENDING);            
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