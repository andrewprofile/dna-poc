<?php

namespace DNA\HttpClient\Uri;

class UriBuilder
{
    /**
     * @var string
     */
    private $baseUri;

    /**
     * @var string
     */
    private $uri;

    public function __construct(string $baseUri, string $uri)
    {
        $this->baseUri = $baseUri;
        $this->uri = $uri;
    }

    /**
     * @return string
     */
    public function build(): string
    {
        return $this->baseUri . $this->uri;
    }
}