<?php

declare(strict_types=1);

namespace SomeWork\CorrelationId;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use SomeWork\CorrelationId\Logger\CorrelationIdLogger;
use SomeWork\CorrelationId\IdGeneratorInterface;

final class CorrelationIdMiddleware implements MiddlewareInterface
{
    private const HEADER_NAMES = ['X-Correlation-ID', 'X-Request-ID'];

    private ?LoggerInterface $logger;
    private IdGeneratorInterface $generator;

    public function __construct(?LoggerInterface $logger = null, ?IdGeneratorInterface $generator = null)
    {
        $this->logger = $logger ? new CorrelationIdLogger($logger) : null;
        $this->generator = $generator ?? new Generator\UlidGenerator();
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $id = $this->extractId($request);
        CorrelationIdProvider::set($id);
        $response = $handler->handle($request);
        if (!$response->hasHeader(self::HEADER_NAMES[0])) {
            $response = $response->withHeader(self::HEADER_NAMES[0], $id);
        }
        return $response;
    }

    public function getGenerator(): IdGeneratorInterface
    {
        return $this->generator;
    }

    public function getLogger(): ?LoggerInterface
    {
        return $this->logger;
    }

    private function extractId(ServerRequestInterface $request): string
    {
        foreach (self::HEADER_NAMES as $header) {
            $id = $request->getHeaderLine($header);
            if ($id !== '') {
                return $id;
            }
        }
        return $this->generator->generate();
    }
}
