<?php

declare(strict_types=1);

namespace SomeWork\CorrelationId\Tests;

use PHPUnit\Framework\TestCase;
use SomeWork\CorrelationId\CorrelationIdMiddleware;
use SomeWork\CorrelationId\CorrelationIdProvider;
use SomeWork\CorrelationId\Symfony\CorrelationIdSubscriber;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class CorrelationIdSubscriberTest extends TestCase
{
    public function testUsesHeaderAndAddsResponseHeader(): void
    {
        $middleware = new CorrelationIdMiddleware();
        $subscriber = new CorrelationIdSubscriber($middleware);

        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = new Request();
        $request->headers->set('X-Request-ID', 'abc');

        $event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);
        $subscriber->onRequest($event);

        self::assertSame('abc', CorrelationIdProvider::get());

        $response = new Response();
        $responseEvent = new ResponseEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $response);
        $subscriber->onResponse($responseEvent);

        self::assertSame('abc', $responseEvent->getResponse()->headers->get('X-Correlation-ID'));
    }

    public function testGeneratesIdWhenHeaderMissing(): void
    {
        $middleware = new CorrelationIdMiddleware();
        $subscriber = new CorrelationIdSubscriber($middleware);

        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = new Request();

        $event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);
        $subscriber->onRequest($event);
        $id = CorrelationIdProvider::get();
        self::assertNotEmpty($id);

        $response = new Response();
        $responseEvent = new ResponseEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $response);
        $subscriber->onResponse($responseEvent);

        self::assertSame($id, $responseEvent->getResponse()->headers->get('X-Correlation-ID'));
    }
}
