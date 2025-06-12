<?php

declare(strict_types=1);

namespace SomeWork\CorrelationId\Tests;

use PHPUnit\Framework\TestCase;
use SomeWork\CorrelationId\CorrelationIdProvider;

final class CorrelationIdProviderTest extends TestCase
{
    public function testClearResetsId(): void
    {
        CorrelationIdProvider::set('foo');
        self::assertSame('foo', CorrelationIdProvider::get());
        CorrelationIdProvider::clear();
        self::assertSame('', CorrelationIdProvider::get());
    }

    public function testIsolationBetweenFibers(): void
    {
        $results = [];

        $fiber1 = new \Fiber(function () use (&$results): void {
            CorrelationIdProvider::set('first');
            $results[] = CorrelationIdProvider::get();
            \Fiber::suspend();
            $results[] = CorrelationIdProvider::get();
            CorrelationIdProvider::clear();
        });

        $fiber2 = new \Fiber(function () use (&$results): void {
            CorrelationIdProvider::set('second');
            $results[] = CorrelationIdProvider::get();
            \Fiber::suspend();
            $results[] = CorrelationIdProvider::get();
            CorrelationIdProvider::clear();
        });

        $fiber1->start();
        $fiber2->start();

        $fiber1->resume();
        $fiber2->resume();

        self::assertSame(['first', 'second', 'first', 'second'], $results);
        self::assertSame('', CorrelationIdProvider::get());
    }
}
