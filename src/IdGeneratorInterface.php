<?php

declare(strict_types=1);

namespace SomeWork\CorrelationId;

interface IdGeneratorInterface
{
    public function generate(): string;
}
