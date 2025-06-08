<?php

declare(strict_types=1);

namespace SomeWork\CorrelationId\Symfony;

use SomeWork\CorrelationId\CorrelationIdMiddleware;
use SomeWork\CorrelationId\CorrelationIdProvider;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class CorrelationIdSubscriber implements EventSubscriberInterface
{
    public function __construct(private CorrelationIdMiddleware $middleware)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onRequest', 100],
            KernelEvents::RESPONSE => 'onResponse',
        ];
    }

    public function onRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $id = $this->extractId($request);
        CorrelationIdProvider::set($id);
    }

    public function onResponse(ResponseEvent $event): void
    {
        $response = $event->getResponse();
        $id = CorrelationIdProvider::get();
        if (!$response->headers->has('X-Correlation-ID')) {
            $response->headers->set('X-Correlation-ID', $id);
        }
    }

    private function extractId(Request $request): string
    {
        foreach (['X-Correlation-ID', 'X-Request-ID'] as $header) {
            $id = $request->headers->get($header);
            if (is_string($id) && $id !== '') {
                return $id;
            }
        }
        return $this->middleware->getGenerator()->generate();
    }
}
