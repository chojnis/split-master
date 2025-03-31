<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\UserUpdateDto;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Psr\Log\LoggerInterface;

final class UserUpdateProcessor implements ProcessorInterface
{
    public function __construct(
        private Security $security,
        private UserPasswordHasherInterface $passwordHasher,
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor,
        private LoggerInterface $logger
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        // log
        $this->logger->info('Processing user update', [
            'data' => $data,
        ]);
        if (!$data instanceof UserUpdateDto) {
            return $data;
        }

        $user = $this->security->getUser();
        if (!$user) {
            return new AccessDeniedException('User not authenticated.');
        }

        if ($data->username !== null) {
            $user->setUsername($data->username);
        }

        if ($data->plainPassword !== null) {
            $user->setPassword(
                $this->passwordHasher->hashPassword($user, $data->plainPassword)
            );
        }

        return $this->persistProcessor->process($user, $operation, $uriVariables, $context);
    }
}