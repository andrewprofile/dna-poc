<?php

declare(strict_types=1);

namespace DNA\HttpClient;

use DNA\HttpClient\Provider\Curl\CurlProvider;
use DNA\HttpClient\Provider\Provider;
use DNA\HttpClient\Provider\Exception\ProviderException;
use DNA\HttpClient\Request\RequestMethod;
use DNA\HttpClient\Uri\UriBuilder;

class Client
{
    /**
     * @var string
     */
    protected $licenseKey;

    /**
     * @var string
     */
    protected $apiVersion;

    /**
     * @var string
     */
    protected $pluginVersion;

    /**
     * @var string
     */
    private $remoteVersion;

    /**
     * @var string
     */
    private $apiUrl;

    /**
     * @var bool
     */
    private $isConnected = false;

    /**
     * @var Provider
     */
    private $provider;

    /**
     * Client constructor.
     *
     * @param string $licenseKey
     * @param string $apiVersion
     * @param string $pluginVersion
     * @param string $apiUrl
     *
     * @throws ProviderException
     */
    public function __construct(
        string $licenseKey,
        string $apiVersion,
        string $pluginVersion,
        string $apiUrl = 'https://web.api/'
    ) {
        $this->provider = new CurlProvider();
        $this->licenseKey = $licenseKey;
        $this->apiVersion = $apiVersion;
        $this->pluginVersion = $pluginVersion;
        $this->apiUrl = $apiUrl;
        $this->connect('plugins/versions/latest');
    }

    public function getApiVersion(): string
    {
        return $this->apiVersion;
    }

    public function getPluginVersion(): string
    {
        return $this->pluginVersion;
    }

    public function getRemoteVersion(): string
    {
        return $this->remoteVersion;
    }

    public function isConnected(): bool
    {
        return $this->isConnected;
    }

    public function isNewestVersion(): bool
    {
        return version_compare($this->pluginVersion, $this->remoteVersion) !== -1;
    }

    public function request(string $method, string $uri, ?array $options = null): array
    {
        if (!$this->isConnected()) {
            return [];
        }

        return $this->buildRequest($method, $uri, $options);
    }

    public function get(string $uri, ?array $options = null): array
    {
        return $this->request(RequestMethod::GET, $uri, $options);
    }

    public function post(string $uri, ?array $options = null): array
    {
        return $this->request(RequestMethod::POST, $uri, $options);
    }

    private function buildRequest(string $method, string $uri, ?array $options = null): array
    {
        $uri = "{$uri}.json";

        $headerBuilder = new HeaderBuilder($this->licenseKey, $this->apiVersion, $this->pluginVersion);
        $this->provider->headers($headerBuilder->build());

        $uriBuilder = new UriBuilder($this->apiUrl, $uri);

        return $this->provider->request($method, $uriBuilder->build(), $options);
    }

    private function connect(string $uri): void
    {
        $response = $this->get($uri);
        if (!empty($response['version'])) {
            $this->remoteVersion = $response['version'];
            $this->isConnected = true;
            return;
        }

        $this->isConnected = false;
    }
}
