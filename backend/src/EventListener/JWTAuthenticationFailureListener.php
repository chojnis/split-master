<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * 
 * Listener class for JWT Authentication failure events.
 * 
 */
class JWTAuthenticationFailureListener
{
    public function onAuthenticationFailureResponse(AuthenticationFailureEvent $event)
    {
        $response = new JsonResponse([
            "title" => "Unauthorized",
            "detail" => "Niepoprawne dane logowania.",
            "status" => 401,
            "instance" => "/api/login",
            "type" => "https://tools.ietf.org/html/rfc7235#section-3.1"
        ], 401);

        $event->setResponse($response);
    }
}
