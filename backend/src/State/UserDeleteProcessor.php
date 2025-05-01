<?php

namespace App\State;

use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use App\Service\UserService;
use Symfony\Bundle\SecurityBundle\Security;

/**
 *
 * This class is responsible for implementing the business logic required to delete
 * a user from the system. It follows the ProcessorInterface contract to standardize
 * processing operations within the application.
 *
 */
final class UserDeleteProcessor implements ProcessorInterface
{
    public function __construct(
        private Security $security,
        private UserService $userService
    ) {}

    public function process($data, $operation, $uriVariables = [], $context = []): void
    {
        if (!$data instanceof User) {
            throw new \InvalidArgumentException('Data must be an instance of User.');
        }

        $currentUser = $this->security->getUser();
        if ($currentUser !== $data) {
            throw new \RuntimeException('You can only delete your own account.');
        }

        $this->userService->softDeleteUser($data);
    }
}
