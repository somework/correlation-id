<?php

declare(strict_types=1);

namespace SomeWork\CorrelationId\Storage;

use Fiber;

final class FiberLocalStorage implements CorrelationIdStorageInterface
{
    /** @var array<int, string> */
    private array $fiberStorage = [];

    private string $syncStorage = '';

    public function set(string $id): void
    {
        $fiber = Fiber::getCurrent();
        if ($fiber === null) {
            $this->syncStorage = $id;
            return;
        }
        $this->fiberStorage[spl_object_id($fiber)] = $id;
    }

    public function get(): string
    {
        $fiber = Fiber::getCurrent();
        if ($fiber === null) {
            return $this->syncStorage;
        }
        return $this->fiberStorage[spl_object_id($fiber)] ?? '';
    }

    public function clear(): void
    {
        $fiber = Fiber::getCurrent();
        if ($fiber === null) {
            $this->syncStorage = '';
            return;
        }
        unset($this->fiberStorage[spl_object_id($fiber)]);
    }
}
