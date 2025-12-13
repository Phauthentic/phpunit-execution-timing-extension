<?php

declare(strict_types=1);

namespace Phauthentic\PHPUnit\ExecutionTiming;

final class Test
{
    public function __construct(
        public readonly string $name,
        public readonly float $time
    ) {
    }
}