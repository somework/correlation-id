<?php

declare(strict_types=1);

namespace SomeWork\CorrelationId;

final class CorrelationIdProvider
{
    private static string $correlationId = '';

    public static function set(string $id): void
    {
        self::$correlationId = $id;
    }

    public static function get(): string
    {
        return self::$correlationId;
    }

    public static function clear(): void
    {
        self::$correlationId = '';
    }
}
