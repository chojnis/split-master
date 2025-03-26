<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\SerializerInterface;
use App\Service\RefreshTokenService;

class JWTAuthenticationSuccessListener
{
    public function __construct(
        private SerializerInterface $serializer,
        private RefreshTokenService $refreshTokenService
    ){}

    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event)
    {
        $data = $event->getData();
        $user = $event->getUser();

        if (!$user instanceof UserInterface) {
            return;
        }

        $normalizedUser = $this->serializer->normalize(
            $user, 
            null, 
            ['groups' => ['user:read']]
        );

        $data['user'] = $normalizedUser;
        $data['refresh_token'] = $this->refreshTokenService->generateRefreshToken($user)->getRefreshToken();

        $event->setData($data);
    }
}
