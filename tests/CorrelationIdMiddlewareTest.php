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
            public ?string $capturedId = null;
            public function handle(\Psr\Http\Message\ServerRequestInterface $request): \Psr\Http\Message\ResponseInterface
            {
                $this->capturedId = CorrelationIdProvider::get();
                return new Response();
            }
        };
        $response = $middleware->process($request, $handler);
        self::assertSame('abc', $handler->capturedId);
        self::assertSame('abc', $response->getHeaderLine('X-Correlation-ID'));
        self::assertSame('', CorrelationIdProvider::get());
    }

    public function testGeneratesIdWhenHeaderMissing(): void
    {
        $generator = new UlidGenerator();
        $middleware = new CorrelationIdMiddleware(null, $generator);
        $request = new ServerRequest('GET', '/');
        $handler = new class extends \Nyholm\Psr7\Response implements \Psr\Http\Server\RequestHandlerInterface {
            public ?string $capturedId = null;
            public function handle(\Psr\Http\Message\ServerRequestInterface $request): \Psr\Http\Message\ResponseInterface
            {
                $this->capturedId = CorrelationIdProvider::get();
                return new Response();
            }
        };
        $response = $middleware->process($request, $handler);
        self::assertNotEmpty($handler->capturedId);
        self::assertSame($handler->capturedId, $response->getHeaderLine('X-Correlation-ID'));
        self::assertSame('', CorrelationIdProvider::get());
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
            private $logger;
            public ?string $capturedId = null;
            public function __construct($logger)
            {
                $this->logger = $logger;
            }
            public function handle(\Psr\Http\Message\ServerRequestInterface $request): \Psr\Http\Message\ResponseInterface
            {
                $this->capturedId = CorrelationIdProvider::get();
                $this->logger->info('test');
                return new Response();
            }
        };
        $middleware->process($request, $handler);
        self::assertArrayHasKey('correlation_id', $logger->context);
        self::assertSame($handler->capturedId, $logger->context['correlation_id']);
        self::assertSame('', CorrelationIdProvider::get());
    }
}
