<?php

declare(strict_types=1);

namespace DNA\MicroKernel\Util;

class Util
{
    public function deCamelize(string $value): string
    {
        return strtolower(preg_replace(['/([a-z\d])([A-Z])/', '/([^_])([A-Z][a-z])/'], '$1_$2', $value));
    }
}
