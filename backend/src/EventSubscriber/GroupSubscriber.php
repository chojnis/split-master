<?php

namespace App\EventSubscriber;

use App\Entity\Group;
use App\Service\GroupMembershipService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use ApiPlatform\Symfony\EventListener\EventPriorities;
use Symfony\Component\HttpFoundation\Request;

use Psr\Log\LoggerInterface;

final class GroupSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private GroupMembershipService $groupMembershipService,
        private LoggerInterface $logger
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

        // if(!$group instanceof Group || $method !== Request::METHOD_POST) {
        if(!$group instanceof Group) {
            return;
        }

        $this->logger->info('GroupSubscriber::addOwnerMembership called');

        $this->groupMembershipService->ensureMembership($group->getOwner(), $group);
    }
}