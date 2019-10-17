<?php

namespace DNA\HttpClient\Provider\Curl;

use DNA\HttpClient\Provider\Exception\ProviderException;
use DNA\HttpClient\Provider\Provider;
use DNA\HttpClient\Request\RequestMethod;
use DNA\HttpClient\Response\JsonResponse;

class CurlProvider implements Provider
{
    /**
     * @var resource
     */
    private $handle;

    /**
     * CurlProvider constructor.
     * @throws ProviderException
     */
    public function __construct()
    {
        if (!self::isAvailable()) {
            throw ProviderException::notLoaded('Curl');
        }

        $this->handle = curl_init();
    }

    public function __destruct()
    {
        curl_close($this->handle);
    }

    public function __clone()
    {
        $this->handle = curl_copy_handle($this->handle);
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array $options
     * @return array
     */
    public function request($method, $uri, $options): array
    {
        curl_setopt($this->handle, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($this->handle, CURLOPT_URL, $uri);
        curl_setopt($this->handle, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($this->handle, CURLOPT_TIMEOUT, 20);
        curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, 1);
        if ($method === RequestMethod::POST) {
            $params = isset($options['body']) ? http_build_query($options['body']) : [];
            $params['_method'] = !isset($options['forcePost']) ? 'PATCH' : 'POST';
            curl_setopt($this->handle, CURLOPT_POST, count($params));
            curl_setopt($this->handle, CURLOPT_POSTFIELDS, $params);
        }

        $response = curl_exec($this->handle);

        return (new JsonResponse($response))->getPayload();
    }

    /**
     * @param array $headers
     */
    public function headers($headers): void
    {
        curl_setopt($this->handle, CURLOPT_HTTPHEADER, $headers);
    }

    /**
     * @return bool
     */
    private static function isAvailable(): bool
    {
        return extension_loaded('curl');
    }
}