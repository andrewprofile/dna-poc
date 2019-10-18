<?php

declare(strict_types=1);

namespace DNA\HttpClient\Provider;

interface Provider
{
    public const VERSION = '1.0.0';

    public function request(string $method, string $uri, array $options): array;
}