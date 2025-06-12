<?php

declare(strict_types=1);

namespace SomeWork\CorrelationId;

use SomeWork\CorrelationId\Storage\CorrelationIdStorageInterface;
use SomeWork\CorrelationId\Storage\FiberLocalStorage;

final class CorrelationIdProvider
{
    private static ?CorrelationIdStorageInterface $storage = null;

    public static function set(string $id): void
    {
        self::getStorage()->set($id);
    }

    public static function get(): string
    {
        return self::getStorage()->get();
    }

    public static function clear(): void
    {
        self::getStorage()->clear();
    }

    public static function setStorage(CorrelationIdStorageInterface $storage): void
    {
        self::$storage = $storage;
    }

    private static function getStorage(): CorrelationIdStorageInterface
    {
        if (self::$storage === null) {
            self::$storage = new FiberLocalStorage();
        }

        return self::$storage;
    }
}
