<?php

namespace DNA\HttpClient\Provider;

interface Provider
{
    public const VERSION = '1.0.0';

    /**
     * @param string $method
     * @param string $uri
     * @param array $options
     * @return array
     */
    public function request($method, $uri, $options): array;
}