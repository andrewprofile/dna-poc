<?php

namespace DNA\MicroKernel\Util;

class Util
{
    public static function deCamelize(string $value): string
    {
        return strtolower(preg_replace(['/([a-z\d])([A-Z])/', '/([^_])([A-Z][a-z])/'], '$1_$2', $value));
    }
}
