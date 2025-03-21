<?php

namespace App\State;

use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Metadata\Operation;
use App\Service\UserService;
use App\Dto\User\RegisterUserDto;
use App\Entity\User;
use Psr\Log\LoggerInterface;

final class UserRegisterProcessor implements ProcessorInterface
{
    public function __construct(private UserService $userService, private LoggerInterface $logger) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): User
    {
        $this->logger->debug('Processing user registration.', [
            'data_type' => get_class($data),
            'data' => $data,
            'context' => $context
        ]);

        if (!$data instanceof RegisterUserDto) {
            throw new \InvalidArgumentException('Invalid data provided.');
        }

        return $this->userService->register($data);
    }
}
