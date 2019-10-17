<?php

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
    public function __construct($licenseKey, $apiVersion, $pluginVersion, $apiUrl = 'https://web.api/')
    {
        $this->provider = new CurlProvider();
        $this->licenseKey = $licenseKey;
        $this->apiVersion = $apiVersion;
        $this->pluginVersion = $pluginVersion;
        $this->apiUrl = $apiUrl;
        $this->connect('plugins/versions/latest');
    }

    /**
     * @return string
     */
    public function getApiVersion(): string
    {
        return $this->apiVersion;
    }

    /**
     * @return string
     */
    public function getPluginVersion(): string
    {
        return $this->pluginVersion;
    }

    /**
     * @return string
     */
    public function getRemoteVersion(): string
    {
        return $this->remoteVersion;
    }

    /**
     * @return bool
     */
    public function isConnected(): bool
    {
        return $this->isConnected;
    }

    /**
     * Checks if the current plugin is the newest version
     *
     * @return bool
     */
    public function isNewestVersion(): bool
    {
        return version_compare($this->pluginVersion, $this->remoteVersion) !== -1;
    }

    /**
     * @param string     $method
     * @param string     $uri
     * @param null|array $options
     * @return array
     */
    public function request($method, $uri, $options = null): array
    {
        if (!$this->isConnected()) {
            return [];
        }

        return $this->buildRequest($method, $uri, $options);
    }

    /**
     * @param string     $uri
     * @param null|array $options
     * @return array
     */
    public function get($uri, $options = null): array
    {
        return $this->request(RequestMethod::GET, $uri, $options);
    }

    /**
     * @param string     $uri
     * @param null|array $options
     * @return array
     */
    public function post($uri, $options = null): array
    {
        return $this->request(RequestMethod::POST, $uri, $options);
    }

    /**
     * @param string     $method
     * @param string     $uri
     * @param null|array $options
     * @return array
     */
    private function buildRequest($method, $uri, $options = null): array
    {
        $uri = "{$uri}.json";

        $headerBuilder = new HeaderBuilder($this->licenseKey, $this->apiVersion, $this->pluginVersion);
        $this->provider->headers($headerBuilder->build());

        $uriBuilder = new UriBuilder($this->apiUrl, $uri);

        return $this->provider->request($method, $uriBuilder->build(), $options);
    }

    /**
     * @param string $uri
     */
    private function connect($uri): void
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
