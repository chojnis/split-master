<?php

namespace App\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class RequestLoggerListener
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        
        // Pobranie ścieżki i metody
        $path = $request->getRequestUri();
        $method = $request->getMethod();

        // Pobranie nagłówków
        $headers = json_encode($request->headers->all());

        // Pobranie body (działa tylko dla JSON)
        $content = $request->getContent();
        $body = $content ? json_decode($content, true) : [];

        // Zapis do logów
        $this->logger->info('📩 API REQUEST:', [
            'method' => $method,
            'path' => $path,
            'headers' => $headers,
            'body' => $body
        ]);
    }
}
