<?php

namespace DNA\HttpClient;

class HeaderBuilder
{
    /**
     * @var string[]
     */
    private $headers;

    /**
     * HeaderBuilder constructor.
     * @param string $licenseKey
     * @param string $apiVersion
     * @param string $pluginVersion
     */
    public function __construct($licenseKey, $apiVersion, $pluginVersion)
    {
        $this->add($this->buildFromData('Authorization', 'Token '.$licenseKey));
        $this->add($this->buildFromData('Accept-User-Agent', $apiVersion));
        $this->add($this->buildFromData('Accept-User-Agent-Version', $pluginVersion));
    }

    /**
     * @param string $header
     */
    public function add($header): void
    {
        $this->headers[] = $header;
    }

    /**
     * @return string[]
     */
    public function build(): array
    {
        return $this->headers;
    }

    /**
     * @param string $key
     * @param string $value
     * @return string
     */
    private function buildFromData($key, $value): string
    {
        return "{$key}: {$value}";
    }
}
