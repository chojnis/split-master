<?php

namespace App\EventSubscriber;

use App\Entity\Group;
use App\Service\GroupService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use ApiPlatform\Symfony\EventListener\EventPriorities;
use Symfony\Component\HttpFoundation\Request;

/**
 * Subscriber for group-related events.
 * 
 * This class implements EventSubscriberInterface to handle various
 * group-related events within the application.
 * 
 */
final class GroupSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private GroupService $groupService
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['addOwnerMembership', EventPriorities::POST_WRITE],
        ];
    }

    public function addOwnerMembership(ViewEvent $event): void
    {
        $group = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if(!$group instanceof Group) {
            return;
        }

        $this->groupService->ensureMembership($group->getOwner(), $group);
    }
}