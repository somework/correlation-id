<?php

declare(strict_types=1);

namespace SomeWork\CorrelationId\Logger;

use Psr\Log\LoggerInterface;
use SomeWork\CorrelationId\CorrelationIdProvider;
use Stringable;

final class CorrelationIdLogger implements LoggerInterface
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function emergency(Stringable|string $message, array $context = []): void
    {
        $this->logger->emergency($message, $this->addContext($context));
    }

    public function alert(Stringable|string $message, array $context = []): void
    {
        $this->logger->alert($message, $this->addContext($context));
    }

    public function critical(Stringable|string $message, array $context = []): void
    {
        $this->logger->critical($message, $this->addContext($context));
    }

    public function error(Stringable|string $message, array $context = []): void
    {
        $this->logger->error($message, $this->addContext($context));
    }

    public function warning(Stringable|string $message, array $context = []): void
    {
        $this->logger->warning($message, $this->addContext($context));
    }

    public function notice(Stringable|string $message, array $context = []): void
    {
        $this->logger->notice($message, $this->addContext($context));
    }

    public function info(Stringable|string $message, array $context = []): void
    {
        $this->logger->info($message, $this->addContext($context));
    }

    public function debug(Stringable|string $message, array $context = []): void
    {
        $this->logger->debug($message, $this->addContext($context));
    }

    public function log($level, Stringable|string $message, array $context = []): void
    {
        $this->logger->log($level, $message, $this->addContext($context));
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     */
    private function addContext(array $context): array
    {
        $context['correlation_id'] = CorrelationIdProvider::get();
        return $context;
    }
}
