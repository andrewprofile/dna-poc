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

    /**
     * UriBuilder constructor.
     * @param string $baseUri
     * @param string $uri
     */
    public function __construct($baseUri, $uri)
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