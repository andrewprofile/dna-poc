<?php

namespace DNA\HttpClient\Exception;

use RuntimeException;

class UnseekableStreamException extends RuntimeException implements Exception
{
    public static function dueToConfiguration() : self
    {
        return new self('Stream is not seekable');
    }

    public static function dueToMissingResource() : self
    {
        return new self('No resource available; cannot seek position');
    }

    public static function dueToPhpError() : self
    {
        return new self('Error seeking within stream');
    }

    public static function forCallbackStream() : self
    {
        return new self('Callback streams cannot seek position');
    }
}
