<?php

declare(strict_types=1);

namespace DNA\HttpClient\Provider\Exception;

use Exception;

class ProviderException extends Exception
{
    public static function notLoaded(string $provider): ProviderException
    {
        return new self(sprintf('%s extension is not loaded', $provider));
    }
}