<?php

declare(strict_types=1);

namespace SomeWork\CorrelationId\Storage;

interface CorrelationIdStorageInterface
{
    public function set(string $id): void;
    public function get(): string;
    public function clear(): void;
}
