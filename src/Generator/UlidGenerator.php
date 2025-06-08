<?php

declare(strict_types=1);

namespace SomeWork\CorrelationId\Generator;

use SomeWork\CorrelationId\IdGeneratorInterface;
use Symfony\Component\Uid\Ulid;

final class UlidGenerator implements IdGeneratorInterface
{
    public function generate(): string
    {
        return (string) new Ulid();
    }
}
