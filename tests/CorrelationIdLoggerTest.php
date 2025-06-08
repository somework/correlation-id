<?php

declare(strict_types=1);

namespace SomeWork\CorrelationId\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use SomeWork\CorrelationId\CorrelationIdProvider;
use SomeWork\CorrelationId\Logger\CorrelationIdLogger;
use Stringable;

final class CorrelationIdLoggerTest extends TestCase
{
    public function testAllMethodsAttachCorrelationId(): void
    {
        $logger = new class implements LoggerInterface {
            public array $received = [];
            public function emergency(Stringable|string $message, array $context = []): void { $this->log('emergency', $message, $context); }
            public function alert(Stringable|string $message, array $context = []): void { $this->log('alert', $message, $context); }
            public function critical(Stringable|string $message, array $context = []): void { $this->log('critical', $message, $context); }
            public function error(Stringable|string $message, array $context = []): void { $this->log('error', $message, $context); }
            public function warning(Stringable|string $message, array $context = []): void { $this->log('warning', $message, $context); }
            public function notice(Stringable|string $message, array $context = []): void { $this->log('notice', $message, $context); }
            public function info(Stringable|string $message, array $context = []): void { $this->log('info', $message, $context); }
            public function debug(Stringable|string $message, array $context = []): void { $this->log('debug', $message, $context); }
            public function log($level, Stringable|string $message, array $context = []): void { $this->received[] = ['level' => $level, 'context' => $context]; }
        };

        $decorated = new CorrelationIdLogger($logger);
        CorrelationIdProvider::set('foo');

        $decorated->emergency('a');
        $decorated->alert('a');
        $decorated->critical('a');
        $decorated->error('a');
        $decorated->warning('a');
        $decorated->notice('a');
        $decorated->info('a');
        $decorated->debug('a');
        $decorated->log('custom', 'a');

        self::assertCount(9, $logger->received);
        foreach ($logger->received as $entry) {
            self::assertSame('foo', $entry['context']['correlation_id']);
        }
    }
}
