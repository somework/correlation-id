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
}
