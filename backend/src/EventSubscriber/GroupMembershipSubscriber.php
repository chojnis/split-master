<?php

namespace App\EventSubscriber;

use App\Entity\GroupMembership;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use ApiPlatform\Symfony\EventListener\EventPriorities;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Group;
use App\Repository\GroupMembershipRepository;

class GroupMembershipSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private GroupMembershipRepository $groupMembershipRepository,
        private EntityManagerInterface $entityManager
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [
                ['handlePreDelete', EventPriorities::PRE_WRITE],
                ['handlePostDelete', EventPriorities::POST_WRITE],
            ],
        ];
    }

    public function handlePreDelete(ViewEvent $event): void
    {
        $membership = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (!$membership instanceof GroupMembership || $method !== Request::METHOD_DELETE) {
            return;
        }


        $groupId = $membership->getGroup()->getId();
        $event->getRequest()->attributes->set('_group_to_check', $groupId);
    }

    public function handlePostDelete(ViewEvent $event): void
    {
        $membership = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if ($method !== Request::METHOD_DELETE) {
            return;
        }

        $groupId = $event->getRequest()->attributes->get('_group_to_check');
        if(!$groupId) {
            return;
        }

        $group = $this->entityManager->find(Group::class, $groupId);
        if (!$group) {
            return;
        }

        $members = $this->groupMembershipRepository->getGroupMembers($group);

        // Delete group if no members left
        if (empty($members)) {
            $this->entityManager->remove($group);
            $this->entityManager->flush();
        }

    }
}