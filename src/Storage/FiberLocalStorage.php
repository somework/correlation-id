<?php

declare(strict_types=1);

namespace SomeWork\CorrelationId\Storage;

use Fiber;
use WeakMap;

final class FiberLocalStorage implements CorrelationIdStorageInterface
{
    /** @var WeakMap<object, string> */
    private WeakMap $fiberStorage;

    private string $syncStorage = '';

    public function __construct()
    {
        $this->fiberStorage = new WeakMap();
    }

    public function set(string $id): void
    {
        $fiber = Fiber::getCurrent();
        if ($fiber === null) {
            $this->syncStorage = $id;
            return;
        }
        $this->fiberStorage[$fiber] = $id;
    }

    public function get(): string
    {
        $fiber = Fiber::getCurrent();
        if ($fiber === null) {
            return $this->syncStorage;
        }
        return $this->fiberStorage[$fiber] ?? '';
    }

    public function clear(): void
    {
        $fiber = Fiber::getCurrent();
        if ($fiber === null) {
            $this->syncStorage = '';
            return;
        }
        unset($this->fiberStorage[$fiber]);
    }
}
