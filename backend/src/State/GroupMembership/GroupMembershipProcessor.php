<?php

namespace App\State\GroupMembership;

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
use ApiPlatform\Metadata\DeleteOperationInterface;
use Psr\Log\LoggerInterface;
use App\Service\GroupService;
use App\Dto\GroupMembership\GroupMembershipInviteDto;
use App\Repository\UserRepository;
use App\Entity\User;

final class GroupMembershipProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor, 
        #[Autowire(service: 'api_platform.doctrine.orm.state.remove_processor')]
        private ProcessorInterface $deleteProcessor,
        private EntityManagerInterface $entityManager,
        private GroupService $groupService,
        private UserRepository $userRepository,
        private Security $security,
        private LoggerInterface $logger,
    ) {}

    public function process($data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $user = $this->security->getUser();
        if (!$user) {
            throw new \AccesDeniedException('User not authenticated.');
        }

        if ($data instanceof GroupMembershipInviteDto && $operation instanceof Post) {
            $groupId = $uriVariables['groupId'];
            $group = $this->entityManager->getRepository(Group::class)->find($groupId);
            if (!$group) {
                throw new \InvalidArgumentException('Nie znaleziono grupy.');
            }

            if($group->getOwner() !== $user) {
                throw new \InvalidArgumentException('Nie jesteś członkiem tej grupy.');
            }

            $email = $data->getEmail();
            $invitedUser = $this->userRepository->findOneByEmail($email);
            if (!$invitedUser) {
                throw new \InvalidArgumentException('Nie znaleziono użytkownika.');
            }

            return $this->groupService->inviteUser($invitedUser, $group);       
        }
        
        if ($data instanceof GroupMembership && $operation instanceof Patch) {
            $status = $data->getStatus();
            $originalData = $this->entityManager->getUnitOfWork()->getOriginalEntityData($data);

            if($originalData['status'] !== GroupMembership::STATUS_PENDING) {
                throw new InvalidStatusChangeException();
            }

            $data->setUpdatedAt(new \DateTime());
            return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
        }

        throw new \InvalidArgumentException('Invalid data type or operation.');
    }
}