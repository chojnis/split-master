<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\SerializerInterface;

class JWTAuthenticationSuccessListener
{
    public function __construct(private SerializerInterface $serializer){}

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

        $event->setData($data);
    }
}
