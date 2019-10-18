<?php

declare(strict_types=1);

namespace DNA\HttpClient;

class HeaderBuilder
{
    /**
     * @var string[]
     */
    private $headers;

    public function __construct(string $licenseKey, string $apiVersion, string $pluginVersion)
    {
        $this->add($this->buildFromData('Authorization', 'Token '.$licenseKey));
        $this->add($this->buildFromData('Accept-User-Agent', $apiVersion));
        $this->add($this->buildFromData('Accept-User-Agent-Version', $pluginVersion));
    }

    public function add(string $header): void
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

    private function buildFromData(string $key, string $value): string
    {
        return "{$key}: {$value}";
    }
}
