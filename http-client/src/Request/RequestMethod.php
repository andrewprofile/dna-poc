<?php

declare(strict_types=1);

namespace DNA\HttpClient\Request;

abstract class RequestMethod
{
    public const GET = 'GET';
    public const POST = 'POST';
    public const PATCH = 'PATCH';
}