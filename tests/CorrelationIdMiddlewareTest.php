<?php

declare(strict_types=1);

namespace SomeWork\CorrelationId\Tests;

use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use SomeWork\CorrelationId\CorrelationIdMiddleware;
use SomeWork\CorrelationId\CorrelationIdProvider;
use SomeWork\CorrelationId\Generator\UlidGenerator;

final class CorrelationIdMiddlewareTest extends TestCase
{
    public function testUsesHeaderIfPresent(): void
    {
        $middleware = new CorrelationIdMiddleware();
        $request = new ServerRequest('GET', '/');
        $request = $request->withHeader('X-Correlation-ID', 'abc');
        $handler = new class extends \Nyholm\Psr7\Response implements \Psr\Http\Server\RequestHandlerInterface {
            public function handle(\Psr\Http\Message\ServerRequestInterface $request): \Psr\Http\Message\ResponseInterface
            {
                return new Response();
            }
        };
        $response = $middleware->process($request, $handler);
        self::assertSame('abc', CorrelationIdProvider::get());
        self::assertSame('abc', $response->getHeaderLine('X-Correlation-ID'));
    }

    public function testGeneratesIdWhenHeaderMissing(): void
    {
        $generator = new UlidGenerator();
        $middleware = new CorrelationIdMiddleware(null, $generator);
        $request = new ServerRequest('GET', '/');
        $handler = new class extends \Nyholm\Psr7\Response implements \Psr\Http\Server\RequestHandlerInterface {
            public function handle(\Psr\Http\Message\ServerRequestInterface $request): \Psr\Http\Message\ResponseInterface
            {
                return new Response();
            }
        };
        $response = $middleware->process($request, $handler);
        $id = CorrelationIdProvider::get();
        self::assertNotEmpty($id);
        self::assertSame($id, $response->getHeaderLine('X-Correlation-ID'));
    }

    public function testLoggerReceivesCorrelationId(): void
    {
        $logger = new class implements LoggerInterface {
            public array $context = [];
            public function emergency(string|\Stringable $message, array $context = []): void {}
            public function alert(string|\Stringable $message, array $context = []): void {}
            public function critical(string|\Stringable $message, array $context = []): void {}
            public function error(string|\Stringable $message, array $context = []): void {}
            public function warning(string|\Stringable $message, array $context = []): void {}
            public function notice(string|\Stringable $message, array $context = []): void {}
            public function info(string|\Stringable $message, array $context = []): void { $this->context = $context; }
            public function debug(string|\Stringable $message, array $context = []): void {}
            public function log($level, string|\Stringable $message, array $context = []): void {}
        };
        $middleware = new CorrelationIdMiddleware($logger);
        $decorated = $middleware->getLogger();
        $request = new ServerRequest('GET', '/');
        $handler = new class($decorated) extends \Nyholm\Psr7\Response implements \Psr\Http\Server\RequestHandlerInterface {
            public function __construct(private $logger) {}
            public function handle(\Psr\Http\Message\ServerRequestInterface $request): \Psr\Http\Message\ResponseInterface
            {
                $this->logger->info('test');
                return new Response();
            }
        };
        $middleware->process($request, $handler);
        self::assertArrayHasKey('correlation_id', $logger->context);
        self::assertSame(CorrelationIdProvider::get(), $logger->context['correlation_id']);
    }
}
