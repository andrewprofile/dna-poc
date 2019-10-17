<?php

namespace DNA\HttpClient\Provider\Exception;

use Exception;

class ProviderException extends Exception
{
    /**
     * @param string $provider
     * @return ProviderException
     */
    public static function notLoaded($provider): ProviderException
    {
        return new self(sprintf('%s extension is not loaded', $provider));
    }
}