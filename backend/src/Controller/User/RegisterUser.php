<?php

namespace App\Controller\User;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Attribute\AsController;
use App\Service\UserService;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use App\Dto\User\RegisterUserDto;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[AsController]
final class RegisterUser extends AbstractController
{
    public function __construct(
        private UserService $userService, 
        private SerializerInterface $serializer,
        private LoggerInterface $logger
    ) {}

    public function __invoke(Request $request): User
    {        
        $data = $request->getContent();
        $registerUserDto = $this->serializer->deserialize($data, RegisterUserDto::class, 'json');

        return $this->userService->register($registerUserDto);
    }
}
