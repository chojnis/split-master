<?php

namespace App\State;

use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Metadata\Operation;
use App\Service\UserService;
use App\Dto\User\RegisterUserDto;
use App\Entity\User;

final class UserRegisterProcessor implements ProcessorInterface
{
    public function __construct(private UserService $userService) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): User
    {
        if (!$data instanceof RegisterUserDto) {
            throw new \InvalidArgumentException('Invalid data provided.');
        }

        return $this->userService->register($data);
    }
}
